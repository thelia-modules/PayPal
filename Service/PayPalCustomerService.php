<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace PayPal\Service;

use Monolog\Logger;
use PayPal\Api\OpenIdSession;
use PayPal\Api\OpenIdTokeninfo;
use PayPal\Api\OpenIdUserinfo;
use PayPal\Model\PaypalCustomer;
use PayPal\Model\PaypalCustomerQuery;
use PayPal\Service\Base\PayPalBaseService;
use Thelia\Core\Security\SecurityContext;

/**
 * Class PayPalCustomerService
 * @package PayPal\Service
 */
class PayPalCustomerService
{
    /** @var SecurityContext */
    protected $securityContext;

    /**
     * PayPalService constructor.
     * @param SecurityContext $securityContext
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @param $authorizationCode
     * @return OpenIdUserinfo
     * @throws \Exception
     */
    public function getUserInfoWithAuthorizationCode($authorizationCode)
    {
        try {
            $accessToken = OpenIdTokeninfo::createFromAuthorizationCode(
                ['code' => $authorizationCode],
                null,
                null,
                PayPalBaseService::getApiContext()
            );

            return $this->getUserInfo($accessToken->getAccessToken());
        } catch (\Exception $ex) {
            PayPalLoggerService::log($ex->getMessage(), [], Logger::ERROR);
            throw $ex;
        }
    }

    /**
     * @param $accessToken
     * @return OpenIdUserinfo
     */
    public function getUserInfo($accessToken)
    {
        $params = array('access_token' => $accessToken);
        $userInfo = OpenIdUserinfo::getUserinfo($params, PayPalBaseService::getApiContext());

        return $userInfo;
    }

    /**
     * @return PaypalCustomer
     */
    public function getCurrentPayPalCustomer()
    {
        $payPalCustomer = new PaypalCustomer();

        if (null !== $customer = $this->securityContext->getCustomerUser()) {

            $payPalCustomer = PaypalCustomerQuery::create()->findOneById($customer->getId());

        }

        return $payPalCustomer;
    }

    /**
     * @param $refreshToken
     * @return OpenIdTokeninfo
     * @throws \Exception
     */
    public function generateAccessTokenFromRefreshToken($refreshToken)
    {
        try {
            $tokenInfo = new OpenIdTokeninfo();
            $tokenInfo = $tokenInfo->createFromRefreshToken(['refresh_token' => $refreshToken], PayPalBaseService::getApiContext());

            return $tokenInfo;
        } catch (\Exception $ex) {
            PayPalLoggerService::log($ex->getMessage(), [], Logger::ERROR);
            throw $ex;
        }
    }

    /**
     * @param $refreshToken
     * @return OpenIdUserinfo
     * @throws \Exception
     */
    public function getUserInfoWithRefreshToken($refreshToken)
    {
        try {
            $tokenInfo = $this->generateAccessTokenFromRefreshToken($refreshToken);

            return $this->getUserInfo($tokenInfo->getAccessToken());
        } catch (\Exception $ex) {
            PayPalLoggerService::log($ex->getMessage(), [], Logger::ERROR);
            throw $ex;
        }
    }

    /**
     * @return string
     */
    public function getUrlToRefreshToken()
    {
        //Get Authorization URL returns the redirect URL that could be used to get user's consent
        $redirectUrl = OpenIdSession::getAuthorizationUrl(
            'http://25b3ee89.ngrok.io/',
            [
                'openid',
                'profile',
                'address',
                'email',
                'phone',
                'https://uri.paypal.com/services/paypalattributes',
                'https://uri.paypal.com/services/expresscheckout',
                'https://uri.paypal.com/services/invoicing'
            ],
            null,
            null,
            null,
            PayPalBaseService::getApiContext()
        );

        return $redirectUrl;
    }
}
