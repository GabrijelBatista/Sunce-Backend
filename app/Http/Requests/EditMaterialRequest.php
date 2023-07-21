<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EditMaterialRequest extends FormRequest
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
            'id' => ['required', Rule::exists('materials')->where(fn ($query) => $query->where('user_id', Auth::id()))],
            'name' => ['required', 'string', 'max:50', Rule::unique('materials')->where(fn ($query) => $query->where([['user_id', Auth::id()], ['id', '!=', $this->id]]))],
            'price_per_uom' => ['required', 'numeric', 'min:0', 'max:100000'],
            'uom' => ['required', 'in:kg,l'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('type', 2))]
        ];
    }
}
