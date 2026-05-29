<?php

namespace App\Services;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailConfigurationService
{
    /**
     * Dynamically set the mail configuration from an SmtpSetting record.
     *
     * @param SmtpSetting $setting
     * @return void
     */
    public static function setSmtpConnection(SmtpSetting $setting): void
    {
        $config = [
            'transport' => 'smtp',
            'host' => $setting->host,
            'port' => (int) $setting->port,
            'encryption' => ($setting->encryption === 'none' || !$setting->encryption) ? null : $setting->encryption,
            'username' => $setting->username,
            'password' => $setting->password,
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ];

        // Set the custom mailer config dynamically
        Config::set('mail.mailers.dynamic_smtp', $config);

        // Also set standard global from details
        Config::set('mail.from.address', $setting->from_address);
        Config::set('mail.from.name', $setting->from_name);

        // Reset the mailer manager so that the config is re-read on resolution
        Mail::purge('dynamic_smtp');
    }
}
