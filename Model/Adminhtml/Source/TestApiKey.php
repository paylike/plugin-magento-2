<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Model\Adminhtml\Source;

use \Esparks\Paylike\Helper\Data as Helper;

/**
 * Class TestApiKey
 */
class TestApiKey extends \Magento\Framework\App\Config\Value
{
    /**
	 * @var Data
	 */
	protected $helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param Helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Helper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Method used for checking if the new value is valid before saving.
     *
     * @return $this
     */
    public function beforeSave()
    {
        /** Check if the new value is empty. */
        if ( ! $this->getValue() ) {
			return $this;
        }

        /** Check if we have saved any validation test public keys. */
        if ( empty( Helper::$validation_test_public_keys ) ) {
            return $this;
        }

        /** Check if the public key is exists among the saved ones. */
        if ( ! in_array( $this->getValue(), Helper::$validation_test_public_keys ) ) {
            /** Mark the new value as invalid */
            $this->_dataSaveAllowed = false;

			$message = __( "The test public key doesn't seem to be valid." );
			throw new \Magento\Framework\Exception\LocalizedException( $message );
		}

        return $this;
    }
}
