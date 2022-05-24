<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lunar\Paylike\Gateway\Http\Client;

use Lunar\Paylike\Model\Adapter\PaylikeAdapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Lunar\Paylike\Helper\Data;

/**
 * Class AbstractTransaction
 */
abstract class AbstractTransaction implements ClientInterface {
	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * @var PaylikeAdapter
	 */
	protected $adapter;

	/**
	 * @var Data
	 */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param Logger         $logger
	 * @param PaylikeAdapter $adapter
	 * @param Data           $helper
	 */
	public function __construct( Logger $logger, PaylikeAdapter $adapter, Data $helper ) {
		$this->logger = $logger;
		$this->adapter = $adapter;
		$this->helper = $helper;
	}

	/**
	 * @inheritdoc
	 */
	public function placeRequest( TransferInterface $transferObject ) {
		$value = $transferObject->getBody();
		$response['object'] = [];

		$amount = $this->helper->getPaylikeAmount( $value['CURRENCY'], $value['AMOUNT'] );
		$data = array(
			'amount'   => $amount,
			'currency' => $value['CURRENCY']
		);
		$response['object'] = [];

		try {
			$response['object'] = $this->process( $value['TXN_ID'], $data );
		} catch ( \Exception $e ) {
			$message = __( $e->getMessage() ?: 'Sorry, but something went wrong' );
			$this->logger->critical( $message );
			throw new ClientException( $message );
		} finally {
			if ( $response['object'] == false ) {
				$response['RESULT_CODE'] = 0;
			} else {
				$response['RESULT_CODE'] = 1;
			}

			$response['TXN_ID'] = $value['TXN_ID'];
			$response['TXN_TYPE'] = $value['TXN_TYPE'];

			$this->logger->debug(
				[
					'request'  => $data,
					'response' => $response
				]
			);
		}

		return $response;
	}

	/**
	 * Process http request
	 *
	 * @param string $transactionId
	 * @param array  $data
	 *
	 * @return Paylike response
	 */
	abstract protected function process( $transactionid, array $data );
}
