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

namespace PayPal\Controller;

use PayPal\Form\ConfigurationForm;
use PayPal\PayPal;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Thelia;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;
use Thelia\Tools\Version\Version;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class ConfigurePaypal
 * @package Paypal\Controller
 */
#[Route('/admin/module/paypal/configure', name: 'paypal_configure')]
class ConfigurationController extends BaseAdminController
{
    /*
     * Checks paypal.configure || paypal.configure.sandbox form and save config into json file
     */
    /**
     * @return mixed|\Symfony\Component\HttpFoundation\Response|\Thelia\Core\HttpFoundation\Response
     */
    #[Route('', name: '_save', methods: 'POST')]
    public function configureAction(RequestStack $requestStack, Translator $translator)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Paypal', AccessManager::UPDATE)) {
            return $response;
        }

        $configurationForm = $this->createForm(ConfigurationForm::getName());

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

            if ($requestStack->getCurrentRequest()->get('save_mode') === 'stay') {
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
            $translator->trans("Paypal configuration", [], PayPal::DOMAIN_NAME),
            $error_msg,
            $configurationForm,
            $ex
        );

        // Before 2.2, the errored form is not stored in session
        if (Version::test(Thelia::THELIA_VERSION, '2.2', false, "<")) {
            return $this->render('module-configure', [ 'module_code' => PayPal::getModuleCode()]);
        } else {
            return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/PayPal'));
        }
    }

    #[Route('/log', name: '_log', methods: ['GET'])]
    public function logAction(\Twig\Environment $twig): \Symfony\Component\HttpFoundation\Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Paypal', AccessManager::VIEW)) {
            return $response;
        }

        $request = $this->getRequest();
        $page = max(1, (int) ($request->query->get('page') ?? 1));
        $limit = max(1, (int) ($request->query->get('limit') ?? 100));

        $query = \PayPal\Model\PaypalLogQuery::create()
            ->orderByCreatedAt(\Propel\Runtime\ActiveQuery\Criteria::DESC);

        $totalCount = (clone $query)->count();
        $logs = $query
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->find();

        return new \Symfony\Component\HttpFoundation\Response(
            $twig->render('@PayPalModule/backOffice/default-twig/paypal/paypal-log.html.twig', [
                'logs' => $logs,
                'page' => $page,
                'limit' => $limit,
                'total_count' => $totalCount,
                'page_count' => (int) ceil($totalCount / $limit),
            ])
        );
    }
}
