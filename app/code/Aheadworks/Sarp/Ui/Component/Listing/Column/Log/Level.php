<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\Component\Listing\Column\Log;

use Aheadworks\Sarp\Model\Logger\Source\Level as LevelSource;

/**
 * Class Level
 * @package Aheadworks\Sarp\Ui\Component\Listing\Column\Log
 */
class Level extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var array
     */
    private $levelClassMap = [
        LevelSource::NOTICE => 'level-notice',
        LevelSource::WARNING => 'level-warning',
        LevelSource::ERROR => 'level-error'
    ];

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $index = $this->getName();
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$index] && isset($this->levelClassMap[$item[$index]])) {
                    $item[$index . '_levelClass'] = $this->levelClassMap[$item[$index]];
                }
            }
        }
        return $dataSource;
    }
}
