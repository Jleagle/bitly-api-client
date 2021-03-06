bitly-api-client
================

#### To use the class, you need to use one of three static methods.

If you have a bitly account username and password:

```php
$bitly = Bitly::usernamePassword($username, $password);
```

If you have an access token (you can get one from https://bitly.com/a/oauth_apps):

```php
$bitly = Bitly::accessToken($accessToken);
```

If you want to log the user in using OAuth:

```php
$bitly = Bitly::authorize($clientId, $clientSecret, $projectUrl, $state);
```

#### From there you have access to each endpoint:

Return the click rate for content containing 'obama':

```php
$realtimeClickrates = $bitly->shorten('https://github.com/Jleagle/bitly-api-client');
```
