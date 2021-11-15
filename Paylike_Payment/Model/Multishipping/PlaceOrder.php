<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Esparks\Paylike\Model\Multishipping;

use Esparks\Paylike\Observer\DataAssignObserver;

// use PayPal\Braintree\Gateway\Command\GetPaymentNonceCommand;
// use PayPal\Braintree\Model\Ui\ConfigProvider;
// use PayPal\Braintree\Model\Ui\PayPal\ConfigProvider as PaypalConfigProvider;
// use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
// use Magento\Sales\Api\Data\OrderPaymentInterface;
// use Magento\Vault\Api\Data\PaymentTokenInterface;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderInterface;
use Magento\Sales\Api\Data\OrderInterface;
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

    // /**
    //  * @var OrderPaymentExtensionInterfaceFactory
    //  */
    // private $paymentExtensionFactory;

    // /**
    //  * @var GetPaymentNonceCommand
    //  */
    // private $getPaymentNonceCommand;

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
        // $this->getPaymentNonceCommand = $getPaymentNonceCommand;
    }

    /**
     * @inheritdoc
     */
    public function place(array $orderList): array
    {
// /** DEBUGGING */
// $e = new \Exception();
// echo '<pre>';
// print_r($e->getTraceAsString());
// print_r($orderList, true);
// exit;


        if (empty($orderList)) {
            return [];
        }

        $errorList = [];
        $firstOrder = $this->orderManagement->place(array_shift($orderList));
        // get payment token from first placed order
        // $paymentToken = $this->getPaymentToken($firstOrder);
// var_dump($orderList);
// die;
        foreach ($orderList as $order) {
            try {
                // /** @var OrderInterface $order */
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

    private function setOrderInfo(OrderPaymentInterface $orderPayment): void
    {
        $orderPayment->setAdditionalInformation(
            'payliketransactionid',
            '6192626ed929cf62a8e458d7'
        );
    }



    // /**
    //  * Sets vault payment method.
    //  *
    //  * @param OrderPaymentInterface $orderPayment
    //  * @param PaymentTokenInterface $paymentToken
    //  * @return void
    //  */
    // private function setVaultPayment(OrderPaymentInterface $orderPayment, PaymentTokenInterface $paymentToken): void
    // {
    //     $vaultMethod = $this->getVaultPaymentMethod(
    //         $orderPayment->getMethod()
    //     );
    //     $orderPayment->setMethod($vaultMethod);

    //     $publicHash = $paymentToken->getPublicHash();
    //     $customerId = $paymentToken->getCustomerId();
    //     $result = $this->getPaymentNonceCommand->execute(
    //         ['public_hash' => $publicHash, 'customer_id' => $customerId]
    //     )
    //         ->get();

    //     $orderPayment->setAdditionalInformation(
    //         DataAssignObserver::PAYMENT_METHOD_NONCE,
    //         $result['paymentMethodNonce']
    //     );
    //     $orderPayment->setAdditionalInformation(
    //         PaymentTokenInterface::PUBLIC_HASH,
    //         $publicHash
    //     );
    //     $orderPayment->setAdditionalInformation(
    //         PaymentTokenInterface::CUSTOMER_ID,
    //         $customerId
    //     );
    // }

    // /**
    //  * Returns vault payment method.
    //  *
    //  * For placing sequence of orders, we need to replace the original method on the vault method.
    //  *
    //  * @param string $method
    //  * @return string
    //  */
    // private function getVaultPaymentMethod(string $method): string
    // {
    //     $vaultPaymentMap = [
    //         ConfigProvider::CODE => ConfigProvider::CC_VAULT_CODE,
    //         PaypalConfigProvider::PAYPAL_CODE => PaypalConfigProvider::PAYPAL_VAULT_CODE
    //     ];

    //     return $vaultPaymentMap[$method] ?? $method;
    // }

    // /**
    //  * Returns payment token.
    //  *
    //  * @param OrderInterface $order
    //  * @return PaymentTokenInterface
    //  * @throws \BadMethodCallException
    //  */
    // private function getPaymentToken(OrderInterface $order): PaymentTokenInterface
    // {
    //     $orderPayment = $order->getPayment();
    //     $extensionAttributes = $this->getExtensionAttributes($orderPayment);
    //     $paymentToken = $extensionAttributes->getVaultPaymentToken();

    //     if ($paymentToken === null) {
    //         throw new \BadMethodCallException('Vault Payment Token should be defined for placed order payment.');
    //     }

    //     return $paymentToken;
    // }

    // /**
    //  * Gets payment extension attributes.
    //  *
    //  * @param OrderPaymentInterface $payment
    //  * @return OrderPaymentExtensionInterface
    //  */
    // private function getExtensionAttributes(OrderPaymentInterface $payment): OrderPaymentExtensionInterface
    // {
    //     $extensionAttributes = $payment->getExtensionAttributes();
    //     if (null === $extensionAttributes) {
    //         $extensionAttributes = $this->paymentExtensionFactory->create();
    //         $payment->setExtensionAttributes($extensionAttributes);
    //     }

    //     return $extensionAttributes;
    // }
}
