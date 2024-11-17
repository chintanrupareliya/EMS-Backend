<?php

//common validation for job CRUD

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'employment_type' => 'required|string|in:Full-time,Part-time,Contract,Freelance,Internship,Remote',
            'salary' => 'nullable|numeric',
            'required_experience' => 'nullable|array',
            'required_skills' => 'nullable|array',
            'posted_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ];
    }
}
