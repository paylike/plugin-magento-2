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
    function (ko,
              $,
              Component,
              quote,
              placeOrderAction,
              customerEmailValidator,
              redirectOnSuccessAction,
              url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Esparks_Paylike/payment/paylikepaymentmethod',
                payliketransactionid: ''
            },

            /** Returns send check to info */
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            getDescription: function () {
                return window.checkoutConfig.description;
            },

            getCardLogos: function () {
                var logosString = window.checkoutConfig.cards;

                if (!logosString) {
                    return '';
                }

                var logos = logosString.split(',');
                var imghtml = "";
                if (logos.length > 0) {
                    for (var i = 0; i < logos.length; i++) {
                        imghtml = imghtml + "<img src='" + window.checkoutConfig.url[i] + "' alt='" + logos[i] + "' width='45'>";
                    }
                }

                return imghtml;
            },

            displayPopup: function () {
                if (!customerEmailValidator.validate()) {
                    return false;
                }

                var self = this;

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
                    paylikeConfig.custom.customer.name = quote.billingAddress()['firstname'] + " " + quote.billingAddress()['lastname'];
                    paylikeConfig.custom.customer.email = quote.guestEmail;
                }

                paylikeConfig.custom.customer.phoneNo = quote.billingAddress().telephone;
                paylikeConfig.custom.customer.address = quote.billingAddress().street[0] + ", " + quote.billingAddress().city + ", " + quote.billingAddress().region + " " + quote.billingAddress().postcode + ", " + quote.billingAddress().countryId;

                PaylikeLogger.setContext(paylikeConfig, $, url);

                PaylikeLogger.log("Opening paylike popup");

                paylike.pay(paylikeConfig, function (err, res) {
                    if (err) {
                        if(err === "closed") {
                          PaylikeLogger.log("Paylike popup closed by user");
                        }

                        return console.warn(err);
                    }

                    if (res.transaction.id !== undefined && res.transaction.id !== "") {
                        self.payliketransactionid = res.transaction.id;
                        PaylikeLogger.log("Paylike payment successfull. Transaction ID: " + res.transaction.id);
                        /*
                          In order to intercept the error of placeOrder request we need to monkey-patch
                          the `addErrorMessage` function of the messageContainer:
                           - first we duplicate the function on the same `messageContainer`, keeping the same `this`
                           - next we override the function with a new one, were we log the error, and then we call the old function
                        */
                        self.messageContainer.oldAddErrorMessage = self.messageContainer.addErrorMessage;
                        self.messageContainer.addErrorMessage = async function (messageObj) {
                          await PaylikeLogger.log("Place order failed. Reason: " + messageObj.message);

                          self.messageContainer.oldAddErrorMessage(message);
                        }

                        /*
                          In order to log the placeOrder success, we need deactivate
                          the redirect after order placed and call it manually, after
                          we send the logs to the server
                        */
                        self.redirectAfterPlaceOrder = false;
                        self.afterPlaceOrder = async function (args) {
                          await PaylikeLogger.log("Order placed successfully");
                          redirectOnSuccessAction.execute();
                        }

                        /* Everything is now setup, we can try placing the order */
                        self.placeOrder();
                    }

                    else {
                        PaylikeLogger.log("No transaction id returned from paylike, order not placed");

                        return false;
                    }
                });
            },

            getCode: function () {
                return this.item.method;
            },

            getData: function () {
                return {
                    "method": this.item.method,
                    'additional_data': {
                        'payliketransactionid': this.payliketransactionid
                    }
                };
            },


        });
    }
);
