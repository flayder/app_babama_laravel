<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\Order\OrderCreateDto;
use App\Dto\Order\OrderDto;
use App\Dto\UserCreateDto;
use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderCreateRequest;
use App\Http\Requests\Api\OrderStoreRequest;
use App\Http\Resources\OrderResource;
use App\Jobs\PayOrderJob;
use App\Models\Order;
use App\Models\Promocode;
use App\Models\Service;
use App\Models\User;
use App\Modules\Transaction\DTO\TransactionCreateDto;
use App\Modules\Transaction\Facades\Transaction;
use App\Services\ApiProviderService;
use App\Services\Balance;
use App\Services\Integrations\PartnerSoc\PartnerSocService;
use App\Services\OrderDiscountService;
use App\Services\OrderService;
use App\Services\SellerService;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class OrderController extends Controller
{

    public function __construct(
        private OrderDiscountService $orderDiscountService,
        private ApiProviderService $apiProviderService
    )
    {
    }

    /** @OA\Get (
     *     path="/api/my/orders",
     *     @OA\Response( response=200, description="OK")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with('service')
            ->whereHas('service')
            ->where('user_id', auth()->id())
            ->latest()->paginate(500);

        return OrderResource::collection($orders);
    }

    public function lastUnpaidOrder(): OrderResource
    {
        $order = Order::query()
            ->with('service')
            ->where('status', 'unpaid')
            ->whereHas('service')
            ->where('user_id', auth()->id())
            ->where('created_at', '>', now()->subDay())
            ->latest()
            ->first();

        return OrderResource::make($order);
    }
    public function storeUnauthorized(OrderCreateRequest $request)
    {
        if (!empty($request->input('email'))) {
            $userDto = new UserCreateDto();
            $userDto->email = $request->input('email');
            $userDto->password = Str::random(6);
            $user = $userDto->create();
            Auth::guard('web')->login($user);
        } else {
            return \request()->json([
                'message' => 'Unauthenticated',
            ]);
        }

        return $this->store($request, $userDto->isNew);
    }



    /** @OA\Post (
     *     path="/api/order",
     *       @OA\Parameter(in="price", name="price", required=true, @OA\Schema(type="string"),
     *      @OA\Examples(example="string", value="15.25", summary="An int value.")),
     *       @OA\Response( response=200, description="OK")
     * )
     * @throws HttpClientException
     */
    public function store(OrderCreateRequest $request, bool $isNew = false)
    {
        /**
         * @var User $user
         */
        $user = \auth()->user();
        $tryingToFindSameOrder = false;

        if(
            isset($request->category) &&
            isset($request->service) &&
            isset($request->quantity) &&
            isset($request->link)
        )
        {
            $tryingToFindSameOrder = Order::query()
                ->where('category_id', $request->category)
                ->where('service_id', $request->service)
                ->where('quantity', $request->quantity)
                ->where('link', $request->link)
                ->where('user_id', $user->id)
                ->where('created_at', '>', now()->subMinutes(5))
                ->latest()
                ->first();
        }

        if($tryingToFindSameOrder) return;

        $orderService = OrderService::build();

        $orderCreateDto = new OrderCreateDto($request, $user, $isNew);
        
        $orderDto = $orderService->store($orderCreateDto);

        Transaction::addOrder($orderDto);

        dispatch(new PayOrderJob($orderService, $user));

        $token = $isNew ? $user->createToken('apiToken')->plainTextToken : null;

        $result = [
            'order_id' => $orderDto->id,
            'message' => 'Your order has been created'
        ];

        if ($token) {
            $result['token'] = $token;
        }

        return response()->json($result);
    }

    /**
     * @throws HttpClientException
     */
    public function repeatOrder(int $previousOrderId): JsonResponse
    {
        $previousOrder = Order::findOrFail($previousOrderId);
        $user = auth()->user();

        $newOrder = $previousOrder->replicate();
        $newOrder->status = 'processing';
        $newOrder->remains = $previousOrder->start_counter;
        $newOrder->created_at = $newOrder->updated_at = now();

        if ($user->balance < $newOrder->price) {
            $newOrder->status_description = "Недостаточно средств для заказа";
            $newOrder->save();

            return response()->json([
                'message' => "Недостаточно средств для заказа"
            ], 403);
        }

        $this->apiProviderService->makeOrder($newOrder, $newOrder->service, $newOrder->toArray());

        OrderCreated::dispatch($newOrder);

        return response()->json([
            'message' => 'Успешный заказ!'
        ]);
    }

    public function repayOrder(int $failedOrderId): JsonResponse
    {
        $order = Order::findOrFail($failedOrderId);
        $orderService = OrderService::build($order);
        dispatch(new PayOrderJob($orderService, \auth()->user()));


        return response()->json([
            'message' => 'Your order has been submitted'
        ]);
    }

    public function processOrderServiceParameters(array $data,Service $service, Order $order): array
    {
        if (!isset($data['gender']) || $data['gender'] === 'all') {
            $data['gender'] = 'any';
        }

        $parameter = $service->parameters()->where('country_id', $data['country'])->first();

        if ($parameter && $parameter->{$data['gender']}) {
            $service->api_service_id = $parameter->{$data['gender']}['api_service_id'];
            $service->api_provider_id = $parameter->{$data['gender']}['api_provider_id'];
            $service->price += $parameter->{$data['gender']}['service_price_diff'];
            $order->service_parameter_id = $parameter->id;
            $order->service_parameter_gender = $data['gender'];
        }

        return $data;
    }

    public function applyPromoCodeToOrderAndGetPrice($promocodeId, float $price, Order $order): mixed
    {
        $promocode = Promocode::findOrFail($promocodeId);

        $price = $this->orderDiscountService->getPriceAfterDiscount($promocode, $price);

        $order->promocode_id = $promocodeId;

        return $price;
    }

    public function setOrderFields(array $req, Order $order)
    {
        $order->user_id = $req['user_id'];
        $order->category_id = $req['category'];
        $order->service_id = $req['service'];
        $order->link = $req['link'];
        $order->quantity = $req['quantity'];
        $order->status = 'unpaid';
        $order->price = $req['price'];
        $order->runs = isset($req['runs']) && !empty($req['runs']) ? $req['runs'] : null;
        $order->interval = isset($req['interval']) && !empty($req['interval']) ? $req['interval'] : null;
    }
}
