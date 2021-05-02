<?php

namespace HashAndSalt\Twitter;

@include_once __DIR__ . '/vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;


class Twitter {
   private static $connection = null;
   
   public $tweets;
   public $single;

   public function init()
   {
    self::$connection = new TwitterOAuth(option('twit.consumerkey'), option('twit.consumersecret'), option('twit.accesstoken'), option('twit.accesstokensecret'));
   }

   public function timeline($type, $count, $er, $screenname) {

    if (!self::$connection) {
      \HashAndSalt\Twitter\Twitter::init();
    }

    $twitterCache = kirby()->cache('hashandsalt.kirby-twitter.tweets');

    $cachename = $screenname ? $screenname : 'timeline';

    $this->tweets = $twitterCache->get($cachename);
    // There's nothing in the cache, so let's fetch it
    if ($this->tweets === null) {
        $this->tweets = self::$connection->get($type, ['count' => $count, "exclude_replies" => $er, "tweet_mode" => 'extended', "screen_name" => $screenname]);
        $this->tweets = json_decode(json_encode($this->tweets), true);
        $twitterCache->set($cachename, $this->tweets, option('twit.cachelife'));
    }

    $this->tweets = $this->setlinks($this->tweets);
    return $this->tweets;
   }

   public function single($id) {

    if (!self::$connection) {
      \HashAndSalt\Twitter\Twitter::init();
    }

    $twitterCache = kirby()->cache('hashandsalt.kirby-twitter.tweets');
    $this->single = $twitterCache->get($id);
    // There's nothing in the cache, so let's fetch it
    if ($this->single === null) {
        $this->single = self::$connection->get("statuses/show", ["id" => $id, "tweet_mode" => 'extended']);
        $this->single = json_decode(json_encode($this->single), true);
        $twitterCache->set($id, $this->single, option('twit.cachelife'));
    }
    $this->single = $this->setlinks($this->single);
    return $this->single;
   }


   public function setLinks($source) {
    array_walk_recursive(
      $source,
        function (&$value, &$key) {
          if (in_array($key, array('url','text','full_text','expanded_url','description','display_url'), true ) ) {
            if (!is_array($value)) {
              $value = $this->linkify($value);
            }
          }
        }
      );
    return $source;
  }


   public function linkify($value, $protocols = array('http', 'https', 'twitter', 'mail'), array $attributes = array('target' => '_blank'))
    {
    // Link attributes
    $attr = '';
    foreach ($attributes as $key => $val) {
        $attr = ' ' . $key . '="' . htmlentities($val) . '"';
    }

    $links = array();

    // Extract existing links and tags
    $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);

    // Extract text links for each protocol
    foreach ((array)$protocols as $protocol) {
        switch ($protocol) {
            case 'http':
            case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$link</a>") . '>'; }, $value); break;
            case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
            case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>") . '>'; }, $value); break;
            default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
        }
    }

    // Insert all link
    return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
    }
   
}
