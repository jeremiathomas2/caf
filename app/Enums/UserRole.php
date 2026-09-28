<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Administrator = 'administrator';
    case SeasonManager = 'season_manager';
    case RegistrationOfficer = 'registration_officer';
    case FloorManager = 'floor_manager';
    case FinanceOfficer = 'finance_officer';
    case CommunicationsOfficer = 'communications_officer';
    case ContentEditor = 'content_editor';
    case Judge = 'judge';
    case Auditor = 'auditor';
    case GroupLeader = 'group_leader';

    /**
     * Human label shown in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Administrator => 'Administrator',
            self::SeasonManager => 'Season Manager',
            self::RegistrationOfficer => 'Registration Officer',
            self::FloorManager => 'Floor Manager',
            self::FinanceOfficer => 'Finance Officer',
            self::CommunicationsOfficer => 'Communications Officer',
            self::ContentEditor => 'Content Editor',
            self::Judge => 'Judge / Reviewer',
            self::Auditor => 'Auditor',
            self::GroupLeader => 'Group Leader',
        };
    }

    /**
     * Short description shown on the settings → roles panel.
     */
    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Full control incl. settings, roles, integrations',
            self::Administrator => 'Full operational control, without settings or roles',
            self::SeasonManager => 'Manages one or more seasons end to end',
            self::RegistrationOfficer => 'Processes registrations and documents',
            self::FloorManager => 'On-the-day floor, check-in and stage operations',
            self::FinanceOfficer => 'Money in, money out, reconciliation',
            self::CommunicationsOfficer => 'Campaigns and the unified inbox',
            self::ContentEditor => 'Pages, news, programme, gallery, sponsors',
            self::Judge => 'Scores assigned applications only',
            self::Auditor => 'Read-only plus audit log access',
            self::GroupLeader => 'Own registration and payment',
        };
    }

    /**
     * Relative access level badge shown on the settings → roles panel.
     */
    public function accessLevel(): string
    {
        return match ($this) {
            self::SuperAdmin, self::Administrator => 'Highest',
            self::SeasonManager, self::FinanceOfficer => 'High',
            self::RegistrationOfficer, self::CommunicationsOfficer, self::ContentEditor, self::FloorManager => 'Medium',
            self::Judge => 'Low — isolated',
            self::Auditor => 'Read-only',
            self::GroupLeader => 'Own data only',
        };
    }

    /**
     * Permission keys granted to this role.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => self::allPermissions(),

            // Everything the system can do day to day, minus the keys that
            // hand out roles, change settings, or wire up integrations.
            self::Administrator => array_values(array_diff(
                self::allPermissions(),
                ['settings.manage'],
            )),

            self::SeasonManager => [
                'admin.access', 'dashboard.view',
                'registrations.view', 'registrations.manage', 'registrations.export',
                'judging.view', 'judging.manage',
                'payments.view', 'payments.manage', 'payments.export',
                'communications.view', 'communications.manage',
                'analytics.view', 'audit.view',
                'content.view', 'content.manage',
                'portal.view', 'settings.view', 'seasons.manage', 'teams.view',
            ],
            self::RegistrationOfficer => [
                'admin.access', 'dashboard.view', 'seasons.view',
                'registrations.view', 'registrations.manage', 'registrations.export',
                'judging.view', 'payments.view',
                'communications.view', 'communications.manage',
                'portal.view', 'teams.view',
            ],

            // Check-in and stage-side work. Sees registrations and the group
            // contact list, but never money or scoring.
            self::FloorManager => [
                'admin.access', 'dashboard.view', 'seasons.view',
                'registrations.view', 'registrations.manage', 'registrations.export',
                'communications.view', 'communications.manage',
                'portal.view', 'teams.view',
            ],

            self::FinanceOfficer => [
                'admin.access', 'dashboard.view', 'seasons.view',
                'registrations.view',
                'payments.view', 'payments.manage', 'payments.export',
                'analytics.view', 'audit.view',
            ],
            self::CommunicationsOfficer => [
                'admin.access', 'dashboard.view', 'seasons.view',
                'registrations.view',
                'communications.view', 'communications.manage',
                'analytics.view',
            ],
            self::ContentEditor => [
                'admin.access', 'dashboard.view', 'seasons.view',
                'content.view', 'content.manage', 'portal.view',
            ],
            self::Judge => ['admin.access', 'dashboard.view', 'seasons.view', 'judging.view', 'judging.score'],
            self::Auditor => [
                'admin.access', 'dashboard.view', 'seasons.view', 'registrations.view',
                'payments.view', 'judging.view', 'analytics.view',
                'audit.view', 'content.view', 'portal.view', 'settings.view', 'teams.view',
            ],
            self::GroupLeader => [],
        };
    }

    /**
     * Roles permitted to reach the management system at all.
     *
     * @return list<string>
     */
    public static function adminRoles(): array
    {
        return [
            self::SuperAdmin->value,
            self::Administrator->value,
            self::SeasonManager->value,
            self::RegistrationOfficer->value,
            self::FloorManager->value,
            self::FinanceOfficer->value,
            self::CommunicationsOfficer->value,
            self::ContentEditor->value,
            self::Judge->value,
            self::Auditor->value,
        ];
    }

    /**
     * Roles that may be assigned registrations to review.
     *
     * @return list<string>
     */
    public static function reviewerRoles(): array
    {
        return [
            self::SuperAdmin->value,
            self::Administrator->value,
            self::SeasonManager->value,
            self::RegistrationOfficer->value,
            self::Judge->value,
        ];
    }

    /**
     * Every permission key the system knows about.
     *
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        return [
            'admin.access',
            'dashboard.view',
            'seasons.view', 'seasons.manage',
            'registrations.view', 'registrations.manage', 'registrations.export',
            'judging.view', 'judging.manage', 'judging.score',
            'payments.view', 'payments.manage', 'payments.export',
            'communications.view', 'communications.manage',
            'analytics.view',
            'audit.view',
            'content.view', 'content.manage',
            'portal.view',
            'settings.view', 'settings.manage',
            'teams.view', 'teams.manage',
        ];
    }
}
