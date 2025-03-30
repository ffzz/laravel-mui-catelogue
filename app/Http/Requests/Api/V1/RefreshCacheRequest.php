<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ContentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'id' => ['nullable', 'integer', 'min:1'],
            'contentType' => ['nullable', 'string', new Enum(ContentType::class)]
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
            'contentType.enum' => 'The content type must be one of: ' . implode(', ', ContentType::values()) . '.',
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
        // Allow cache refresh without parameters
    }
}
