<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeeRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'type' => 'string|in:E',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'emp_no' => 'nullable|string|max:255'
        ];
        
    }
}
