<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'service_title' => 'required|string|max:150',
            'activity_id' => 'required|string',
            'min_amount' => 'required|numeric',
            'max_amount' => 'required|numeric',
            'service_status' => 'required',
            'price' => 'required|numeric',
            'priority' => 'required|numeric',
            'api_provider_id' => 'nullable',
            'api_service_id' => 'nullable',
            'service_parameter' => 'sometimes',
            'amount_percent' => 'nullable|numeric',
            'short_description' => 'sometimes',
            'service_description' => 'sometimes',
            'link_demo' => 'sometimes'
        ];
    }
}
