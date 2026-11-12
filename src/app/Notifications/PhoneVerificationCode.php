<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PhoneVerificationCode extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly int $expiresInMinutes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if (app()->getLocale() === 'ar') {
            return (new MailMessage)
                ->subject('رمز إكمال خطوة تأكيد رقم الهاتف - HAG Brokers')
                ->greeting('مرحباً،')
                ->line('استخدم الرمز التالي لإكمال خطوة تأكيد رقم الهاتف في حسابك:')
                ->line('**'.$this->code.'**')
                ->line('صلاحية الرمز '.$this->expiresInMinutes.' دقائق.')
                ->line('تنبيه: إرسال هذا الرمز إلى بريدك الإلكتروني لا يثبت ملكيتك لرقم الهاتف المدخل.')
                ->line('إذا لم تطلب هذا الرمز، يمكنك تجاهل هذه الرسالة.');
        }

        return (new MailMessage)
            ->subject('Phone confirmation code - HAG Brokers')
            ->greeting('Hello,')
            ->line('Use the following code to complete the phone confirmation step on your account:')
            ->line('**'.$this->code.'**')
            ->line('This code expires in '.$this->expiresInMinutes.' minutes.')
            ->line('Important: Sending this code to your email does not prove ownership of the phone number you entered.')
            ->line('If you did not request this code, you can ignore this email.');
    }
}
