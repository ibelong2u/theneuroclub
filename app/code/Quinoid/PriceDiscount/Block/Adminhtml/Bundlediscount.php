<?php
namespace Quinoid\PriceDiscount\Block\Adminhtml;
class Bundlediscount extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_controller = 'adminhtml_bundlediscount';/*block grid.php directory*/
        $this->_blockGroup = 'Quinoid_PriceDiscount';
        $this->_headerText = __('Bundlediscount');
        $this->_addButtonLabel = __('Add Discount'); 
        parent::_construct();

    }
}
