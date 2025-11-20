<?php

namespace JDS\Http\Middleware\Services;

class ChecksumService implements ChecksumInterface
{
    public function generate(object $entity): string
    {
        // convert the object into a normalized array
        // exclude checksum fields or other fields you don't want included
        $data = $this->normalize($entity);

        // encode deterministically to avoid hash drift
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return hash('sha256', $json);
    }

    public function normalize(object $entity): array
    {
        // use get object vars or reflection depending on your framework
        $vars = get_object_vars($entity);

        // remove fields that should not affect checksum
        unset($vars['checksum']);

        // sort keys so ordering never affects checksum
        ksort($vars);
        return $vars;
    }
}