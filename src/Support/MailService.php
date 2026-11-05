<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Mailer;
use App\Models\User;

final class MailService
{
    public static function sendVerification(array $user, string $token): void
    {
        $link = email_url('/verificar-email?token=' . urlencode($token));
        $html = Mailer::render('emails/verify', [
            'subject' => t('auth.verify_email_subject'),
            'preheader' => t('auth.verify_email_intro'),
            'user' => $user,
            'link' => $link,
        ]);
        Mailer::send($user['email'], t('auth.verify_email_subject'), $html);
    }

    public static function sendWelcomeCompany(array $company, array $user): void
    {
        $html = Mailer::render('emails/company-received', [
            'subject' => t('mail.company_received_subject'),
            'preheader' => t('auth.company_pending_text'),
            'company' => $company,
            'user' => $user,
        ]);
        Mailer::send($user['email'], t('mail.company_received_subject'), $html);
    }

    public static function sendCompanyApproved(array $company, string $email): void
    {
        $html = Mailer::render('emails/company-approved', [
            'subject' => t('mail.company_approved_subject'),
            'preheader' => t('mail.company_approved_intro'),
            'company' => $company,
            'link' => email_url('/login'),
        ]);
        Mailer::send($email, t('mail.company_approved_subject'), $html);
    }

    public static function sendOrderConfirmation(array $order, array $items): void
    {
        $link = !empty($order['user_id'])
            ? email_url('/conta/encomendas/' . $order['id'])
            : email_url('/checkout/sucesso/' . $order['order_number']);
        $html = Mailer::render('emails/order', [
            'subject' => t('order.email_subject', ['number' => $order['order_number']]),
            'preheader' => t('order.email_intro'),
            'order' => $order,
            'items' => $items,
            'link' => $link,
            'isGuest' => empty($order['user_id']),
        ]);
        Mailer::send($order['email'], t('order.email_subject', ['number' => $order['order_number']]), $html);
    }
}
