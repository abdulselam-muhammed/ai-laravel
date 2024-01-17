<?php

namespace App\Http\Requests;

use App\Models\UserPackage;
use Illuminate\Foundation\Http\FormRequest;

class UserPackageRequest extends FormRequest
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
        $id = request()->route('id');
        $userPackage = UserPackage::where(['user_id' => auth()->id(), 'id' => $id])->first();
        return [
            'write_languageses' =>  'required|array|min:'.$userPackage->write_languages.'|max:'.$userPackage->write_languages,
            'use_toneses' =>  'required|array|min:'.$userPackage->access_tones.'|max:'.$userPackage->access_tones,
        ];
    }
}
