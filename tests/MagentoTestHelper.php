<?php

namespace Magento;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class MagentoTestHelper {
	// all of these require a default
	/** @var $wd \Lmc\Steward\WebDriver\RemoteWebDriver */
	public $wd;
	public $base_url;
	public $user;
	public $client_user;
	public $pass;
	public $client_pass;
	public $currency = 'USD';
	public $capture_mode = 'delayed';
	public $admin_prefix = 'admin';
	public $stop_email = false;
	public $log_version = false;
	public $main_test;

	/**
	 * MagentoTestHelper constructor.
	 *
	 * @param \Magento\MagentoTest|\Magento\MagentoFullTest $magento_test
	 */
	public function __construct( $magento_test ) {
		$this->main_test    = $magento_test;
		$this->wd           = $magento_test->wd;
		$this->base_url     = getenv( 'ENVIRONMENT_URL' );
		$this->admin_prefix = getenv( 'ADMIN_PREFIX' );
		$this->user         = getenv( 'ENVIRONMENT_USER' );
		$this->client_user  = getenv( 'ENVIRONMENT_CLIENT_USER' );
		$this->pass         = getenv( 'ENVIRONMENT_PASS' );
		$this->client_pass  = getenv( 'ENVIRONMENT_CLIENT_PASS' );
	}


	/**
	 * @param      $pagePath
	 * @param null $waitForSelector
	 *
	 * @return MagentoTestHelper
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function goToPage( $pagePath, $waitForSelector = null, $admin = false ) {
		if ( $admin ) {
			$pagePath = $this->admin_prefix . '/' . $pagePath;
		}
		$url = $this->helperGetUrl( $pagePath );
		if ( $url != $this->wd->getCurrentURL() ) {
			$this->wd->get( $this->helperGetUrl( $pagePath ) );
		}


		// wait for the element to be visible before we send input
		if ( $waitForSelector ) {
			$this->waitForElement( $waitForSelector );
		}

		return $this;
	}

	/**
	 * @param      $pagePath
	 *
	 * @param bool $admin
	 *
	 * @return MagentoTestHelper
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function waitForPage( $pagePath, $admin = false ) {
        $url = $this->helperGetUrl( $pagePath,$admin );
        $url = explode( '@', $url );
        if ( count( $url ) > 1 ) {
            $left = explode( '://', $url[0] );
            $url = $left[0] . '://' . $url[1];
        } else {
            $url = $url[0];
        }

        $this->wd->wait( 10, 500 )->until(
            WebDriverExpectedCondition::urlIs( $url )
        );

		return $this;
	}

	/**
	 * @param $selector
	 *
	 * @return MagentoTestHelper
	 */
	public function click( $selector, $moveTo = true ) {
		$element = $this->find( $selector );
		if ( $moveTo ) {
			$this->moveMouse( $element );

			$this->waitForElement($selector);
		}
		$element->click();

		return $this;
	}

	/**
	 * set value from select
	 *
	 * @param $selectQuery
	 * @param $value
	 *
	 * @return $this
	 * @throws NoSuchElementException
	 * @throws UnexpectedTagNameException
	 */
	public function selectValue( $selectQuery, $value ) {
		$select = new WebDriverSelect( $this->find( $selectQuery ) );
		$select->selectByValue( $value );

		return $this;
	}

	/**
	 * @param $selectQuery
	 * @param $value
	 *
	 * @return bool
	 * @throws NoSuchElementException
	 * @throws UnexpectedTagNameException
	 */
	public function isSelected( $selectQuery, $value ) {
		$select = new WebDriverSelect( $this->find( $selectQuery ) );
		$option = $select->getFirstSelectedOption();

		return $option->getText() == $value;
	}

	/**
	 * @param $selector
	 * @param $keys
	 *
	 * @return MagentoTestHelper
	 */
	public function type( $selector, $keys ) {
		$this->find( $selector )->clear()->sendKeys( $keys );

		return $this;
	}

	/**
	 * Check if an element has the text it should have
	 *
	 * @param $selector
	 * @param $keys
	 *
	 * @return bool
	 */
	public function hasValue( $selector, $keys ) {
		return ( $this->find( $selector )->getAttribute( 'value' ) == $keys );
	}

	/**
	 * @param $selector
	 * @param $keys
	 *
	 * @return MagentoTestHelper
	 */
	public function checkbox( $selector ) {
		$checkbox = $this->find( $selector );
		if ( $checkbox->isSelected() ) {
		} else {
			$checkbox->click();
		}

		return $this;
	}

	public function uncheck( $selector ) {
		$checkbox = $this->find( $selector );
		if ( ! $checkbox->isSelected() ) {
		} else {
			$checkbox->click();
		}

		return $this;
	}

	/**
	 * @param $selector
	 *
	 * @return string
	 */
	public function getText( $selector ) {
		return $this->find( $selector )->getText();
	}


	/**
	 * @param $query
	 * @param $index
	 *
	 * @return RemoteWebElement
	 */
	public function pluckElement( $query, $index ) {
		return $this->wd->findElements( $this->getElement( $query ) )[ $index ];
	}

	/**
	 * @param $query
	 *
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function elementExists( $query ) {
		$element = $this->getElement( $query );
		$this->wd->wait( 2, 100 )->until(
			WebDriverExpectedCondition::presenceOfElementLocated( $element )
		);
	}

	/**
	 * @param $query
	 *
	 * @return string
	 */
	public function getElementData( $query, $attribute ) {
		return $this->find( $query )->getAttribute( 'data-' . $attribute );
	}

	/**
	 * @return string
	 */
	public function moveToElement( $query ) {
		$parent = $this->find( $this->getElement( $query ) );

		return $this->wd->getMouse()->mouseMove( $parent->getCoordinates() );
	}

	public function findElements( $query ) {
		return $this->wd->findElements( $this->getElement( $query ) );
	}

	/**
	 * @param                  $query
	 * @param RemoteWebElement $parent
	 *
	 * @return RemoteWebElement
	 */
	public function findChild( $query, $parent ) {
		return $parent->findElement( $this->getElement( $query ) );
	}

	/**
	 * @param $query
	 *
	 * @return MagentoTestHelper
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function waitForElement( $query, $time = 15 ) {
		$element = $this->getElement( $query );
		$this->wd->wait( $time, 1000 )->until(
			WebDriverExpectedCondition::visibilityOfElementLocated( $element )
		);

		return $this;
	}

	/**
	 * @param $query
	 *
	 * @return MagentoTestHelper
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function waitElementDisappear( $query ) {
		$element = $this->getElement( $query );
		$this->wd->wait( 10, 1000 )->until(
			WebDriverExpectedCondition::invisibilityOfElementLocated( $element )
		);

		return $this;
	}

	/**
	 *
	 */
	public function acceptAlert() {
		$this->wd->switchTo()->alert()->accept();
	}

	/**
	 *
	 */
	public function pressEnter() {
		$this->wd->getKeyboard()->pressKey( "\xEE\x80\x87" );
	}

	/**
	 *
	 */
	public function pressBackspace() {
		$this->wd->getKeyboard()->pressKey( "\xEE\x80\x83" );
	}

	/**
	 * @param $navigate_f
	 * @param $timeout
	 *
	 * @throws NoSuchElementException
	 * @throws TimeOutException
	 */
	public function waitForPageReload( $navigate_f, $timeout ) {
		$driver = $this->wd;
		$id     = $this->wd->findElement( WebDriverBy::cssSelector( 'html' ) )->getID();
		call_user_func( $navigate_f );
		$driver->wait( $timeout )->until(
			( function () use ( $id ) {
				$html = $this->wd->findElement( WebDriverBy::cssSelector( 'html' ) );
				if ( $html->getId() != $id ) {
					return true;
				}
			} ) );
	}

	/**
	 * @param RemoteWebElement $element
	 */
	private function moveMouse( $element ) {
		$this->wd->getMouse()->mouseMove( $element->getCoordinates() );
	}

	/**
	 * @param $query
	 *
	 * @return \Facebook\WebDriver\Remote\RemoteWebElement
	 */
	private function find( $query ) {
		$webdriverBy = $this->getElement( $query );
		if ( ! ( $webdriverBy instanceof WebDriverBy ) ) {
			return $webdriverBy;
		}

		return $this->wd->findElement( $this->getElement( $webdriverBy ) );
	}


	/**
	 * @param $query
	 *
	 * @return WebDriverBy
	 */
	private function getElement( $query ) {
		if ( is_object( $query ) ) {
			return $query;
		}
		$first_char = substr( $query, 0, 1 );
		switch ( $first_char ) {
			case '#':
				$element = WebDriverBy::id( str_replace( '#', '', $query ) );
				break;
			case '/':
				$element = WebDriverBy::xpath( $query );
				break;
			case '.':
				$element = WebDriverBy::cssSelector( $query );
				break;
			default:
				$element = WebDriverBy::name( $query );
		}

		return $element;
	}

	/**
	 * @param      $page
	 *
	 * @param bool $admin
	 *
	 * @return string
	 */
	private function helperGetUrl( $page, $admin = false ) {
		if ( $admin ) {
			$page = $this->admin_prefix . '/' . $page;
		}
		$this->main_test->log( '%s', $this->base_url );

		return $this->base_url . '/' . $page;
	}

	public function get_slug( $str, $delimiter = '-' ) {

		$slug = strtolower( trim( preg_replace( '/[\s-]+/', $delimiter, preg_replace( '/[^A-Za-z0-9-]+/', $delimiter, preg_replace( '/[&]/', 'and', preg_replace( '/[\']/', '', iconv( 'UTF-8', 'ASCII//TRANSLIT', $str ) ) ) ) ), $delimiter ) );

		return $slug;

	}


}
