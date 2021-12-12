<?php
if (option('twit.apiVersion') === '1.1') {   
    $tweets = $page->tweets($cachename, 'statuses/user_timeline', $params);
} elseif (option('twit.apiVersion') === '2') {
    $user = $page->twitterUserId($screenname);
    $tweets = $page->tweets($cachename, 'users/'.$user.'/tweets', $params);
    $tweets = $tweets['data'];
}
?>

<ul>
<?php if (option('twit.apiVersion') === '1.1'): ?>
    <?php foreach ($tweets as $tweet) : ?>
    <li>
        <?php echo '<p>'.$tweet['full_text'].'</p>';
        if ($media === true && A::get($tweet, 'entities.media')) {
            echo Html::img($tweet['entities']['media'][0]['media_url_https']);
        }?>
    </li>
    <?php endforeach ?>
<?php endif; ?>

<?php if (option('twit.apiVersion') === '2'): ?>  
    <?php foreach ($tweets as $tweet) : ?>
    <li>
     <?php echo '<p>'.$tweet['text'].'</p>';?>
    </li>
    <?php endforeach ?>
<?php endif; ?>
</ul>