<?php

namespace App\Modules\Transaction\Enums;

use Illuminate\Database\Eloquent\Collection;

enum TransactionTypeEnum: string
{
    case ORDER = 'order'; // Создание заказа
    case ORDER_STATUS = 'order_status'; // Смена статуса заказа
    case REPLENISHMENT = 'replenishment'; // Пополнение баланса
    case PAY = 'pay'; // Оплата заказа

    public static function toArray(): array
    {
        $result = Collection::empty();

        foreach (self::cases() as $case) {
            $result->push($case->value);
        }

        return $result->toArray();
    }

    /**
     * @return string - расшифровка типа
     */
    public function label(): string
    {
        return self::getLabel($this);
    }

    /**
     * @param OrderStateEnum $value - расшифровка типа
     * @return string
     */
    public static function getLabel(self $value): string
    {
        return match ($value) {
            self::ORDER => 'Создан новый заказ',
            self::ORDER_STATUS => 'Смена статуса заказа',
            self::REPLENISHMENT => 'Пополнение баланса',
            self::PAY => 'Оплата заказа',
        };
    }
}
