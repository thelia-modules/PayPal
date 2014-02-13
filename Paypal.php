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

use Thelia\Module\BaseModule;
use Thelia\Model\Order;
use Thelia\Model\ModuleQuery;
use Thelia\Module\PaymentModuleInterface;
use Thelia\Model\Base\ModuleImageQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Tools\Redirect;
use Thelia\Tools\URL;
use Thelia\Install\Database;

/**
 * Class Paypal
 * @package Paypal
 * @author Thelia <info@thelia.net>
 */
class Paypal extends BaseModule implements PaymentModuleInterface
{
    const PAYPAL_MAX_PRODUCTS = 9;
    const PAYPAL_MAX_PRICE = 8000;

    const STATUS_PAID = 2;
    const STATUS_CANCELED = 5;

    public function pay(Order $order)
    {
        Redirect::exec(URL::getInstance()->absoluteUrl("/module/paypal/goto/".$order->getId()));
    }


    /**
     * @param string $type
     * @return string
     */
    public static function getPaypalURL($type,$order_id) {
        $ret="";
        switch($type) {
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
        /** @var Order $order */
        $order = $this->container->get('request')->getSession()->getOrder();
        return $order->getOrderProducts()->count() <= self::PAYPAL_MAX_PRODUCTS &&
            $order->getTotalAmount() < self::PAYPAL_MAX_PRICE;
    }

    public function postActivation(ConnectionInterface $con = null)
    {
        $database = new Database($con->getWrappedConnection());

        $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));

        /* insert the images from image folder if first module activation */
        $module = $this->getModuleModel();
        if(ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
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
    public function getCode() {
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

}
