<?php

namespace PayPal\Service;


use PayPal\PayPal;
use PayPal\Service\Base\PayPalBaseService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PayPalApiService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,

    )
    {
    }


    public function sendPostResquest($body, $url)
    {
        $clientId = PayPalBaseService::getLogin();
        $clientSecret = PayPalBaseService::getPassword();

        if (null === $clientId || null === $clientSecret) {
            throw new \Exception("Please configure Paypal in the module configuration");
        }

        $authToken = $this->getAuthToken($clientId, $clientSecret);

       return $this->sendApiRequest(
            'POST',
            $url,
            $authToken,
            $body
        );

    }

    private function getAuthToken($clientId, $clientSecret)
    {
        $response = $this->httpClient->request('POST', PayPal::getBaseUrl().PayPal::PAYPAL_API_AUTH_URL, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'auth_basic' => [$clientId, $clientSecret],
            'body' => ['grant_type' => 'client_credentials']
        ]);

        $content = json_decode($response->getContent(), true);

        return $content['access_token'];
    }

    public function sendApiRequest($method, $url, $authToken, $body)
    {
        $param = [];

        if ($method === 'POST'){
            $param['headers'] = [
                'Content-Type' => 'application/json',
                //'PayPal-Request-Id' => $paypalRequestId,
                'Authorization' => 'Bearer '.$authToken,
            ];

            $param['body'] = json_encode($body);
        }

        return $this->httpClient->request($method, $url, $param);
    }
}