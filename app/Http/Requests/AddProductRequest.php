<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150', Rule::unique('products')->where(fn ($query) => $query->where('user_id', Auth::id()))],
            'price_sell' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('type', 1))],
            'materials' => ['required', 'array', 'min:1', 'max:100'],
            'materials.*.id' => [Rule::exists('materials', 'id')->where(fn ($query) => $query->where('user_id', Auth::id()))]
        ];
    }
}
