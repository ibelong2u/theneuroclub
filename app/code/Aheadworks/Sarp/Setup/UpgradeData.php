<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Setup;

use Aheadworks\Sarp\Model\Profile;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Generator\SequenceConfig;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\SalesSequence\Model\Builder as SequenceBuilder;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpgradeData
 * @package Aheadworks\Sarp\Setup
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SequenceBuilder
     */
    private $sequenceBuilder;

    /**
     * @var SequenceConfig
     */
    private $sequenceConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param SequenceBuilder $sequenceBuilder
     * @param SequenceConfig $sequenceConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SequenceBuilder $sequenceBuilder,
        SequenceConfig $sequenceConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->createSequence();
        }
    }

    /**
     * Create profile sequence
     *
     * @return void
     */
    private function createSequence()
    {
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->sequenceBuilder->setPrefix($this->sequenceConfig->get('prefix'))
                ->setSuffix($this->sequenceConfig->get('suffix'))
                ->setStartValue($this->sequenceConfig->get('startValue'))
                ->setStoreId($store->getId())
                ->setStep($this->sequenceConfig->get('step'))
                ->setWarningValue($this->sequenceConfig->get('warningValue'))
                ->setMaxValue($this->sequenceConfig->get('maxValue'))
                ->setEntityType(Profile::ENTITY)
                ->create();
        }
    }
}
