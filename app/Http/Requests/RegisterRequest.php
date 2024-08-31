<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidEmailDomain; // Import the custom rule

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Set to true if you want to allow all users to make this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', new ValidEmailDomain], // Apply the custom rule
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'nullable|exists:roles,id|integer',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.unique' => 'The email address is already taken.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
