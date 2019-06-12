<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Esparks\Paylike\Observer\DataAssignObserver;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Checkout\Model\Cart
     */

    protected $cart;

    /**
     * @param ConfigInterface $config
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        ConfigInterface $config,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->config = $config;
        $this->cart = $cart;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException(__('Payment data object should be provided'));
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $address = $order->getBillingAddress();
        $quote = $this->cart->getQuote();
        $payments = $payment->getPayment();
        $transactionResult = $payments->getAdditionalInformation(DataAssignObserver::TRANSACTION_RESULT);

        return [
            'TXN_TYPE' => 'authorize',
            'TXN_ID' => $transactionResult,
            'ORDER_ID' => $order->getOrderIncrementId(),
            'AMOUNT' => $quote->getGrandTotal(),
            'CURRENCY' => $quote->getQuoteCurrencyCode(),
            'EMAIL' => $address->getEmail()
        ];
    }
}
