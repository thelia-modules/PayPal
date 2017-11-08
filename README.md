# PayPal

* I)   Install notes
* II)  Configure your PayPal account
* III) Module options payments

## I)  Installation

### Composer

> **WARNING** : A console access is required to update dependencies. If you don't have a console access, please get the latest 2.x version of the module here : https://github.com/thelia-modules/Paypal/tree/2.x

To install the module with Composer, open a console, navigate to the Thelia diorectory and type the following command to add the dependency to Thelia composer.json file.

```
composer require thelia/paypal-module:~3.0.0
```

## II) Configure your PayPal account

- Log In on [developer.paypal.com] (https://developer.paypal.com "developer.paypal.com")
- Create REST API apps [here] (https://developer.paypal.com/developer/applications/ "here")
- Click on Create App
- Fill the fields : App Name & Sandbox developer account
- Click on Create App
- Note the Client ID to use it later in the module configuration
- Note the Client SECRET to use it later in the module configuration

#### In SANDBOX WEBHOOKS
- To fill this part, go to your module configuration page to see the urls to implement

#### In SANDBOX APP SETTINGS
- To fill this part, go to your module configuration page to see the urls to implement


## III) Module options payments

#### Classic PayPal payment
![alt classic paypal payment](https://github.com/thelia-modules/Paypal/blob/master/images/payment_classic.png?raw=true)
- This method will redirect to the PayPal platform to proceed payment

#### InContext Classic PayPal payment
![alt classic paypal payment](https://github.com/thelia-modules/Paypal/blob/master/images/payment_classic_incontext.png?raw=true)
- This method will allow the customer to pay from a PayPal inContext popup directly from your website (no redirection to the PayPal plateform)

#### Credit card
![alt classic paypal payment](https://github.com/thelia-modules/Paypal/blob/master/images/payment_credit_card.png?raw=true)
- This method allow the customer to pay directly by a credit card without a PayPal account. 'The merchant must have a Pro PayPal account UK and the website must be in HTTPS'

#### Recursive payment
![alt classic paypal payment](https://github.com/thelia-modules/Paypal/blob/master/images/payment_recursive.png?raw=true)
- This method use the 'PayPal AGRREMENTS' and allow you to use recursive payments on your website. If you want to log all PayPal actions, you need to configure the PayPal webhooks and to have a wabsite in HTTPS

#### Express checkout
![alt classic paypal payment](https://github.com/thelia-modules/Paypal/blob/master/images/payment_express_checkout.png?raw=true)
- This method allow the customer to proceed the payment directly from the cart from a PayPal inContext popup.
