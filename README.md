# Magento 2.* plugin for Paylike [![Build Status](https://travis-ci.org/paylike/plugin-magento-2.svg?branch=master)](https://travis-ci.org/paylike/plugin-magento-2)

This plugin is *not* developed or maintained by Paylike but kindly made
available by a user.

Released under the GPL V3 license: https://opensource.org/licenses/GPL-3.0


## Supported Magento versions

[![Last succesfull test](https://log.derikon.ro/api/v1/log/read?tag=magento2&view=svg&label=Magento&key=ecommerce&background=F26322)](https://log.derikon.ro/api/v1/log/read?tag=magento2&view=html)

*The plugin has been tested with most versions of Magento at every iteration. We recommend using the latest version of Magento, but if that is not possible for some reason, test the plugin with your Magento version and it would probably function properly.*


## Automatic installation

  Once you have installed Magento, follow these simple steps:
  1. Signup at [paylike.io](https://paylike.io) (it’s free)
  1. Create a live account
  1. Create an app key for your Magento website
  1. Upload the files trough the extension manager
  1. Activate the module using trough the extension manager.
  1. Insert the app key and your public key in the Payment module settings for the Paylike plugin

## Manual installation

  Once you have installed Magento, follow these simple steps:
  1. Signup at [paylike.io](https://paylike.io) (it’s free)
  1. Create a live account
  1. Create an app key for your Magento website
  1. Download/purchase the extension achive from Magento Marketplace;
  1. Login to your Magento 2.x Hosting site (for details contact your hosting provider);
  1. Open some kind File Manager for listing Hosting files and directories and locate the Magento root directory where Magento 2.x is installed (also can be FTP or Filemanager in CPanel for example);
  1. Unzip the file in temporary directory;
  1. Upload the content of unzipped extension without original folder (only content of unzipped folder);
into the Magneto “app/code/Esparks/Paylike/” folder (create empty folders "code/Esparks/Paylike/"):
  1. Login to your Magento 2.x Hosting site using SSH connection (for details contact our hosting provider)
  1. Run the following commands from root:
    * php bin/magento setup:upgrade
    * composer require paylike/php-api ^1.0.3
    * php bin/magento cache:clean
  1. Now go and open the Magento 2.x Admin panel;
  1. The module should now be auto installed and visible under Stores>>Configuration>>Sales>>Payment Methods, the module will be listed here in "OTHER PAYMENT METHODS" list;
  1. Insert the app key and your public key in the Payment module settings for the Paylike plugin;

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
