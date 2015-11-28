<?php
namespace Jleagle\Bitly;

use Jleagle\Bitly\Enums\TimeUnitEnum;
use Jleagle\Bitly\Enums\TimezoneEnum;
use Jleagle\Bitly\Exceptions\BitlyApiException;
use Jleagle\Bitly\Exceptions\BitlyException;
use Jleagle\CurlWrapper\Curl;
use Jleagle\CurlWrapper\Exceptions\CurlInvalidJsonException;
use Jleagle\CurlWrapper\Response;

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
   * @param string $username
   * @param string $password
   *
   * @return $this
   *
   * @throws BitlyException
   */
  protected function _getAccessTokenUsernamePassword($username, $password)
  {
    $response = $this->_request(
      'post',
      '/oauth/access_token',
      [],
      [$username, $password],
      false
    );

    try
    {
      $array = $response->getJson();
      throw new BitlyException(
        $array['status_code'] . ': ' . $array['status_txt']
      );
    }
    catch(CurlInvalidJsonException $e)
    {
      $this->_accessToken = $response;
      return $this;
    }
  }

  /**
   * @param string $clientId
   * @param string $clientSecret
   * @param string $code
   * @param string $redirectUrl
   *
   * @return $this
   *
   * @throws BitlyException
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

    try
    {
      $array = $response->getJson();
      throw new BitlyException(
        $array['status_code'] . ': ' . $array['status_txt']
      );
    }
    catch(CurlInvalidJsonException $e)
    {
      parse_str($response->getOutput(), $output);
      $this->_accessToken = $output['access_token'];
      return $this;
    }
  }

  /**
   * Returns phrases that are receiving an uncharacteristically high volume of
   * click traffic, and the individual links (hashes) driving traffic to pages
   * containing these phrases.
   *
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * Given a bitly URL or hash (or multiple), returns the target (long) URL.
   *
   * @param string $hash
   *
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
   */
  public function userSaveCustomDomainKeyword(
    $keywordLink, $targetLink, $overwrite = false
  )
  {
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @return array
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
   * @param string $type
   * @param string $path
   * @param array  $data
   * @param array  $auth
   * @param bool   $accessToken
   *
   * @return Response
   *
   * @throws BitlyApiException
   */
  protected function _request(
    $type = 'get', $path = '', $data = [], $auth = [], $accessToken = true
  )
  {
    $data = array_filter($data);
    $data['format'] = 'json';

    if($accessToken)
    {
      $data['access_token'] = $this->_accessToken;
    }

    if($type == 'get')
    {
      $curl = Curl::get(self::API . $path, $data);
    }
    else
    {
      $curl = Curl::post(self::API . $path, $data);
    }

    if($auth && count($auth) == 2)
    {
      $curl->setBasicAuth($auth[0], $auth[1]);
    }

    $response = $curl->run();

    if($response->getHttpCode() != 200)
    {
      throw new BitlyApiException('Something went wrong when talking to Bitly');
    }

    return $response;
  }

  /**
   * @param Response $request
   *
   * @return array
   *
   * @throws BitlyException
   */
  protected function _checkStatusCode(Response $request)
  {
    $array = $request->getJson();

    if($array['status_code'] == 200)
    {
      return $array['data'];
    }

    throw new BitlyException($array['status_txt']);
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
