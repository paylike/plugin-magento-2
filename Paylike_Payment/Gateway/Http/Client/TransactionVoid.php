<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lunar\Paylike\Gateway\Http\Client;

class TransactionVoid extends AbstractTransaction
{
    /**
     * Process http request
     * @param string $transactionId
     * @param array $data
     * @return Paylike response
     */
    protected function process($transactionid, array $data)
    {
        return $this->adapter->void(
            $transactionid,
            $data
        );
    }
}
