<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lunar\Paylike\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TransactionMode
 */
class TransactionMode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_TEST = 'test';
    const MODE_LIVE = 'live';

    /**
     * Possible transaction mode types
     *
     * @return array
     */

    public function toOptionArray()
    {
        return [
            [
                'value' => self::MODE_TEST,
                'label' => __('Test'),
            ],
            [
                'value' => self::MODE_LIVE,
                'label' => __('Live')
            ]
        ];
    }
}
