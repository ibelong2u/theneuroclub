<?php
namespace Quinoid\PriceDiscount\Block\Adminhtml\Bundlediscount\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_bundlediscount_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Bundlediscount Information'));
    }
}