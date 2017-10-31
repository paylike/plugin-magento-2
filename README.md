# Magento 2.* plugin for Paylike

This plugin is *not* developed or maintained by Paylike but kindly made
available by a user.

Released under the GPL V3 license: https://opensource.org/licenses/GPL-3.0


## Supported Magento versions

- \>= 2.0.0

## Installation

1.Once you have installed Magento, follow these simple steps:
  Signup at (paylike.io) [https://paylike.io] (it’s free)
  
  1. Create a live account
  1. Create an app key for your Magento website
  1. Upload the files to the code folder of your site or trough the extension manager
  1. Activate the module using command line or trough the extension manager.
  1. Insert the app key and your public key in the Payment module settings for the Paylike plugin
  

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
