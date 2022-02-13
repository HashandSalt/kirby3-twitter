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
     * Temp Data
     *
     * @var mixed
     */
    public $data;



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
     * Cache
     *
     * @var string
     */

    public $twitterCache;


     /**
     * User
     *
     * @var mixed
     */
    public $user;

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
     * Fetches Twitter Data
     *
     * @param string $uid
     * @param string $path
     * @param array $params
     * @return array
     */
    public function tweet(string $uid, string $path, array $params): array
    {
        # Ensure API connection
        if (!self::$connection) {
            \HashAndSalt\Twitter\Twitter::init();
        }

        self::$connection->setApiVersion(option('twit.apiVersion'));

        $tweets = self::$connection->get($path, $params);

        $tweets = $this->twitterCache($uid, $tweets);

        return $this->tweets = $this->setLinks($tweets);

        
    }


    /**
     * Fetches User by ID
     *
     * @param string $id
     * @return string
     */

    public function twitterUserName(string $id): string
    {
        # Ensure API connection
        if (!self::$connection) {
            \HashAndSalt\Twitter\Twitter::init();
        }
        self::$connection->setApiVersion(option('twit.apiVersion'));
        $userString = '';
        if (option('twit.apiVersion') === '1.1') {
            $userData = self::$connection->get('users/lookup', ['user_id' => $id]);
            $userString = $userData[0]->screen_name;
        } elseif (option('twit.apiVersion') === '2') {
            $userData = self::$connection->get('users', ['ids' => $id]);
            $userString = $userData->data[0]->username;
        }
      
        
        return $this->user = $userString;
    }


    /**
     * Fetches User by Name
     *
     * @param string $name
     * @return array
     */

    public function twitterUserId(string $name): string
    {
        # Ensure API connection
        if (!self::$connection) {
            \HashAndSalt\Twitter\Twitter::init();
        }
        self::$connection->setApiVersion(option('twit.apiVersion'));
        $userString = '';
        if (option('twit.apiVersion') === '1.1') {
            $userData = self::$connection->get('users/lookup', ['screen_name' => $name]);
            $userString = $userData[0]->id_str;
        } elseif (option('twit.apiVersion') === '2') {
            $userData = self::$connection->get('users/by/username/' . $name);
            $userString = $userData->data->id;
        }
       
        
        return $this->user = $userString;
    }
 



    /**
     * Cache the data from Twitter
     *
     * @param string $name
     * @return array
     */

   public function twitterCache(string $cacheName, mixed $cacheData): array
   {

    $twitterCache = kirby()->cache('hashandsalt.kirby-twitter.tweets');
    
    $data = $twitterCache->get($cacheName);

    if ($data === null) {
        # .. fetch it!

        $data = $cacheData;

        $data = json_decode(json_encode($data), true);

        # Cache results
        $twitterCache->set($cacheName, $data, option('twit.cachelife'));
    }

    return $this->data = $data;

   }
   
    /**
     * Extracts texts to `linkify` their contents
     *
     * @param array $source
     * @return array
     */
    public function setLinks(mixed $source): array
    {
        array_walk_recursive(
            $source, function (&$value, $key) {
                if (in_array($key, ['url', 'text', 'full_text', 'expanded_url', 'description', 'display_url'], true)) {
                    if (!is_array($value) && !empty($value)) {
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
