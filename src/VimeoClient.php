<?php

namespace Derhaeuptling\VimeoApi;

use Vimeo\Vimeo;

class VimeoClient extends Vimeo
{
    /**
     * Cache
     * @var VideoCache
     */
    protected $cache;

    protected $reqs = 0;

    /**
     * @param VideoCache $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * Make an API request to Vimeo.
     *
     * @param string $url    A Vimeo API Endpoint. Should not include the host
     * @param array  $params An array of parameters to send to the endpoint. If the HTTP method is GET, they will be added to the url, otherwise they will be written to the body
     * @param string $method The HTTP Method of the request
     * @param bool   $json_body
     *
     * @return array This array contains three keys, 'status' is the status code, 'body' is an object representation of the json response body, and headers are an associated array of response headers
     */
    public function request($url, $params = array(), $method = 'GET', $json_body = true)
    {
        if ($this->reqs++ > 10) {
            die('request limit'); // @todo @debug
        }

        $cacheKey = 'request_'.md5(implode('_', [$url, serialize($params), $method, ($json_body ? 1 : 0)]));

        if ($this->cache->hasData($cacheKey)) {
            return $this->cache->getData($cacheKey);
        }

        $response = parent::request($url, $params, $method, $json_body);
//\System::log(print_r($response, true), __METHOD__, TL_ERROR);exit;
        if ($response['status'] === 200) {
            $this->cache->setData($cacheKey, $response);
        }

        return $response;
    }
}