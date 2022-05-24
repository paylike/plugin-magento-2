<?php
namespace Lunar\Paylike\Model\Adminhtml\Source;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class LogsActions extends Field {
  protected $_template = 'Lunar_Paylike::system/config/LogsActions.phtml';

  public function __construct(Context $context, array $data = []) {
    parent::__construct($context, $data);
  }

  public function render(AbstractElement $element) {
    $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
    return parent::render($element);
  }

  protected function _getElementHtml(AbstractElement $element) {
    return $this->_toHtml();
  }

  public function getCustomUrl() {
    return $this->getUrl('router/controller/action');
  }

  public function getExportButtonHtml() {
    $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(['id' => 'paylike_export_button', 'label' => __('Export logs')]);

    return $button->toHtml();
  }

  public function getDeleteButtonHtml() {
    $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(['id' => 'paylike_delete_button', 'label' => __('Delete logs')]);

    return $button->toHtml();
  }
}
