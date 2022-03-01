/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'mage/url'
    ],
    function (
            $,
            quote,
            customer,
            url
            ) {
        'use strict';

        return function displayPopup (callback) {

            /** Initialize Paylike object. */
            var paylike = Paylike({key: window.checkoutConfig.publicapikey});

            var paylikeConfig = window.checkoutConfig.config;
            var checkoutQuoteData = window.checkoutConfig.quoteData;
            var checkoutCustomerData = window.checkoutConfig.customerData;

            var multiplier = window.checkoutConfig.multiplier;
            var grandTotal = parseFloat(checkoutQuoteData.grand_total);

            paylikeConfig.amount.value = Math.round(grandTotal * multiplier);
            window.paylikeminoramount = paylikeConfig.amount.value;


            /** Change test key value from string 'test' with a boolean value. */
            paylikeConfig.test = ('test' == paylikeConfig.test) ? (true) : (false);

            quote.guestEmail = window.checkoutConfig.customerData.email;
            /** Need to be logged in to perform checkout with multishipping. */
            customer.setIsLoggedIn(window.checkoutConfig.isCustomerLoggedIn);

            var customerAddresses = checkoutCustomerData.addresses;

            /** Get billing address from customer addresses. */
            var billingAddress = '';

            for (var key in customerAddresses){
                if (customerAddresses[key].default_billing) {
                    var billingAddress = customerAddresses[key];
                }
            }

            if (!customer.isLoggedIn) {
                paylikeConfig.custom.customer.name = billingAddress.firstname + " " + billingAddress.lastname;
                paylikeConfig.custom.customer.email = quote.guestEmail;
            }

            paylikeConfig.custom.customer.phoneNo = billingAddress.telephone;
            paylikeConfig.custom.customer.address = billingAddress.street[0] + ", " +
                                                    billingAddress.city + ", " +
                                                    billingAddress.region.region + " " +
                                                    billingAddress.postcode + ", " +
                                                    billingAddress.country_id;

            PaylikeLogger.setContext(paylikeConfig, $, url);

            PaylikeLogger.log("Opening paylike popup");

            paylike.pay(paylikeConfig, callback);
        };

    }
);