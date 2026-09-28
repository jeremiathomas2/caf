<?php

namespace Database\Seeders;

use App\Models\Integration;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Operational settings and third-party integrations for the admin Settings
 * screen.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['general', 'festival.name', 'Culture Acapella Festival', 'string', 'Festival name', 'Shown in the public header and on invoices.'],
            ['general', 'festival.tagline', 'Where every voice finds its harmony', 'string', 'Tagline', 'Short line used under the festival name.'],
            ['general', 'festival.email', 'hello@cultureacapellafestival.com', 'string', 'Contact email', 'Public contact address.'],
            ['general', 'festival.phone', '+255 754 000 000', 'string', 'Contact phone', 'Public contact number.'],
            ['general', 'festival.address', 'Kigamboni Cultural Centre, Arusha, Tanzania', 'string', 'Address', 'Office address shown in the footer.'],

            ['registration', 'registration.opens_days_before', '180', 'integer', 'Open window', 'How many days before launch registration is announced.'],
            ['registration', 'registration.closes_days_after', '30', 'integer', 'Close window', 'How many days before the festival registration closes.'],
            ['registration', 'registration.require_members', 'true', 'boolean', 'Require member list', 'Require every member to be named at registration.'],
            ['registration', 'registration.allow_partial', 'true', 'boolean', 'Allow part payments', 'Let groups pay a deposit to hold a place.'],

            ['finance', 'finance.default_currency', 'TZS', 'string', 'Default currency', 'Applied to new seasons and invoices.'],
            ['finance', 'finance.rounding_increment', '100', 'integer', 'Rounding increment', 'Amounts are rounded to the nearest multiple of this.'],
            ['finance', 'finance.bank_details', 'CRDB Bank, Arusha Branch. Account name: Culture Acapella Festival.', 'string', 'Bank details', 'Printed on every invoice.'],
            ['finance', 'finance.mobile_money', 'M-Pesa till 5261890', 'string', 'Mobile money', 'Printed on every invoice.'],

            ['notifications', 'notifications.email_on_submission', 'true', 'boolean', 'Notify on submission', 'Email the applicant when a registration is received.'],
            ['notifications', 'notifications.email_on_status_change', 'true', 'boolean', 'Notify on status change', 'Email the applicant when a status changes.'],
            ['notifications', 'notifications.staff_notify_new', 'true', 'boolean', 'Notify staff', 'Alert the registration office about new applications.'],

            ['security', 'security.force_2fa_staff', 'true', 'boolean', 'Require 2FA for staff', 'Require two-factor authentication for all admin accounts.'],
            ['security', 'security.session_lifetime', '120', 'integer', 'Session lifetime', 'Minutes before an idle admin session expires.'],
        ];

        foreach ($settings as $order => [$group, $key, $value, $type, $label, $hint]) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'value' => $value,
                    'type' => $type,
                    'label' => $label,
                    'hint' => $hint,
                    'sort_order' => $order,
                ],
            );
        }

        $integrations = [
            ['mpesa', 'M-Pesa', 'Mobile money payments for Tanzanian applicants.', 'MP', 'from-mpesa', true],
            ['email', 'SMTP / Mail', 'Transactional email for applicant notifications.', 'EM', 'from-slate', true],
            ['storage', 'File storage', 'Uploads for logos, gallery and documents.', 'FS', 'from-indigo', true],
            ['sms', 'SMS provider', 'Status updates by SMS for applicants who opt in.', 'SM', 'from-emerald', false],
            ['analytics', 'Analytics', 'Aggregate page view reporting for the public site.', 'AN', 'from-amber', false],
        ];

        foreach ($integrations as $order => [$key, $name, $description, $initials, $gradient, $enabled]) {
            Integration::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name' => $name,
                    'description' => $description,
                    'initials' => $initials,
                    'gradient' => $gradient,
                    'is_enabled' => $enabled,
                    'last_checked_at' => now()->subHours($order + 1),
                    'last_success_at' => $enabled ? now()->subHours($order + 1) : null,
                    'sort_order' => $order,
                ],
            );
        }
    }
}
