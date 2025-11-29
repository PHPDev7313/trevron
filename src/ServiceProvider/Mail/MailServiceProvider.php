<?php

namespace JDS\ServiceProvider\Mail;


use League\Container\Argument\Literal\BooleanArgument;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\ServiceProviderInterface;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{

    protected array $provides = [
        PHPMailer::class,
        SMTP::class,
        Exception::class,
        MailService::class
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $this->container->add(PHPMailer::class)
            ->addArgument(new BooleanArgument(true)
        );

        $this->container->add(SMTP::class);

        $this->container->add(Exception::class);

        $this->container->add(MailService::class)
            ->addArguments([
                $this->container->get(PHPMailer::class),
                $this->container,
                $this->container->get(SMTP::class),
            ]
        );
    }
}

