<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
	"apiList" => [
        [
            "class" => "frontend\controllers\TableController",
            "label" => "桌子接口",
        ],
        [
            "class" => "frontend\controllers\DeckController",
            "label" => "牌组接口",
        ],
        [
            "class" => "frontend\controllers\CardController",
            "label" => "卡牌接口",
        ],
    ]
];
