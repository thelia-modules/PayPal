# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- paypal_customer
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_customer`
(
    `id` INTEGER NOT NULL,
    `paypal_user_id` INTEGER NOT NULL,
    `credit_card_id` VARCHAR(40),
    `name` VARCHAR(255),
    `given_name` VARCHAR(255),
    `family_name` VARCHAR(255),
    `middle_name` VARCHAR(255),
    `picture` VARCHAR(255),
    `email_verified` TINYINT,
    `gender` VARCHAR(255),
    `birthday` VARCHAR(255),
    `zoneinfo` VARCHAR(255),
    `locale` VARCHAR(255),
    `language` VARCHAR(255),
    `verified` TINYINT,
    `phone_number` VARCHAR(255),
    `verified_account` VARCHAR(255),
    `account_type` VARCHAR(255),
    `age_range` VARCHAR(255),
    `payer_id` VARCHAR(255),
    `postal_code` VARCHAR(255),
    `locality` VARCHAR(255),
    `region` VARCHAR(255),
    `country` VARCHAR(255),
    `street_address` VARCHAR(255),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`,`paypal_user_id`),
    CONSTRAINT `fk_paypal_payer_customer_id`
        FOREIGN KEY (`id`)
        REFERENCES `customer` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- paypal_planified_payment
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_planified_payment`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `frequency` VARCHAR(255) NOT NULL,
    `frequency_interval` INTEGER NOT NULL,
    `cycle` INTEGER NOT NULL,
    `min_amount` DECIMAL(16,6) DEFAULT 0.000000,
    `max_amount` DECIMAL(16,6) DEFAULT 0.000000,
    `position` INTEGER DEFAULT 0 NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- paypal_cart
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_cart`
(
    `id` INTEGER NOT NULL,
    `credit_card_id` VARCHAR(40),
    `planified_payment_id` INTEGER,
    `express_payment_id` VARCHAR(255),
    `express_payer_id` VARCHAR(255),
    `express_token` VARCHAR(255),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `FI_paypal_cart_planified_payment_id` (`planified_payment_id`),
    CONSTRAINT `fk_paypal_cart_cart_id`
        FOREIGN KEY (`id`)
        REFERENCES `cart` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_paypal_cart_planified_payment_id`
        FOREIGN KEY (`planified_payment_id`)
        REFERENCES `paypal_planified_payment` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- paypal_order
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_order`
(
    `id` INTEGER NOT NULL,
    `payment_id` VARCHAR(50),
    `agreement_id` VARCHAR(255),
    `credit_card_id` VARCHAR(40),
    `state` VARCHAR(20),
    `amount` DECIMAL(16,6) DEFAULT 0.000000,
    `description` LONGTEXT,
    `payer_id` VARCHAR(255),
    `token` VARCHAR(255),
    `planified_title` VARCHAR(255) NOT NULL,
    `planified_description` LONGTEXT,
    `planified_frequency` VARCHAR(255) NOT NULL,
    `planified_frequency_interval` INTEGER NOT NULL,
    `planified_cycle` INTEGER NOT NULL,
    `planified_actual_cycle` INTEGER DEFAULT 0 NOT NULL,
    `planified_min_amount` DECIMAL(16,6) DEFAULT 0.000000,
    `planified_max_amount` DECIMAL(16,6) DEFAULT 0.000000,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    `version` INTEGER DEFAULT 0,
    `version_created_at` DATETIME,
    `version_created_by` VARCHAR(100),
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_paypal_order_order_id`
        FOREIGN KEY (`id`)
        REFERENCES `order` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- paypal_plan
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_plan`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `paypal_order_id` INTEGER NOT NULL,
    `plan_id` VARCHAR(255),
    `state` VARCHAR(255),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `FI_paypal_plan_paypal_order_id` (`paypal_order_id`),
    CONSTRAINT `fk_paypal_plan_paypal_order_id`
        FOREIGN KEY (`paypal_order_id`)
        REFERENCES `paypal_order` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- paypal_log
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_log`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `customer_id` INTEGER,
    `order_id` INTEGER,
    `hook` VARCHAR(255),
    `channel` VARCHAR(255),
    `level` INTEGER,
    `message` LONGTEXT,
    `time` INTEGER,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `FI_paypal_log_customer_id` (`customer_id`),
    INDEX `FI_paypal_log_order_id` (`order_id`),
    CONSTRAINT `fk_paypal_log_customer_id`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_paypal_log_order_id`
        FOREIGN KEY (`order_id`)
        REFERENCES `order` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- paypal_planified_payment_i18n
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_planified_payment_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT,
    PRIMARY KEY (`id`,`locale`),
    CONSTRAINT `paypal_planified_payment_i18n_FK_1`
        FOREIGN KEY (`id`)
        REFERENCES `paypal_planified_payment` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- paypal_order_version
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_order_version`
(
    `id` INTEGER NOT NULL,
    `payment_id` VARCHAR(50),
    `agreement_id` VARCHAR(255),
    `credit_card_id` VARCHAR(40),
    `state` VARCHAR(20),
    `amount` DECIMAL(16,6) DEFAULT 0.000000,
    `description` LONGTEXT,
    `payer_id` VARCHAR(255),
    `token` VARCHAR(255),
    `planified_title` VARCHAR(255) NOT NULL,
    `planified_description` LONGTEXT,
    `planified_frequency` VARCHAR(255) NOT NULL,
    `planified_frequency_interval` INTEGER NOT NULL,
    `planified_cycle` INTEGER NOT NULL,
    `planified_actual_cycle` INTEGER DEFAULT 0 NOT NULL,
    `planified_min_amount` DECIMAL(16,6) DEFAULT 0.000000,
    `planified_max_amount` DECIMAL(16,6) DEFAULT 0.000000,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    `version` INTEGER DEFAULT 0 NOT NULL,
    `version_created_at` DATETIME,
    `version_created_by` VARCHAR(100),
    `id_version` INTEGER DEFAULT 0,
    PRIMARY KEY (`id`,`version`),
    CONSTRAINT `paypal_order_version_FK_1`
        FOREIGN KEY (`id`)
        REFERENCES `paypal_order` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
