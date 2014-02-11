<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/6/13
 * Time: 3:19 PM
 *
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */
namespace Paypal\Classes\NVP\Operations;

/**
 * Class NvpOperationInterface
 */
interface PaypalNvpOperationInterface
{
    /**
     * Generate NVP request message
     *
     * @return string
     */
    public function getRequest();

    /**
     * Get Operation Name
     *
     * @return string Operation name
     */
    public function getOperationName();

}
