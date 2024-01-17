<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'gateway' => 'required',
            'currency' => 'required',
            'bank_id' => 'required_if:gateway,bank',
            'bank_slip' => 'required_if:gateway,bank|mimes:png,jpg'
        ];
    }
}
