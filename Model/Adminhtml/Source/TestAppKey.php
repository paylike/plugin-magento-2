<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Model\Adminhtml\Source;

use \Esparks\Paylike\Helper\Data as Helper;

/**
 * Class TestAppKey
 */
class TestAppKey extends \Magento\Framework\App\Config\Value
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
     * @param \Esparks\Paylike\Helper\Data
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

        /** Create a Paylike Api client. */
        $paylike_client = new \Paylike\Paylike( $this->getValue() );

        /** Validate the test app key by extracting the identity of the paylike client. */
        try {
			$identity = $paylike_client->apps()->fetch();
		} catch ( \Paylike\Exception\ApiException $exception ) {
            /** Mark the new value as invalid */
            $this->_dataSaveAllowed = false;

            $message = __( "The test private key doesn't seem to be valid." );
            $message = $this->helper->handle_exceptions( $exception, $message );
			throw new \Magento\Framework\Exception\LocalizedException( $message );
        }

        /** Extract and save all the test public keys of the merchants with the above extracted identity. */
        try {
			$merchants = $paylike_client->merchants()->find( $identity['id'] );
			if ( $merchants ) {
				foreach ( $merchants as $merchant ) {
					if ( $merchant['test'] ) {
						Helper::$validation_test_public_keys[] = $merchant['key'];
					}
				}
			}
		} catch ( \Paylike\Exception\ApiException $exception ) {
			// we handle in the following statement
        }

        if ( empty( Helper::$validation_test_public_keys ) ) {
            /** Mark the new value as invalid */
            $this->_dataSaveAllowed = false;

			$message = __( "The test private key is not valid or set to live mode." );
			throw new \Magento\Framework\Exception\LocalizedException( $message );
		}

        return $this;
    }
}
