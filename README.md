Bitly API Client
================

[![Build Status (Scrutinizer)](https://scrutinizer-ci.com/g/Jleagle/bitly-api-client/badges/build.png)](https://scrutinizer-ci.com/g/Jleagle/bitly-api-client)
[![Code Quality (scrutinizer)](https://scrutinizer-ci.com/g/Jleagle/bitly-api-client/badges/quality-score.png)](https://scrutinizer-ci.com/g/Jleagle/bitly-api-client)
[![Latest Stable Version](https://poser.pugx.org/Jleagle/bitly-api-client/v/stable.png)](https://packagist.org/packages/Jleagle/bitly-api-client)
[![Latest Unstable Version](https://poser.pugx.org/Jleagle/bitly-api-client/v/unstable.png)](https://packagist.org/packages/Jleagle/bitly-api-client)

#### To use the class, you need to use one of three static methods.

If you have a bitly account username and password:

```php
$bitly = \Jleagle\Bitly::usernamePassword($username, $password);
```

If you have an access token (you can get one from https://bitly.com/a/oauth_apps):

```php
$bitly = \Jleagle\Bitly::accessToken($accessToken);
```

If you want to log the user in using OAuth:

```php
$bitly = \Jleagle\Bitly::authorize($clientId, $clientSecret, $projectUrl, $state);
```

#### From there you have access to each endpoint:

Return the click rate for content containing 'obama':

```php
$realtimeClickrates = $bitly->realtimeClickrate('obama');
```
