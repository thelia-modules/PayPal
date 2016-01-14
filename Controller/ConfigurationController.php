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

namespace Paypal\Controller;

use Paypal\Classes\API\PaypalApiLogManager;
use Paypal\Paypal;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Thelia;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;
use Thelia\Tools\Version\Version;

/**
 * Class ConfigurePaypal
 * @package Paypal\Controller
 * @author Thelia <info@thelia.net>
 */
class ConfigurationController extends BaseAdminController
{

    public function downloadLog()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'atos', AccessManager::UPDATE)) {
            return $response;
        }

        $logFilePath = PaypalApiLogManager::getLogFilePath();

        return Response::create(
            @file_get_contents($logFilePath),
            200,
            array(
                'Content-type' => "text/plain",
                'Content-Disposition' => sprintf('Attachment;filename=paypal-log.txt')
            )
        );
    }

    /*
     * Checks paypal.configure || paypal.configure.sandbox form and save config into json file
     */
    public function configure()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Paypal', AccessManager::UPDATE)) {
            return $response;
        }

        $configurationForm = $this->createForm('paypal.form.configure');

        try {
            $form = $this->validateForm($configurationForm, "POST");

            // Get the form field values
            $data = $form->getData();

            foreach ($data as $name => $value) {
                if (is_array($value)) {
                    $value = implode(';', $value);
                }

                Paypal::setConfigValue($name, $value);
            }

            $this->adminLogAppend(
                "paypal.configuration.message",
                AccessManager::UPDATE,
                sprintf("Paypal configuration updated")
            );

            if ($this->getRequest()->get('save_mode') == 'stay') {
                // If we have to stay on the same page, redisplay the configuration page/
                $url = '/admin/module/Paypal';
            } else {
                // If we have to close the page, go back to the module back-office page.
                $url = '/admin/modules';
            }

            return $this->generateRedirect(URL::getInstance()->absoluteUrl($url));
        } catch (FormValidationException $ex) {
            $error_msg = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $error_msg = $ex->getMessage();
        }

        $this->setupFormErrorContext(
            $this->getTranslator()->trans("Paypal configuration", [], Paypal::DOMAIN),
            $error_msg,
            $configurationForm,
            $ex
        );

        // Before 2.2, the errored form is not stored in session
        if (Version::test(Thelia::THELIA_VERSION, '2.2', false, "<")) {
            return $this->render('module-configure', [ 'module_code' => 'Paypal' ]);
        } else {
            return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/Paypal'));
        }
    }
}
