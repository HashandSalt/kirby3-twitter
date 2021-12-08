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
        'tweets' => function ($type, $count, $er, $screenName) {
            $init = new Twitter();
            return $init->timeline($type, $count, $er, $screenName);
        },
        'tweet' => function (string $id) {
            return (new Twitter())->single($id);
        },
    ],
]);
