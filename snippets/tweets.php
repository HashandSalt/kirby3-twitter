<?php $tweets = $page->tweets($type, $count, $excludeReplies, $screenName, $media); ?>

<ul class="tweetlist">
    <?php foreach ($tweets as $tweet) : ?>
    <li>
        <?php snippet('twitter/tweet', compact('tweet')) ?>
    </li>
    <?php endforeach ?>
</ul>
