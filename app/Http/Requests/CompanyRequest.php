<?php

//common validation for company CRUD

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'website' => 'required|url',
            'status' => 'required|in:A,I',
            'admin.first_name' => 'required|string|max:255',
            'admin.last_name' => 'required|string|max:255',
            'admin.address' => 'required|string|max:255',
            'admin.city' => 'required|string|max:255',
            'admin.dob' => 'required|date',
            'admin.joining_date' => 'required|date',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Check if the request is for update
        if ($this->has('admin.email')) {
            // Skip the email uniqueness check for update
            $rules['company_email'] = 'required|email|unique:companies';
            $rules['admin.email'] = 'required|email|unique:users,email';
        } else {
            // Include the email uniqueness check for create
            // $rules['company_email'] = 'required|unique:companies,company_email,' . $this->id;
        }

        return $rules;
    }
}
