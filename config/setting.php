<?php

return [
    'error_code' => '500',
    'error_message' => 'Internal Error',
    'success_code' => '200',
    'success_add_message' => 'Sucess Add Data',
    'deleted' => '1',
    'not_deleted' => '0',
    'active' => 1,
    'deactive' => 0,
    'meta_token' => env('META_TOKEN'),
    'webhook_url' => env('WEBHOOK_URL'),
    'recaptcha_secret' => env('RECAPTCHA_SECRET_KEY'),
    'free_captcha' => [ 64, 65, 66, 67, 68, 71, 23, 60, 1],
];
