<?php

@include_once __DIR__ . '/vendor/autoload.php';

load([
    'hashandsalt\\twitter\\twitter' => 'src/classes/Twitter.php'
], __DIR__);

use HashAndSalt\Twitter\Twitter;

Kirby::plugin('hashandsalt/twitter', [

    'snippets' => [
        'twitter/tweet'     => __DIR__ . '/snippets/tweet.php',
        'twitter/tweets'    => __DIR__ . '/snippets/tweets.php'
      ],
  
    'pageMethods' => [
        'tweets' => function ($type, $count = 25, $er = true, $screenname = null) {
            $init = new Twitter();
            return $init->timeline($type, $count = 25, $er = true, $screenname = null);
        },
        'tweet' => function ($id) {
            $init = new Twitter();
            return $init->single($id);
        }
    ]

]);
