<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
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
            'name' => 'required|unique:packages,name,' . $this->id,
            'generate_characters' => 'required|numeric',
            'monthly_price' => 'required|numeric',
            'write_languages' => 'required|numeric',
            'access_tones' => 'required|numeric',
            'yearly_price' => 'required|numeric',
        ];
    }
}
