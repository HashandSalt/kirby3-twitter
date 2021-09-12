<?php

@include_once __DIR__ . '/vendor/autoload.php';

load([
    'hashandsalt\\twitter\\twitter' => 'src/classes/Twitter.php',
], __DIR__);

use HashAndSalt\Twitter\Twitter;


Kirby::plugin('hashandsalt/twitter', [
    # Snippets
    'snippets' => [
        'twitter/tweet'  => __DIR__ . '/snippets/tweet.php',
        'twitter/tweets' => __DIR__ . '/snippets/tweets.php',
    ],

    # Page methods
    'pageMethods' => [
        'tweets' => function (string $type, int $count, bool $excludeReplies, string $screenName) {
            return (new Twitter())->timeline($type, $count, $excludeReplies, $screenName);
        },
        'tweet' => function (string $id) {
            return (new Twitter())->single($id);
        },
    ],
]);
