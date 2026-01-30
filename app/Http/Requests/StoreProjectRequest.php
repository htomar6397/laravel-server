<?php

namespace App\Http\Requests;

class StoreProjectRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasPermission('create_projects');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:projects,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'theme_id' => 'required|exists:themes,id',
            'org_unit_id' => 'required|exists:organizational_units,id',
            'sector' => 'required|string|max:100',
            'donor' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:PLANNING,ACTIVE,SUSPENDED,COMPLETED,CANCELLED',
            'completion_percentage' => 'nullable|numeric|min:0|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geojson' => 'nullable|json',
            'location_description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Project code is required.',
            'code.unique' => 'Project code must be unique.',
            'name.required' => 'Project name is required.',
            'theme_id.required' => 'Theme selection is required.',
            'theme_id.exists' => 'Selected theme is invalid.',
            'org_unit_id.required' => 'Organizational unit selection is required.',
            'org_unit_id.exists' => 'Selected organizational unit is invalid.',
            'budget.required' => 'Budget amount is required.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget cannot be negative.',
            'start_date.before_or_equal' => 'Start date must be before or equal to end date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'geojson.json' => 'GeoJSON must be valid JSON.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'code' => 'Project Code',
            'name' => 'Project Name',
            'description' => 'Description',
            'theme_id' => 'Theme',
            'org_unit_id' => 'Organizational Unit',
            'sector' => 'Sector',
            'donor' => 'Donor',
            'budget' => 'Budget',
            'currency' => 'Currency',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'status' => 'Status',
            'completion_percentage' => 'Completion Percentage',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'geojson' => 'GeoJSON Data',
            'location_description' => 'Location Description',
        ];
    }

    /**
     * Custom validation logic after standard validation.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    protected function after($validator)
    {
        // Validate that completion percentage matches status
        if ($this->has('completion_percentage') && $this->has('status')) {
            $completion = $this->input('completion_percentage');
            $status = $this->input('status');

            if ($status === 'COMPLETED' && $completion < 100) {
                $validator->errors()->add('completion_percentage', 'Completion percentage must be 100% for completed projects.');
            }

            if ($status === 'PLANNING' && $completion > 0) {
                $validator->errors()->add('completion_percentage', 'Completion percentage should be 0% for planning projects.');
            }
        }

        // Validate budget currency
        if ($this->has('currency')) {
            $validCurrencies = ['TZS', 'USD', 'EUR', 'GBP'];
            if (!in_array(strtoupper($this->input('currency')), $validCurrencies)) {
                $validator->errors()->add('currency', 'Currency must be one of: ' . implode(', ', $validCurrencies));
            }
        }
    }

    /**
     * Get sanitized input data.
     *
     * @return array
     */
    public function sanitized(): array
    {
        $data = $this->all();

        // Convert currency to uppercase
        if (isset($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }

        // Set default completion percentage if not provided
        if (!isset($data['completion_percentage'])) {
            $data['completion_percentage'] = 0;
        }

        // Add created_by field
        $data['created_by'] = $this->user()->id;

        return $data;
    }
}
