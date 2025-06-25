<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'summary'     => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time'  => 'required|date|after:now',
            'end_time'    => 'required|date|after:start_time',
        ];
    }
    //Messages with the function if exist errors
    public function messages(): array
    {
        return [
            'summary.required'    => 'El resumen es obligatorio.',
            'start_time.required' => 'La fecha de inicio es obligatoria.',
            'end_time.required'   => 'La fecha de fin es obligatoria.',
            'start_time.after'    => 'La fecha de inicio debe ser posterior a la fecha actual.',
            'end_time.after'      => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
