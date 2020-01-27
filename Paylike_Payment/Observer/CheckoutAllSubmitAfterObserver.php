<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\Method\Logger;

class CheckoutAllSubmitAfterObserver implements ObserverInterface
{
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     *
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    protected $invoiceSender;

    /**
     *
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @param Logger $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     */
    public function __construct(
        Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
		if(!isset($order)){
			return $this;
		}
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodName = $payment->getMethod();

        if ($methodName != "paylikepaymentmethod"){
            return $this;
        }

        $capturemode =  $this->scopeConfig->getValue('payment/paylikepaymentmethod/capture_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($capturemode == "instant"){
            if(!$order->getId()) {
                return $this;
            }
            

            try {
                $invoices = $this->invoiceCollectionFactory->create()
                    ->addAttributeToFilter('order_id', array('eq' => $order->getId()));
                $invoices->getSelect()->limit(1);

                if ((int)$invoices->count() !== 0) {
                    return null;
                }

                if(!$order->canInvoice()) {
                    return null;
                }

                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();

                $invoiceEmailmode =  $this->scopeConfig->getValue('payment/paylikepaymentmethod/invoice_email');
                 if (!$invoice->getEmailSent() && $invoiceEmailmode=='1') {
                    try {
                        $this->invoiceSender->send($invoice);
                    } catch (\Exception $e) {
                        // Do something if failed to send                          
                    }
                }
            } catch (\Exception $e) {
                $order->addStatusHistoryComment('Exception message: '.$e->getMessage(), false);
                $order->save();
                return null;
            }
        }

        else if($capturemode == "delayed"){

            $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $order->save();
        }
        
        return $this;
    }
}