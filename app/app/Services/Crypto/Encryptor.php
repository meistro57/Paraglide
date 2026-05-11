<?php

namespace App\Services\Crypto;

use RuntimeException;

class Encryptor
{
    private const NONCE_BYTES = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

    private ?string $key;

    public function __construct(?string $key = null)
    {
        $this->key = $key;
    }

    public function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(self::NONCE_BYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->resolveKey());

        return $nonce.$ciphertext;
    }

    public function decrypt(string $ciphertext): string
    {
        if (strlen($ciphertext) < self::NONCE_BYTES) {
            throw new RuntimeException('Ciphertext payload is too short.');
        }

        $nonce = substr($ciphertext, 0, self::NONCE_BYTES);
        $payload = substr($ciphertext, self::NONCE_BYTES);

        $plaintext = sodium_crypto_secretbox_open($payload, $nonce, $this->resolveKey());

        if ($plaintext === false) {
            throw new RuntimeException('Unable to decrypt ciphertext payload.');
        }

        return $plaintext;
    }

    public function encryptColumn(?string $plaintext): ?string
    {
        if ($plaintext === null) {
            return null;
        }

        return $this->encrypt($plaintext);
    }

    public function decryptColumn(?string $ciphertext): ?string
    {
        if ($ciphertext === null) {
            return null;
        }

        return $this->decrypt($ciphertext);
    }

    private function resolveKey(): string
    {
        $key = $this->key ?? config('app.key');

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('Encryption key is not configured.');
        }

        $decoded = $this->decodeKey($key);

        if (strlen($decoded) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new RuntimeException('Encryption key must be 32 bytes.');
        }

        return $decoded;
    }

    private function decodeKey(string $key): string
    {
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            if ($decoded === false) {
                throw new RuntimeException('Encryption key base64 payload is invalid.');
            }

            return $decoded;
        }

        return $key;
    }
}
