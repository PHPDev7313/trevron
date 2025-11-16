<?php

namespace JDS\Http\Middleware\Services;


use JDS\Configuration\Config;
use JDS\Processing\ErrorProcessor;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;


class MailService
{


    public function __construct(
        private PHPMailer $mail,
        private Config $config,
    )
    {
        $this->setup();
    }

    public function setup(): void
    {
        $this->mail->isSMTP();
        $this->mail->Host           = $this->config->get('mailHost');
        $this->mail->SMTPAuth       = true;
        $this->mail->Username       = $this->config->get('mailUser');
        $this->mail->Password       = $this->config->get('mailPass');
        $this->mail->SMTPSecure     = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port           = $this->config->get('mailPort');
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

            $this->mail->Subject = $options['subject'];
            $this->mail->Body = $options['body'];
            $this->mail->isHtml($options['is_html'] ?? true);
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
        } catch (Exception $e) {
            ErrorProcessor::process(
                $e,
                1150,
                "MailService Error: {$this->mail->ErrorInfo} :: Exception: {$e->getMessage()}",
                'error'
            );
            return false;
        }
    }
}