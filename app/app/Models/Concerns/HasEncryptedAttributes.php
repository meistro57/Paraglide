<?php

namespace App\Models\Concerns;

use App\Services\Crypto\Encryptor;

trait HasEncryptedAttributes
{
    protected function encryptedAttributes(): array
    {
        return property_exists($this, 'encrypted') && is_array($this->encrypted)
            ? $this->encrypted
            : [];
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptedAttributes(), true)) {
            $value = app(Encryptor::class)->encryptColumn($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        if (! in_array($key, $this->encryptedAttributes(), true)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return $value;
        }

        return app(Encryptor::class)->decryptColumn($value);
    }
}
