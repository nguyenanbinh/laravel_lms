<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method reads the SMTP settings from the database and sets them as the
     * configuration for the mail system.
     *
     * @return void
     */
    public function register(): void
    {
        // Check if the 'smtp_settings' table exists
        if (Schema::hasTable('smtp_settings')) {
            // Retrieve the first SMTP setting from the database
            $smtpSetting = SmtpSetting::first();

            // If SMTP settings exist, set them as the configuration for the mail system
            if ($smtpSetting) {
                $data = [
                    // Set the mail driver
                    'driver' => $smtpSetting->mailer,
                    // Set the SMTP host
                    'host' => $smtpSetting->host,
                    // Set the SMTP port
                    'port' => $smtpSetting->port,
                    // Set the SMTP username
                    'username' => $smtpSetting->username,
                    // Set the SMTP password
                    'password' => $smtpSetting->password,
                    // Set the SMTP encryption
                    'encryption' => $smtpSetting->encryption,
                    // Set the sender's email address and name
                    'from' => [
                        'address' => $smtpSetting->from_address,
                        'name' => 'AducaCourses'
                    ]
                ];

                // Set the SMTP settings as the configuration for the mail system
                Config::set('mail', $data);
            }
        } // end if
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
