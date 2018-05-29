<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\Component\Form\Element\SubscriptionPlan;

use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsPool;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Input;

/**
 * Class TotalBillingCycles
 * @package Aheadworks\Sarp\Ui\Component\Form\Element\SubscriptionPlan
 */
class TotalBillingCycles extends Input
{
    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var RestrictionsPool
     */
    private $restrictionsPool;

    /**
     * @param ContextInterface $context
     * @param EngineMetadataPool $engineMetadataPool
     * @param RestrictionsPool $restrictionsPool
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        EngineMetadataPool $engineMetadataPool,
        RestrictionsPool $restrictionsPool,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->engineMetadataPool = $engineMetadataPool;
        $this->restrictionsPool = $restrictionsPool;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (!isset($config['isInputModePerEngine'])) {
            $isInputModePerEngine = [];
            foreach ($this->engineMetadataPool->getEnginesCodes() as $engineCode) {
                $restrictions = $this->restrictionsPool->getRestrictions($engineCode);
                $isInputModePerEngine[$engineCode] = $restrictions->canBeFinite();
            }
            $config['isInputModePerEngine'] = $isInputModePerEngine;
        }
        $this->setData('config', $config);
        parent::prepare();
    }
}
