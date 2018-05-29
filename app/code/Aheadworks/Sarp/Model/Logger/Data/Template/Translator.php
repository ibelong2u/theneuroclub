<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Template;

use Magento\Framework\Phrase;

/**
 * Class Translator
 * @package Aheadworks\Sarp\Model\Logger\Data\Template
 */
class Translator
{
    /**
     * Construction general regular expression
     */
    const CONSTRUCTION_GENERAL_PATTERN = '/{{(.*?)}}/si';

    /**
     * Translate string value containing template directives
     *
     * @param $value
     * @return Phrase
     */
    public function translate($value)
    {
        $placeholderArgs = [];
        if (preg_match_all(self::CONSTRUCTION_GENERAL_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $index => $construction) {
                $placeholderArgs[$index] = $construction[0];
                $value = str_replace($construction[0], '%' . ($index + 1), $value);
            }
        }
        return new Phrase($value, $placeholderArgs);
    }
}
