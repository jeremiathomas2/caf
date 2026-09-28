<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Shortlisted = 'shortlisted';
    case Approved = 'approved';
    case Waitlisted = 'waitlisted';
    case Confirmed = 'confirmed';
    case NotSelected = 'not_selected';
    case Rejected = 'rejected';
    case Disqualified = 'disqualified';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::Shortlisted => 'Shortlisted',
            self::Approved => 'Approved',
            self::Waitlisted => 'Waitlisted',
            self::Confirmed => 'Confirmed',
            self::NotSelected => 'Not Selected',
            self::Rejected => 'Rejected',
            self::Disqualified => 'Disqualified',
        };
    }

    /**
     * Stages a group may still move forward to from the current status.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Submitted => [self::UnderReview, self::Approved, self::Rejected, self::Disqualified],
            self::UnderReview => [self::Shortlisted, self::Approved, self::Waitlisted, self::Rejected, self::Disqualified],
            self::Shortlisted => [self::Approved, self::Waitlisted, self::NotSelected, self::Rejected],
            self::Approved => [self::Confirmed, self::Waitlisted, self::Disqualified],
            self::Waitlisted => [self::Approved, self::Confirmed, self::NotSelected],
            self::Confirmed => [self::Disqualified, self::Waitlisted],
            self::NotSelected, self::Rejected, self::Disqualified => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Whether an editor may still amend the submitted details of a group.
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::Submitted, self::UnderReview], true);
    }

    /**
     * Statuses that count as a successful outcome for reporting.
     *
     * @return list<self>
     */
    public static function successful(): array
    {
        return [self::Approved, self::Confirmed, self::Shortlisted];
    }

    /**
     * Statuses that count as a closed outcome for reporting.
     *
     * @return list<self>
     */
    public static function closed(): array
    {
        return [self::NotSelected, self::Rejected, self::Disqualified];
    }
}
