<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Model\Adapter;

use Magento\Payment\Gateway\ConfigInterface;
use Esparks\Paylike\lib\Paylike\Client;
use Esparks\Paylike\lib\Paylike\Transaction;

/**
 * Class PaylikeAdapter
 * @codeCoverageIgnore
 */
class PaylikeAdapter
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->setPrivateKey();
    }

    /**
     * @param string|null $value
     */
    public function setPrivateKey()
    {
        $transactionmode = $this->config->getValue('transaction_mode');
        $privatekey = '';
        if($transactionmode == "test"){
            $privatekey = $this->config->getValue('test_app_key');
        }

        else if($transactionmode == "live"){
            $privatekey = $this->config->getValue('live_app_key');
        }
        Client::setKey($privatekey);
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

}
