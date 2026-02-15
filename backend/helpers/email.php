<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email via SMTP using PHPMailer.
 *
 * @param string $to        Recipient email address
 * @param string $subject   Email subject line
 * @param string $htmlBody  Full HTML body
 * @param array  $options   Optional: 'cc', 'bcc', 'attachments', 'replyTo', 'smtp_email', 'smtp_password', 'from_name'
 * @return array ['success' => bool, 'message_id' => string|null, 'error' => string|null]
 */
function sendEmail($to, $subject, $htmlBody, $options = []) {
    // Per-user SMTP or global fallback
    $smtpUser = !empty($options['smtp_email']) ? $options['smtp_email'] : SMTP_USERNAME;
    $smtpPass = !empty($options['smtp_password']) ? $options['smtp_password'] : SMTP_PASSWORD;
    $fromEmail = !empty($options['smtp_email']) ? $options['smtp_email'] : SMTP_FROM_EMAIL;
    $fromName = !empty($options['from_name']) ? $options['from_name'] . ' - ' . FIRM_NAME : SMTP_FROM_NAME;

    if (empty($smtpUser) || empty($smtpPass)) {
        return [
            'success'    => false,
            'message_id' => null,
            'error'      => 'Email not configured. Please set SMTP credentials in settings or backend/config/email.php'
        ];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'tls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = SEND_TIMEOUT;

        $mail->setFrom($fromEmail, $fromName);

        if (!empty($options['replyTo'])) {
            $mail->addReplyTo($options['replyTo']);
        }

        $mail->addAddress($to);

        if (!empty($options['cc'])) {
            foreach ((array)$options['cc'] as $cc) {
                $mail->addCC($cc);
            }
        }
        if (!empty($options['bcc'])) {
            foreach ((array)$options['bcc'] as $bcc) {
                $mail->addBCC($bcc);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(
            ['<br>', '<br/>', '<br />', '</p>'],
            "\n",
            $htmlBody
        ));
        $mail->CharSet = 'UTF-8';

        if (!empty($options['attachments'])) {
            foreach ($options['attachments'] as $filePath) {
                if (file_exists($filePath)) {
                    $mail->addAttachment($filePath);
                }
            }
        }

        $mail->send();

        return [
            'success'    => true,
            'message_id' => $mail->getLastMessageID(),
            'error'      => null
        ];
    } catch (Exception $e) {
        return [
            'success'    => false,
            'message_id' => null,
            'error'      => $mail->ErrorInfo
        ];
    }
}
