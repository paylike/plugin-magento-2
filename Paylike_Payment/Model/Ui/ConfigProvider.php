<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Esparks\Paylike\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Esparks\Paylike\Gateway\Http\Client\TransactionAuthorize;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface {
	const PLUGIN_CODE = 'paylikepaymentmethod';
	const MAGENTO_PAYLIKE_VERSION = '1.4.2';
	protected $scopeConfig;
	protected $_cart;
	protected $_assetRepo;
	protected $_storeManager;
	protected $locale;
	protected $cards;
	protected $helper;

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Framework\View\Asset\Repository $assetRepo,
		\Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Locale\Resolver $locale,
		\Esparks\Paylike\Model\Adminhtml\Source\AcceptedCards $cards,
		\Esparks\Paylike\Helper\Data $helper
	) {
		$this->scopeConfig             = $scopeConfig;
		$this->_cart                   = $cart;
		$this->_assetRepo              = $assetRepo;
		$this->cartRepositoryInterface = $cartRepositoryInterface;
		$this->_storeManager           = $storeManager;
		$this->locale                  = $locale;
		$this->cards                   = $cards;
		$this->helper                  = $helper;
	}

	/**
	 * Retrieve assoc array of checkout configuration
	 *
	 * @return array
	 */
	public function getConfig() {
		return [
			'payment'      => [
				self::PLUGIN_CODE => [
					'transactionResults' => [
						TransactionAuthorize::SUCCESS => __( 'Success' ),
						TransactionAuthorize::FAILURE => __( 'Fraud' )
					]
				]
			],
			'description'  => $this->getDescription(),
			'config'       => $this->getConfigJSON(),
			'publicapikey' => $this->getPublicApiKey(),
			'cards'        => $this->getAcceptedCards(),
			'url'          => $this->getImageUrl(),
			'multiplier'   => $this->getPaylikeMultiplier( $this->getStoreCurrentCurrency() )
		];
	}

	/**
	 * Retrieve description from backend
	 *
	 * @return string
	 */

	public function getDescription() {
		return $this->scopeConfig->getValue( 'payment/paylikepaymentmethod/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
	}

	/**
	 * Retrieve URLs of selected credit cards from backend
	 *
	 * @return array
	 */

	public function getImageUrl() {
		$acceptedcards = $this->getAcceptedCards();
		$selectedcards = explode( ",", $acceptedcards );

		$finalcards = array();
		$key        = 0;
		foreach ( $selectedcards as $value ) {
			$finalcards[ $key ] = $this->_assetRepo->getUrl( 'Esparks_Paylike::images/paymenticons/' . $value . '.svg' );
			$key                = $key + 1;
		}

		return $finalcards;
	}

	/**
	 * Get quote object associated with cart. By default it is current customer session quote
	 *
	 * @return \Magento\Quote\Model\Quote
	 */

	protected function _getQuote() {
		return $this->_cart->getQuote();
	}

	/**
	 * Retrieve title of backup from backend
	 *
	 * @return string
	 */

	public function getPopupTitle() {
		$title = $this->scopeConfig->getValue( 'payment/paylikepaymentmethod/popup_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
		if ( ! $title ) {

			$title =$this->_storeManager->getStore()->getName();
		}

		return $title;
	}

	public function getLogsEnabled() {
		$enabled = $this->scopeConfig->getValue( 'payment/paylikepaymentmethod/enable_logs', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );

		return $enabled === "1";
	}

	/**
	 * Retrieve accepted credit cards from backend
	 *
	 * @return string
	 */

	public function getAcceptedCards() {
		return $this->scopeConfig->getValue( 'payment/paylikepaymentmethod/acceptedcards', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
	}

	/**
	 * Retrieve public API key according to the mode selected from backend
	 *
	 * @return string
	 */

	public function getPublicApiKey() {
		$mode = $this->scopeConfig->getValue( 'payment/paylikepaymentmethod/transaction_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
		$key  = "";
		if ( $mode == "test" ) {
			$key = $this->scopeConfig->getValue( 'payment/paylikepaymentmethod/test_api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
		} else if ( $mode == "live" ) {
			$key = $this->scopeConfig->getValue( 'payment/paylikepaymentmethod/live_api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
		}

		return $key;
	}

	/**
	 * Retrieve current store currency
	 *
	 * @return string
	 */

	public function getStoreCurrentCurrency() {
		return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

	}

	/**
	 * Get Paylike multiplier for currency
	 *
	 * @param string $currency Accepted currency.
	 *
	 * @return float|int
	 */

	public function getPaylikeMultiplier( $currency ) {
		return $this->helper->getPaylikeCurrencyMultiplier( $currency );

	}

	public function getPaylikeAmount( $currency,$amount ) {
		return $this->helper->getPaylikeAmount( $currency,$amount );

	}

	public function getPaylikeExponent( $currency ) {
		return $this->helper->getPaylikeCurrency( $currency )['exponent'];

	}

	/**
	 * Retrieve config values for popup of Paylike
	 *
	 * @return string
	 */

	public function getConfigJSON() {
		$test_mode  = $this->scopeConfig->getValue( 'payment/paylikepaymentmethod/transaction_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
		$quote    	= $this->_getQuote();
		$title    	= $this->getPopupTitle();
		$currency 	= $this->getStoreCurrentCurrency();
		$total      = $quote->getGrandTotal();

		$amount = $this->getPaylikeAmount($currency,$total);

		$exponent = $this->getPaylikeExponent($currency);

		$email    = $quote->getBillingAddress()->getEmail();
		$products = array();
		foreach ( $quote->getAllVisibleItems() as $item ) {
			$product    = array(
				'ID'       => $item->getProductId(),
				'SKU'      => $item->getSku(),
				'name'     => $item->getName(),
				'quantity' => $item->getQty()
			);
			$products[] = $product;
		}

		$quoteId 	  = $quote->getId();
		$quote        = $this->cartRepositoryInterface->get( $quote->getId() );
		$customerData = $quote->getCustomer();
		$address      = $quote->getBillingAddress();
		$name         = $customerData->getFirstName() . " " . $customerData->getLastName();
		$logsEnabled  = $this->getLogsEnabled();

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$ip            = $objectManager->get( 'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress' );

		$customer = array(
			'name'    => $name,
			'email'   => $email,
			'phoneNo' => $address->getTelephone(),
			'address' => "",
			'IP'      => $ip->getRemoteAddress()
		);

		$productMetadata = $objectManager->get( 'Magento\Framework\App\ProductMetadataInterface' );
		$magentoversion  = $productMetadata->getVersion();

		$platform = array(
			'name'    => 'Magento',
			'version' => $magentoversion
		);


		$version = SELF::MAGENTO_PAYLIKE_VERSION;

		return [
			'test'    		=> $test_mode,
			'title'    		=> $title,
			'amount'   		=> [
				'currency' => $currency,
				'exponent' => $exponent,
				'value'    => $amount,
			],
			'locale'   		=> $this->locale->getLocale(),
			'custom'   		=> [
				'quoteId' 				=> $quoteId,
				'products'  			=> $products,
				'customer'  			=> $customer,
				'platform'  			=> $platform,
				'paylikePluginVersion'  => $version,
				'logsEnabled'   		=> $logsEnabled,
			]
		];
	}
}
