<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name'),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => 'permission name']),
            'name.string' => __('validation.string', ['attribute' => 'permission name']),
            'name.max' => __('validation.max.string', ['attribute' => 'permission name', 'max' => 255]),
            'name.unique' => __('validation.unique', ['attribute' => 'permission name']),
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::debug('Validation failed in StorePermissionRequest', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all(),
        ]);
        parent::failedValidation($validator);
    }
}
