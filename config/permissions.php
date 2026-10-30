<?php

return [
    'roles' => ['admin', 'editor', 'moderator', 'support', 'user'],
    'matrix' => [
        'admin' => ['*'],
        'editor' => ['courses.manage', 'media.manage', 'lessons.manage'],
        'moderator' => ['reviews.moderate', 'users.view'],
        'support' => ['chat.manage', 'users.view'],
        'user' => [],
    ],
];
