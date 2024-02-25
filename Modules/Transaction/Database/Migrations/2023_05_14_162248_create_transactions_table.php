<?php

declare(strict_types=1);

use App\Modules\Transaction\Enums\TransactionStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid()->default(uuid_create());
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->decimal('amount', 11, 2)->nullable();
            $table->enum('type', \App\Modules\Transaction\Enums\TransactionTypeEnum::toArray());
            $table->enum('status', TransactionStatusEnum::toArray())->default(TransactionStatusEnum::FAILED->value);
            $table->string('detail')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
