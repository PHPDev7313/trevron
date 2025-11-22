<?php

namespace JDS\ServiceProvider\Encryption;

final class Serializer
{
    public static function encode(CipherPackage $p): string
    {
        $payload = [
            'v' => $p->version,
            'alg' => $p->alg,
            'nonce' => base64_encode($p->nonce),
            'ct' => base64_encode($p->ciphertext)
        ];
        if ($p->aad !== null) {
            $payload['aad'] = base64_encode($p->aad);
        }
        return json_encode($payload);
    }

    public static function decode(string $encoded): CipherPackage
    {
        $arr = json_decode($encoded, true);
        if (!isset($arr['alg'], $arr['nonce'], $arr['ct'])) {
            throw new CipherRuntimeException('Invalid Cipher package');
        }

        return new CipherPackage(
            $arr['alg'],
            base64_decode($arr['nonce']),
            base64_decode($arr['ct']),
            isset($arr['aad']) ? base64_decode($arr['aad']) : null
        );
    }
}

