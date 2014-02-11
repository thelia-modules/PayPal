<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Quentin Dufour
 * Date: 01/08/13
 * Time: 16:00
 */

namespace Paypal\Classes;

/**
 * Class PayPalVariableRepository
 * Aim to store and get variables from the database.
 */
class PayPalVariableRepository
{

    // API credentials
    CONST VARIABLE_COLUMN_API_USERNAME = 'paypaloffi_configuration_api_username';
    CONST VARIABLE_COLUMN_API_PASSWORD = 'paypaloffi_configuration_api_password';
    CONST VARIABLE_COLUMN_API_SIGNATURE = 'paypaloffi_configuration_api_signature';
    CONST VARIABLE_COLUMN_SANDBOX_API_USERNAME = 'paypaloffi_configuration_sandbox_api_username';
    CONST VARIABLE_COLUMN_SANDBOX_API_PASSWORD = 'paypaloffi_configuration_sandbox_api_password';
    CONST VARIABLE_COLUMN_SANDBOX_API_SIGNATURE = 'paypaloffi_configuration_sandbox_api_signature';
    CONST VARIABLE_COLUMN_MODE_SANDBOX = 'paypaloffi_configuration_mode_sandbox';

    // API value
    CONST VARIABLE_COLUMN_API_CHECKOUT_IPN_URL = 'paypaloffi_configuration_api_checkout_ipn_url';
    CONST VARIABLE_COLUMN_API_REFUND_IPN_URL = 'paypaloffi_configuration_api_refund_ipn_url';
    CONST VARIABLE_COLUMN_API_PAYMENT_TYPE = 'paypaloffi_configuration_api_payment_type';
    CONST VARIABLE_COLUMN_API_ENABLE_PAYMENT_IN_2_CLICS = 'paypaloffi_configuration_api_enable_payment_in_2_clics';
    CONST VARIABLE_COLUMN_API_PAYMENT_DEFAULT_SHIPPING_PRICE = 'paypaloffi_configuration_api_payment_default_shipping_price';
    CONST VARIABLE_COLUMN_API_PAYMENT_DEFAULT_SHIPPING_TYPE = 'paypaloffi_configuration_api_payment_default_shipping_type';

    //CHECKOUT FORM
    //enterprise
    CONST VARIABLE_COLUMN_CHECKOUT_PHONE = 'paypaloffi_checkout_phone';
    CONST VARIABLE_COLUMN_CHECKOUT_CBT = 'paypaloffi_checkout_cbt';
    CONST VARIABLE_COLUMN_CHECKOUT_CUSTOM = 'paypaloffi_checkout_custom';

    //content
    CONST VARIABLE_COLUMN_CHECKOUT_SHOW_BILLING_ADDRESS = 'paypaloffi_checkout_show_billing_address';
    CONST VARIABLE_COLUMN_CHECKOUT_SHOW_BILLING_EMAIL = 'paypaloffi_checkout_show_billing_email';
    CONST VARIABLE_COLUMN_CHECKOUT_SHOW_BILLING_PHONE = 'paypaloffi_checkout_show_billing_phone';
    CONST VARIABLE_COLUMN_CHECKOUT_SHOW_SHIPPING_ADDRESS = 'paypaloffi_checkout_show_shipping_address';
    CONST VARIABLE_COLUMN_CHECKOUT_SHOW_CUSTOMER_NAME = 'paypaloffi_checkout_show_customer_name';
    CONST VARIABLE_COLUMN_CHECKOUT_SHOW_CARD_INFO = 'paypaloffi_checkout_show_card_info';
    CONST VARIABLE_COLUMN_CHECKOUT_SHOW_HOSTED_THANKYOU_PAGE = 'paypaloffi_checkout_show_hosted_thankyou_page';

    //logo
    CONST VARIABLE_COLUMN_CHECKOUT_LOGO_TEXT = 'paypaloffi_checkout_logo_text';
    CONST VARIABLE_COLUMN_CHECKOUT_LOGO_IMAGE = 'paypaloffi_checkout_logo_image';
    CONST VARIABLE_COLUMN_CHECKOUT_LOGO_IMAGE_POSITION = 'paypaloffi_checkout_logo_image_position';
    CONST VARIABLE_COLUMN_CHECKOUT_LOGO_FONT = 'paypaloffi_checkout_logo_font';
    CONST VARIABLE_COLUMN_CHECKOUT_LOGO_FONT_SIZE = 'paypaloffi_checkout_logo_font_size';
    CONST VARIABLE_COLUMN_CHECKOUT_LOGO_FONT_COLOR = 'paypaloffi_checkout_logo_font_color';

    //appearance
    CONST VARIABLE_COLUMN_CHECKOUT_TEMPLATE = 'paypaloffi_checkout_template';
    CONST VARIABLE_COLUMN_CHECKOUT_PAGE_TITLE_TEXT_COLOR = 'paypaloffi_checkout_page_title_text_color';
    CONST VARIABLE_COLUMN_CHECKOUT_PAGE_TITLE_COLLAPSE_BG_COLOR = 'paypaloffi_checkout_page_collapse_bg_color';
    CONST VARIABLE_COLUMN_CHECKOUT_PAGE_TITLE_COLLAPSE_TEXT_COLOR = 'paypaloffi_checkout_page_collapse_text_color';
    CONST VARIABLE_COLUMN_CHECKOUT_PAGE_BUTTON_BG_COLOR = 'paypaloffi_checkout_page_button_bg_color';
    CONST VARIABLE_COLUMN_CHECKOUT_PAGE_BUTTON_TEXT_COLOR = 'paypaloffi_checkout_page_button_text_color';
    CONST VARIABLE_COLUMN_CHECKOUT_SECTIONBORDER = 'paypaloffi_checkout_sectionborder';
    CONST VARIABLE_COLUMN_CHECKOUT_CPP_HEADER_IMAGE = 'paypaloffi_checkout_cpp_header_image';
    CONST VARIABLE_COLUMN_CHECKOUT_SUBHEADER_TEXT = 'paypaloffi_checkout_subheaderText';

    CONST VARIABLE_COLUMN_CHECKOUT_HEADER_HEIGHT = 'paypaloffi_checkout_header_height';
    CONST VARIABLE_COLUMN_CHECKOUT_HEADER_BG_COLOR = 'paypaloffi_checkout_header_bg_color';

    CONST VARIABLE_COLUMN_CHECKOUT_BODY_BG_COLOR = 'paypaloffi_checkout_body_bg_color';
    CONST VARIABLE_COLUMN_CHECKOUT_BODY_BG_IMG = 'paypaloffi_checkout_body_bg_img';
    CONST VARIABLE_COLUMN_CHECKOUT_ORDER_SUMMARY_BG_COLOR = 'paypaloffi_checkout_order_summary_bg_color';
    CONST VARIABLE_COLUMN_CHECKOUT_ORDER_SUMMARY_BG_IMG = 'paypaloffi_checkout_order_summary_bg_img';

    CONST VARIABLE_COLUMN_CHECKOUT_FOOTER_TEXT_COLOR = 'paypaloffi_checkout_footer_text_color';
    CONST VARIABLE_COLUMN_CHECKOUT_FOOTER_TEXTLINK_COLOR = 'paypaloffi_checkout_footer_textlink_color';


    /**
     * Return all available variable keys
     *
     * @return array
     */
    public function getAvailableVariableKeys()
    {
        return array_merge(
            $this->getAvailablePaypalAccountVariableKeys(),
            $this->getAvailablePaypalCustomizationVariableKeys()
        );
    }

    /**
     * Return all Paypal account available variable keys
     *
     * @return array
     */
    public function getAvailablePaypalAccountVariableKeys()
    {
        return array(
            self::VARIABLE_COLUMN_API_USERNAME,
            self::VARIABLE_COLUMN_API_PASSWORD,
            self::VARIABLE_COLUMN_API_SIGNATURE,
            self::VARIABLE_COLUMN_SANDBOX_API_USERNAME,
            self::VARIABLE_COLUMN_SANDBOX_API_PASSWORD,
            self::VARIABLE_COLUMN_SANDBOX_API_SIGNATURE,
            self::VARIABLE_COLUMN_MODE_SANDBOX,
            self::VARIABLE_COLUMN_API_CHECKOUT_IPN_URL,
            self::VARIABLE_COLUMN_API_PAYMENT_TYPE,
            self::VARIABLE_COLUMN_API_ENABLE_PAYMENT_IN_2_CLICS,
            self::VARIABLE_COLUMN_API_PAYMENT_DEFAULT_SHIPPING_PRICE,
            self::VARIABLE_COLUMN_API_PAYMENT_DEFAULT_SHIPPING_TYPE
        );
    }

    /**
     * Return all Paypal customization available variable keys
     *
     * @return array
     */
    public function getAvailablePaypalCustomizationVariableKeys()
    {
        return array(
            self::VARIABLE_COLUMN_CHECKOUT_LOGO_TEXT,
            self::VARIABLE_COLUMN_CHECKOUT_LOGO_IMAGE,
            self::VARIABLE_COLUMN_CHECKOUT_BODY_BG_COLOR,
        );
    }

    /**
     * Set one or more variable from the database
     *
     * @param array $list An array of key value to set in the database
     */
    public function setVariable(array $list)
    {
        $toUpdate = $this->getVariable($list, true);
        $toCreate = $this->toCreate($list, $toUpdate);

        if ($toCreate !== null) {
            $sqlCreate = 'INSERT INTO variable (nom, valeur, cache, protege) VALUES';
            $i = 0;
            foreach ($toCreate as $key => $value) {
                $sqlCreate .= ' (\'' . $this->escape($key) . '\', \'' . $this->escape($list[$key]) . '\' , 1, 1)';
                if (++$i < sizeof($toCreate)) {
                    $sqlCreate .= ',';
                }
            }
            $sqlCreate .= '; ';
            mysql_query($sqlCreate);
        }

        if ($toUpdate !== null) {
            $sqlUpdate = 'UPDATE variable SET valeur = CASE nom';
            foreach ($toUpdate as $key => $value) {
                $sqlUpdate .= ' WHEN \'' . $this->escape($key) . '\' THEN \'' . $this->escape($list[$key]) . '\'';
            }
            $sqlUpdate .= ' ELSE valeur END; ';
            mysql_query($sqlUpdate);
        }
    }

    /**
     * Get one or more variable from the database
     *
     * @param array $list   An array of key value representing keys to be fetched
     * @param bool  $strict If true, only found row will be returned
     *
     * @return array An array of key value with variables.
     */
    public function getVariable(array $list, $strict = false)
    {

        $sql = 'SELECT nom, valeur FROM variable WHERE 0=1';

        foreach ($list as $key => $value) {
            $sql .= ' OR nom=\'' . $key . '\'';
        }

        $databaseResults = mysql_query($sql);

        //Empty the list
        if ($strict) {
            $list = array();
        }

        while ($variable = mysql_fetch_object($databaseResults)) {
            $list[$variable->nom] = $variable->valeur;
        }

        return $list;
    }

    /**
     * Return an array of variable wich must be created
     * <!> We can't use array_diff here.
     *
     * @param array $list
     * @param array $toUpdate
     *
     * @return array
     */
    protected function toCreate($list, $toUpdate)
    {
        $toCreate = array();

        foreach ($list as $key => $value) {
            if (!array_key_exists($key, $toUpdate)) {
                $toCreate[$key] = trim($value);
            }
        }

        return $toCreate;
    }

    /**
     * Escape an entry
     *
     * @param string $entry string to escape
     *
     * @return string escaped string
     */
    protected function escape($entry)
    {
        return mysql_real_escape_string(trim($entry));
    }
}