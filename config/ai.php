<?php

return [

    /*
    |--------------------------------------------------------------------------
    | "Tanya Si Lentera" Chat Tutor
    |--------------------------------------------------------------------------
    |
    | Batas penggunaan fitur chat tutor berbasis Generative AI. Nilai default
    | mengikuti PRD dan dapat diubah melalui environment variable.
    |
    */

    'chat' => [
        'max_input_characters' => 200,
        'max_messages_per_session' => (int) env('AI_CHAT_MAX_MESSAGES_PER_SESSION', 10),
        'daily_limit' => (int) env('AI_DAILY_LIMIT_CHAT', 30),
        'max_output_tokens' => (int) env('AI_CHAT_MAX_OUTPUT_TOKENS', 2048),
        'thinking_level' => env('AI_CHAT_THINKING_LEVEL', 'low'),
        'history_turns' => 4,
        'max_context_characters' => 4000,
    ],

];
