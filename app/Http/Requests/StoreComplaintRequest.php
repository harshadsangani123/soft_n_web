<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return auth()->user() && auth()->user()->isCustomer();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {        
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Complaint title is required.',
            'title.max' => 'Complaint title cannot exceed 255 characters.',
            'description.required' => 'Complaint description is required.',
            'description.max' => 'Complaint description cannot exceed 2000 characters.',
        ];
    }
}
