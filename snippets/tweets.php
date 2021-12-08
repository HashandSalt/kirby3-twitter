<?php $tweets = $page->tweets($type, $count, $excludeReplies, $screenName); ?>

<ul class="tweetlist">
    <?php foreach ($page->tweets($type, $count, $excludereplies, $screenname) as $tweet) : ?>
    <li>
        <?php snippet('twitter/tweet', compact('tweet')) ?>
    </li>
    <?php endforeach ?>
</ul>
