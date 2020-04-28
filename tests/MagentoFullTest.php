<?php

namespace Magento;


use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Lmc\Steward\Test\AbstractTestCase;

/**
 * @group magento_full_test
 */
class MagentoFullTest extends AbstractTestCase {

	public $runner;

	/**
	 *
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	public function testUsdPaymentBeforeOrderInstant() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'first_test'    => true,
				'currency'      => 'USD',
				'stop_emails'   => true,
				'capture_mode'  => 'instant',
				'checkout_mode' => 'before_order',
			)
		);
	}

	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	public function testUsdPaymentBeforeOrderDelayed() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'capture_mode'  => 'delayed',
				'checkout_mode' => 'before_order',
			)
		);
	}

	/**
	 *
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	public function testEURPaymentBeforeOrderDelayed() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'      => 'EUR',
				'capture_mode'  => 'delayed',
				'checkout_mode' => 'before_order',
			)
		);
	}

	/**
	 *
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	public function testDkkPaymentBeforeOrderInstant() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'               => 'DKK',
				'capture_mode'           => 'instant',
				'checkout_mode'          => 'before_order',
				'exclude_manual_payment' => false,
			)
		);
	}


	/**
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 * @throws UnexpectedTagNameException
	 */
	public function testJpyPaymentBeforeOrderDelayed() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'      => 'JPY',
				'capture_mode'  => 'delayed',
				'checkout_mode' => 'before_order',
			)
		);
	}

}
