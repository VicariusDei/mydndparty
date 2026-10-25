<?php

return [
    'app_env' => 'local',
    'app_debug' => true,
    'allow_demo_auth' => false,
    'demo_user_id' => 1,
    'npc_ai' => [
        'api_key' => getenv('OPENAI_API_KEY') ?: '',
        'text_model' => 'gpt-4.1-mini',
        'transcription_model' => 'gpt-transcribe',
        'speech_model' => 'gpt-4o-mini-tts',
    ],
    'db' => [
        'host' => 'localhost',
        'name' => 'mydndparty',
        'user' => 'db_user',
        'pass' => 'db_password',
        'charset' => 'utf8mb4',
    ],
];
