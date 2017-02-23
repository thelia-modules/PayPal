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

use PayPal\Event\PayPalEvents;
use PayPal\Event\PayPalPlanifiedPaymentEvent;
use PayPal\Form\PayPalFormFields;
use PayPal\Form\PayPalPlanifiedPaymentCreateForm;
use PayPal\Form\PayPalPlanifiedPaymentUpdateForm;
use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\PayPal;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Admin\AbstractCrudController;
use Thelia\Core\Security\AccessManager;

/**
 * Class PayPalPlanifiedPaymentController
 * @package PayPal\Controller
 */
class PayPalPlanifiedPaymentController extends AbstractCrudController
{
    /** @var string */
    protected $currentRouter = PayPal::ROUTER;

    /**
     * PayPalPlanifiedPaymentController constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'team',
            'id',
            'order',
            'paypal.back.planified.payment',
            PayPalEvents::PAYPAL_PLANIFIED_PAYMENT_CREATE,
            PayPalEvents::PAYPAL_PLANIFIED_PAYMENT_UPDATE,
            PayPalEvents::PAYPAL_PLANIFIED_PAYMENT_DELETE
        );
    }

    /**
     * The default action is displaying the list.
     *
     * @return Response
     */
    public function defaultAction()
    {
        // Check current user authorization
        if (null !== $response = $this->checkAuth($this->resourceCode, $this->getModuleCode(), AccessManager::VIEW)) {
            return $response;
        }

        return $this->renderList();
    }

    /**
     * Return the creation form for this object
     * @return PayPalPlanifiedPaymentCreateForm
     */
    protected function getCreationForm()
    {
        return $this->createForm(PayPalPlanifiedPaymentCreateForm::FORM_NAME);
    }

    /**
     * Return the update form for this object
     * @return PayPalPlanifiedPaymentUpdateForm
     */
    protected function getUpdateForm()
    {
        return $this->createForm(PayPalPlanifiedPaymentUpdateForm::FORM_NAME);
    }

    /**
     * Hydrate the update form for this object, before passing it to the update template
     *
     * @param PaypalPlanifiedPayment $object
     * @return PayPalPlanifiedPaymentUpdateForm
     */
    protected function hydrateObjectForm($object)
    {
        /** @var \Thelia\Model\Lang $lang */
        $lang = $this->getRequest()->getSession()->get('thelia.current.lang');
        $object->getTranslation($lang->getLocale());

        $data = [
            PayPalFormFields::FIELD_PP_ID => $object->getId(),
            PayPalFormFields::FIELD_PP_TITLE => $object->getTitle(),
            PayPalFormFields::FIELD_PP_DESCRIPTION => $object->getDescription(),
            PayPalFormFields::FIELD_PP_FREQUENCY => $object->getFrequency(),
            PayPalFormFields::FIELD_PP_FREQUENCY_INTERVAL => $object->getFrequencyInterval(),
            PayPalFormFields::FIELD_PP_CYCLE => $object->getCycle(),
            PayPalFormFields::FIELD_PP_MIN_AMOUNT => $object->getMinAmount(),
            PayPalFormFields::FIELD_PP_MAX_AMOUNT => $object->getMaxAmount(),
            PayPalFormFields::FIELD_PP_POSITION => $object->getPosition()
        ];

        return $this->createForm(PayPalPlanifiedPaymentUpdateForm::FORM_NAME, 'form', $data);
    }

    /**
     * Creates the creation event with the provided form data
     *
     * @param mixed $formData
     * @return PayPalPlanifiedPaymentEvent
     */
    protected function getCreationEvent($formData)
    {
        $planifiedPayment = new PaypalPlanifiedPayment();

        $planifiedPayment = $this->fillObjectWithDataForm($planifiedPayment, $formData);

        $planifiedPaymentEvent = new PayPalPlanifiedPaymentEvent($planifiedPayment);

        return $planifiedPaymentEvent;
    }

    /**
     * Creates the update event with the provided form data
     *
     * @param mixed $formData
     * @return PayPalPlanifiedPaymentEvent
     */
    protected function getUpdateEvent($formData)
    {
        if (null === $planifiedPayment = PaypalPlanifiedPaymentQuery::create()->findOneById($formData[PayPalFormFields::FIELD_PP_ID])) {
            throw new \InvalidArgumentException(
                $this->getTranslator()->trans(
                    'Invalid planified payment id : %id',
                    ['%id' => $formData[PayPalFormFields::FIELD_PP_ID]],
                    PayPal::DOMAIN_NAME
                )
            );
        }

        $planifiedPayment = $this->fillObjectWithDataForm($planifiedPayment, $formData);

        $planifiedPaymentEvent = new PayPalPlanifiedPaymentEvent($planifiedPayment);

        return $planifiedPaymentEvent;
    }

    /**
     * @param PaypalPlanifiedPayment $planifiedPayment
     * @param $formData
     * @return PaypalPlanifiedPayment
     */
    protected function fillObjectWithDataForm(PaypalPlanifiedPayment $planifiedPayment, $formData)
    {
        $planifiedPayment
            ->setFrequency($formData[PayPalFormFields::FIELD_PP_FREQUENCY])
            ->setFrequencyInterval($formData[PayPalFormFields::FIELD_PP_FREQUENCY_INTERVAL])
            ->setCycle($formData[PayPalFormFields::FIELD_PP_CYCLE])
            ->setMinAmount($formData[PayPalFormFields::FIELD_PP_MIN_AMOUNT])
            ->setMaxAmount($formData[PayPalFormFields::FIELD_PP_MAX_AMOUNT])
            ->setLocale($formData[PayPalFormFields::FIELD_PP_LOCALE])
            ->setTitle($formData[PayPalFormFields::FIELD_PP_TITLE])
            ->setDescription($formData[PayPalFormFields::FIELD_PP_DESCRIPTION])
        ;

        return $planifiedPayment;
    }

    /**
     * Creates the delete event with the provided form data
     * @return PayPalPlanifiedPaymentEvent
     */
    protected function getDeleteEvent()
    {
        return new PayPalPlanifiedPaymentEvent(
            $this->getExistingObject()
        );
    }

    /**
     * Return true if the event contains the object, e.g. the action has updated the object in the event.
     *
     * @param PayPalPlanifiedPaymentEvent $event
     * @return bool
     */
    protected function eventContainsObject($event)
    {
        return $event->getPayPalPlanifiedPayment() ? true : false;
    }

    /**
     * Get the created object from an event.
     * @param PayPalPlanifiedPaymentEvent $event
     * @return PaypalPlanifiedPayment
     */
    protected function getObjectFromEvent($event)
    {
        return $event->getPayPalPlanifiedPayment();
    }

    /**
     * Load an existing object from the database
     * @return PaypalPlanifiedPayment
     */
    protected function getExistingObject()
    {
        if (null === $planifiedPayment = PaypalPlanifiedPaymentQuery::create()->findOneById((int)$this->getRequest()->get('planifiedPaymentId'))) {
            throw new \InvalidArgumentException(
                $this->getTranslator()->trans('Invalid planified payment id : %id',
                    ['%id' => (int)$this->getRequest()->get('planifiedPaymentId')], PayPal::DOMAIN_NAME)
            );
        }

        return $planifiedPayment;
    }

    /**
     * Returns the object label form the object event (name, title, etc.)
     *
     * @param PaypalPlanifiedPayment $object
     * @return string
     */
    protected function getObjectLabel($object)
    {
        return $object->getTitle();
    }

    /**
     * Returns the object ID from the object
     *
     * @param PaypalPlanifiedPayment $object
     * @return int
     */
    protected function getObjectId($object)
    {
        return $object->getId();
    }

    /**
     * Render the main list template
     *
     * @param mixed $currentOrder , if any, null otherwise.
     * @return Response
     */
    protected function renderListTemplate($currentOrder)
    {
        $this->getListOrderFromSession('planified_payment', 'order', 'manual');

        return $this->render(
            'paypal/planified-payment',
            [
                'order' => $currentOrder,
                'selected_menu' => 'planified'
            ]
        );
    }

    /**
     * Render the edition template
     * @return Response
     */
    protected function renderEditionTemplate()
    {
        return $this->render('paypal/planified-payment-edit', $this->getEditionArguments());
    }

    /**
     * Must return a RedirectResponse instance
     * @return Response
     */
    protected function redirectToEditionTemplate()
    {
        return $this->generateRedirectFromRoute(
            'paypal.admin.configuration.planified.update',
            [],
            $this->getEditionArguments()
        );
    }

    /**
     * Must return a RedirectResponse instance
     * @return Response
     */
    protected function redirectToListTemplate()
    {
        return $this->generateRedirectFromRoute('paypal.admin.configuration.planified');
    }

    /**
     * @return array
     */
    private function getEditionArguments()
    {
        return [
            'planifiedPaymentId' => (int)$this->getRequest()->get('planifiedPaymentId')
        ];
    }
}
