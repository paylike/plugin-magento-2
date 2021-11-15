/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/customer-email-validator',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url'
    ],
    function (
      ko,
      $,
      Component,
      quote,
      placeOrderAction,
      customerEmailValidator,
      redirectOnSuccessAction,
      url
    ) {
        'use strict';

        return function displayPopup(callback) {
            var self = this;
console.log(window.checkoutConfig)
            /** Initialize Paylike object. */
            var paylike = Paylike({key: window.checkoutConfig.publicapikey});

            var paylikeConfig = window.checkoutConfig.config;
            var multiplier = window.checkoutConfig.multiplier;
            var grandTotal = parseFloat(quote.totals()['grand_total']);
            var taxAmount = parseFloat(quote.totals()['tax_amount']);
            var totalAmount = grandTotal + taxAmount;

            paylikeConfig.amount.value = Math.round(totalAmount * multiplier);

            /** Change test key value from string 'test' with a boolean value. */
            paylikeConfig.test = ('test' == paylikeConfig.test) ? (true) : (false);

            window.paylikeminoramount = paylikeConfig.amount.value;

            if (quote.guestEmail) {
                paylikeConfig.custom.customer.name = quote.billingAddress()?.['firstname'] + " " + quote.billingAddress()?.['lastname'];
                paylikeConfig.custom.customer.email = quote.guestEmail;
            }

            paylikeConfig.custom.customer.phoneNo = quote.billingAddress()?.telephone;

            paylikeConfig.custom.customer.address = quote.billingAddress()?.street[0] + ", " +
                                                    quote.billingAddress()?.city + ", " +
                                                    quote.billingAddress()?.region + " " +
                                                    quote.billingAddress()?.postcode + ", " +
                                                    quote.billingAddress()?.countryId;

            PaylikeLogger.setContext(paylikeConfig, $, url);

            PaylikeLogger.log("Opening paylike popup");

            paylike.pay(paylikeConfig, callback);
        };
    }
);
