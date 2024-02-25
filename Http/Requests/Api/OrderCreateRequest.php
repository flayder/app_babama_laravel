<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class OrderCreateRequest extends FormRequest
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
            'category' => 'required|integer|exists:categories,id',
            'service' => 'required|integer|min:1|not_in:0',
            'link' => 'required|string',
            'email' => 'required|email',
            'quantity' => 'required|integer',
            'country' => 'sometimes|required|exists:countries,id',
            'gender' => 'sometimes|required|string',
            'promocode_code' => 'sometimes|required',
            'runs' => 'integer',
            'interval' => 'integer',
        ];
    }

    public function messages()
    {
        return [
            'link.url' => 'Формат ссылки не валиден!',
            'link.required' => 'Формат ссылки не валиден!',
            '*.required' => 'Поле :attribute обязательно для заполнения!',
            'promocode_id.exists' => 'Промокод не валиден!',
        ];
    }

}
