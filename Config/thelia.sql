
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- paypal_config
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `paypal_config`;

CREATE TABLE `paypal_config`
(
    `name` VARCHAR(255) NOT NULL,
    `value` VARCHAR(255),
    PRIMARY KEY (`name`)
) ENGINE=InnoDB;

INSERT INTO `paypal_config`(name) VALUES
  ("login"),("password"),("signature"),("sandbox"),
  ("login_sandbox"),("password_sandbox"),("signature_sandbox");

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
