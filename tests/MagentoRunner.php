<?php


namespace Magento;

use Facebook\WebDriver\Exception\NoAlertOpenException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Lmc\Steward\ConfigProvider;

class MagentoRunner extends MagentoTestHelper {

	/**
	 * @param $args
	 *
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	public function ready( $args ) {
		$this->set( $args );
		$this->go();
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function loginAdmin() {
		$this->goToPage( 'admin', '#username', true );
		while ( ! $this->hasValue( '#username', $this->user ) ) {
			$this->typeLogin();
		}
		$this->click( '.action-login' );
		$this->waitForPage( 'admin/dashboard/', true );
	}


	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function changeCurrency() {
		$driver = $this->wd;
		$this->goToPage( '', '#switcher-currency-trigger' );
		$this->click( '#switcher-currency-trigger' );
		try {
			$this->waitForElement( '.switcher-dropdown',2 );
		}catch (TimeOutException $e){
			$this->click( '#switcher-currency-trigger' );
		}
		$currency_not_in_default = 0;
		try {
			$currency_element = $driver->findElement( WebDriverBy::xpath( "//*[@id='switcher-currency']//strong//span[text()[contains(.,'" . $this->currency . "')]]" ) );
			if ( $currency_element ) {
				$currency_element->click();
			}
		} catch ( NoSuchElementException $e ) {
			$currency_not_in_default = 1;
		}
		if ( $currency_not_in_default ) {
			$currency_element = $driver->findElement( WebDriverBy::xpath( "//*[@id='switcher-currency']//li//a[text()[contains(.,'" . $this->currency . "')]]" ) );
			if ( $currency_element ) {
				$currency_element->click();
			}
		}


	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	public function disableEmail() {
		if ( $this->stop_email === true ) {
			$this->goToPage( 'admin/system_config/edit/section/system', '#system_smtp-head', true );
			$this->click( '#system_smtp-head' );
			$this->uncheck( '#system_smtp_disable_inherit' );
			$this->selectValue( '#system_smtp_disable', 1 );
			$this->submitAdmin();
		}
	}


	/**
	 *
	 */
	public function captureMode() {
		$this->selectValue( '#payment_us_paylikepaymentmethod_capture_mode', $this->capture_mode );

	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function invoiceOrder() {
		$this->click( '#order_invoice' );
		$this->waitForElement( '.admin__page-section-content #invoice_totals .actions .save' );
		$this->click( '.admin__page-section-content #invoice_totals .actions .save' );
		$this->waitForElement( '#order_history_block' );
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function changeMode() {
		$this->goToPage( 'admin/system_config/edit/section/payment', '#row_payment_us_paylikepaymentmethod', true );
		$this->captureMode();
		$this->submitAdmin();
	}

	/**
	 *
	 */
	public function submitAdmin() {
		$this->click( '#save' );
	}

	/**
	 *
	 */
	public function proceedToCheckout() {
		$this->wd->get( $this->helperGetUrl( 'checkout' ) );
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function selectOrder() {


		$this->goToPage( 'sales/order/', '.admin__data-grid-wrap a.action-menu-item', true );

		$this->waitForElement( '.admin__data-grid-wrap a.action-menu-item' );
		$this->waitElementDisappear( '.admin__data-grid-loading-mask' );
		try {
			$this->click( '.admin__data-grid-wrap a.action-menu-item' );
		}catch (UnknownServerException $exception){
			$this->selectOrder();
		}
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function placeOrder() {
		$this->waitForElement( '#place_order' );
		$this->waitElementDisappear( '.blockUI.blockOverlay' );
		$this->click( '#place_order' );
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function addProductToCart() {
		$this->waitForElement( '.button.product_type_simple.add_to_cart_button.ajax_add_to_cart' );
		$this->click( '.button.product_type_simple.add_to_cart_button.ajax_add_to_cart' );
		$this->waitForElement( ".added_to_cart.wc-forward" );
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function clearCartItem() {
		try {
			$cartCount = $this->getText( '.minicart-wrapper span.counter .counter-number' );
		} catch ( StaleElementReferenceException $exception ) {
			// try again
			$cartCount = $this->getText( '.minicart-wrapper span.counter .counter-number' );
		}
		$cartCount = preg_replace( "/[^0-9.]/", "", $cartCount );
		if ( $cartCount ) {
			$this->click( '.minicart-wrapper' );
			$this->waitForElement( '.minicart-items' );
			$productRemoves = $this->findElements( '.minicart-items .product .action.delete' );
			try {
				$productRemoves[0]->click();
			} catch ( StaleElementReferenceException $exception ) {
				// can happen
			}
			$this->waitForElement( '.modal-popup.confirm._show' );
			$this->click( '.action-primary.action-accept' );
			$this->waitElementDisappear( '.modal-popup.confirm' );
			$this->clearCartItem();

		}
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function logInFrontend() {
		$this->goToPage( 'customer/account/login/' );
		$this->type( '#email', $this->client_user );
		$this->type( '#pass', $this->client_pass );
		$this->click( '#send2' );
		$this->waitForPage( 'customer/account/' );
	}

	public function chooseShipping() {
		$this->waitElementDisappear( '.loading-mask' );
		$this->waitForElement( '//td[contains(text(), "Fixed")]', 30 );
		$this->click( '//td[contains(text(), "Fixed")]' );
		$this->click( '.actions-toolbar button.continue' );
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function choosePaylike() {
		$this->waitElementDisappear( '.loading-mask' );
		try {
			$this->waitForElement( '.payment-method #paylikepaymentmethod' );
		} catch ( TimeOutException $exception ) {
			$this->proceedToCheckout();
			$this->chooseShipping();
			$this->choosePaylike();

			return true;
		}
		$this->click( '.payment-method #paylikepaymentmethod' );
		$this->waitForElement( '.payment-method._active .payment-method-content .actions-toolbar .primary button.action.primary.checkout' );
		$this->click( '.payment-method._active .payment-method-content .actions-toolbar .primary button.action.primary.checkout' );

	}


	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function finalPaylike() {
		$driver         = $this->wd;
		$amount         = $driver->executeScript( "return window.paylikeminoramount" );
		$expectedAmount = $this->getText( '.grand.totals .amount span.price' );
		$expectedAmount = preg_replace( "/[^0-9.]/", "", $expectedAmount );
		$expectedAmount = trim( $expectedAmount, '.' );
		$expectedAmount = ceil( round( $expectedAmount, 3 ) * get_paylike_currency_multiplier( $this->currency ) );
		$this->main_test->assertEquals( $expectedAmount, $amount, "Checking minor amount for " . $this->currency );

		$this->popupPaylike();
		$this->waitForPage( 'checkout/onepage/success/' );
		// because the title of the page matches the checkout title, we need to use the order received class on body
		$this->main_test->assertEquals( 'Thank you for your purchase!', $this->getText( '.page-title .base' ), "Checking message for " . $this->currency );
	}

	/**
	 *
	 *
	 */
	public function addToCart() {

		$this->click( "#product-addtocart-button" );
		try {
			$this->waitForElement( ".message-success", 5 );
		} catch ( NoSuchElementException $exception ) {
			$this->wd->navigate()->refresh();
			$this->waitForElement( "#product-addtocart-button" );
			$this->addToCart();
		}


	}

	public function clearAdminMessage() {
		$message = $this->findElements( '#message' );
		if ( $message[0] ) {
			$dismiss = $this->findChild( '.notice-dismiss', $message[0] );
			$dismiss->click();
		}

	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function saveOrder() {
		$this->waitForPageReload( function () {
			$this->click( '.save_order' );
		}, 5000 );
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function popupPaylike() {
		try {
			$this->waitForElement( '.paylike.overlay .payment form #card-number' );
			$this->type( '.paylike.overlay .payment form #card-number', 41000000000000 );
			$this->type( '.paylike.overlay .payment form #card-expiry', '11/22' );
			$this->type( '.paylike.overlay .payment form #card-code', '122' );
			$this->click( '.paylike.overlay .payment form button' );
		} catch ( NoSuchElementException $exception ) {
			$this->confirmOrder();
			$this->popupPaylike();
		}

	}


	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function refund() {
		$this->click( '#sales_order_view_tabs_order_invoices' );
		$this->waitForElement( '.admin__data-grid-wrap a.action-menu-item' );
		$this->waitElementDisappear( '.admin__data-grid-loading-mask' );
		$this->click( '.admin__data-grid-wrap a.action-menu-item' );
		$this->waitForElement( '#capture' );
		//$refund       = preg_match_all( '!\d+!', $this->getText( '.order-subtotal-table tfoot strong span.price' ), $refund_value );
		//$refund_value = $refund_value[0];
		$this->click( '#capture' );
		$this->waitForElement( '.actions .submit-button.refund' );
		$this->click( '.actions .submit-button.refund' );

		$this->waitForElement( '#order_history_block' );
		$text = $this->pluckElement( '.note-list li .note-list-comment', 0 )->getText();
		$this->main_test->assertStringStartsWith( 'We refunded', $text, "Refunded" );
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function capture() {

		$this->invoiceOrder();
		$text = $this->pluckElement( '.note-list li .note-list-comment', 0 )->getText();
		$this->main_test->assertStringStartsWith( 'Captured amount of', $text, "Captured" );
	}


	/**
	 *  Insert user and password on the login screen
	 */
	private function typeLogin() {
		$this->type( '#username', $this->user );
		$this->type( '#login', $this->pass );
	}

	/**
	 * @param $args
	 */
	private function set( $args ) {
		foreach ( $args as $key => $val ) {
			$name = $key;
			if ( isset( $this->{$name} ) ) {
				$this->{$name} = $val;
			}
		}
	}

	/**
	 * @param $page
	 *
	 * @return string
	 */
	private function helperGetUrl( $page ) {
		return $this->base_url . '/' . $page;
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	private function settings() {
		$this->disableEmail();
		$this->changeMode();
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	private function directPayment() {
		$this->goToPage( 'fusion-backpack.html', '#product-addtocart-button' );
		$this->clearCartItem();
		$this->addToCart();
		$this->logInFrontend();
		$this->proceedToCheckout();
		$this->chooseShipping();
		$this->choosePaylike();
		$this->finalPaylike();
		$this->selectOrder();
		if ( $this->capture_mode == 'delayed' ) {
			$this->capture();
		}
		$this->refund();
	}


	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	private function getVersions() {
		$this->goToPage( 'admin/system_config/edit/section/payment/', '#row_payment_us_paylikepaymentmethod', true );
		$magento = $this->getSystemVersion();
		$paylike = $this->getPluginVersion();

		return [ 'ecommerce' => $magento, 'plugin' => $paylike ];
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	private function outputVersions() {
		$versions = $this->getVersions();
		$this->main_test->log( '----VERSIONS----' );
		$this->main_test->log( 'Magento %s', $versions['ecommerce'] );
		$this->main_test->log( 'Paylike %s', $versions['plugin'] );
	}

	private function getPluginVersion() {
		$element = $this->wd->findElement( WebDriverBy::cssSelector( '.paylike-version' ) );
		$classes = $element->getAttribute( 'class' );
		$version = str_replace( 'paylike-version version-', '', $classes );
		$version = str_replace( 'select admin__control-select', '', $version );

		return $version;
	}

	private function getSystemVersion() {
		$element = $this->wd->findElement( WebDriverBy::cssSelector( '.magento-version' ) );

		return str_replace( 'Magento ver.', '', $element->getText() );

	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	private function logVersionsRemotly() {
		$versions = $this->getVersions();
		$this->wd->get( getenv( 'REMOTE_LOG_URL' ) . '&key=' . $this->get_slug( $versions['ecommerce'] ) . '&tag=magento2&view=html&' . http_build_query( $versions ) );
		$this->waitForElement( '#message' );
		$message = $this->getText( '#message' );
		$this->main_test->assertEquals( 'Success!', $message, "Remote log failed" );
	}


	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	private function go() {
		$this->changeWindow();
		$this->loginAdmin();

		if ( $this->log_version ) {
			$this->logVersionsRemotly();

			return $this;
		}
		$this->outputVersions();
		$this->settings();
		$this->changeCurrency();
		$this->directPayment();

	}

	/**
	 *
	 */
	private function changeWindow() {
		$this->wd->manage()->window()->setSize( new WebDriverDimension( 1600, 996 ) );
	}


}

