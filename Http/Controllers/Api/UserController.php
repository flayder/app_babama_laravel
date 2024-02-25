<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Mail\SendMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function show(): UserResource
    {
        $user = auth()->user();

        return UserResource::make($user);
    }

    public function update(UserUpdateRequest $request)
    {
        $validated = $request->validated();
        /* @var User $user */
        $user = auth()->user();

        if (isset($validated['new_password'])) {
            $validated['password'] = Hash::make($validated['new_password']);

            if (!Hash::check($validated['old_password'], $user->getAuthPassword())) {
                return response()->json([
                    'message' => 'Неверный пароль'
                ])->setStatusCode(403);
            }

            $basic = (object) config('basic');
            $email_from = $basic->sender_email;
            $subject = 'Ваши доступы к Babama.ru';
            $message = "Дублируем сюда Ваши доступы к нашему сайту. И да, приятных Вам накруток.
                    <br><br>
                    Ваш логин: {$user->email}<br>
                    Ваш новый пароль: {$validated['new_password']}";

            Mail::to($user->email)->queue(new SendMail($email_from, $subject, $message));
        }

        if (isset($validated['email'])) {
            if ($validated['email'] !== $user->email) {
                $user->update([
                    'email_verified_at' => null
                ]);
            }
        }

        $user->update($validated);

        return UserResource::make($user);
    }
}
