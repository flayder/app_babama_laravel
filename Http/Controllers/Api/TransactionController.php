<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function index()
    {
//        $transactions = Transaction::where('user_id', auth()->id())->latest()->paginate(500);
        $transactions = \App\Modules\Transaction\Facades\Transaction::getByUser(auth()->user());


        return TransactionResource::collection($transactions);
    }

    public function getByUuid(string $uuid)
    {
        $transaction = \App\Modules\Transaction\Facades\Transaction::getByUuid($uuid);

        return response()->json($transaction);
    }

    public function getUserStatusByUuid(string $uuid)
    {
        $transaction = \App\Modules\Transaction\Facades\Transaction::getByUuid($uuid);

        $newUser = !empty($transaction?->order?->newUser);

        return response()->json([
            'new_user' => $newUser
        ]);
    }
}
