<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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


    public function validator($factory)
    {
        return $factory->make(
            $this->sanitize(), $this->container->call([$this, 'rules']), $this->messages()
        );
    }

    public function sanitize(): array
    {

        return $this->all();
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
            'activity_id' => 'required|int|exists:activities,id',
            'min_amount' => 'required|numeric',
            'max_amount' => 'required|numeric',
            'service_status' => 'required',
            'priority' => 'required|numeric',
            'price' => 'required|numeric',
            'api_provider_id' => 'nullable',
            'api_service_id' => 'nullable',
            'service_parameter' => 'sometimes',
            'amount_percent' => 'nullable|numeric',
            'short_description' => 'sometimes',
            'link_demo' => 'sometimes',
        ];
    }
}
