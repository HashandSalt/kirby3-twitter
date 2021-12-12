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
        'tweets' => function ($uid, $path, $params) {
            $init = new Twitter();
            return $init->tweet($uid, $path, $params);
        },
        'twitterUserName' => function ($id) {
            $init = new Twitter();
            return $init->twitterUserName($id);
        },
        'twitterUserId' => function ($name) {
            $init = new Twitter();
            return $init->twitterUserId($name);
        }
    ],
]);
