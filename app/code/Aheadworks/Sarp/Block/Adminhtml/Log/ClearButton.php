<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Log;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ClearButton
 * @package Aheadworks\Sarp\Block\Adminhtml\Log
 */
class ClearButton implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $clearLogConfirmMsg =
            __("Are you sure you want to delete all the log records? This action cannot be reversed.");
        $data = [
            'label' => __('Clear Log'),
            'class' => 'primary',
            'on_click' => 'deleteConfirm(\'' . $clearLogConfirmMsg . '\', \'' . $this->getClearLogUrl() . '\')',
        ];
        return $data;
    }

    /**
     * @return string
     */
    public function getClearLogUrl()
    {
        return $this->getUrl('*/*/clear');
    }
}
