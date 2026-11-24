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


    /**
     * @param string|null $requestId sent as PayPal-Request-Id, so that PayPal answers a retried call
     *                               with the outcome of the first one instead of doing it twice
     */
    public function sendPostResquest($body, $url, ?string $requestId = null)
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
            $body,
            $requestId
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

    public function sendApiRequest($method, $url, $authToken, $body, ?string $requestId = null)
    {
        $param = [];

        if ($method === 'POST'){
            $param['headers'] = [
                'Content-Type' => 'application/json',
                //'PayPal-Request-Id' => $paypalRequestId,
                'Authorization' => 'Bearer '.$authToken,
            ];

            if (null !== $requestId) {
                $param['headers']['PayPal-Request-Id'] = $requestId;
            }

            $param['body'] = json_encode($body);
        }

        return $this->httpClient->request($method, $url, $param);
    }
}