<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\UserCreateDto;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Refer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /** @OA\Post ( path="/api/promo-code/checkout",
     *     @OA\Parameter(in="path", name="name", required=true, @OA\Schema(type="string"),
     *     @OA\Examples(example="name", value="Jon", summary="An int value.") ),
     *     @OA\Parameter(in="path", name="email", required=true, @OA\Schema(type="string"),
     *     @OA\Examples(example="email", value="example@mail.com", summary="An int value.") ),
     *     @OA\Parameter(in="path", name="password", required=true, @OA\Schema(type="string"),
     *     @OA\Examples(example="password", value="password", summary="An int value.") ),
     *     @OA\Response( response=200, description="OK")
     * )
     */
    public function signUp(Request $request): Response|Application|ResponseFactory
    {
        $data = $request->validate([
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $ref = $request->input('ref');

        $userDto = new UserCreateDto();
        $userDto->email = $data['email'];
        $userDto->password = $data['password'];

        $user = $userDto->create();

        if($ref) {
            $referModal = Refer::where('link', $ref)->first();

            if($referModal)
                $referModal->users()->syncWithoutDetaching($user->id);
        }

        $token = $user->createToken('apiToken')->plainTextToken;

        $res = [
            'user' => UserResource::make($user)->resolve(),
            'token' => $token,
        ];

        return response($res, 201);
    }

    /** @OA\Post ( path="/api/promo-code/checkout",
     *     @OA\Parameter(in="path", name="email", required=true, @OA\Schema(type="string"),
     *     @OA\Examples(example="email", value="example@mail.com", summary="An int value.") ),
     *     @OA\Parameter(in="path", name="password", required=true, @OA\Schema(type="string"),
     *     @OA\Examples(example="password", value="password", summary="An int value.") ),
     *     @OA\Response( response=200, description="OK")
     * )
     */
    public function login(Request $request): Response|Application|ResponseFactory
    {
        $data = $request->validate([
            'email' => 'required|string|exists:users,email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response([
                'message' => 'Данный пароль не подходит для введённого аккаунта.',
            ], 401);
        }

        $token = $user->createToken('apiToken')->plainTextToken;

        $res = [
            'user' => UserResource::make($user)->resolve(),
            'token' => $token,
        ];

        return response($res, 201);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response([
            'message' => 'user logged out',
        ], 200);
    }
}
