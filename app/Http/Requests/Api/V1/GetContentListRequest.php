<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ContentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('noCache')) {
            $noCache = $this->input('noCache');
            // Convert various possible string values to boolean
            if (is_string($noCache)) {
                $noCache = filter_var($noCache, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $this->merge(['noCache' => $noCache]);
            }
        }
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
                new Enum(ContentType::class)
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
            'contentType.enum' => 'The content type must be one of: ' . implode(', ', ContentType::values()) . '.',
            'page.integer' => 'The page number must be a whole number.',
            'page.min' => 'The page number must be at least 1.',
            'perPage.integer' => 'The items per page must be a whole number.',
            'perPage.min' => 'The items per page must be at least 1.',
            'perPage.max' => 'The items per page cannot exceed 100.',
            'noCache.boolean' => 'The noCache parameter must be true or false.',
        ];
    }
}
