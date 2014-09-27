Bitly API Client
============

### To use the class, you need to use one of four static methods.

If you have a bitly account username and password:

```php
$bitly = \Jleagle\Bitly::usernamePassword($username, $password);
```
If you only want to interact with your own account you can get an access token from https://bitly.com/a/oauth_apps and use the following:

```php
$bitly = \Jleagle\Bitly::accessToken($accessToken);
```

If you want to redirect the user to auth with Bitly:

```php
$bitly = \Jleagle\Bitly::authorize($clientId, $clientSecret, $projectUrl, $state);
```

If you don't need to interact with any API endpoints that require auth:

```php
$bitly = \Jleagle\Bitly::noAuth();
```

### From there you have access to each endpoint documented at http://dev.bitly.com/data_apis.html:

Return the click rate for content containing 'obama'

$realtimeClickrates = $bitly->realtimeClickrate('obama');
