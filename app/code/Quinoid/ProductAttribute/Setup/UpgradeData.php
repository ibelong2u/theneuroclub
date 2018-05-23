<?php

namespace Quinoid\ProductAttribute\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface {

	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory) {

      $this->eavSetupFactory = $eavSetupFactory;
   }

	public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
		
		if (version_compare($context->getVersion(), '1.0.1', '<')) {
            
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'product_video_description',
                [
                    'label' => 'Product Video Description',
                    'type' => 'text',
                    'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 2,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'wysiwyg_enabled' => true,
                    'is_html_allowed_on_front' => true,
                    'unique' => false,
                    'is_used_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'group' => 'Attributes',
                ]
            );
            
			$eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'bundle_items_features',
                [
                    'label' => 'Bundle Items Features',
                    'type' => 'text',
                    'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 2,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'wysiwyg_enabled' => true,
                    'is_html_allowed_on_front' => true,
                    'unique' => false,
                    'is_used_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'group' => 'Attributes',
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'tagline',
                [
                    'label' => 'Product Tagline',
                    'type' => 'varchar',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'wysiwyg_enabled' => true,
                    'is_html_allowed_on_front' => true,
                    'unique' => false,
                    'is_used_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'group' => 'Content',
                ]
            );
		}
	}
}