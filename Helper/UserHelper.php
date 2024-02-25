<?php

namespace App\Helper;

use App\Dto\UserCreateDto;
use App\Mail\SendMail;
use App\Mail\UserCreateMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserHelper
{
    public static function userCreate(UserCreateDto $createDto)
    {

        $user = self::userByEmail($createDto->email);
        if ($user) {
            return $user;
        }

        $user = User::create([
            'email' => $createDto->email,
            'password' => bcrypt($createDto->password),
            'firstname' => $createDto->firstName,
            'lastName' => $createDto->lastName,
            'userName' => $createDto->userName,
            'address' => $createDto->address,
            'phone' => $createDto->phone,
            'phoneCode' => $createDto->phoneCode,
            'language_id' => 14,
        ]);

        $createDto->isNew = true;

        $basic = (object) config('basic');
        $email_from = $basic->sender_email;
        $subject = 'Ваши доступы к Babama.ru';

        try {
            Mail::to($user->email)->queue(new UserCreateMail($email_from, $subject, $createDto));
        } catch (\Throwable $exception) {
            Log::error('Mail Error', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'user' => $user
            ]);
        }

        return $user;
    }

    public static function userByEmail($email)
    {
        $user = User::where('email', $email)->first();

        return !empty($user) ? $user : null;
    }


}
