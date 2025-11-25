<?php

namespace JDS\ServiceProvider\Mail;

use JDS\Contracts\ServiceProvider\ServiceProviderInterface;
use League\Container\Argument\Literal\BooleanArgument;
use League\Container\Container;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailServiceProvider implements ServiceProviderInterface
{
    public function __construct(private Container $container)
    {
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

