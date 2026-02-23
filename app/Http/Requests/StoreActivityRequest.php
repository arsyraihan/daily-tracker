<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
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
            'tanggal' => ['required', 'date'],
            'task' => ['required', 'string', 'max:200'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i',],
            'output' => ['nullable', 'string',],
            'kategori' => ['required', 'in:BSC / OKR,Daily Task,Improvement Goal,Cuti,Day Off,Libur,Ibadah'],
        ];
    }
}
