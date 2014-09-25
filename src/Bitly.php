<?php
namespace Jleagle;

use GuzzleHttp\Client as Guzzle;

class Bitly
{

  private $api = 'https://api-ssl.bitly.com';
  private $accessToken;

  public function __construct($accessToken = null)
  {

  }

  public function oauthAccessToken($username, $password)
  {
    // You can get one here https://bitly.com/a/oauth_apps
    $response = $this->_postRequest('/oauth/access_token', ['auth' =>  [$username, $password]]);

    if ($this->isJson($response))
    {
      $response = json_decode($response);
      throw new \Exception($response['status_code'].': '.$response['status_txt']);
    }

    $this->accessToken = $response;
    return $response;

  }

  private function _getRequest()
  {
    $client = new Guzzle();
    $response = $client->get($this->api);
  }

  private function _postRequest($path, $data = [])
  {
    $client = new Guzzle();
    $response = $client->post($this->api.$path, $data);

    if ($response->getStatusCode() != 200)
    {
      throw new \Exception('Status code not 200');
    }

    return $response->getBody();
  }

  private function isJson($string)
  {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
  }

}
