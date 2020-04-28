<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Esparks\Paylike\Model\Adminhtml\Source;

use \Esparks\Paylike\Helper\Data as Helper;

/**
 * Class LiveApiKey
 */
class LiveAppKey extends \Magento\Framework\App\Config\Value
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

        $api_exception = null;
        /** Create a Paylike Api client. */
        $paylike_client = new \Paylike\Paylike( $this->getValue() );

        /** Validate the live app key by extracting the identity of the paylike client. */
        try {
			$identity = $paylike_client->apps()->fetch();
		} catch ( \Paylike\Exception\ApiException $exception ) {
            /** Mark the new value as invalid */
            $this->_dataSaveAllowed = false;

            $message = __( "The live private key doesn't seem to be valid." );
            $message = $this->helper->handle_exceptions( $exception, $message );
			throw new \Magento\Framework\Exception\LocalizedException( $message );
        }

        /** Extract and save all the live public keys of the merchants with the above extracted identity. */
        try {
			$merchants = $paylike_client->merchants()->find( $identity['id'] );
			if ( $merchants ) {
				foreach ( $merchants as $merchant ) {
					if ( $merchant['test'] ) {
						Helper::$validation_live_public_keys[] = $merchant['key'];
					}
				}
			}
		} catch ( \Paylike\Exception\ApiException $exception ) {
            // we handle in the following statement
            $api_exception = $exception;
        }

        if ( empty( Helper::$validation_live_public_keys ) ) {
            /** Mark the new value as invalid */
            $this->_dataSaveAllowed = false;

            $message = __( "The live private key is not valid or set to test mode." );
            if ( $api_exception ) {
                $message = $this->helper->handle_exceptions( $api_exception, $message );
            }
			throw new \Magento\Framework\Exception\LocalizedException( $message );
		}

        return $this;
    }
}
