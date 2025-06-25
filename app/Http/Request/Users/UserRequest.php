<?php

namespace App\Http\Request\Users;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'role'         => 'required|in:doctor,patient',
            'name'         => 'required|string|max:255',
            'lastname'     => 'required|string|max:255',
            'age'          => 'nullable|integer|min:0',
            'gender'       => 'nullable|in:male,female,other',
            'email'        => 'required|email|max:255|unique:users,email,' . $this->user,
            'address'      => 'nullable|string|max:255',
            'phonenumber'  => 'nullable|string|max:20',
            'dateofborn'   => 'nullable|date',
            'password'     => $this->isMethod('post') ? 'required|string|min:6|confirmed' : 'nullable|string|min:6|confirmed',
        ];
    }
}
