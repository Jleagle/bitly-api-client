<?php
namespace Jleagle\Bitly;

use GuzzleHttp\Client as Guzzle;
use Jleagle\Bitly\Enums\TimeUnitEnum;
use Jleagle\Bitly\Enums\TimezoneEnum;

class Bitly
{
  const API = 'https://api-ssl.bitly.com';

  protected $_accessToken;

  /**
   * @param string $accessToken
   *
   * @return Bitly
   */
  public static function accessToken($accessToken)
  {
    $instance = new self();
    $instance->_accessToken = $accessToken;
    return $instance;
  }

  /**
   * @param string $username
   * @param string $password
   *
   * @return Bitly
   * @throws \Exception
   */
  public static function usernamePassword($username, $password)
  {
    $instance = new self();
    $instance->_getAccessTokenUsernamePassword($username, $password);
    return $instance;
  }

  /**
   * @param string $clientId
   * @param string $clientSecret
   * @param string $redirectUrl
   * @param string $state
   *
   * @return Bitly
   * @throws \Exception
   */
  public static function authorize(
    $clientId, $clientSecret, $redirectUrl, $state = null
  )
  {
    if(isset($_GET['code']) && $_GET['code'])
    {
      $instance = new self();
      $instance->_getAccessTokenCode(
        $clientId,
        $clientSecret,
        $_GET['code'],
        $redirectUrl
      );
      return $instance;
    }
    else
    {
      $data = [
        'client_id'    => $clientId,
        'redirect_uri' => $redirectUrl,
        'state'        => $state,
      ];

      $url = 'https://bitly.com/oauth/authorize?' . http_build_query($data);
      header('Location: ' . $url);
      exit;
    }
  }

  /**
   * Returns a specified number of "high-value" Bitlinks that are popular
   * across bitly at this particular moment.
   *
   * @param int $limit
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function highValue($limit = 10)
  {
    $data = [
      'limit' => $limit,
    ];
    $return = $this->_request('get', '/v3/highvalue', $data);
    return $this->_checkStatusCode($return);
  }

  /**
   * Search links receiving clicks across bitly by content, language, location,
   * and more.
   *
   * @param string   $query
   * @param int      $limit
   * @param int      $offset
   * @param string   $lang
   * @param string   $cities
   * @param string   $domain
   * @param string   $fullDomain
   * @param string[] $fields
   *
   * @return \stdClass[]
   */
  public function search(
    $query, $limit = 10, $offset = 0, $lang = null, $cities = null,
    $domain = null, $fullDomain = null, array $fields = null
  )
  {
    if(is_array($fields))
    {
      $fields = implode(',', $fields);
    }

    $data = [
      'query'       => $query,
      'limit'       => $limit,
      'offset'      => $offset,
      'lang'        => $lang,
      'cities'      => $cities,
      'domain'      => $domain,
      'full_domain' => $fullDomain,
      'fields'      => $fields,
    ];
    $return = $this->_request('get', '/v3/search', $data);
    return $this->_checkStatusCode($return)->results;
  }

  /**
   * Returns phrases that are receiving an uncharacteristically high volume of
   * click traffic, and the individual links (hashes) driving traffic to pages
   * containing these phrases.
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function realtimeBurstingPhrases()
  {
    $return = $this->_request('get', '/v3/realtime/bursting_phrases');
    return $this->_checkStatusCode($return);
  }

  /**
   * Returns phrases that are receiving a consistently high volume of click
   * traffic, and the individual links (hashes) driving traffic to pages
   * containing these phrases.
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function realtimeHotPhrases()
  {
    $return = $this->_request('get', '/v3/realtime/hot_phrases');
    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the click rate for content containing a specified phrase.
   *
   * @param string $phrase
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function realtimeClickrate($phrase)
  {
    $data = [
      'phrase' => $phrase,
    ];

    $return = $this->_request('get', '/v3/realtime/clickrate', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns metadata about a single Bitlink.
   *
   * @param string $link
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkInfo($link)
  {
    $data = [
      'link' => $link,
    ];

    $return = $this->_request('get', '/v3/link/info', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the “main article” from the linked page, as determined by the
   * content extractor, in either HTML or plain text format.
   *
   * @param string $link
   * @param string $contentType
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkContent($link, $contentType = 'html')
  {
    $data = [
      'link'         => $link,
      'content_type' => $contentType,
    ];

    $return = $this->_request('get', '/v3/link/content', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the detected categories for a document, in descending order of
   * confidence.
   *
   * @param string $link
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkCategory($link)
  {
    $data = [
      'link' => $link,
    ];

    $return = $this->_request('get', '/v3/link/category', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the "social score" for a specified Bitlink. Note that the social
   * score are highly dependent upon activity (clicks) occurring on the Bitlink.
   * If there have not been clicks on a Bitlink within the last 24 hours, it is
   * possible a social score for that link does not exist.
   *
   * @param string $link
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkSocial($link)
  {
    $data = [
      'link' => $link,
    ];

    $return = $this->_request('get', '/v3/link/social', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the significant locations for the Bitlink or None if locations do
   * not exist. Note that locations are highly dependent upon activity (clicks)
   * occurring on the Bitlink. If there have not been clicks on a Bitlink within
   * the last 24 hours, it is possible that location data for that link does not
   * exist.
   *
   * @param string $link
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkLocation($link)
  {
    $data = [
      'link' => $link,
    ];

    $return = $this->_request('get', '/v3/link/location', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the significant languages for the Bitlink. Note that languages are
   * highly dependent upon activity (clicks) occurring on the Bitlink. If there
   * have not been clicks on a Bitlink within the last 24 hours, it is possible
   * that language data for that link does not exist.
   *
   * @param string $link
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkLanguage($link)
  {
    $data = [
      'link' => $link,
    ];

    $return = $this->_request('get', '/v3/link/language', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Given a bitly URL or hash (or multiple), returns the target (long) URL.
   *
   * @param string $hash
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function expand($hash)
  {
    $hash = $this->_getHashFromUrl($hash);

    $data = [
      'hash' => $hash,
    ];

    $return = $this->_request('get', '/v3/expand', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * This is used to return the page title for a given Bitlink.
   *
   * @param string $hash
   * @param bool   $expandUser
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function info($hash, $expandUser = null)
  {
    $hash = $this->_getHashFromUrl($hash);

    $data = [
      'hash'        => $hash,
      'expand_user' => $expandUser ? 'true' : 'false',
    ];

    $return = $this->_request('get', '/v3/info', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * This is used to query for a Bitlink based on a long URL.
   *
   * @param string $url
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkLookup($url)
  {
    $data = [
      'url' => $url,
    ];

    $return = $this->_request('get', '/v3/link/lookup', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Given a long URL, returns a bitly short URL.
   *
   * @param string $longUrl
   * @param string $domain - Use the ShortenDomainEnum enum
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function shorten($longUrl, $domain = null)
  {
    $data = [
      'longUrl' => $longUrl,
      'domain'  => $domain,
    ];

    $return = $this->_request('get', '/v3/shorten', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * @param string $link
   * @param array  $fields - An array with UserLinkEditEnum values as keys
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function userLinkEdit($link, array $fields)
  {
    $data = [
      'link' => $link,
      'edit' => implode(',', array_keys($fields)),
    ];

    $data = array_merge($data, $fields);
    $return = $this->_request('get', '/v3/user/link_edit', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * This is used to query for a Bitlink shortened by the authenticated user
   * based on a long URL.
   *
   * @param string $url
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function userLinkLookup($url)
  {
    $data = [
      'url' => $url,
    ];

    $return = $this->_request('get', '/v3/user/link_lookup', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Saves a long URL as a Bitlink in a user's history, with optional pre-set
   * metadata. (Also returns a short URL for that link.)
   *
   * @param string $url
   * @param string $title
   * @param string $note
   * @param bool   $private
   * @param int    $userTimestamp
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function userLinkSave(
    $url, $title = null, $note = null, $private = null, $userTimestamp = null
  )
  {
    $data = [
      'url'     => $url,
      'title'   => $title,
      'note'    => $note,
      'private' => $private ? 'true' : 'false',
      'user_ts' => $userTimestamp,
    ];

    $return = $this->_request('get', '/v3/user/link_save', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Save a Custom Bitlink for a custom short domain.
   *
   * @param string $keywordLink
   * @param string $targetLink
   * @param bool   $overwrite
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function userSaveCustomDomainKeyword(
    $keywordLink, $targetLink, $overwrite = false
  )
  {
    // todo - make nice error codes, check docs
    $data = [
      'keyword_link' => $keywordLink,
      'target_link'  => $targetLink,
      'overwrite'    => $overwrite ? 'true' : 'false',
    ];

    $return = $this->_request(
      'get',
      '/v3/user/save_custom_domain_keyword',
      $data
    );

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the number of clicks on a single Bitlink.
   *
   * @param string $link
   * @param string $unit     - Use the TimeUnitEnum enum
   * @param int    $units
   * @param string $timezone - Use the TimezoneEnum enum
   * @param bool   $rollup
   * @param int    $limit
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkClicks(
    $link, $unit = TimeUnitEnum::DAY, $units = -1,
    $timezone = TimezoneEnum::GMT, $rollup = false, $limit = 100
  )
  {
    $data = [
      'link'     => $link,
      'unit'     => $unit,
      'units'    => $units,
      'timezone' => $timezone,
      'rollup'   => $rollup ? 'true' : 'false',
      'limit'    => $limit,
    ];

    $return = $this->_request('get', '/v3/link/clicks', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns metrics about the countries referring click traffic to a single
   * Bitlink.
   *
   * @param string $link
   * @param string $unit     - Use the TimeUnitEnum enum
   * @param int    $units
   * @param string $timezone - Use the TimezoneEnum enum
   * @param int    $limit
   * @param int    $unitReferenceTs
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkCountries(
    $link, $unit = TimeUnitEnum::DAY, $units = -1,
    $timezone = TimezoneEnum::GMT, $limit = 100, $unitReferenceTs = null
  )
  {
    $data = [
      'link'              => $link,
      'unit'              => $unit,
      'units'             => $units,
      'timezone'          => $timezone,
      'limit'             => $limit,
      'unit_reference_ts' => $unitReferenceTs,
    ];

    $return = $this->_request('get', '/v3/link/countries', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns users who have encoded this long URL (optionally only those in the
   * requesting user's social graph).
   *
   * @param string $link
   * @param bool   $myNetwork
   * @param bool   $subAccounts
   * @param int    $limit
   * @param bool   $expandUser
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkEncoders(
    $link, $myNetwork = false, $subAccounts = false, $limit = 10,
    $expandUser = false
  )
  {
    $data = [
      'link'        => $link,
      'my_network'  => $myNetwork ? 'true' : 'false',
      'subaccounts' => $subAccounts ? 'true' : 'false',
      'limit'       => $limit,
      'expand_user' => $expandUser ? 'true' : 'false',
    ];

    $return = $this->_request('get', '/v3/link/encoders', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns users who have encoded this link (optionally only those in the
   * requesting user's social graph), sorted by the number of clicks on each
   * encoding user's link.
   *
   * @param string $link
   * @param bool   $myNetwork
   * @param bool   $subAccounts
   * @param int    $limit
   * @param bool   $expandUser
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkEncodersByCount(
    $link, $myNetwork = false, $subAccounts = false, $limit = 10,
    $expandUser = false
  )
  {
    $data = [
      'link'        => $link,
      'my_network'  => $myNetwork ? 'true' : 'false',
      'subaccounts' => $subAccounts ? 'true' : 'false',
      'limit'       => $limit,
      'expand_user' => $expandUser ? 'true' : 'false',
    ];

    $return = $this->_request('get', '/v3/link/encoders_by_count', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the number of users who have shortened (encoded) a single Bitlink.
   *
   * @param string $link
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkEncodersCount($link)
  {
    $data = [
      'link' => $link,
    ];

    $return = $this->_request('get', '/v3/link/encoders_count', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns metrics about the pages referring click traffic to a single
   * Bitlink.
   *
   * @param string $link
   * @param string $unit     - Use the TimeUnitEnum enum
   * @param int    $units
   * @param string $timezone - Use the TimezoneEnum enum
   * @param int    $limit
   * @param int    $unitReferenceTs
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkReferrers(
    $link, $unit = TimeUnitEnum::DAY, $units = -1,
    $timezone = TimezoneEnum::GMT, $limit = 100, $unitReferenceTs = null
  )
  {
    $data = [
      'link'              => $link,
      'unit'              => $unit,
      'units'             => $units,
      'timezone'          => $timezone,
      'limit'             => $limit,
      'unit_reference_ts' => $unitReferenceTs,
    ];

    $return = $this->_request('get', '/v3/link/referrers', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns metrics about the pages referring click traffic to a single
   * Bitlink, grouped by referring domain.
   *
   * @param string $link
   * @param string $unit     - Use the TimeUnitEnum enum
   * @param int    $units
   * @param string $timezone - Use the TimezoneEnum enum
   * @param int    $limit
   * @param int    $unitReferenceTs
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkReferrersByDomain(
    $link, $unit = TimeUnitEnum::DAY, $units = -1,
    $timezone = TimezoneEnum::GMT, $limit = 100, $unitReferenceTs = null
  )
  {
    $data = [
      'link'              => $link,
      'unit'              => $unit,
      'units'             => $units,
      'timezone'          => $timezone,
      'limit'             => $limit,
      'unit_reference_ts' => $unitReferenceTs,
    ];

    $return = $this->_request('get', '/v3/link/referrers_by_domain', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns metrics about the domains referring click traffic to a single
   * Bitlink.
   *
   * @param string $link
   * @param string $unit     - Use the TimeUnitEnum enum
   * @param int    $units
   * @param string $timezone - Use the TimezoneEnum enum
   * @param int    $limit
   * @param int    $unitReferenceTs
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkReferringDomains(
    $link, $unit = TimeUnitEnum::DAY, $units = -1,
    $timezone = TimezoneEnum::GMT, $limit = 100, $unitReferenceTs = null
  )
  {
    $data = [
      'link'              => $link,
      'unit'              => $unit,
      'units'             => $units,
      'timezone'          => $timezone,
      'limit'             => $limit,
      'unit_reference_ts' => $unitReferenceTs,
    ];

    $return = $this->_request('get', '/v3/link/referring_domains', $data);

    return $this->_checkStatusCode($return);
  }

  /**
   * Returns metrics about a shares of a single link.
   *
   * @param string $link
   * @param string $unit     - Use the TimeUnitEnum enum
   * @param int    $units
   * @param string $timezone - Use the TimezoneEnum enum
   * @param bool   $rollup
   * @param int    $limit
   * @param int    $unitReferenceTs
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function linkShares(
    $link, $unit = TimeUnitEnum::DAY, $units = -1,
    $timezone = TimezoneEnum::GMT, $rollup = false, $limit = 100,
    $unitReferenceTs = null
  )
  {
    $data = [
      'link'              => $link,
      'unit'              => $unit,
      'units'             => $units,
      'timezone'          => $timezone,
      'rollup'            => $rollup ? 'true' : 'false',
      'limit'             => $limit,
      'unit_reference_ts' => $unitReferenceTs,
    ];

    $return = $this->_request('get', '/v3/link/shares', $data);

    return $this->_checkStatusCode($return);
  }

  public function oauthApp()
  {
  }

  public function userInfo()
  {
  }

  public function userLinkHistory()
  {
  }

  public function userNetworkHistory()
  {
  }

  public function userTrackingDomainList()
  {
  }

  public function userClicks()
  {
  }

  public function userCountries()
  {
  }

  public function userPopularEarnedByClicks()
  {
  }

  public function userPopularEarnedByShortens()
  {
  }

  public function userPopularLinks()
  {
  }

  public function userPopularOwnedByClicks()
  {
  }

  public function userPopularOwnedByShortens()
  {
  }

  public function userReferrers()
  {
  }

  public function userReferringDomains()
  {
  }

  public function userShareCounts()
  {
  }

  public function userShareCountsByShareType()
  {
  }

  public function userShortenCounts()
  {
  }

  public function organizationBrandMessages()
  {
  }

  public function organizationClicks()
  {
  }

  public function organizationIntersectingLinks()
  {
  }

  public function organizationLeaderboard()
  {
  }

  public function organizationMissedOpportunities()
  {
  }

  public function organizationPopularLinks()
  {
  }

  public function organizationShortenCounts()
  {
  }

  public function bundleArchive()
  {
  }

  public function bundleBundlesByUser()
  {
  }

  public function bundleClone()
  {
  }

  public function bundleCollaboratorAdd()
  {
  }

  public function bundleCollaboratorRemove()
  {
  }

  public function bundleContents()
  {
  }

  public function bundleCreate()
  {
  }

  public function bundleEdit()
  {
  }

  public function bundleLinkAdd()
  {
  }

  public function bundleLinkCommentAdd()
  {
  }

  public function bundleLinkCommentEdit()
  {
  }

  public function bundleLinkCommentRemove()
  {
  }

  public function bundleLinkEdit()
  {
  }

  public function bundleLinkRemove()
  {
  }

  public function bundleLinkReorder()
  {
  }

  public function bundlePendingCollaboratorRemove()
  {
  }

  public function bundleReorder()
  {
  }

  public function bundleViewCount()
  {
  }

  public function userBundleHistory()
  {
  }

  public function bitlyProDomain()
  {
  }

  public function userTrackingDomainClicks()
  {
  }

  public function userTrackingDomainShortenCounts()
  {
  }

  public function nsqStats()
  {
  }

  /**
   * @param string $username
   * @param string $password
   *
   * @throws \Exception
   */
  protected function _getAccessTokenUsernamePassword($username, $password)
  {
    $response = $this->_request(
      'post',
      '/oauth/access_token',
      null,
      [$username, $password],
      false
    );
    if($this->_isJson($response))
    {
      $response = json_decode($response);
      throw new \Exception(
        $response->status_code . ': ' . $response->status_txt
      );
    }

    $this->_accessToken = $response;
  }

  /**
   * @param string $clientId
   * @param string $clientSecret
   * @param string $code
   * @param string $redirectUrl
   *
   * @throws \Exception
   */
  protected function _getAccessTokenCode(
    $clientId, $clientSecret, $code, $redirectUrl
  )
  {
    $data = [
      'client_id'     => $clientId,
      'client_secret' => $clientSecret,
      'code'          => $code,
      'redirect_uri'  => $redirectUrl
    ];
    $response = $this->_request(
      'post',
      '/oauth/access_token',
      $data,
      [],
      false
    );

    if($this->_isJson($response))
    {
      // todo - Is this IF needed?
      $response = json_decode($response);
      throw new \Exception(
        $response->status_code . ': ' . $response->status_txt
      );
    }

    parse_str($response, $output);
    $this->_accessToken = $output['access_token'];
  }

  /**
   * @param string $type
   * @param string $path
   * @param array  $data
   * @param array  $auth
   * @param bool   $accessToken
   *
   * @return string
   * @throws \Exception
   */
  protected function _request(
    $type = 'get', $path = '', $data = [], $auth = [], $accessToken = true
  )
  {
    $client = new Guzzle();

    $data = array_filter($data);

    if($accessToken)
    {
      $data['access_token'] = $this->_accessToken;
    }

    if($type == 'get')
    {
      $getData = [
        'query' => $data,
      ];
      $getData['query']['format'] = 'json';
      $response = $client->get(self::API . $path, $getData);
    }
    else
    {
      $postData = [
        'query' => $data,
        'auth'  => $auth,
      ];
      $postData['body']['format'] = 'json';
      $response = $client->post(self::API . $path, $postData);
    }

    if($response->getStatusCode() != 200)
    {
      throw new \Exception('Status code not 200');
    }

    return (string)$response->getBody();
  }

  /**
   * @param string $string
   *
   * @return bool
   */
  protected function _isJson($string)
  {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
  }

  /**
   * @param array $data
   *
   * @return \stdClass
   * @throws \Exception
   */
  protected function _checkStatusCode($data)
  {
    $data = json_decode($data);
    if($data->status_code == 200)
    {
      return $data->data;
    }

    throw new \Exception($data->status_txt);
  }

  /**
   * @param string $url
   *
   * @return mixed
   */
  protected function _getHashFromUrl($url)
  {
    $hash = explode('/', $url);

    return end($hash);
  }
}
