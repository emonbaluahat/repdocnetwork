<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private static ?PHPMailer $mailer = null;

    private static function init(): PHPMailer
    {
        if (self::$mailer === null) {
            self::$mailer = new PHPMailer(true);

            if (APP_ENV === 'development') {
                self::$mailer->SMTPDebug = 0;
            }

            if (MAIL_HOST) {
                self::$mailer->isSMTP();
                self::$mailer->Host = MAIL_HOST;
                self::$mailer->SMTPAuth = true;
                self::$mailer->Username = MAIL_USER;
                self::$mailer->Password = MAIL_PASS;
                self::$mailer->SMTPSecure = MAIL_ENCRYPTION;
                self::$mailer->Port = MAIL_PORT;
            } else {
                self::$mailer->isMail();
            }

            self::$mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            self::$mailer->CharSet = 'UTF-8';
            self::$mailer->Encoding = 'base64';
        }

        return self::$mailer;
    }

    public static function send(string $to, string $subject, string $body, ?string $altBody = null): bool
    {
        try {
            $mailer = self::init();
            $mailer->clearAddresses();
            $mailer->addAddress($to);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->AltBody = $altBody ?: strip_tags($body);
            return $mailer->send();
        } catch (Exception $e) {
            logger('Mail send failed: ' . $e->getMessage(), 'ERROR', ['to' => $to]);
            return false;
        }
    }

    public static function sendOtp(string $to, string $otp): bool
    {
        $subject = __('mail.otp_subject');
        $body = __('mail.otp_body', ['otp' => $otp]);
        return self::send($to, $subject, $body);
    }

    public static function sendInvite(string $to, string $shopName, string $inviteUrl): bool
    {
        $subject = __('mail.invite_subject', ['shop' => $shopName]);
        $body = __('mail.invite_body', ['shop' => $shopName, 'url' => $inviteUrl]);
        return self::send($to, $subject, $body);
    }
}
