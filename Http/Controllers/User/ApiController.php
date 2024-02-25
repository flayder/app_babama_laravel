<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function index()
    {
        $data['api_token'] = Auth::user()->api_token;
        if (Auth::check()) {
            return view('user.pages.api.index', $data);
        }

        return redirect()->route('apiDocs');
    }

    public function apiGenerate()
    {
        $user = Auth::user();
        $user->api_token = Str::random(20);
        $user->save();

        return $user->api_token;
    }
}
