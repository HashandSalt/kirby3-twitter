<?php

if (option('twit.apiVersion') === '1.1') {
    
    $tweet = $page->tweets($cachename, 'statuses/show', $params);

    echo '<p>' . $tweet['full_text'] . '</p>';
    if ($media === true && A::get($tweet, 'entities.media')) {
        echo Html::img($tweet['entities']['media'][0]['media_url_https']);
    }

} elseif (option('twit.apiVersion') === '2') {
    $tweet = $page->tweets($cachename, 'tweets', $params);
    echo '<p>' . $tweet['data'][0]['text'] . '</p>';
}

?>