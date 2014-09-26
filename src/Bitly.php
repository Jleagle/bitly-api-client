<?php
namespace Jleagle;

use GuzzleHttp\Client as Guzzle;

class Bitly
{

  private $api = 'https://api-ssl.bitly.com';
  private $accessToken;

  public function __construct()
  {
  }

  /**
   * @param string $accessToken
   *
   * @return Bitly
   */
  public static function accessToken($accessToken)
  {
    $instance = new self();
    $instance->accessToken = $accessToken;
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
  public static function authorize($clientId, $clientSecret, $redirectUrl, $state = null)
  {
    if (isset($_GET['code']) && $_GET['code'])
    {
      $instance = new self();
      $instance->_getAccessTokenCode($clientId, $clientSecret, $_GET['code'], $redirectUrl);
      return $instance;
    }
    else
    {
      $data = [
        'client_id'    => $clientId,
        'redirect_uri' => $redirectUrl,
        'state'        => $state,
      ];
      header('Location: https://bitly.com/oauth/authorize?'.http_build_query($data));
      exit;
    }
  }

  /**
   * @return Bitly
   */
  public static function noAuth()
  {
    return new self();
  }

  /**
   * Returns a specified number of "high-value" Bitlinks that are popular across bitly at this particular moment.
   *
   * @param int $limit - The maximum number of high-value links to return.
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function highValue($limit = 10)
  {
    $data = [
      'access_token' => $this->accessToken,
      'limit' => $limit,
    ];
    $return = $this->_request('get', '/v3/highvalue', $data);
    return $this->_checkStatusCode($return);
  }

  public function search()
  {

  }

  /**
   * Returns phrases that are receiving an uncharacteristically high volume of click traffic, and the individual links (hashes) driving traffic to pages containing these phrases.
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function realtimeBurstingPhrases()
  {
    $data = [
      'access_token' => $this->accessToken,
    ];
    $return = $this->_request('get', '/v3/realtime/bursting_phrases', $data);
    return $this->_checkStatusCode($return);
  }

  /**
   * Returns phrases that are receiving a consistently high volume of click traffic, and the individual links (hashes) driving traffic to pages containing these phrases.
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function realtimeHotPhrases()
  {
    $data = [
      'access_token' => $this->accessToken,
    ];
    $return = $this->_request('get', '/v3/realtime/hot_phrases', $data);
    return $this->_checkStatusCode($return);
  }

  /**
   * Returns the click rate for content containing a specified phrase.
   *
   * @param string $phrase - the phrase for which you'd like to get the click rate.
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function realtimeClickrate($phrase)
  {
    $data = [
      'access_token' => $this->accessToken,
      'phrase' => $phrase,
    ];
    $return = $this->_request('get', '/v3/realtime/clickrate', $data);
    return $this->_checkStatusCode($return);
  }

  public function linkInfo()
  {

  }

  public function linkContent()
  {

  }

  public function linkCategory()
  {

  }

  public function linkSocial()
  {

  }

  public function linkLocation()
  {

  }

  public function linkLanguage()
  {

  }

  public function expand()
  {

  }

  public function info()
  {

  }

  public function linkLookup()
  {

  }

  public function shorten()
  {

  }

  public function userLinkEdit()
  {

  }

  public function userLinkLookup()
  {

  }

  public function userLinkSave()
  {

  }

  public function userSaveCustomDomainKeyword()
  {

  }

  public function linkClicks()
  {

  }

  public function linkCountries()
  {

  }

  public function linkEncoders()
  {

  }

  public function linkEncodersByCount()
  {

  }

  public function linkEncodersCount()
  {

  }

  public function linkReferrers()
  {

  }

  public function linkReferrersByDomain()
  {

  }

  public function linkReferringDomains()
  {

  }

  public function linkShares()
  {

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

  public function nsqLookup()
  {

  }

  public function nsqStats()
  {

  }

  private function _getAccessTokenUsernamePassword($username, $password)
  {
    $response = $this->_request('post', '/oauth/access_token', null, [$username, $password]);
    if ($this->_isJson($response))
    {
      $response = \GuzzleHttp\json_decode($response);
      throw new \Exception($response->status_code.': '.$response->status_txt);
    }

    $this->accessToken = $response;
  }

  private function _getAccessTokenCode($clientId, $clientSecret, $code, $redirectUrl)
  {
    $data = [
      'client_id'     => $clientId,
      'client_secret' => $clientSecret,
      'code'          => $code,
      'redirect_uri'  => $redirectUrl
    ];
    $response = $this->_request('post', '/oauth/access_token', $data);

    if ($this->_isJson($response))
    {
      // todo - Is this IF needed?
      $response = \GuzzleHttp\json_decode($response);
      throw new \Exception($response->status_code.': '.$response->status_txt);
    }

    parse_str($response, $output);
    $this->accessToken = $output['access_token'];
  }

  private function _request($type = 'get', $path = '', $data = [], $auth = [])
  {
    $client = new Guzzle();

    if ($type == 'get')
    {
      $getData = [
        'query' => $data,
      ];
      $getData['query']['format'] = 'json';
      $response = $client->get($this->api.$path, $getData);
    }
    else
    {
      $postData = [
        'query' => $data,
        'auth'  => $auth,
      ];
      $postData['body']['format'] = 'json';
      $response = $client->post($this->api.$path, $postData);
    }

    if ($response->getStatusCode() != 200)
    {
      throw new \Exception('Status code not 200');
    }

    return (string)$response->getBody();
  }

  private function _isJson($string)
  {
    try
    {
      \GuzzleHttp\json_decode($string);
      return true;
    }
    catch(\InvalidArgumentException $e)
    {
      return false;
    }
  }

  private function _checkStatusCode($data)
  {
    $data = \GuzzleHttp\json_decode($data);
    if ($data->status_code == 200)
    {
      return $data->data;
    }

    throw new \Exception($data->status_txt);
  }

}
