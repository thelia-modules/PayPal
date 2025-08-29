<?php

namespace PayPal\Migration;

use ApyUtilities\Model\Migration\AbstractMigration;
use PDO;
use Propel\Runtime\Propel;
use Thelia\Model\ModuleI18n;
use Thelia\Model\ModuleI18nQuery;

/**
 * Migration pour rajouter le module PayPal dans toutes les langues disponibles dans le FO
 * Class Migration20250423104754
 * @package PayPal\Migration
 */
class Migration20250423104754 extends AbstractMigration
{
    const AVAILABLE_LANGUAGES = [
        'cs_CZ',
        'de_DE',
        'en_US',
        'es_ES',
        'fr_FR',
        'it_IT',
        'ru_RU',
    ];

    private $missingLanguages = [];


    /**
     * {@inheritdoc}
     */
    public function checkDuringActivation(): bool
    {
        return $this->needMigration();
    }

    /**
     * {@inheritdoc}
     */
    public function runDuringActivation()
    {
        $this->doRun();
    }

    /**
     * {@inheritdoc}
     */
    public function checkDuringMigration(): bool
    {
        return $this->needMigration();
    }

    /**
     * {@inheritdoc}
     */
    public function runDuringMigration()
    {
        $this->doRun();
    }

    private function doRun()
    {
        // Rajoute le module PayPal dans la langue manquante
        $paypalModuleId = ModuleI18nQuery::create()->findOneByTitle('PayPal');
        if ($paypalModuleId) {
            $moduleId = $paypalModuleId->getId();
            foreach ($this->missingLanguages as $language) {
                /** @var PDO $con */
                $con = Propel::getConnection();

                $sql = <<<SQL
                    INSERT INTO module_i18n (id, locale, title)
                    VALUES ($moduleId , '$language', 'PayPal')
                SQL;

                $con->exec($sql);
            }
        }
    }

    /**
     * @return bool
     */
    private function needMigration(): bool
    {
        foreach (self::AVAILABLE_LANGUAGES as $lang) {
            $PaypalModuleLanguages = ModuleI18nQuery::create()->filterByLocale($lang)
                ->filterByTitle('PayPal')
                ->findOne();

            if (!$PaypalModuleLanguages instanceof ModuleI18n) {
                $this->missingLanguages[] = $lang;
            }
        }

        return !empty($this->missingLanguages);
    }
}
