<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use App\Repositories\UserRepository;
use App\Services\PromocodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromoCodeController extends Controller
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /** @OA\Post ( path="/api/promo-code/checkout",
     *     @OA\Parameter(in="path", name="code", required=true, @OA\Schema(type="string"),
     *     @OA\Examples(example="string", value="roYcfeoJq", summary="An int value.") ),
     *     @OA\Response( response=200, description="OK")
     * )
     */
    public function checkout(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'email' => 'required|email',
                'activity_id' => 'sometimes|exists:activities,id',
            ]);

            $user = $this->userRepository->getByEmail($request->input('email'));
            $promocodeService = new PromocodeService($request->input('code'));


            $promoCode = $promocodeService->get();

            if (empty($promoCode)) {
                return response()->json([
                    'message' => 'Промокод не валиден!',
                ],403);
            }
            elseif (($promoCode->limit && $promoCode->count_remains <= 0) || !$promoCode->is_active) {
                return response()->json([
                    'message' => 'Промокод больше не действует!',
                ],403);
            }

            if ($user && $promocodeService->checkByUser($user)) {
                return response()->json([
                    'message' => 'Промокод уже был использован!',
                ],403);
            }

            $type = match ($promoCode->discount_in) {
                '%' => 'percent',
                'rub' => 'count'
            };
        } catch (\Throwable $e) {
            Log::error('[PromocodeController::checkout]', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
                'trace' => $e->getTrace()
            ]);
            return response()->json([
                'message' => 'Внутрення ошибка. Попробуйте позже',
            ],400);
        }


        return response()->json([
            'code' => $promoCode->code,
            'type' => $type,
            'count' => $promoCode->amount,
            'message' => 'Промокод - ' . $promoCode->amount,
        ]);
    }
}
