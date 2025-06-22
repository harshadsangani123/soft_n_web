<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComplaintStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return auth()->user() && auth()->user()->isTechnician();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    Complaint::STATUS_IN_PROGRESS,
                    Complaint::STATUS_NOT_AVAILABLE,
                    Complaint::STATUS_RESOLVED
                ])
            ],
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status. Must be in_progress, not_available, or resolved.',
        ];
    }
}
