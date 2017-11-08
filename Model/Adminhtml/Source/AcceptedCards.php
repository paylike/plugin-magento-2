<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class AcceptedCards
 */
class AcceptedCards implements \Magento\Framework\Option\ArrayInterface
{
    const CARD_VISA = 'visa';
    const CARD_VISAELECTRON = 'visaelectron';
    const CARD_MASTERCARD = 'mastercard';
    const CARD_MAESTRO = 'maestro';


    /**
     * Possible checkout mode types
     *
     * @return array
     */

    public function toOptionArray()
    {
        return [
            [
                'value' => self::CARD_VISA,
                'label' => __('Visa'),
            ],
            [
                'value' => self::CARD_VISAELECTRON,
                'label' => __('Visa Electron'),
            ],
            [
                'value' => self::CARD_MASTERCARD,
                'label' => __('MasterCard'),
            ],
            [
                'value' => self::CARD_MAESTRO,
                'label' => __('Maestro'),
            ],
        ];
    }
}
