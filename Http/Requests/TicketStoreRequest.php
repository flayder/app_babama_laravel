<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class TicketStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function failedValidation(Validator $validator)
    {

        throw new HttpResponseException( response()->json((new ValidationException($validator))
            ->errorBag($this->errorBag)->errors())->setStatusCode(400));

    }


    public function rules(): array
    {
        return [
            'email' => 'required|email:rfc,dns|max:50',
            'subject' => 'required|string|min:4|max:100',
            'message' => 'required|min:15|max:1000',
        ];
    }
}
