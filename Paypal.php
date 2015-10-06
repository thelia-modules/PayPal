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

namespace Paypal;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Core\Thelia;
use Thelia\Model\Cart;
use Thelia\Module\BaseModule;
use Thelia\Model\Order;
use Thelia\Model\ModuleQuery;
use Thelia\Module\PaymentModuleInterface;
use Thelia\Model\ModuleImageQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Tools\URL;
use Thelia\Install\Database;

use Thelia\Core\HttpFoundation\Session\Session;

/**
 * Class Paypal
 * @package Paypal
 * @author Thelia <info@thelia.net>
 */
class Paypal extends BaseModule implements PaymentModuleInterface
{
    const PAYPAL_MAX_PRODUCTS = 9;
    const PAYPAL_MAX_PRICE = 8000;

    public function pay(Order $order)
    {
        return RedirectResponse::create(URL::getInstance()->absoluteUrl("/module/paypal/goto/".$order->getId()));
    }

    /**
     * @param  string $type
     * @return string
     */
    public static function getPaypalURL($type,$order_id)
    {
        $ret="";
        switch ($type) {
            case 'cancel':
                $ret=URL::getInstance()->absoluteUrl("/module/paypal/cancel/".$order_id);
                break;
            case 'paiement':
                $ret=URL::getInstance()->absoluteUrl("/module/paypal/ok/".$order_id);
                break;
        }

        return $ret;
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
        /** @var Session $session */
        $session = $this->container->get('request')->getSession();
        /** @var Cart $cart */
        $cart = $session->getSessionCart($this->getDispatcher());
        /** @var \Thelia\Model\Order $order */
        $order = $session->getOrder();
        /** @var \Thelia\TaxEngine\TaxEngine $taxEngine */
        $taxEngine = $this->container->get("thelia.taxengine");
        /** @var \Thelia\Model\Country $country */
        $country = $taxEngine->getDeliveryCountry();

        $item_number = $cart->countCartItems();
        $price = $cart->getTaxedAmount($country) + $order->getPostage();

        $valid = false;

        if ($item_number <= self::PAYPAL_MAX_PRODUCTS) {
            $valid = $this->checkMinMaxAmount();
        }

        return $valid;
    }

    /**
     * Check if total order amount is in the module's limits
     *
     * @return bool true if the current order total is within the min and max limits
     */
    protected function checkMinMaxAmount()
    {
        // Check if total order amount is in the module's limits
        $order_total = $this->getCurrentOrderTotalAmount();

        $min_amount = 0;
        $max_amount = self::PAYPAL_MAX_PRICE;

        return
            $order_total > 0
            &&
            ($min_amount <= 0 || $order_total >= $min_amount) && ($max_amount <= 0 || $order_total <= $max_amount);
    }

    public function preActivation(ConnectionInterface $con = null)
    {
        if (version_compare(Thelia::THELIA_VERSION, '2.1.0', '<')) {
            return false;
        }

        return true;
    }

    public function postActivation(ConnectionInterface $con = null)
    {
        $database = new Database($con->getWrappedConnection());

        $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));

        /* insert the images from image folder if first module activation */
        $module = $this->getModuleModel();
        if (ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
            $this->deployImageFolder($module, sprintf('%s/images', __DIR__), $con);
        }

        /* set module title */
        $this->setTitle(
            $module,
            array(
                "en_US" => "Paypal",
                "fr_FR" => "Paypal",
            )
        );
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return "Paypal";
    }

    /**
     * @return int
     */
    public static function getModCode($flag=false)
    {
        $obj = new Paypal();
        $mod_code = $obj->getCode();
        if($flag) return $mod_code;
        $search = ModuleQuery::create()
            ->findOneByCode($mod_code);

        return $search->getId();
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
}
