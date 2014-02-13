
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- paypal_config
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `paypal_config`
(
    `name` VARCHAR(255) NOT NULL,
    `value` VARCHAR(255),
    PRIMARY KEY (`name`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;


INSERT IGNORE INTO `paypal_config`(name) VALUES
  ("login"),("password"),("signature"),("sandbox"),
  ("login_sandbox"),("password_sandbox"),("signature_sandbox");

-- ---------------------------------------------------------------------
-- Mail templates for paypal
-- ---------------------------------------------------------------------
-- First, delete existing entries
SET @var := 0;
SELECT @var := `id` FROM `message` WHERE name="payment_confirmation_paypal";
DELETE FROM `message` WHERE `id`=@var;
-- Try if ON DELETE constraint isn't set
DELETE FROM `message_i18n` WHERE `id`=@var;

INSERT INTO `message` (`name`, `secured`, `text_layout_file_name`, `text_template_file_name`, `html_layout_file_name`, `html_template_file_name`, `created_at`, `updated_at`, `version`, `version_created_at`, `version_created_by`) VALUES
('payment_confirmation_paypal', NULL, NULL, NULL, NULL, NULL, '2014-02-13 16:50:03', '2014-02-13 16:53:55', 2, '2014-02-13 16:53:55', NULL);

SET @max := (SELECT MAX(`id`) from `message`);
INSERT INTO `message_i18n` (`id`, `locale`, `title`, `subject`, `text_message`, `html_message`) VALUES
(@max , 'en_US', 'Payment confirmation paypal', 'Paiement de la commande : {$order_ref}', 'Confirmation du paiement de la commande {$order_ref} via Paypal\r\n\r\nVotre facture est disponible dans la rubrique mon compte sur {config key="url_site"}', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>courriel de confirmation de commande de {config key="url_site"} </title>
    {literal}
    <style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size:100%; text-align:center;}#liencompte {margin:15px 0 ; text-align:center; font-size:10pt;}#wrapper {width:480pt;margin:0 auto;}#entete {padding-bottom:20px;margin-bottom:10px;border-bottom:1px dotted #000;}#logotexte {float:left;width:180pt;height:75pt;border:1pt solid #000;font-size:18pt;text-align:center;}#logoimg{float:left;}#h2 {margin:0;padding:0;font-size:140%;text-align:center;}#h3 {margin:0;padding:0;font-size:120%;text-align:center;}#tableprix {margin:0 auto;border-collapse:collapse;font-size:80%;}#intitules {font-weight:bold;text-align:center;}#ref {width:65pt;border:1px solid #000;}#designation {width:278pt;border:1px solid #000;}#pu {width:65pt;border:1px solid #000;}#qte {width:60pt;border:1px solid #000;}.ligneproduit{font-weight:normal;}.cellref{text-align:right;padding-right:6pt;border:1px solid #000;}.celldsg{text-align:left;padding-left:6pt;border:1px solid #000;}.cellpu{text-align:right;padding-right:6pt;border:1px solid #000;}.cellqte{text-align:right;padding-right:6pt;border:1px solid #000;}.lignevide{border-bottom:1px solid #000;}.totauxtitre{text-align:right;padding-right:6pt;border-left:1px solid #000;}.totauxcmdtitre{text-align:right;padding-right:6pt;border-left:1px solid #000;border-bottom:1px solid #000;}.totauxprix{text-align:right;padding-right:6pt;border:1px solid #000;}.blocadresses{display:inline;float:left;width:228pt;margin:12pt 4pt 12pt 5pt;font-size:80%;line-height:18pt;text-align:left;border:1px solid #000;}.stylenom{margin:0;padding:0 0 0 10pt;border-bottom:1px solid #000;}.styleliste{margin:0;padding:0 0 0 10pt;}</style>
    {/literal}
</head>
<body>
<div id="wrapper">
    <div id="entete"><h1 id="logotexte">{config key="store_name"}</h1>
        <h2 id="info">Confirmation du paiement de la commande</h2>
        <h3 id="commande">N&deg; {$order_ref} </h3>
    </div>
    <p id="liencompte">Le suivi de votre commande est disponible dans la rubrique mon compte sur <a href="{config key="url_site"}">{config key="url_site"}</a></p>
</div>
</body>
</html>'),
(@max , 'fr_FR', 'Payment confirmation paypal', 'Paiement de la commande : {$order_ref}', 'Confirmation du paiement de la commande {$order_ref} via Paypal\r\n\r\nVotre facture est disponible dans la rubrique mon compte sur {config key="url_site"}', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>courriel de confirmation de commande de {config key="url_site"} </title>
    {literal}
    <style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size:100%; text-align:center;}#liencompte {margin:15px 0 ; text-align:center; font-size:10pt;}#wrapper {width:480pt;margin:0 auto;}#entete {padding-bottom:20px;margin-bottom:10px;border-bottom:1px dotted #000;}#logotexte {float:left;width:180pt;height:75pt;border:1pt solid #000;font-size:18pt;text-align:center;}#logoimg{float:left;}#h2 {margin:0;padding:0;font-size:140%;text-align:center;}#h3 {margin:0;padding:0;font-size:120%;text-align:center;}#tableprix {margin:0 auto;border-collapse:collapse;font-size:80%;}#intitules {font-weight:bold;text-align:center;}#ref {width:65pt;border:1px solid #000;}#designation {width:278pt;border:1px solid #000;}#pu {width:65pt;border:1px solid #000;}#qte {width:60pt;border:1px solid #000;}.ligneproduit{font-weight:normal;}.cellref{text-align:right;padding-right:6pt;border:1px solid #000;}.celldsg{text-align:left;padding-left:6pt;border:1px solid #000;}.cellpu{text-align:right;padding-right:6pt;border:1px solid #000;}.cellqte{text-align:right;padding-right:6pt;border:1px solid #000;}.lignevide{border-bottom:1px solid #000;}.totauxtitre{text-align:right;padding-right:6pt;border-left:1px solid #000;}.totauxcmdtitre{text-align:right;padding-right:6pt;border-left:1px solid #000;border-bottom:1px solid #000;}.totauxprix{text-align:right;padding-right:6pt;border:1px solid #000;}.blocadresses{display:inline;float:left;width:228pt;margin:12pt 4pt 12pt 5pt;font-size:80%;line-height:18pt;text-align:left;border:1px solid #000;}.stylenom{margin:0;padding:0 0 0 10pt;border-bottom:1px solid #000;}.styleliste{margin:0;padding:0 0 0 10pt;}</style>
    {/literal}
</head>
<body>
<div id="wrapper">
    <div id="entete"><h1 id="logotexte">{config key="store_name"}</h1>
        <h2 id="info">Confirmation du paiement de la commande</h2>
        <h3 id="commande">N&deg; {$order_ref} </h3>
    </div>
    <p id="liencompte">Le suivi de votre commande est disponible dans la rubrique mon compte sur <a href="{config key="url_site"}">{config key="url_site"}</a></p>
</div>
</body>
</html>');