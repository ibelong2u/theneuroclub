<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Log;

/**
 * Interface RendererInterface
 * @package Aheadworks\Sarp\Block\Adminhtml\Log
 */
interface RendererInterface
{
    /**
     * Produce html output using the given data source
     *
     * @param mixed $data
     * @return string
     */
    public function render($data);
}
