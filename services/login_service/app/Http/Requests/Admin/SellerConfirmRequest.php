<?php

namespace App\Http\Requests\Admin;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SellerConfirmRequest extends Request
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

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
        response()->json([
            'status' => false,
            'message' => $validator->errors()->all()
                ], getStatusCodes('VALIDATION_ERROR'))
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $active = app('config')->get("enum.common.verify_status")['VERIFIED'];
        $inactive = app('config')->get("enum.common.verify_status")['NOT_VERIFIED'];

        return [
            'verify_status' => 'required|in:' . $active . ',' . $inactive . ''
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {

        return [
            'verify_status.required' => 'VERIFY_STATUS_REQUIRED',
            'verify_status.in' => 'INVALID_VERIFY_STATUS',
        ];
    }

}
