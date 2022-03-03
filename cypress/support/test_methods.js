/// <reference types="cypress" />

'use strict';

import { PaylikeTestHelper } from './test_helper.js';

export var TestMethods = {

    /** Admin & frontend user credentials. */
    StoreUrl: (Cypress.env('ENV_ADMIN_URL').match(/^(?:http(?:s?):\/\/)?(?:[^@\n]+@)?(?:www\.)?([^:\/\n?]+)/im))[0],
    AdminUrl: Cypress.env('ENV_ADMIN_URL'),
    RemoteVersionLogUrl: Cypress.env('REMOTE_LOG_URL'),

    /** Construct some variables to be used bellow. */
    ShopName: 'magento2',
    PaylikeName: 'paylike',
    PaymentMethodsAdminUrl: '/admin/system_config/edit/section/payment/',
    OrdersPageAdminUrl: '/sales/order/',

    /**
     * Login to admin backend account
     */
    loginIntoAdminBackend() {
        cy.loginIntoAccount('input[name="login[username]"]', 'input[name="login[password]"]', 'admin');
        // cy.wait(5000);
    },
    /**
     * Login to client|user frontend account
     */
    loginIntoClientAccount() {
        cy.loginIntoAccount('input[name="login[username]"]', 'input[name="login[password]"]', 'client');
    },

    /**
     * Modify Paylike settings
     * @param {String} captureMode
     */
    changePaylikeCaptureMode(captureMode) {
        /** Go to Paylike payment method. */
        cy.goToPage(this.PaymentMethodsAdminUrl);

        /** Check if Paylike section is not visible then click. */
        cy.get('#row_payment_us_paylikepaymentmethod a').then(($paymentMethodRow) => {
            if(!$paymentMethodRow.hasClass('open')) {
                $paymentMethodRow.trigger('click');
            }
        });

        /** Select capture mode. */
        cy.selectOptionContaining('#payment_us_paylikepaymentmethod_capture_mode', captureMode)

        /** Save. */
        cy.get('button[id=save]').click();
    },

    /**
     * Make payment with specified currency and process order
     *
     * @param {String} currency
     * @param {String} paylikeAction
     * @param {Boolean} partialAmount
     */
     payWithSelectedCurrency(currency, paylikeAction, partialAmount = false) {
        /** Make an instant payment. */
        it(`makes a Paylike payment with "${currency}"`, () => {
            this.makePaymentFromFrontend(currency);
        });

        /** Process last order from admin panel. */
        it(`process (${paylikeAction}) an order from admin panel`, () => {
            this.processOrderFromAdmin(paylikeAction, partialAmount);
        });
    },

    /**
     * Make an instant payment
     * @param {String} currency
     */
    makePaymentFromFrontend(currency) {
        /**
         * Go to specific product page.
         */
        cy.goToPage(this.StoreUrl + '/fusion-backpack.html', {timeout: 20000});

        this.changeShopCurrency(currency);

        /** Wait for page to load. */
        cy.wait(5000);

        cy.get('#product-addtocart-button', {timeout: 4000}).click();

        /** Go to shipping step. */
        cy.goToPage(this.StoreUrl + '/checkout', {timeout: 20000});

        /** Wait for page to load. */
        cy.wait(30000);

        /** Make sure that shipping method is selected. */
        cy.get('input[value=flatrate_flatrate]').click();
        /** Go next. */
        cy.get('.button > span').click();

        /** Wait for page to load. */
        cy.wait(10000);

        /** Choose Paylike. */
        cy.get(`input[value*=${this.PaylikeName}]`).click();

        /** Wait the price to refresh. */
        cy.wait(15000);

        /** Check amount. */
        cy.get('td[data-th="Order Total"] > strong > span.price').then($grandTotal => {
            var expectedAmount = PaylikeTestHelper.filterAndGetAmountInMinor($grandTotal, currency);
            cy.window().then($win => {
                expect(expectedAmount).to.eq(Number($win.checkoutConfig.config.amount.value));
            });
        });

        /** Show paylike popup. */
        cy.get(':nth-child(5) > div.primary > .action').click();

        /**
         * Fill in Paylike popup.
         */
         PaylikeTestHelper.fillAndSubmitPaylikePopup();

        cy.wait(2000);

        cy.get('h1 > span.base').should('contain', 'Thank you for your purchase!');
    },

    /**
     * Process last order from admin panel
     * @param {String} paylikeAction
     * @param {Boolean} partialAmount
     */
    processOrderFromAdmin(paylikeAction, partialAmount = false) {
        /** Go to admin orders page. */
        cy.goToPage(this.OrdersPageAdminUrl);

        /** Wait to load orders. */
        cy.wait(20000);

        /** Set position relative on toolbars. */
        PaylikeTestHelper.setPositionRelativeOn('header.page-header.row');
        PaylikeTestHelper.setPositionRelativeOn('div.sticky-header');
        PaylikeTestHelper.setPositionRelativeOn('tr[data-bind="foreach: {data: getVisible(), as: \'$col\'}"]');
        PaylikeTestHelper.setPositionRelativeOn('.admin__data-grid-header');
        PaylikeTestHelper.setPositionRelativeOn('.page-main-actions');
        PaylikeTestHelper.setPositionRelativeOn('div[data-ui-id="page-actions-toolbar-content-header"]');

        cy.wait(20000);

        /** Click on first (latest in time) order from orders table. */
        cy.get('tr.data-row').first().click();

        /**
         * Take specific action on order
         */
        this.paylikeActionOnOrderAmount(paylikeAction, partialAmount);
    },

    /**
     * Capture an order amount
     * @param {String} paylikeAction
     * @param {Boolean} partialAmount
     */
     paylikeActionOnOrderAmount(paylikeAction, partialAmount = false) {
        switch (paylikeAction) {
            case 'capture':
                cy.get('button[data-ui-id="sales-order-ready-for-pickup-order-invoice-button"]').click();
                cy.get('select[name="invoice[capture_case]"]').select('online');
                cy.get('button[data-ui-id="order-items-submit-button"]').click();
                break;
            case 'refund':
                cy.get('#sales_order_view_tabs_order_invoices').click();
                /** Wait to load invoices. */
                cy.wait(20000);
                cy.get('a[href*="/order_invoice/view/invoice_id/"]').first().click();
                cy.get('button[data-ui-id="sales-invoice-view-credit-memo-button"]').click();
                /** Keep partial amount. */
                if (partialAmount) {
                    /**
                     * Put 8 major units to be subtracted from amount.
                     * Premise: any product must have price >= 8.
                     */
                    cy.get('input[name="creditmemo[adjustment_negative]"]').clear().type(8);
                    cy.get('button[data-ui-id="update-totals-button"]').click();
                }
                /** Submit. */
                cy.get('button[data-ui-id="order-items-submit-button"]').click();
                break;
            case 'void':
                cy.get('button[data-ui-id="sales-order-ready-for-pickup-void-payment-button"]').click();
                cy.get('button.action-primary.action-accept').should('be.visible').click();
                break;
        }

        /** Check if success message. */
        cy.get('div[data-ui-id="messages-message-success"]').should('be.visible');
    },

    /**
     * Change shop currency in frontend
     */
    changeShopCurrency(currency) {
        cy.get('#switcher-currency-trigger').then($actualCurrency => {
            /** Check if currency is not already selected, then select it. */
            if (!$actualCurrency.text().includes(currency)) {
                /**
                 * We need to "refresh" form_key cookie to be able to change currency
                 * The cookie is set at the beginning, but not refresh during
                 * frontend/backend page switch
                 * (we get "invalid form key" error when click on selected currency)
                 * Then, we collect form_key from DOM, and set to cookie again
                 */
                let formKey = Cypress.$('input[name=form_key]').first().val();
                cy.setCookie('form_key', formKey)

                Cypress.$('#switcher-currency ul').attr('style', 'display: block;');
                cy.get(`#switcher-currency li.currency-${currency} a`).click();
            }
        });
    },

    /**
     * Get Shop & Paylike versions and send log data.
     */
    logVersions() {
        /** Go to payment methods page. */
        cy.goToPage(this.PaymentMethodsAdminUrl);

        /** Get framework version. */
        cy.get('p.magento-version').then($footerVersion => {
            var footerVersion = ($footerVersion.text()).replace('Magento', '');
            var frameworkVersion = footerVersion.replace('ver. ', '');
            cy.wrap(frameworkVersion).as('frameworkVersion');
        });

        /** Get Paylike version. */
        cy.get('.paylike-version').invoke('attr', 'class').then($pluginVersion => {
            var $pluginVersion = ($pluginVersion).replace(/[^0-9.]/g, '');
            cy.wrap($pluginVersion).as('pluginVersion');
        });

        /** Get global variables and make log data request to remote url. */
        cy.get('@frameworkVersion').then(frameworkVersion => {
            cy.get('@pluginVersion').then(pluginVersion => {

                cy.request('GET', this.RemoteVersionLogUrl, {
                    key: frameworkVersion,
                    tag: this.ShopName,
                    view: 'html',
                    ecommerce: frameworkVersion,
                    plugin: pluginVersion
                }).then((resp) => {
                    expect(resp.status).to.eq(200);
                });
            });
        });
    },
}