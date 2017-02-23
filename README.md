# PayPal

* I)   <a href="#i--installation-1">Install notes</a>
* II)  <a href="#ii-how-to-use">How to use</a>
* III) <a href="#iii-integration">Integration</a>

## I)  Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is ```PayPal```.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/paypal-module:~3.0.0
```

## II) How to use

To use the module, you first need to activate it in the back-office, tab Modules, and click on "Configure" on the line
of paypal module. Enter your paypal login informations and save.
Don't forget to do some fake orders in sandbox mode ( check "Active sandbox mode" box in tab Configure sandbox in the
configuration page, and save ).

