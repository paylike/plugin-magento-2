# Magento 2.* plugin for Paylike [![Build Status](https://travis-ci.org/paylike/plugin-magento-2.svg?branch=master)](https://travis-ci.org/paylike/plugin-magento-2)

This plugin is *not* developed or maintained by Paylike but kindly made
available by a user.

Released under the GPL V3 license: https://opensource.org/licenses/GPL-3.0


## Supported Magento versions

[![Last succesfull test](https://log.derikon.ro/api/v1/log/read?tag=magento2&view=svg&label=Magento&key=ecommerce&background=F26322)](https://log.derikon.ro/api/v1/log/read?tag=magento2&view=html)

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

## Manual installation

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
    * php bin/magento setup:upgrade
    * composer require paylike/php-api ^1.0.3
    * php bin/magento cache:clean
  11. Open the Magento 2.x Admin panel;
  12. The module should now be auto installed and visible under "Stores >> Configuration >> Sales >> Payment Methods", the module will be listed here inside the "OTHER PAYMENT METHODS" list;
  13. Insert the app key and your public key in the Payment module settings for the Paylike plugin.

## Updating settings

Under the Magento Paylike payment method settings, you can:
  * Update the payment method text in the payment gateways list
  * Change the logos displayed in the description
  * Update the payment method description in the payment gateways list
  * Update the title that shows up in the payment popup
  * Add test/live keys
  * Set payment mode (test/live)
  * Change the capture type (Instant/Manual by changing the order status)

 ## How to

  1. Capture
    * In Instant mode, the orders are captured automatically
    * In delayed mode you can capture an order by creating an invoice
  2. Refund
    * To refund an order you can use the credit memo on the invoice.
  3. Void
    * To void an order you can use the void action if the order hasn't been captured. If it has only refund is available.
