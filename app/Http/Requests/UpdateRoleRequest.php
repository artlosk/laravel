<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasPermissionTo('manage-roles');
    }

    public function rules()
    {
        /** @var Role $role */
        $role = $this->route('role');
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.role_name_required'),
            'name.unique' => __('validation.role_name_unique'),
        ];
    }
}
