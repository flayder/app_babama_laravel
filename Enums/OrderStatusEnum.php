<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case CANCELED = 'canceled';
    case UNPAID = 'unpaid';
    case COMPLETED = 'completed';
    case PARTIAL = 'partial';
    case PROCESSING = 'processing';
    case PENDING = 'pending';
    case REFUNDED = 'refunded';
    case IN_PROGRESS = 'in progress';

    /**
     * @return string - расшифровка статуса
     */
    public function label(): string
    {
        return self::getLabel($this);
    }

    /**
     * @param OrderStatusEnum $value - расшифровка статуса
     * @return string
     */
    public static function getLabel(self $value): string
    {
        return match ($value) {
            self::CANCELED => 'Отменен',
            self::UNPAID => 'Не оплачен',
            self::COMPLETED => 'Завершен',
            self::PARTIAL => 'Исполнен частично',
            self::PROCESSING => 'В обработке',
            self::PENDING => 'Ожидание',
            self::IN_PROGRESS => 'Выполняется',
            self::REFUNDED => 'Возвращен',
        };
    }
}
