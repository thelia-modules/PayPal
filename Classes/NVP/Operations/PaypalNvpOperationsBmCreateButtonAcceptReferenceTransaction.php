<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/5/13
 * Time: 5:36 PM
 *
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */
namespace Paypal\Classes\NVP\Operations;
use Paypal\Classes\API\PaypalApiCredentials;
/**
 * Class NvpBmCreateButton
 * Manage NVP BMCreateButtonOperation Accept Reference Transaction Operation
 */
class PaypalNvpOperationsBmCreateButtonAcceptReferenceTransaction extends PaypalNvpOperationsBase
{
    /**
     * {@inheritdoc}
     * @param PaypalApiCredentials $credentials
     * @param $buttonCode
     * @param $buttonType
     * @param array $payload
     */
    public function __construct(
        PaypalApiCredentials $credentials = null,
        $buttonCode = PaypalNvpOperationsBmCreateButton::BUTTON_CODE_ENCRYPTED,
        $buttonType = PaypalNvpOperationsBmCreateButton::BUTTON_TYPE_AUTOBILLING,
        array $payload = null
    )
    {
        $this->operationName = 'BMCreateButton';
        if ($credentials ===null) {
            $this->credentials = new PaypalApiCredentials(
                new PayPalVariableRepository()
            );
        } else {
            $this->credentials = $credentials;
        }

        $this->buttonCode = $buttonCode;
        $this->buttonType = $buttonType;
        $this->payload = $payload;
    }




}
