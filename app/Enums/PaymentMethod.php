<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Mpesa = 'mpesa';
    case MixxByYas = 'mixx_by_yas';
    case AirtelMoney = 'airtel_money';
    case Halopesa = 'halopesa';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Waiver = 'waiver';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Mpesa => 'M-Pesa',
            self::MixxByYas => 'Mixx by Yas',
            self::AirtelMoney => 'Airtel Money',
            self::Halopesa => 'Halopesa',
            self::Card => 'Card',
            self::BankTransfer => 'Bank transfer',
            self::Waiver => 'Waiver',
            self::Manual => 'Manual',
        };
    }

    /**
     * Mobile money providers grouped separately in the revenue chart.
     *
     * @return list<self>
     */
    public static function aggregators(): array
    {
        return [
            self::Mpesa,
            self::MixxByYas,
            self::AirtelMoney,
            self::Halopesa,
            self::Card,
            self::BankTransfer,
        ];
    }

    public function isMobileMoney(): bool
    {
        return in_array($this, [self::Mpesa, self::MixxByYas, self::AirtelMoney, self::Halopesa], true);
    }
}
