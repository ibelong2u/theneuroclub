<?php
/**
 * Copyright © 2015 Quinoid. All rights reserved.
 */

namespace Quinoid\PriceDiscount\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
	
        $installer = $setup;

        $installer->startSetup();

		/**
         * Create table 'pricediscount_bundlediscount'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('pricediscount_bundlediscount')
        )
		->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'pricediscount_bundlediscount'
        )
		->addColumn(
            'firstproduct',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'firstproduct'
        )
		->addColumn(
            'secondproduct',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'secondproduct'
        )
		->addColumn(
            'discount',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'discount'
        )
		/*{{CedAddTableColumn}}}*/
		
		
        ->setComment(
            'Quinoid PriceDiscount pricediscount_bundlediscount'
        );
		
		$installer->getConnection()->createTable($table);
		/*{{CedAddTable}}*/

        $installer->endSetup();

    }
}
