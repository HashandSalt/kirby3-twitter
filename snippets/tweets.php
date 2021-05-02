<?php $tweets = $page->tweets($type, $count, $excludereplies, $screenname); ?>

<ul class="tweetlist">
    <?php foreach ($tweets as $tweet): ?>

        <li>
        
            <?= $tweet['full_text'] ?>
        
            <?php
            if($media === true) {
                if(A::get($tweet, 'entities.media')) {
                $url = $tweet['entities']['media'][0]['media_url_https'];
                echo Html::img($url);
                }
            }
            ?>  
        
        </li>

    <?php endforeach; ?>
</ul>