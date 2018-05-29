<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Template\Html;

use Aheadworks\Sarp\Block\Adminhtml\Log\Renderer\InvoiceLink;
use Aheadworks\Sarp\Block\Adminhtml\Log\Renderer\OrderLink;
use Aheadworks\Sarp\Block\Adminhtml\Log\RendererInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Class RendererPool
 * @package Aheadworks\Sarp\Model\Logger\Data\Template\Html
 */
class RendererPool
{
    /**
     * @var array
     */
    private $renders = [
        'orderLink' => OrderLink::class,
        'invoiceLink' => InvoiceLink::class
    ];

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Create renderer
     *
     * @param string $rendererCode
     * @return RendererInterface|null
     * @throws \LogicException
     */
    public function createRenderer($rendererCode)
    {
        if (!isset($this->renders[$rendererCode])) {
            return null;
        }
        $rendererInstance = $this->layout->createBlock($this->renders[$rendererCode]);
        if (!$rendererInstance instanceof RendererInterface) {
            throw new \LogicException(
                sprintf('Renderer %s does not implement required interface.', $rendererCode)
            );
        }
        return $rendererInstance;
    }
}
