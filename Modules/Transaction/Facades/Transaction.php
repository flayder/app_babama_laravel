<?php

namespace App\Modules\Transaction\Facades;

use App\Dto\Order\OrderDto;
use App\Models\User;
use App\Modules\Transaction\DTO\TransactionDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @package Transaction
 *
 * @method static TransactionDto addReplenishment(User $user, float $amount, ?OrderDto $order = null)
 * @method static TransactionDto addPayOrder(OrderDto $orderDto)
 * @method static TransactionDto addOrder(OrderDto $orderDto)
 * @method static TransactionDto orderStatusChange(OrderDto $orderDto)
 * @method static TransactionDto get(int $id)
 * @method static TransactionDto getByUuid(string $uuid)
 * @method static Collection getByOrder(OrderDto $orderDto)
 * @method static Collection getByUser(User $user)
 * @method static void setFailed(TransactionDto $transactionDto)
 * @method static void setSuccess(TransactionDto $transactionDto)
 * @method static void setDetail(string $detail)
 */
class Transaction extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'modules.transaction';
    }
}

