<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EditCategoryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', Rule::exists('categories')->where(fn ($query) => $query->where('user_id', Auth::id()))],
            'name' => ['required', 'string', 'max:50', Rule::unique('categories')->where(fn ($query) => $query->where([['user_id', Auth::id()], ['id', '!=', $this->id]]))],
            'type' => ['required', 'in:1,2']
        ];
    }
}
