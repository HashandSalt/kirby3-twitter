# Kirby Twit: Work with Twitter Timelines

A small plugin that is a wrapper around [twitteroauth](https://github.com/abraham/twitteroauth). Allows you to display tweets on your website without having use Twitters embedded timelines.

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
'twit.consumerkey'       => 'XXX',
'twit.consumersecret'    => 'XXX',
'twit.accesstoken'       => 'XXX',
'twit.accesstokensecret' => 'XXX',
'twit.cachelife'         =>  30,
```

## Usage

To get from your own timeline:

```
<?= snippet('twitter/tweets', ['type' => 'statuses/home_timeline', 'count' => 6, 'excludereplies' => true, 'screenname' => null, 'media' => true])?>
```

You can access more then `statuses/user_timeline`, like `statuses/home_timeline`. Refer to the [Twitter api](https://developer.twitter.com/en/docs/tweets/timelines/api-reference/get-statuses-home_timeline) for more options.

## Get from another timeline

To get tweets from another timeline, you pass in a screen name as the 4th parameter:

```
<?= snippet('twitter/tweets', ['type' => 'statuses/user_timeline', 'count' => 6, 'excludereplies' => true, 'screenname' => 'getkirby', 'media' => true])?>
```

The full information from the API is in the collection. `dump()` the collection to see other information you may want to use.

Modify the snippets accoridng to your desired HTML.

## Get a specific tweet by id

<?= snippet('twitter/tweet', ['id' => '1388604038015447042', 'media' => true])?>

## Known Issues

The Twitter API is a bit dumb. It counts retweets as a tweet. If you ask for 6 tweets and only got 4 back, 2 of them were probably re-tweeted. Not much to be done about that, other then asking for more then you need and only looping out the first 6, but you could still run into the problem again.


## License

MIT
