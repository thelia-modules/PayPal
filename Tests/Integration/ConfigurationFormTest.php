<?php

declare(strict_types=1);

namespace PayPal\Tests\Integration;

use PayPal\Form\ConfigurationForm;
use PayPal\PayPal;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;
use Thelia\Module\BaseModule;
use Thelia\Test\IntegrationTestCase;

/**
 * A configuration page that does not show the capture mode, as the Smarty back office, keeps the
 * mode the shop chose: saving it never switches a shop that authorizes first back to taking the
 * payment at once.
 */
final class ConfigurationFormTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (new \ReflectionProperty(BaseModule::class, 'moduleIds'))->setValue(null, []);

        if (null === ModuleQuery::create()->findOneByCode(PayPal::getModuleCode())) {
            (new Module())
                ->setCode(PayPal::getModuleCode())
                ->setFullNamespace(PayPal::class)
                ->setVersion('1.0.0')
                ->setType(BaseModule::PAYMENT_MODULE_TYPE)
                ->setCategory('payment')
                ->setActivate(BaseModule::IS_ACTIVATED)
                ->save();
        }
    }

    public function testAPageWithoutTheCaptureModeKeepsTheOneChosen(): void
    {
        PayPal::setConfigValue('capture_mode', PayPal::CAPTURE_MODE_AUTHORIZE);

        self::assertSame(PayPal::CAPTURE_MODE_AUTHORIZE, $this->submitted([])['capture_mode']);
    }

    public function testThePageThatShowsItChangesIt(): void
    {
        PayPal::setConfigValue('capture_mode', PayPal::CAPTURE_MODE_AUTHORIZE);

        self::assertSame(PayPal::CAPTURE_MODE_CAPTURE, $this->submitted(['capture_mode' => PayPal::CAPTURE_MODE_CAPTURE])['capture_mode']);
    }

    /**
     * @param array<string, string> $fields
     *
     * @return array<string, mixed>
     */
    private function submitted(array $fields): array
    {
        $form = $this->getService(TheliaFormFactory::class)
            ->createForm(ConfigurationForm::class, FormType::class, [], ['csrf_protection' => false])
            ->getForm();

        // As a POST of the page submits it: the fields it does not send are missing.
        $form->submit($fields);

        return (array) $form->getData();
    }
}
