<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Template\Formatter;

use Aheadworks\Sarp\Model\Logger\Data\Template\FormatterInterface;

/**
 * Class Date
 * @package Aheadworks\Sarp\Model\Logger\Data\Template\Formatter
 */
class Date implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format($data)
    {
        $date = new \DateTime($data);
        return $date->format('M d, Y');
    }
}
