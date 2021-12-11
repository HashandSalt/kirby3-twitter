<?php

namespace HashAndSalt\Twitter;

@include_once __DIR__ . '/vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;


class Twitter {
    /**
     * @var \Abraham\TwitterOAuth\TwitterOAuth
     */
    private static $connection = null;


    /**
     * Multiple tweets
     *
     * @var array
     */
    public $tweets;


    /**
     * Single tweet
     *
     * @var array
     */
    public $single;


    /**
     * Initializes connection with Twitter API
     *
     * @return void
     */
    public function init()
    {
        self::$connection = new TwitterOAuth(
            option('twit.consumerkey'),
            option('twit.consumersecret'),
            option('twit.accesstoken'),
            option('twit.accesstokensecret')
        );


    }


    /**
     * Fetches all tweets in timeline
     *
     * @param string $type
     * @param int $count
     * @param bool $excludeReplies
     * @param string $screenName
     * @return array
     */
    public function timeline(string $type, int $count, bool $excludeReplies, mixed $screenName): array
    {
        # Ensure API connection
        if (!self::$connection) {
            \HashAndSalt\Twitter\Twitter::init();
        }

        self::$connection->setApiVersion(option('twit.apiVersion'));

        # Initialize cache
        $twitterCache = kirby()->cache('hashandsalt.kirby-twitter.tweets');

        # Determine cache key
        $cacheName = $screenName ? $screenName : 'timeline';

        $tweets = $twitterCache->get($cacheName);

        # If there's nothing in the cache ..
        if ($tweets === null) {
            # .. fetch it!

            if (option('twit.apiVersion') === '1.1') {
            $tweets = self::$connection->get($type, [
                'tweet_mode' => 'extended',
                'screen_name' => $screenName,
                'exclude_replies' => $excludeReplies,
                'count' => $count,
            ]);
            } elseif (option('twit.apiVersion') === '2') {
                $tweets = self::$connection->get($type, [
                    'ids' => $screenName,
                ]);
            }

            $tweets = json_decode(json_encode($tweets), true);

            # Cache results
            $twitterCache->set($cacheName, $tweets, option('twit.cachelife'));
        }

        return $this->tweets = $this->setLinks($tweets);
    }


    /**
     * Fetches single tweet by ID
     *
     * @param string $id
     * @return array
     */
    public function single(string $id): array
    {
        # Ensure API connection
        if (!self::$connection) {
            \HashAndSalt\Twitter\Twitter::init();
        }

        # Initialize cache
        $twitterCache = kirby()->cache('hashandsalt.kirby-twitter.tweets');

        # Determine cache key
        $single = $twitterCache->get($id);

        # If there's nothing in the cache ..
        if ($single === null) {
            # .. fetch it!

            if (option('twit.apiVersion') === '1.1') {
                $single = self::$connection->get('statuses/show', [
                    'tweet_mode' => 'extended',
                    'id' => $id,
                ]);
                } elseif (option('twit.apiVersion') === '2') {
                   
                }

            $single = json_decode(json_encode($single), true);

            # Cache results
            $twitterCache->set($id, $single, option('twit.cachelife'));
        }

        return $this->single = $this->setLinks($single);
   }


    /**
     * Extracts texts to `linkify` their contents
     *
     * @param array $source
     * @return array
     */
    public function setLinks(array $source): array
    {
        array_walk_recursive(
            $source, function (&$value, $key) {
                if (in_array($key, ['url', 'text', 'full_text', 'expanded_url', 'description', 'display_url'], true)) {
                    if (!is_array($value)) {
                        $value = $this->linkify($value);
                    }
                }
            }
        );

        return $source;
    }


    /**
     * Converts linkable text to hyperlinks
     *
     * @param string $value
     * @param array $protocols
     * @param array $attributes
     * @return string
     */
    public function linkify(mixed $value, array $protocols = ['http', 'https', 'twitter', 'mail'], array $attributes = ['target' => '_blank', 'rel' => 'noopener']): string
    {
        # Link attributes
        $attr = '';

        foreach ($attributes as $key => $val) {
            $attr = ' ' . $key . '="' . htmlentities($val) . '"';
        }

        $links = [];

        # Extract existing links and tags
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) {
            return '<' . array_push($links, $match[1]) . '>';
        }, $value);

        # Extract text links for each protocol
        foreach ((array)$protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$link</a>") . '>'; }, $value); break;
                case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
                case 'twitter': $value = preg_replace_callback('/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>") . '>'; }, $value); break;
                default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
            }
        }

        # Insert all links
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) {
            return $links[$match[1] - 1];
        }, $value);
    }
}
