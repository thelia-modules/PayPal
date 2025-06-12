<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace PayPal;

use Monolog\Logger;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Model\PaypalCartQuery;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\Service\Base\PayPalBaseService;
use PayPal\Service\PayPalAgreementService;
use PayPal\Service\PayPalLoggerService;
use PayPal\Service\PayPalPaymentService;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Propel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Translation\Translator;
use Thelia\Install\Database;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderStatusQuery;
use Thelia\Module\AbstractPaymentModule;
use Thelia\Tools\URL;

class PayPal extends AbstractPaymentModule
{
    /** @var string */
    const DOMAIN_NAME = 'paypal';
    const ROUTER = 'router.PayPal';

    /**
     * The confirmation message identifier
     */
    const CONFIRMATION_MESSAGE_NAME = 'paypal_payment_confirmation';
    const RECURSIVE_MESSAGE_NAME = 'paypal_recursive_payment_confirmation';

    const CREDIT_CARD_TYPE_VISA = 'visa';
    const CREDIT_CARD_TYPE_MASTERCARD = 'mastercard';
    const CREDIT_CARD_TYPE_DISCOVER = 'discover';
    const CREDIT_CARD_TYPE_AMEX = 'amex';

    const PAYPAL_METHOD_PAYPAL = 'paypal';
    const PAYPAL_METHOD_EXPRESS_CHECKOUT = 'express_checkout';
    const PAYPAL_METHOD_CREDIT_CARD = 'credit_card';
    const PAYPAL_METHOD_PLANIFIED_PAYMENT = 'planified_payment';

    const PAYPAL_PAYMENT_SERVICE_ID = 'paypal_payment_service';
    const PAYPAL_CUSTOMER_SERVICE_ID = 'paypal_customer_service';
    const PAYPAL_AGREEMENT_SERVICE_ID = 'paypal_agreement_service';

    const PAYMENT_STATE_APPROVED = 'approved';
    const PAYMENT_STATE_CREATED = 'created';
    const PAYMENT_STATE_REFUSED = 'refused';

    const PAYPAL_API_BASE_SANDBOX_URL = "https://api-m.sandbox.paypal.com";
    const PAYPAL_API_BASE_URL = "https://api-m.paypal.com";
    const PAYPAL_API_AUTH_URL = "/v1/oauth2/token";
    const PAYPAL_API_CREATE_ORDER_URL = "/v2/checkout/orders";
    const PAYPAL_API_CREATE_PLAN_URL = "/v1/billing/plans";
    const PAYPAL_API_CREATE_PRODUCT_URL = "/v1/catalogs/products";

    /**
     *  Method used by payment gateway.
     *
     *  If this method return a \Thelia\Core\HttpFoundation\Response instance, this response is send to the
     *  browser.
     *
     *  In many cases, it's necessary to send a form to the payment gateway. On your response you can return this form already
     *  completed, ready to be sent
     *
     * @param Order $order
     * @return RedirectResponse
     * @throws \Exception
     */
    public function pay(Order $order)
    {
        return new RedirectResponse(URL::getInstance()->absoluteUrl('/order/paypal/pay', ["order_id" => $order->getId()]));
    }

    /**
     *
     * This method is call on Payment loop.
     *
     * If you return true, the payment method will de display
     * If you return false, the payment method will not be display
     *
     * @return boolean
     */
    public function isValidPayment()
    {
        $isValid = false;

        // Check if total order amount is within the module's limits
        $order_total = $this->getCurrentOrderTotalAmount();

        $min_amount = Paypal::getConfigValue('minimum_amount', 0);
        $max_amount = Paypal::getConfigValue('maximum_amount', 0);

        if (
            ($order_total > 0)
            &&
            ($min_amount <= 0 || $order_total >= $min_amount)
            &&
            ($max_amount <= 0 || $order_total <= $max_amount)
        ) {
            // Check cart item count
            $cartItemCount = $this->getRequest()->getSession()->getSessionCart($this->getDispatcher())->countCartItems();

            if ($cartItemCount <= Paypal::getConfigValue('cart_item_count', 9)) {
                $isValid = true;

                if (PayPalBaseService::isSandboxMode()) {
                    // In sandbox mode, check the current IP
                    $raw_ips = explode("\n", Paypal::getConfigValue('allowed_ip_list', ''));

                    $allowed_client_ips = array();

                    foreach ($raw_ips as $ip) {
                        $allowed_client_ips[] = trim($ip);
                    }

                    $client_ip = $this->getRequest()->getClientIp();

                    $isValid = in_array($client_ip, $allowed_client_ips);
                }
            }
        }

        return $isValid;
    }

    /**
     * if you want, you can manage stock in your module instead of order process.
     * Return false to decrease the stock when order status switch to pay
     *
     * @return bool
     */
    public function manageStockOnCreation()
    {
        return false;
    }

    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $con
     */
    public function postActivation(ConnectionInterface $con = null): void
    {
        $database = new Database($con);
        $database->insertSql(null, array(__DIR__ . "/Config/create.sql"));

        // Setup some default values at first install
        if (null === self::getConfigValue('minimum_amount', null)) {
            self::setConfigValue('minimum_amount', 0);
            self::setConfigValue('maximum_amount', 0);
            self::setConfigValue('send_payment_confirmation_message', 1);
            self::setConfigValue('cart_item_count', 999);
        }

        if (null === MessageQuery::create()->findOneByName(self::CONFIRMATION_MESSAGE_NAME)) {
            $message = new Message();

            $message
                ->setName(self::CONFIRMATION_MESSAGE_NAME)
                ->setHtmlTemplateFileName('paypal-payment-confirmation.html')
                ->setTextTemplateFileName('paypal-payment-confirmation.txt')
                ->setLocale('en_US')
                ->setTitle('Paypal payment confirmation')
                ->setSubject('Payment of order {$order_ref}')
                ->setLocale('fr_FR')
                ->setTitle('Confirmation de paiement par Paypal')
                ->setSubject('Confirmation du paiement de votre commande {$order_ref}')
                ->save()
            ;
        }

        if (null === MessageQuery::create()->findOneByName(self::RECURSIVE_MESSAGE_NAME)) {
            $message = new Message();

            $message
                ->setName(self::RECURSIVE_MESSAGE_NAME)
                ->setHtmlTemplateFileName('paypal-recursive-payment-confirmation.html')
                ->setTextTemplateFileName('paypal-recursive-payment-confirmation.txt')
                ->setLocale('en_US')
                ->setTitle('Paypal payment confirmation')
                ->setSubject('Payment of order {$order_ref}')
                ->setLocale('fr_FR')
                ->setTitle('Confirmation de paiement par Paypal')
                ->setSubject('Confirmation du paiement de votre commande {$order_ref}')
                ->save()
            ;
        }

        /* Deploy the module's image */
        $module = $this->getModuleModel();

        if (ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
            $this->deployImageFolder($module, sprintf('%s/images', __DIR__), $con);
        }
    }

    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $finder = (new Finder())
            ->files()
            ->name('#.*?\.sql#')
            ->sortByName()
            ->in(__DIR__ . DS . 'Config' . DS . 'Update')
        ;

        $database = new Database($con);

        /** @var \Symfony\Component\Finder\SplFileInfo $updateSQLFile */
        foreach ($finder as $updateSQLFile) {
            if (version_compare($currentVersion, str_replace('.sql', '', $updateSQLFile->getFilename()), '<')) {
                $database->insertSql(
                    null,
                    [
                        $updateSQLFile->getPathname()
                    ]
                );
            }
        }
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude(['/I18n/*'])
            ->autowire(true)
            ->autoconfigure(true);
    }

    public static function getBaseUrl()
    {
        if ((bool)PayPal::getConfigValue('sandbox') === true){
            return self::PAYPAL_API_BASE_SANDBOX_URL;
        }

        return self::PAYPAL_API_BASE_URL;
    }
}
