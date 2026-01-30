<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_FAILED'
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedAuthorization()
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'This action is unauthorized.',
                    'error_code' => 'UNAUTHORIZED'
                ], 403)
            );
        }

        parent::failedAuthorization();
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->after($validator);
        });
    }

    /**
     * Custom validation logic after standard validation.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    protected function after($validator)
    {
        // Override in child classes for custom validation logic
    }

    /**
     * Get sanitized input data.
     *
     * @return array
     */
    public function sanitized(): array
    {
        return $this->all();
    }

    /**
     * Check if request is for API.
     *
     * @return bool
     */
    protected function isApi(): bool
    {
        return $this->expectsJson() || $this->is('api/*');
    }
}
