<?php
/* File: app/code/Atwix/OrderFlow/Setup/InstallData.php */

namespace Esparks\Paylike\Setup;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;


class UpgradeData implements UpgradeDataInterface
{
    /**
     * Custom Processing Order-Status code
     */
    const ORDER_STATUS_PAYMENT_RECEIVED_CODE = 'payment_received';

    /**
     * Custom Processing Order-Status label
     */
    const ORDER_STATUS_PAYMENT_RECEIVED_lABEL = 'Payment Received';

    /**
     * Status Factory
     *
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * Status Resource Factory
     *
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * InstallData constructor
     *
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     *
     * @throws Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '3.0.0', '<')) {


            $this->addNewOrderProcessingStatus();
      
        }
    }

    /**
     * Create new order processing status and assign it to the existent state
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addNewOrderProcessingStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::ORDER_STATUS_PAYMENT_RECEIVED_CODE,
            'label' => self::ORDER_STATUS_PAYMENT_RECEIVED_lABEL,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {

            return;
        }

        $status->assignState(Order::STATE_PROCESSING, false, true);
    }


}