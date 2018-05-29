<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Log\Renderer;

use Magento\Backend\Block\Template;

/**
 * Class Anchor
 *
 * @method string getHref()
 * @method Anchor setHref(string $href)
 * @method string getTarget()
 * @method Anchor setTarget(string $target)
 * @method string getTitle()
 * @method Anchor setTitle(string $title)
 *
 * @package Aheadworks\Sarp\Block\Adminhtml\Log\Renderer
 */
class Anchor extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp::log/renderer/anchor.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getHref()) {
            return '';
        }
        return parent::_toHtml();
    }
}
