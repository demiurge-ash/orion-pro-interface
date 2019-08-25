<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PassRequest extends FormRequest
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
            'card_id'           => 'regex:/^[a-zA-Z0-9]+$/|size:16|nullable',
            'date_employment'   => 'date_format:Y-m-d|required',
            'date_dismissal'    => 'date_format:Y-m-d|nullable',
            'department'        => 'numeric|required',
            'organization'      => 'numeric|required',
            'status'            => 'numeric|required',
            'citizenship'       => 'max:250|nullable',
            'passport'          => 'max:250|nullable',
            'last_name'         => 'max:250|required',
            'first_name'        => 'max:250|required',
            'middle_name'       => 'max:250|required',
            'group'             => 'numeric|required',
            'position'          => 'numeric|required',
            'photo'             => 'file|nullable',
            'pass_valid_from'   => 'required_with:card_id|date_format:Y-m-d|nullable',
            'pass_valid_to'     => 'required_with:card_id|date_format:Y-m-d|nullable',
            'pass_color'        => 'numeric|required',
            'info'              => 'boolean',
            'comments'          => 'nullable',
        ];
    }
}
