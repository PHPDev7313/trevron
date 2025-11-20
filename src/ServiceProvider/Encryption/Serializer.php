<?php

namespace JDS\ServiceProvider\Encryption;

final class Serializer
{
    public static function toStoredString(CipherPackage $p): string
    {
        $obj = [
            'v' => $p->version,
            'alg' => $p->alg,
            'nonce' => base64_encode($p->nonce),
            'ct' => base64_encode($p->ciphertext)
        ];
        if ($p->aad !== null) {
            $obj['aad'] = base64_encode($p->aad);
        }
        return json_encode($obj);
    }

    public static function fromStoredString(string $stored): CipherPackage
    {
        $data = json_decode($stored, true);
        if (!is_array($data) || !isset($data['alg'], $data['nonce'], $data['ct'])) {
            throw new CipherRuntimeException('Invalid Cipher package');
        }
        $aad = $data['aad'] ?? null;
        return new CipherPackage(
            $data['alg'],
            base64_decode($data['nonce']),
            base64_decode($data['ct']),
            $aad ? base64_decode($aad) : null
        );
    }
}

