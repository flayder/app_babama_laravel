<?php

namespace App\Modules\Transaction\Enums;

use Illuminate\Database\Eloquent\Collection;

enum TransactionStatusEnum: string
{
    case SUCCESS = 'success';

    case FAILED = 'failed';

    case PENDING = 'pending';

    public static function toArray(): array
    {
        $result = Collection::empty();

        foreach (self::cases() as $case) {
            $result->push($case->value);
        }

        return $result->toArray();
    }

    /**
     * @return string - расшифровка статуса
     */
    public function label(): string
    {
        return self::getLabel($this);
    }

    /**
     * @param OrderStateEnum $value - расшифровка статуса
     * @return string
     */
    public static function getLabel(self $value): string
    {
        return match ($value) {
            self::SUCCESS => 'Выполнено',
            self::FAILED => 'Ошибка',
            self::PENDING => 'Ожидание',
        };
    }

}
