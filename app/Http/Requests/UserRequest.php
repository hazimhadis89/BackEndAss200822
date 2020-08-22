<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => '{SOMETIMES}required|string|max:255',
            'email' => '{SOMETIMES}required|email:rfc,dns|max:255|unique:users{UNIQUE}',
        ];

        if ($this->isMethod('PUT')) {
            // replace {SOMETIMES} & {UNIQUE}
            $rules['name'] = str_replace('{SOMETIMES}', 'sometimes|', $rules['name']);
            $rules['email'] = str_replace('{SOMETIMES}', 'sometimes|', $rules['email']);
            $user = $this->route('user');
            $rules['email'] = str_replace('{UNIQUE}', ",email,$user", $rules['email']);
        } else {
            // remove {SOMETIMES} & {UNIQUE}
            $rules['name'] = str_replace('{SOMETIMES}', '', $rules['name']);
            $rules['email'] = str_replace('{SOMETIMES}', '', $rules['email']);
            $rules['email'] = str_replace('{UNIQUE}', '', $rules['email']);
        }

        return $rules;
    }
}
