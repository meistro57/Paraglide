<?php

namespace Tests\Unit;

use App\Services\Crypto\Encryptor;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EncryptorTest extends TestCase
{
    public function test_encrypt_and_decrypt_round_trip(): void
    {
        $encryptor = new Encryptor(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));

        $ciphertext = $encryptor->encrypt('hello lyra');

        $this->assertNotSame('hello lyra', $ciphertext);
        $this->assertSame('hello lyra', $encryptor->decrypt($ciphertext));
    }

    public function test_encrypt_uses_unique_nonce_for_same_plaintext(): void
    {
        $encryptor = new Encryptor(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));

        $first = $encryptor->encrypt('same text');
        $second = $encryptor->encrypt('same text');

        $this->assertNotSame($first, $second);
    }

    public function test_encrypt_and_decrypt_column_handle_null(): void
    {
        $encryptor = new Encryptor(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));

        $this->assertNull($encryptor->encryptColumn(null));
        $this->assertNull($encryptor->decryptColumn(null));
    }

    public function test_decrypt_throws_on_invalid_payload(): void
    {
        $encryptor = new Encryptor(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));

        $this->expectException(RuntimeException::class);

        $encryptor->decrypt('invalid');
    }
}
