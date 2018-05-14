<?php

namespace Quinoid\ProductAttribute\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install( ModuleDataSetupInterface $setup, ModuleContextInterface $context )
    {
        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'primary_banner',
            [
                'label' => 'Primary Banner',
                'type' => 'text',
                'input' => 'textarea',
                'required' => false,
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
            'secondary_banner',
            [
                'label' => 'Secondary Banner',
                'type' => 'text',
                'input' => 'textarea',
                'required' => false,
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
            'product_video',
            [
                'label' => 'Product Video URL (Embedded Youtube/Vimeo)',
                'type' => 'varchar',
                'description' => 'Embedded Vimeo/Youtube URL',
                'input' => 'text',
                'required' => false,
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
            'additional_info',
            [
                'group' => 'Content',
                'type' => 'text',
                'label' => 'Additional Information',
                'input' => 'textarea',
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'group' => 'Content',
            ]
        );
        $setup->endSetup();
    }
}
