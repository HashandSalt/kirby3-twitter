<?php $tweet = $page->tweet($id); ?>

<p><?= $tweet['full_text'] ?></p>
  
<?php
      
if($media === true) {
    if(A::get($tweet, 'entities.media')) {
        $url = $tweet['entities']['media'][0]['media_url_https'];
        echo Html::img($url);
    }
}
       
?>