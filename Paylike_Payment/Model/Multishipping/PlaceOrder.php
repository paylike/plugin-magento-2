<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Esparks\Paylike\Model\Multishipping;

// use Esparks\Paylike\Observer\DataAssignObserver;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Order payments processing for multishipping checkout flow.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrder implements PlaceOrderInterface
{
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;


    /**
     * @param OrderManagementInterface $orderManagement
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     */
    public function __construct(
        OrderManagementInterface $orderManagement,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
    ) {
        $this->orderManagement = $orderManagement;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
    }

    /**
     * @inheritdoc
     */
    public function place(array $orderList): array
    {
        if (empty($orderList)) {
            return [];
        }


        /////////////////////// bellow code need to be improved when the redirect from Paylike modal will work //////////////////
        ///////////// this File/class  is declared in \etc\di.xml, final lines ///////////////////////


        $errorList = [];
        // $firstOrder = $this->orderManagement->place(array_shift($orderList));
        // get payment token from first placed order
        // $paymentToken = $this->getPaymentToken($firstOrder);

        foreach ($orderList as $order) {
            try {
                // $orderPayment = $order->getPayment();
                // $this->setVaultPayment($orderPayment, $paymentToken);
                // $this->setOrderInfo($orderPayment);
                $this->orderManagement->place($order);
            } catch (\Exception $e) {
                $incrementId = $order->getIncrementId();
                $errorList[$incrementId] = $e;
            }
        }

        return $errorList;
    }

    // private function setOrderInfo(OrderPaymentInterface $orderPayment): void
    // {
    //     $orderPayment->setAdditionalInformation(
    //         'payliketransactionid',
    //         '6192626ed929cf62a8e458d7'
    //     );
    // }
}
