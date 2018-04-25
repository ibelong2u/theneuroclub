<?php

namespace Quinoid\CategoryAttribute\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install( ModuleDataSetupInterface $setup, ModuleContextInterface $context )
    {
        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'category_banner_link',
            [
                'type' => 'varchar',
                'label' => 'Banner Link',
                'input' => 'text',
                'required' => false,
                'sort_order' => 100,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'group' => 'Content',
            ]
        );
        $eavSetup->addAttribute(
             \Magento\Catalog\Model\Category::ENTITY,
             'category_banner_image',
             [
                 'type' => 'varchar',
                 'label' => 'Banner Image',
                 'input' => 'image',
                 'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                 'required' => false,
                 'sort_order' => 200,
                 'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                 'wysiwyg_enabled' => true,
                 'is_html_allowed_on_front' => true,
                 'group' => 'Content',
             ]
         );
        $setup->endSetup();
    }
}
