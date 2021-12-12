# Kirby Twit: Work with Twitter Timelines

A small plugin that is a wrapper around [twitteroauth](https://github.com/abraham/twitteroauth). Allows you to display tweets on your website without having use Twitters embedded timelines. The plugin supports both V1.1 and V2 of the API. Plugin was tested on PHP 8 using Kirby 3.6+

Features:

* Display tweets on your site using your own markup.
* Caches results from API, in a unique file per set
* Automagically turns all links, hashtags and @ mentions into clickable links.

****

## Commerical Usage

This plugin is free but if you use it in a commercial project please consider to
- [make a donation 🍻](https://paypal.me/hashandsalt?locale.x=en_GB) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/36141?link=1170)


****

## How to use Kirby Twitter

First you need access to the Twitter API, and for that you need an account. Register your website as an [application here](https://developer.twitter.com/en/apps).

****


## Installation

### Download

Download and copy this repository to `/site/plugins/kirby3-twitter`.


### Composer

```
composer require hashandsalt/kirby3-twitter
```


## Setup

You wont get far without authenticating. Set the following in your config to gain access to your feed:

```
'cache.hashandsalt.kirby-twitter.tweets' => true,
'twit.bearer'            => 'XXX',
'twit.consumerkey'       => 'XXX',
'twit.consumersecret'    => 'XXX',
'twit.accesstoken'       => 'XXX',
'twit.accesstokensecret' => 'XXX',
'twit.apiVersion'        => '1.1',
'twit.cachelife'         =>  30,
```


## Usage

To switch between APIs set the config option accordingly. `'twit.apiVersion' => '1.1'`

Assuming the below details to work with:

```
$tweetid = 1469448484185067523;
$myid = 815859093273509888;
$screenName = 'getkirby';
```

The Plugin makes three page methods available.

// for getting single tweets or timelines
```
$page->tweets(...);
```
// For getting user info
```
$page->twitterUserName($myid);
$page->twitterUserId($screenName);
```


### API V1.1

#### Single Tweet
To get a sungle tweet, set the name of the cache data file as the first param, second param is the API path, followed by optional API parameters.
```
$result = $page->tweets('mysingletweet', 'statuses/show', ['id' => $tweetid, 'tweet_mode' => 'extended']);
```
#### Username From ID
Returns a users name from an ID number
```
$result2 = $page->twitterUserName($myid);
```
#### ID From Username
Returns a users ID from an username

```
$result3 = $page->twitterUserId($screenName);
```
#### Own Time line
Returns a your own timeline given an ID. Set the name of the cache data file as the first param, second param is the API path, followed by optional API parameters.
```
$result4 = $page->tweets('mytweets', 'statuses/home_timeline', ['count' => 20, 'exclude_replies' => true, 'tweet_mode' => 'extended']);
```
#### Other users Time line
Returns a users timeline given an ID. Set the name of the cache data file as the first param, second param is the API path, followed by optional API parameters.
```
$result5 = $page->tweets('someUser', 'statuses/user_timeline', ['screen_name' => $screenName, 'count' => 20, 'exclude_replies' => true, 'tweet_mode' => 'extended']);
```


### API V2

#### Single Tweet
To get a sungle tweet, set the name of the cache data file as the first param, second param is the API path, followed by optional API parameters.

```
$result = $page->tweets('mysingletweet', 'tweets', ['ids' => $tweetid]); 
```

#### Username From ID

Returns a users name from an ID number

``` 
$result2 = $page->twitterUserName($myid);
```

#### ID From Username

Returns a users ID from an username

```
$result3 = $page->twitterUserId($screenName);
```
#### Get a users Timeline

Returns a users timeline given an ID. Set the name of the cache data file as the first param, second param is the API path, followed by optional API parameters.
``` 
$result4 =  $page->tweets($result3, 'users/'.$result3.'/tweets', [
  'max_results' => 20
]);
```

## Known Issues

The Twitter API is a bit dumb. It counts retweets as a tweet. If you ask for 6 tweets and only got 4 back, 2 of them were probably retweeted. Not much to be done about that, other then asking for more then you need and only looping out the first 6, but you could still run into the problem again.


## License

MIT
