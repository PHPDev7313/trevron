<?php

declare(strict_types=1);

namespace JDS\ServiceProvider;

use JDS\Contracts\Validation\ValidatorInterface;
use JDS\Validation\ArrayRule;
use JDS\Validation\DateRule;
use JDS\Validation\EmailRule;
use JDS\Validation\EnumRule;
use JDS\Validation\JsonRule;
use JDS\Validation\LicenseRule;
use JDS\Validation\MaxRule;
use JDS\Validation\MinRule;
use JDS\Validation\NumericRule;
use JDS\Validation\RequiredRule;
use JDS\Validation\RuleRegistery;
use JDS\Validation\Validator;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\ServiceProviderInterface;

class ValidationServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected $provides = [
        RuleRegistery::class,
        ValidatorInterface::class,
        Validator::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $container = $this->getContainer();

        // Build registry with all default rules
        $registery = new RuleRegistery();
        $registery->add('required', new RequiredRule());
        $registery->add('min', new MinRule());
        $registery->add('max', new MaxRule());
        $registery->add('email', new EmailRule());
        $registery->add('json', new JsonRule());
        $registery->add('license', new LicenseRule());
        $registery->add('enum', new EnumRule());
        $registery->add('array', new ArrayRule());
        $registery->add('numeric', new NumericRule());
        $registery->add('date', new DateRule());

        $container->add(RuleRegistery::class, $registery);

        $container->add(ValidatorInterface::class, Validator::class);
    }
}