<?php

namespace Derhaeuptling\VimeoApi;

use Contao\Config;
use Contao\System;
use Vimeo\Vimeo;

class Client extends Vimeo
{
    /**
     * Get the Vimeo client
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $accessToken
     *
     * @return Vimeo
     */
    public static function getInstance($clientId = null, $clientSecret = null, $accessToken = null)
    {
        $clientId     = $clientId ?: Config::get('vimeo_clientId');
        $clientSecret = $clientSecret ?: Config::get('vimeo_clientSecret');
        $accessToken  = $accessToken ?: Config::get('vimeo_accessToken');

        return new static($clientId, $clientSecret, $accessToken);
    }

    /**
     * Make an API request to Vimeo.
     *
     * @param string $url    A Vimeo API Endpoint. Should not include the host
     * @param array  $params An array of parameters to send to the endpoint. If the HTTP method is GET, they will be added to the url, otherwise they will be written to the body
     * @param string $method The HTTP Method of the request
     * @param bool   $json_body
     *
     * @return array
     */
    public function request($url, $params = array(), $method = 'GET', $json_body = true)
    {
        // @todo @debug - start
        System::log('Vimeo request to: '.$url, __METHOD__, TL_ERROR);

        if (!$GLOBALS['TMP']) {
            $GLOBALS['TMP'] = 0;
        }

        $GLOBALS['TMP']++;

        if ($GLOBALS['TMP'] > 100) {
            die('request limit');
        }
        // @todo @debug - end

        try {
            $response = parent::request($url, $params, $method, $json_body);
        } catch (\Exception $e) {
            System::log(
                sprintf('Vimeo request (%s) failed with exception: %s', $url, $e->getMessage()),
                __METHOD__,
                TL_ERROR
            );

            return [];
        }

        if ($response['status'] !== 200) {
            System::log(
                sprintf('Vimeo request (%s) failed (code %s): %s', $url, $response['status'], print_r($response, true)),
                __METHOD__,
                TL_ERROR
            );
        }

        // Update the stats
        $this->updateStats($response['headers']);

        return $response;
    }

    /**
     * Update the statistics
     *
     * @param array $headers
     */
    protected function updateStats(array $headers)
    {
        Stats::set(Stats::CURRENT_LIMIT, $headers['X-RateLimit-Remaining']);
        Stats::set(Stats::TOTAL_LIMIT, $headers['X-RateLimit-Limit']);
        Stats::set(Stats::LIMIT_RESET_TIME, strtotime($headers['X-RateLimit-Reset']));
    }
}