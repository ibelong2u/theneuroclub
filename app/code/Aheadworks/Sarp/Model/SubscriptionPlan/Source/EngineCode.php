<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionPlan\Source;

use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class EngineCode
 * @package Aheadworks\Sarp\Model\SubscriptionPlan\Source
 */
class EngineCode implements OptionSourceInterface
{
    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var array
     */
    private $options;

    /**
     * @param EngineMetadataPool $engineMetadataPool
     */
    public function __construct(EngineMetadataPool $engineMetadataPool)
    {
        $this->engineMetadataPool = $engineMetadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            foreach ($this->engineMetadataPool->getEnginesCodes() as $engineCode) {
                $metadata = $this->engineMetadataPool->getMetadata($engineCode);
                $this->options[] = [
                    'value' => $engineCode,
                    'label' => __($metadata->getLabel())
                ];
            }
        }
        return $this->options;
    }
}
