<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lunar\Paylike\Model\Adapter;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Store\Model\ScopeInterface;

use Lunar\Paylike\lib\Paylike\Client;
use Lunar\Paylike\lib\Paylike\Transaction;

/**
 * Class PaylikeAdapter
 * @codeCoverageIgnore
 * Adapter used for capture/refund/void an order from admin panel
 */
class PaylikeAdapter
{
	const PLUGIN_CODE = 'paylikepaymentmethod';

    private $scopeConfig;
    private $request;
    private $orderRepository;
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param OrderRepository $orderRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
            ScopeConfigInterface $scopeConfig,
            RequestInterface $request,
            OrderRepository $orderRepository,
            StoreManagerInterface $storeManager
        )
    {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;

        $this->setPrivateKey();
    }

    /**
     * @param string|null $value
     */
    public function setPrivateKey()
    {
        $transactionMode = $this->getStoreConfigValue('transaction_mode');

        $privateKey = '';

        if($transactionMode == "test"){
            $privateKey = $this->getStoreConfigValue('test_app_key');
        }

        else if($transactionMode == "live"){
            $privateKey = $this->getStoreConfigValue('live_app_key');
        }

        Client::setKey($privateKey);
    }

    /**
     * @param string $transactionId
     * @param array $data
     * @return array Paylike response
     */
    public function capture($transactionId, array $data)
    {
        return Transaction::capture($transactionId, $data);
    }

    /**
     * @param string $transactionId
     * @param array $data
     * @return array Paylike response
     */
    public function void($transactionId, array $data)
    {
        return Transaction::void($transactionId, $data);
    }

    /**
     * @param string $transactionId
     * @param array $data
     * @return array Paylike response
     */
    public function refund($transactionId, array $data)
    {
        return Transaction::refund($transactionId, $data);
    }

    /**
     * Get store config value
     *
     * @param string $configId
     */
    private function getStoreConfigValue($configId)
    {
        /** FRONTEND order processing. */
        $orderStoreId = $this->storeManager->getStore()->getId();

        /**
         * ADMIN order processing (if order_id is present, the request is from admin)
         *
         * Get order Id from request.
         * Get order by id
         * Get store id from order
         */
        if ($this->request->getParam('order_id')) {
            $orderId = $this->request->getParam('order_id');
            $order = $this->orderRepository->get($orderId);

            $orderStoreId = $order->getStore()->getId();
        }

        /**
         * "path" is composed based on etc/adminhtml/system.xml as "section_id/group_id/field_id"
         */
        $configPath = 'payment/' . self::PLUGIN_CODE . '/' . $configId;

        return $this->scopeConfig->getValue(
            /*path*/ $configPath,
            /*scopeType*/ ScopeInterface::SCOPE_STORE,
            /*scopeCode*/ $orderStoreId
        );
    }
}
