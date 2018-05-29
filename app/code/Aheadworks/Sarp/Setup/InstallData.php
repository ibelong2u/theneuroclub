<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Setup;

use Aheadworks\Sarp\Model\Product\Attribute\Source\SubscriptionType as SourceSubscriptionType;
use Aheadworks\Sarp\Model\Product\Type\Restrictions as TypeRestrictions;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price as BackendPrice;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Aheadworks\Sarp\Setup
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var TypeRestrictions
     */
    private $productTypeRestrictions;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param TypeRestrictions $productTypeRestrictions
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        TypeRestrictions $productTypeRestrictions
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productTypeRestrictions = $productTypeRestrictions;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $applyTo = implode(
            ',',
            $this->productTypeRestrictions->getSupportedProductTypes()
        );

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'aw_sarp_subscription_type',
            [
                'type' => 'int',
                'group' => 'Subscription Configuration',
                'label' => 'Subscription',
                'input' => 'select',
                'sort_order' => 1,
                'source' => SourceSubscriptionType::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => SourceSubscriptionType::NO,
                'apply_to' => $applyTo,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true
            ]
        )->addAttribute(
            Product::ENTITY,
            'aw_sarp_regular_price',
            [
                'type' => 'decimal',
                'group' => 'Subscription Configuration',
                'label' => 'Regular Payment Price',
                'input' => 'price',
                'backend' => BackendPrice::class,
                'sort_order' => 2,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'apply_to' => $applyTo,
                'visible_on_front' => false
            ]
        )->addAttribute(
            Product::ENTITY,
            'aw_sarp_trial_price',
            [
                'type' => 'decimal',
                'group' => 'Subscription Configuration',
                'label' => 'Trial Price',
                'input' => 'price',
                'backend' => BackendPrice::class,
                'sort_order' => 3,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'apply_to' => $applyTo,
                'visible_on_front' => false,
                'note' => 'Leave empty to disable'
            ]
        )->addAttribute(
            Product::ENTITY,
            'aw_sarp_initial_fee',
            [
                'type' => 'decimal',
                'group' => 'Subscription Configuration',
                'label' => 'Initial Fee',
                'input' => 'price',
                'backend' => BackendPrice::class,
                'sort_order' => 4,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'apply_to' => $applyTo,
                'visible_on_front' => false,
                'note' => 'Leave empty to disable'
            ]
        );
    }
}
