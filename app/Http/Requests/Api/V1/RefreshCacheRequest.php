<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RefreshCacheRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|min:1',
            'contentType' => [
                'nullable',
                'string',
                'in:course,live learning,resource,video,program,page,partnered content'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.integer' => 'The content ID must be a whole number.',
            'id.min' => 'The content ID must be at least 1.',
            'contentType.in' => 'The content type must be one of: course, live learning, resource, video, program, page, or partnered content.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->input('id') && !$this->input('contentType')) {
                $validator->errors()->add('parameters', 'Either content ID or content type must be provided.');
            }
        });
    }
}
