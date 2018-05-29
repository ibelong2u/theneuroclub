<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Template;

/**
 * Interface FormatterInterface
 * @package Aheadworks\Sarp\Model\Logger\Data\Template
 */
interface FormatterInterface
{
    /**
     * Format value
     *
     * @param string $data
     * @return string
     */
    public function format($data);
}
