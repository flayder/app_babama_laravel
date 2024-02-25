<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'sometimes|required|email:rfc,dns|unique:users,email,'.auth()->id(),
            'firstname' => 'sometimes|required|string|min:2|max:40',
            'new_password' => ['required', 'sometimes', 'string', 'min:6'],
            'old_password' => ['required', 'sometimes', 'string'],
        ];
    }
}
