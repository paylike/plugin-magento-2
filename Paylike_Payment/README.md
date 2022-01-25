# Magento 2.* plugin for Paylike [![CircleCI](https://circleci.com/gh/paylike/plugin-magento-2.svg?style=shield)](https://circleci.com/gh/paylike/plugin-magento-2)

This plugin is *not* developed or maintained by Paylike but kindly made
available by a user.

Released under the GPL V3 license: https://opensource.org/licenses/GPL-3.0


## Supported Magento versions

[![Last succesfull test](https://log.derikon.ro/api/v1/log/read?tag=magento2&view=svg&label=Magento&key=ecommerce&background=F26322)](https://log.derikon.ro/api/v1/log/read?tag=magento2&view=html)

Magento version last tested on: 2.4.3-p1

*The plugin has been tested with most versions of Magento at every iteration. We recommend using the latest version of Magento, but if that is not possible for some reason, test the plugin with your Magento version and it would probably function properly.*


## Automatic installation

Once you have installed Magento, follow these simple steps:
  1. Signup at [paylike.io](https://paylike.io) (it’s free);
  2. Create a live account;
  3. Create an app key for your Magento website;
  4. Purchase the extension archive from the Magento Marketplace;
  5. Upload the files trough the Extension Manager;
  6. Activate the module using the Extension Manager;
  7. The module should now be auto installed and visible under "Stores >> Configuration >> Sales >> Payment Methods", the module will be listed here inside the "OTHER PAYMENT METHODS" list;
  8. Insert the app key and your public key in the Payment module settings for the Paylike plugin.

## Manual installation (mode 1)

Once you have installed Magento, follow these simple steps:
  1. Signup at [paylike.io](https://paylike.io) (it’s free);
  2. Create a live account;
  3. Create an app key for your Magento website;
  4. Purchase and download the extension archive from the Magento Marketplace;
  5. Login to your Magento 2.x Hosting site (for details contact your hosting provider);
  6. Open some kind File Manager for listing Hosting files and directories and locate the Magento root directory where Magento 2.x is installed (also can be FTP or Filemanager in CPanel for example);
  7. Unzip the file in a temporary directory;
  8. Upload the content of the unzipped extension without the original folder (only content of unzipped folder) into the Magneto “<MAGENTO_ROOT_FOLDER>/app/code/Esparks/Paylike/” folder (create empty folders "code/Esparks/Paylike/");
  9. Login to your Magento 2.x Hosting site using SSH connection (for details contact our hosting provider);
  10. Run the following commands from the Magento root directory:
    * `php bin/magento setup:upgrade`
    * `composer require paylike/php-api ^1.0.8`
    * `php bin/magento cache:clean`
  11. Open the Magento 2.x Admin panel;
  12. The module should now be auto installed and visible under "Stores >> Configuration >> Sales >> Payment Methods", the module will be listed here inside the "OTHER PAYMENT METHODS" list;
  13. Insert the app key and your public key in the Payment module settings for the Paylike plugin.

## Manual installation (mode 2) (more details here [devdocs.magento.com](https://devdocs.magento.com/extensions/install/))

Once you have installed Magento, follow these simple steps:
  1. Signup at [paylike.io](https://paylike.io) (it’s free);
  2. Create a live account;
  3. Create an app key for your Magento website;
  4. Purchase the extension from the Magento Marketplace;
  5. Login to your Magento 2.x Hosting site using SSH connection (for details contact your hosting provider);
  6. Run the following commands from the Magento root directory (more info in the official documentation):
      - `composer require esparks/module-paylike` (this will also install paylike/php-api ^1.0.8` package specified in composer.json file in this module)
      - `php bin/magento module:enable Esparks_Paylike --clear-static-content`
      - `php bin/magento setup:upgrade`
      - `php bin/magento setup:di:compile`
      - `php bin/magento cache:clean`
  6. Open the Magento 2.x Admin panel;
  7. The module should now be auto installed and visible under "Stores >> Configuration >> Sales >> Payment Methods", the module will be listed here inside the "OTHER PAYMENT METHODS" list;
  8. Insert the app key and your public key in the Payment module settings for the Paylike plugin.

## Updating settings

Under the Magento Paylike payment method settings, you can:
  * Enable/disable the module
  * Update the payment method title in the payment gateways list
  * Change the credit card logos displayed in the description
  * Update the payment method description in the payment gateways list
  * Update the title that shows up in the payment popup
  * Set transaction/payment mode (test/live)
  * Add test/live keys
  * Change the capture mode (Instant/Delayed by changing the order status)
  * Enable sending invoices by email
  * Change new order status
  * Enable payment logs

 ## Upgrading module
  * To update or upgrade the module run following commands:
       - `composer update esparks/module-paylike` (upgrade to latest version)<br>
       or (for eg.)
       - `composer require esparks/module-paylike ^1.3.3` (upgrade to version 1.3.3)

  * After that, run the following commands:
      - `php bin/magento setup:upgrade --keep-generated`
      - `php bin/magento setup:static-content:deploy`
      - `php bin/magento cache:clean`

 ## How to

  1. Capture
      * In Instant mode, the orders are captured automatically
      * In delayed mode you can capture an order by creating an invoice with `Capture Online` Amount status (at the bottom)
  2. Refund
      * To refund an order you can use the `Credit memo` on the invoice.
  3. Void
      * To void an order you can use the `Void` action if the order hasn't been captured. If it has, only refund is available.

  ## Available features

  1. Capture
      * Magento admin panel: full capture
      * Paylike admin panel: full/partial capture
  2. Refund
      * Magento admin panel: full refund
      * Paylike admin panel: full/partial refund
  3. Void
      * Magento admin panel: full void
      * Paylike admin panel: full/partial void