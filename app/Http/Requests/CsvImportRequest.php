<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CsvImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return $this->ajax() ?
            [
                'file' => ['required', 'file', 'mimes:csv', 'max:' . (1024 * 60)]
            ] : [];
    }
}
