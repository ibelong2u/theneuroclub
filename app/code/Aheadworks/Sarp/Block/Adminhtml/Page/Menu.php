<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Page;

/**
 * Page Menu
 *
 * @method Menu setTitle(string $title)
 * @method string getTitle()
 *
 * @package Aheadworks\Sarp\Block\Adminhtml\Page
 * @codeCoverageIgnore
 */
class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @inheritdoc
     */
    protected $_template = 'Aheadworks_Sarp::page/menu.phtml';
}
