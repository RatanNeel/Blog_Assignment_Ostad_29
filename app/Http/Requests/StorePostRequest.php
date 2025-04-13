<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
        // Optionally, you can add more complex authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
             // Content might be long, ensure DB column is TEXT or LONGTEXT
             'content' => ['required', 'string'],
            'visibility' => ['required', 'in:published,draft'], // Adjust as per your visibility options
            // 'image' => ['nullable', 'image', 'max:2048'], // Optional image upload, max 2MB
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'], // Max 4MB example
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title is required.',
            'content.required' => 'Post content cannot be empty',
            'visibility.required' => 'Please select a visibility option.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The image may not be greater than 2MB.',
            // Add more custom messages as needed
        ];
    }
}
