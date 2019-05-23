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
        'Magento_Checkout/js/action/place-order'
    ],
    function (ko,
              $,
              Component,
              quote,
              placeOrderAction) {
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
                var logos = window.checkoutConfig.cards.split(',');
                var imghtml = "";
                if (logos.length > 0) {
                    for (var i = 0; i < logos.length; i++) {
                        imghtml = imghtml + "<img src='" + window.checkoutConfig.url[i] + "' alt='" + logos[i] + "' width='45'>";
                    }
                }

                return imghtml;
            },

            displayPopup: function () {
                var self = this;
                var paylike = Paylike(window.checkoutConfig.publicapikey);
                var paylikeConfig = window.checkoutConfig.config;
                var multiplier = window.checkoutConfig.multiplier;
                var grandTotal = quote.totals()['grand_total'];
                var taxAmount = quote.totals()['tax_amount'];
                var totalAmount = grandTotal + taxAmount;
                paylikeConfig.amount = Math.ceil(totalAmount * multiplier);
                window.paylikeminoramount = paylikeConfig.amount;
                if (quote.guestEmail) {
                    paylikeConfig.custom.customer.name = quote.billingAddress()['firstname'] + " " + quote.billingAddress()['lastname'];
                    paylikeConfig.custom.customer.email = quote.guestEmail;
                }
                paylikeConfig.custom.customer.phoneNo = quote.billingAddress().telephone;
                paylikeConfig.custom.customer.address = quote.billingAddress().street[0] + ", " + quote.billingAddress().city + ", " + quote.billingAddress().region + " " + quote.billingAddress().postcode + ", " + quote.billingAddress().countryId;
                paylike.popup(paylikeConfig, function (err, res) {
                    if (err)
                        return console.warn(err);

                    if (res.transaction.id !== undefined && res.transaction.id !== "") {
                        self.payliketransactionid = res.transaction.id;
                        console.log("transaction id: ", self.payliketransactionid);
                        self.placeOrder();
                    }

                    else {
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
