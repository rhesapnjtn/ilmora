<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadLessonVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime,video/x-m4v',
                'max:512000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'video.required' => 'File video wajib dipilih.',
            'video.mimetypes' => 'Format video tidak didukung. Gunakan MP4, WebM, OGG, atau MOV.',
            'video.max' => 'Ukuran video maksimal 500 MB.',
        ];
    }
}
