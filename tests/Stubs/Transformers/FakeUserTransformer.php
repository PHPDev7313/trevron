<?php

namespace Tests\Stubs\Transformers;

use JDS\Contracts\Http\ParamTransformerInterface;
use JDS\Exceptions\Validation\ValidationException;

class FakeUserTransformer implements ParamTransformerInterface
{

    public function __construct(
        private FakeUserRepository $repo
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function transform(mixed $value, string $targetType): mixed
    {
        //
        // Convert to string|int (respository accepts both)
        //
        if (is_numeric($value)) {
            $id = $value + 0; // convert "5" into 5, "5.0" into 5
        } else {
            $id = (string)$value;
        }

        $user = $this->repo->findById($id);

        if (!$user) {
            throw new ValidationException("FakeUser '{$value}' not found.");
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $targetType): bool
    {
        return $targetType === FakeUser::class;
    }
}

