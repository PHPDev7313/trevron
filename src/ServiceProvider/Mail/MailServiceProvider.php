<?php

namespace JDS\ServiceProvider\Mail;

use JDS\ServiceProvider\ServiceProviderInterface;
use League\Container\Argument\Literal\BooleanArgument;
use League\Container\Container;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailServiceProvider implements ServiceProviderInterface
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        $this->container->add('mailer', PHPMailer::class)
            ->addArgument(new BooleanArgument(true)
        );

        $this->container->add('smtp', Smtp::class);

        $this->container->add('mail', MailService::class)
            ->addArguments([
                $this->container->get('mailer'),
                $this->container->get('config'),
                $this->container->get('smtp'),
            ]
        );
    }
}

