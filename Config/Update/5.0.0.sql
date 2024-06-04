SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- paypal_planified_payment
-- ---------------------------------------------------------------------
    ALTER TABLE paypal_planified_payment
    ADD `paypal_id` VARCHAR(255) NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;