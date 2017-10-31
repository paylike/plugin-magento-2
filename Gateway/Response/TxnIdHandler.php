<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\Method\Logger;

class TxnIdHandler implements HandlerInterface
{
    const TXN_ID = 'TXN_ID';

    protected $_invoiceService;

    protected $order;

    /**
     * @var Logger
     */
    private $logger;
    
    public function __construct(
        Logger $logger,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order $order
    )
    {
        $this->logger = $logger;
        $this->_invoiceService = $invoiceService;
        $this->order = $order;
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException(__('Payment data object should be provided'));
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();
        /** @var $payment \Magento\Sales\Model\Order\Payment */

        if(isset($response['TXN_TYPE'])){

            $this->logger->debug(["txnidhandler: " => $response['TXN_TYPE']]);

            if($response['TXN_TYPE'] == "void" || $response['TXN_TYPE'] == "refund"){
                $transactionid = $response[self::TXN_ID] . "-" . $response['TXN_TYPE'];
                $payment->setTransactionId($transactionid);
                $payment->setIsTransactionClosed(true);
                $payment->setShouldCloseParentTransaction(true);    
            }

            else{
                $payment->setTransactionId($response[self::TXN_ID]);
                $payment->setIsTransactionClosed(false);
            }
        }

        else{
            $this->logger->debug(["txnidhandler: " => "not set"]);
            $payment->setTransactionId($response[self::TXN_ID]);
            $payment->setIsTransactionClosed(false);
        }
    }
}
