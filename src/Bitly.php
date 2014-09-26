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

  public static function accessToken($accessToken)
  {
    $instance = new self();
    $instance->accessToken = $accessToken;
    return $instance;
  }

  public static function usernamePassword($username, $password)
  {
    $instance = new self();
    $instance->oauthAccessToken($username, $password);
    return $instance;
  }

  public static function redirect()
  {

  }

  public static function authCode()
  {

  }

  public static function noAuth()
  {
    
  }

  public function oauthAccessToken($username, $password)
  {

    if ($this->accessToken)
    {
      return $this->accessToken;
    }

    $response = $this->_request('post', '/oauth/access_token', ['auth' =>  [$username, $password]]);
    if ($this->isJson($response))
    {
      $response = \GuzzleHttp\json_decode($response);
      throw new \Exception($response->status_code.': '.$response->status_txt);
    }

    $this->accessToken = $response;
    return $response;

  }

  public function highValue($limit = 10)
  {
    $data = [
      'query' => [
        'access_token' => $this->accessToken,
        'limit' => $limit
      ]
    ];
    $return = $this->_request('get', '/v3/highvalue', $data);
    return $this->checkStatusCode($return);
  }

  public function search()
  {

  }

  public function realtimeBurstingPhrases()
  {

  }

  public function realtimeHotPhrases()
  {

  }

  public function realtimeClickrate()
  {

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

  private function _request($type = 'get', $path = '', $data = [])
  {
    $client = new Guzzle();

    if ($type == 'get')
    {
      $data['query']['format'] = 'json';
      $response = $client->get($this->api . $path, $data);
    }
    else
    {
      $data['body']['format'] = 'json';
      $response = $client->post($this->api . $path, $data);
    }

    if ($response->getStatusCode() != 200)
    {
      throw new \Exception('Status code not 200');
    }

    return (string)$response->getBody();
  }

  private function isJson($string)
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

  private function checkStatusCode($data)
  {
    $data = \GuzzleHttp\json_decode($data);
    if ($data->status_code == 200)
    {
      return $data->data;
    }

    throw new \Exception($data->status_txt);
  }

}
