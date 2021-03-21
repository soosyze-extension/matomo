<?php

return [
    'matomo.hook.config' => [
        'class'     => 'SoosyzeExtension\Matomo\Hook\Config',
        'arguments' => [ '@user' ],
        'hooks'     => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'matomo.hook.app'    => [
        'class'     => 'SoosyzeExtension\Matomo\Hook\App',
        'arguments' => [ '@config', '@user' ],
        'hooks'     => [
            'app.response.after' => 'onResponseAfter'
        ]
    ]
];
