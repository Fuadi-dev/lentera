<?php

namespace App\Http\Requests;

use App\Models\AiChatSession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAiChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $student = $this->user()?->student;
        if (! $student) {
            return false;
        }

        /** @var AiChatSession|string|null $session */
        $session = $this->route('session');
        if (is_string($session)) {
            $session = AiChatSession::find($session);
        }

        if (! $session instanceof AiChatSession) {
            return false;
        }

        return $session->student_id === $student->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:200'],
        ];
    }
}
