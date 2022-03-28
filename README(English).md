# Addi/Payment

Addi Payment Gateway for Magento 2.


## Installation

```bash    
composer require addi/magento2-payment:1.0.*
php bin/magento module:enable addi_Payment --clear-static-content
php bin/magento setup:upgrade
php bin/magento cache:clean
```


## Update

```bash
composer clear-cache
composer update addi/magento2-payment
bin/magento setup:upgrade
php bin/magento cache:clean
```

## Module Settings – Admin Panel

To configure the module from the merchant's administration panel, go to the section: **Stores > Configuration > Sales > Payment Methods > Addi**

![magento-admin](https://user-images.githubusercontent.com/90288747/137793232-5cee48f1-5706-4022-aee9-25cfc439a2a0.png)


## Module Options (ADDI)

![addi-section](https://user-images.githubusercontent.com/90288747/137793224-cd73cd0c-946d-4d23-bac9-468b6e095c02.png)

- Activated (Dropdown): **Yes** or **No.**
- Title: Non-modifiable text field.
- New order status: Status **“Pending”** by default and not modifiable.
- Countries that apply: Field cannot be modified.
- Countries that apply: **Colombia** or **Brazil**.
- Development Environment: **Yes** or **No**.
- Customer identifier.
- Client's secret identifier.
- Order to display: Display order of the payment method at checkout.

![addi-configurations](https://user-images.githubusercontent.com/90288747/137793219-2c39f534-94d2-46f0-91a2-ef267b2b00d2.png)

## Addi on the checkout page

Once the module is installed in Magento, we proceed to carry out different purchase flows from the store. You can create an account or make the purchase flow as a guest. For the development of this module, the native content provided by Magento was used, considering simple, configurable or virtual products.

## Review and Payments

You'll see the payment method in the Checkout view -
Review of Payments as long as the minimum amount and
maximum available in the shopping cart is in accordance with
the amounts established for each country.

**Colombia:** Minimum Quantity 100000, Maximum Quantity
2000000

**Brasil:** Minimum Quantity 150, Maximum Quantity 3000

![checkout-addi-payment](https://user-images.githubusercontent.com/90288747/137793231-bc3591d0-e75e-402e-a9f1-8c60368fe0d7.png)

**Discount:** It will display a discount label as long as Addi has previously authorized the discount to the merchant that is carrying out the integration of the payment method.

![addi-checkout-discount](https://user-images.githubusercontent.com/90288747/137793218-4fd32205-a361-43ae-9261-0f2e9c81f658.png)

## Order - Administration Panel

The order will be created with the status "Pending payment", once Addi approves the credit, the status will change to "Processing" or if it is not approved it will be displayed under the status "Canceled".

If the credit was approved, through the order detail
will display the following data: **Addi order ID,
Addi App ID, Approved Amount of
Addi, Addi Currency, Addi State.** If the credit
was not approved or an error was generated the fields are
will display empty.

![addi-orders-grid](https://user-images.githubusercontent.com/90288747/137793222-fc744e31-9c8a-4a48-8812-13ce3442f97e.png)

![addi-order-detail](https://user-images.githubusercontent.com/90288747/137793220-69ea67e5-9d5f-49a9-b637-0e1a145ede7b.png)

![addi-order-detail2](https://user-images.githubusercontent.com/90288747/137794787-1164e32a-4dc6-41a7-9d93-94a24290b835.png)

## Addi Widget and Modal – Product Detail

You'll see the Widget and Modal through the product detail view.

![addi-widget-product-detail](https://user-images.githubusercontent.com/90288747/137793228-3af97602-d42c-4cd5-a93e-fce85712e08d.png)


![addi-widget-product-detail2](https://user-images.githubusercontent.com/90288747/137793229-8681691c-20eb-4c8f-8d4c-f94a110da4e7.png)


In the admin panel, click the **Widget Styles tab
Configuration.**

![addi-widget-configurations](https://user-images.githubusercontent.com/90288747/137793226-26f66d9a-ac69-4ba0-b7fd-775fad6ccc34.png)

**Widget Active:** Yes or No

If you wish, you can edit or not the look & feel of the widget so that it is
better fit your business, consider the following fields:

**Border Colour:** black

**Border Radius:** 5px

**Font Colour:** black

**Font Family:** system-ui

**Font Size:** 14px

**Addi icon Background color:** black

**Widget Background color:** transparent

**Margin:** 8px 0px

**Addi Blank Logo:** Yes or No

In the admin panel, click the **Modal tab
Styles Configuration.**

If you wish, you can edit or not the look & feel of the modal so that it is
better fit that of your business, consider the following fields of
the **Modal Styles Configuration:** tab

![addi-widget-configurations2](https://user-images.githubusercontent.com/90288747/137793227-47d123dd-85b1-4121-9bd1-f6e9d22f0d16.png)


**Background Color:** #eee

**Font Color:** black

**Font Family:** system-ui

**Price Color:** #3c65ec

**Banner Background Color:** #4cbd99

**Banner Font Color:** white

**Modal Background Color:** white

**Modal Button Border Color:** #4cbd99

**Button Border Radius:** 5px

**Button Background Color:** transparent

**Button Font Color:** #4cbd99 


## **Questions and comments** ##

If you have any questions about this product or the implementation
contact soporte_aliado@addi.com to obtain
help.
