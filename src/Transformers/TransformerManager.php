<?php

namespace JDS\Transformers;

use JDS\Contracts\Http\ParamTransformerInterface;
use RuntimeException;

final class TransformerManager
{
    /** @var ParamTransformerInterface[] */
    private array $transformers = [];

    /**
     * Register a transformer.
     */
    public function addTransformer(ParamTransformerInterface $transformer): void
    {
        $this->transformers[] = $transformer;
    }

    /**
     * Check if a transformer exists for the target type.
     */
    public function supports(string $targetType): bool
    {
        foreach ($this->transformers as $t) {
            if ($t->supports($targetType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Transform the route value into the real object.
     *
     * @throws RuntimeException
     */
    public function transform(mixed $value, string $targetType): mixed
    {
        foreach ($this->transformers as $t) {
            if ($t->supports($targetType)) {
                return $t->transform($value, $targetType);
            }
        }

        throw new RuntimeException(
            "No transformer registered for type '{$targetType}'."
        );
    }
}
