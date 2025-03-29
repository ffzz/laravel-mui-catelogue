<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetContentListRequest extends FormRequest
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
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1|max:100',
            'contentType' => [
                'nullable',
                'string',
                'in:course,live learning,resource,video,program,page,partnered content'
            ],
            'noCache' => 'nullable|boolean'
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
            'contentType.in' => 'The content type must be one of: course, live learning, resource, video, program, page, or partnered content.',
            'page.integer' => 'The page number must be a whole number.',
            'page.min' => 'The page number must be at least 1.',
            'perPage.integer' => 'The items per page must be a whole number.',
            'perPage.min' => 'The items per page must be at least 1.',
            'perPage.max' => 'The items per page cannot exceed 100.',
            'noCache.boolean' => 'The noCache parameter must be true or false.',
        ];
    }
}
