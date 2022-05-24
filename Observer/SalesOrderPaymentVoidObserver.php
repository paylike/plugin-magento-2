<?php

namespace Lunar\Paylike\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Change the order status to canceled
 */
class SalesOrderPaymentVoidObserver implements ObserverInterface
{
    const PLUGIN_CODE = 'paylikepaymentmethod';

    /**
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var OrderPaymentInterface $payment */
        $payment = $observer->getEvent()->getPayment();
        /** @var Order $order */
        $order = $payment->getOrder();

        if (!empty($order)) {
            $methodName = $payment->getMethod();

            if ($methodName != self::PLUGIN_CODE) {
                return $this;
            }

            if (!$order->getId()) {
                return $this;
            }

            $order->setState(Order::STATE_CANCELED)->setStatus(Order::STATE_CANCELED);
            $order->save();
        }

        return $this;
    }
}
