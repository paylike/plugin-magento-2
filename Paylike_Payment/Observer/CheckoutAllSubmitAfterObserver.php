<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\ScopeInterface;


class CheckoutAllSubmitAfterObserver implements ObserverInterface
{
    const PLUGIN_CODE = 'paylikepaymentmethod';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @var CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     *
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     *
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     *
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @param Logger $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $invoiceCollectionFactory
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $invoiceCollectionFactory,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        InvoiceSender $invoiceSender
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
        $captureMode =  $this->scopeConfig->getValue('payment/' . self::PLUGIN_CODE . '/capture_mode', ScopeInterface::SCOPE_STORE);
        $invoiceEmailMode =  $this->scopeConfig->getValue('payment/' . self::PLUGIN_CODE . '/invoice_email', ScopeInterface::SCOPE_STORE);

        /** Check for "order" - normal checkout flow. */
        $order = $observer->getEvent()->getOrder();
        /** Check for "orders" - multishipping checkout flow. */
        $orders = $observer->getEvent()->getOrders();

        if (!empty($order)) {
            $this->processOrder($order, $captureMode, $invoiceEmailMode);
        } elseif (!empty($orders)) {
            foreach ($orders as $order) {
                $this->processOrder($order, $captureMode, $invoiceEmailMode);
            }
        }

        return $this;
    }

    /**
     * @param Order $order
     * @param $captureMode
     * @param $invoiceEmailMode
     */
    private function processOrder(Order $order, $captureMode, $invoiceEmailMode)
    {
        $payment = $order->getPayment();
        $methodName = $payment->getMethod();

        if ($methodName != self::PLUGIN_CODE) {
            return $this;
        }

        if ("instant" == $captureMode) {
            if (!$order->getId()) {
                return $this;
            }

            try {
                $invoices = $this->invoiceCollectionFactory->create()
                    ->addAttributeToFilter('order_id', array('eq' => $order->getId()));
                $invoices->getSelect()->limit(1);

                if ((int)$invoices->count() !== 0) {
                    return null;
                }

                if (!$order->canInvoice()) {
                    return null;
                }

                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();

                if (!$invoice->getEmailSent() && $invoiceEmailMode == 1) {
                    try {
                        $this->invoiceSender->send($invoice);
                    } catch (\Exception $e) {
                        // Do something if failed to send
                    }
                }
            } catch (\Exception $e) {
                $order->addStatusHistoryComment('Exception message: ' . $e->getMessage(), false); // addStatusHistoryComment() is deprecated !
                $order->save(); // save() is deprecated !
                return null;
            }
        }
        else if ("delayed" == $captureMode) {

            $order->setState(Order::STATE_PENDING_PAYMENT)->setStatus(Order::STATE_PENDING_PAYMENT);
            $order->save();
        }
    }
}