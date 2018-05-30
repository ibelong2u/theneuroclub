<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\Component\Listing\Column;

/**
 * Class Translated
 * @package Aheadworks\Sarp\Ui\Component\Listing\Column
 */
class Translated extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $index = $this->getName();
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$index]) {
                    $item[$index] = __($item[$index]);
                }
            }
        }
        return $dataSource;
    }
}
