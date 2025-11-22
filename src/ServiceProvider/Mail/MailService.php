<?php

namespace JDS\ServiceProvider\Mail;

use JDS\Configuration\Config;
use JDS\Processing\ErrorProcessor;
use League\Container\Container;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


class MailService
{
    private Config $config;
    public function __construct(
        private PHPMailer $mail,
        private Container $container,
        private SMTP $smtp
    )
    {
        $this->config = $this->container->get('config');
        $this->setup();
    }

    private function setup(): void
    {
        $this->mail->isSMTP();
        $this->mail->Host = $this->config->get('mailHost');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->config->get('mailUser');
        $this->mail->Password = $this->config->get('mailPass');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = $this->config->get('mailPort');
        // From
        $this->mail->setFrom(
            $this->config->get('mailFrom'),
            $this->config->get('mailFromName')
        );
    }

    public function send(array $options): bool
    {
        try {
            $this->mail->clearAllRecipients();
            $this->mail->clearAttachments();
            $this->mail->addAddress(
                $options['to'],
                $options['to_name'] ?? ''
            );

            if (!empty($options['cc'])) {
                foreach ($options['cc'] as $cc) {
                    $this->mail->addCC($cc);
                }
            }

            if (!empty($options['bcc'])) {
                foreach ($options['bcc'] as $bcc) {
                    $this->mail->addBCC($bcc);
                }
            }

            if (!empty($options['attachments'])) {
                foreach ($options['attachments'] as $file) {
                    $this->mail->addAttachment($file);
                }
            }
            return $this->mail->send();
        } catch (MailException $me) {
            ErrorProcessor::process(
                $me,
                1150,
                "MailService Error: {$this->mail->ErrorInfo} :: Exception: {$me->getMessage()}",
                'error'
            );
            return false;
        }
    }
}

