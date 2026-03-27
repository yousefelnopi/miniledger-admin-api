<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetLink;

    public function __construct($resetLink)
    {
        $this->resetLink = $resetLink;
    }

    public function build()
    {
        // تم تغيير العنوان للإنجليزي
        return $this->subject('Reset Your Password - POS System 🔒')
                    ->html('<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; }
        .wrapper { padding: 40px 20px; width: 100%; box-sizing: border-box; }
        .card { max-width: 550px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #4F46E5 0%, #3B82F6 100%); padding: 30px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 26px; font-weight: bold; letter-spacing: 1px; }
        .body-content { padding: 40px 30px; text-align: center; }
        .body-content h2 { color: #1f2937; margin-top: 0; font-size: 22px; }
        .body-content p { color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 30px; }
        .btn { display: inline-block; background-color: #4F46E5; color: #ffffff !important; font-weight: bold; text-decoration: none; padding: 14px 30px; border-radius: 8px; font-size: 16px; transition: background-color 0.3s; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2); }
        .btn:hover { background-color: #4338ca; }
        .warning { color: #9ca3af; font-size: 13px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { color: #6b7280; font-size: 13px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>POS System</h1>
            </div>

            <div class="body-content">
                <h2>Password Reset Request 🔐</h2>
                <p>Hello,<br>We received a request to reset the password for your account. No need to worry, you can easily set a new password by clicking the button below.</p>

                <a href="' . $this->resetLink . '" class="btn">Reset Password Now</a>

                <div class="warning">
                    <p>If you did not request to change your password, you can safely ignore this email. Your account remains secure and nothing will be changed.</p>
                </div>
            </div>

            <div class="footer">
                <p>© ' . date('Y') . ' Pos-System Team. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>');
    }
}
