<?php

$tweet = $tweet ?? $page->tweet($id);
$media = $media ?? true;

echo '<p>' . $tweet['full_text'] . '</p>';

if ($media === true && A::get($tweet, 'entities.media')) {
    echo Html::img($tweet['entities']['media'][0]['media_url_https']);
}

