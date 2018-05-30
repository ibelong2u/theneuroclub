<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Aheadworks\Sarp\Setup
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Name of connection for sales related tables of extension
     */
    const CONNECTION_FOR_SALES_RELATED_TABLES = 'sales';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'aw_sarp_subscription_plan'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp_subscription_plan'))
            ->addColumn(
                'subscription_plan_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Subscription Plan Id'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website ID'
            )->addColumn(
                'billing_period',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Billing Period'
            )->addColumn(
                'billing_frequency',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Billing Frequency'
            )->addColumn(
                'total_billing_cycles',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Total Billing Cycles'
            )->addColumn(
                'start_date_type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Start Sate Type'
            )->addColumn(
                'start_date_day_of_month',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Day Of Month Of Start Date'
            )->addColumn(
                'is_initial_fee_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Initial Fee Enabled'
            )->addColumn(
                'is_trial_period_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Trial Period Enabled'
            )->addColumn(
                'trial_total_billing_cycles',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Trial Total Billing Cycles'
            )->addColumn(
                'engine_code',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Engine Code'
            )->addIndex(
                $installer->getIdxName('aw_sarp_subscription_plan', 'website_id'),
                'website_id'
            )->addForeignKey(
                $installer->getFkName('aw_sarp_subscription_plan', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )->setComment('Subscription Plan');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_subscription_plan_description'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp_subscription_plan_description'))
            ->addColumn(
                'subscription_plan_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Subscription Plan Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Title'
            )->addColumn(
                'description',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Description'
            )->addIndex(
                $installer->getIdxName('aw_sarp_subscription_plan_description', ['subscription_plan_id']),
                ['subscription_plan_id']
            )->addIndex(
                $installer->getIdxName('aw_sarp_subscription_plan_description', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp_subscription_plan_description',
                    'subscription_plan_id',
                    'aw_sarp_subscription_plan',
                    'subscription_plan_id'
                ),
                'subscription_plan_id',
                $installer->getTable('aw_sarp_subscription_plan'),
                'subscription_plan_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_sarp_subscription_plan_description', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Subscription Plan Description');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_subscriptions_cart'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp_subscriptions_cart'))
            ->addColumn(
                'cart_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Subscriptions Cart Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store Id'
            )->addColumn(
                'subscription_plan_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Subscription Plan Id'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '1'],
                'Is Active'
            )->addColumn(
                'is_virtual',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Virtual'
            )->addColumn(
                'start_date',
                Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'Start Date'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )->addColumn(
                'customer_group_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Customer Group Id'
            )->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Email'
            )->addColumn(
                'customer_dob',
                Table::TYPE_DATETIME,
                null,
                [],
                'Customer Dob'
            )->addColumn(
                'customer_prefix',
                Table::TYPE_TEXT,
                32,
                [],
                'Customer Prefix'
            )->addColumn(
                'customer_firstname',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Firstname'
            )->addColumn(
                'customer_middlename',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Middlename'
            )->addColumn(
                'customer_lastname',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Lastname'
            )->addColumn(
                'customer_suffix',
                Table::TYPE_TEXT,
                32,
                [],
                'Customer Suffix'
            )->addColumn(
                'customer_is_guest',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true],
                'Customer Is Guest'
            )->addColumn(
                'shipping_method',
                Table::TYPE_TEXT,
                40,
                [],
                'Shipping Method'
            )->addColumn(
                'shipping_description',
                Table::TYPE_TEXT,
                255,
                [],
                'Shipping Description'
            )->addColumn(
                'payment_method_code',
                Table::TYPE_TEXT,
                50,
                [],
                'Payment Method Code'
            )->addColumn(
                'global_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Global Currency Code'
            )->addColumn(
                'base_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Base Currency Code'
            )->addColumn(
                'cart_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Cart Currency Code'
            )->addColumn(
                'base_to_global_rate',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base To Global Rate'
            )->addColumn(
                'base_to_cart_rate',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base To Cart Rate'
            )->addColumn(
                'grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Grand Total'
            )->addColumn(
                'base_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Grand Total'
            )->addColumn(
                'subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Subtotal'
            )->addColumn(
                'base_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Subtotal'
            )->addColumn(
                'trial_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Subtotal'
            )->addColumn(
                'base_trial_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Trial Subtotal'
            )->addColumn(
                'tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Tax Amount'
            )->addColumn(
                'base_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Tax Amount'
            )->addColumn(
                'trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Tax Amount'
            )->addColumn(
                'base_trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Tax Amount'
            )->addColumn(
                'initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Initial Fee'
            )->addColumn(
                'base_initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Initial Fee'
            )->addColumn(
                'shipping_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Shipping Amount'
            )->addColumn(
                'base_shipping_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Shipping Amount'
            )->addIndex(
                $installer->getIdxName('aw_sarp_subscriptions_cart', ['customer_id', 'store_id', 'is_active']),
                ['customer_id', 'store_id', 'is_active']
            )->addIndex(
                $installer->getIdxName('aw_sarp_subscriptions_cart', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName('aw_sarp_subscriptions_cart', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Subscriptions Cart');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_subscriptions_cart_item'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp_subscriptions_cart_item'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Item Id'
            )->addColumn(
                'cart_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Cart Id'
            )->addColumn(
                'parent_item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Parent Item Id'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                [],
                'Name'
            )->addColumn(
                'sku',
                Table::TYPE_TEXT,
                255,
                [],
                'Sku'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Qty'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )->addColumn(
                'buy_request',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Buy Request Serialized'
            )->addColumn(
                'product_options',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Product Options Serialized'
            )->addColumn(
                'regular_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Regular Price'
            )->addColumn(
                'base_regular_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Regular Price'
            )->addColumn(
                'regular_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Regular Price Incl Tax'
            )->addColumn(
                'base_regular_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Regular Price Incl Tax'
            )->addColumn(
                'initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Initial Fee'
            )->addColumn(
                'base_initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Initial Fee'
            )->addColumn(
                'trial_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Price'
            )->addColumn(
                'base_trial_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Price'
            )->addColumn(
                'trial_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Price Incl Tax'
            )->addColumn(
                'base_trial_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Price Incl Tax'
            )->addColumn(
                'row_weight',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Row Weight'
            )->addColumn(
                'row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Row Total'
            )->addColumn(
                'base_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Row Total'
            )->addColumn(
                'row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Row Total Incl Tax'
            )->addColumn(
                'base_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Row Total Incl Tax'
            )->addColumn(
                'tax_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Tax Percent'
            )->addColumn(
                'tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Tax Amount'
            )->addColumn(
                'base_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Tax Amount'
            )->addColumn(
                'trial_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Row Total'
            )->addColumn(
                'base_trial_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Row Total'
            )->addColumn(
                'trial_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Row Total Incl Tax'
            )->addColumn(
                'base_trial_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Row Total Incl Tax'
            )->addColumn(
                'trial_tax_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Trial Tax Percent'
            )->addColumn(
                'trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Trial Tax Amount'
            )->addColumn(
                'base_trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Trial Tax Amount'
            )->addIndex(
                $installer->getIdxName('aw_sarp_subscriptions_cart_item', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('aw_sarp_subscriptions_cart_item', ['cart_id']),
                ['cart_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp_subscriptions_cart_item',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp_subscriptions_cart_item',
                    'cart_id',
                    'aw_sarp_subscriptions_cart',
                    'cart_id'
                ),
                'cart_id',
                $installer->getTable('aw_sarp_subscriptions_cart'),
                'cart_id',
                Table::ACTION_CASCADE
            )->setComment('Subscriptions Cart Item');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_subscriptions_cart_address'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp_subscriptions_cart_address'))
            ->addColumn(
                'address_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Address Id'
            )->addColumn(
                'cart_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Cart Id'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )->addColumn(
                'is_save_in_address_book',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true],
                'Is Save In Address Book'
            )->addColumn(
                'customer_address_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Address Id'
            )->addColumn(
                'address_type',
                Table::TYPE_TEXT,
                10,
                [],
                'Address Type'
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                255,
                [],
                'Email'
            )->addColumn(
                'prefix',
                Table::TYPE_TEXT,
                40,
                [],
                'Prefix'
            )->addColumn(
                'firstname',
                Table::TYPE_TEXT,
                20,
                [],
                'Firstname'
            )->addColumn(
                'middlename',
                Table::TYPE_TEXT,
                20,
                [],
                'Middlename'
            )->addColumn(
                'lastname',
                Table::TYPE_TEXT,
                20,
                [],
                'Lastname'
            )->addColumn(
                'suffix',
                Table::TYPE_TEXT,
                40,
                [],
                'Suffix'
            )->addColumn(
                'company',
                Table::TYPE_TEXT,
                255,
                [],
                'Company'
            )->addColumn(
                'street',
                Table::TYPE_TEXT,
                40,
                [],
                'Street'
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                40,
                [],
                'City'
            )->addColumn(
                'region',
                Table::TYPE_TEXT,
                40,
                [],
                'Region'
            )->addColumn(
                'region_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Region Id'
            )->addColumn(
                'postcode',
                Table::TYPE_TEXT,
                20,
                [],
                'Postcode'
            )->addColumn(
                'country_id',
                Table::TYPE_TEXT,
                30,
                [],
                'Country Id'
            )->addColumn(
                'telephone',
                Table::TYPE_TEXT,
                20,
                [],
                'Phone Number'
            )->addColumn(
                'fax',
                Table::TYPE_TEXT,
                20,
                [],
                'Fax'
            )->addColumn(
                'is_same_as_billing',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true],
                'Is Same As Billing'
            )->addColumn(
                'shipping_method_code',
                Table::TYPE_TEXT,
                20,
                [],
                'Shipping Method Code'
            )->addColumn(
                'shipping_carrier_code',
                Table::TYPE_TEXT,
                20,
                [],
                'Shipping Carrier Code'
            )->addIndex(
                $installer->getIdxName('aw_sarp_subscriptions_cart_address', ['cart_id']),
                ['cart_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp_subscriptions_cart_address',
                    'cart_id',
                    'aw_sarp_subscriptions_cart',
                    'cart_id'
                ),
                'cart_id',
                $installer->getTable('aw_sarp_subscriptions_cart'),
                'cart_id',
                Table::ACTION_CASCADE
            )->setComment('Subscriptions Cart Address');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_profile'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp_profile'))
            ->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Profile Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store Id'
            )->addColumn(
                'reference_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Reference Id'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'is_cart_virtual',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Cart Virtual'
            )->addColumn(
                'subscription_plan_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Subscription Plan Id'
            )->addColumn(
                'subscription_plan_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subscription Plan Name'
            )->addColumn(
                'billing_period',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Billing Period'
            )->addColumn(
                'billing_frequency',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Billing Frequency'
            )->addColumn(
                'total_billing_cycles',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Total Billing Cycles'
            )->addColumn(
                'is_initial_fee_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Initial Fee Enabled'
            )->addColumn(
                'is_trial_period_enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Trial Period Enabled'
            )->addColumn(
                'trial_total_billing_cycles',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Trial Total Billing Cycles'
            )->addColumn(
                'start_date',
                Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'Start Date'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )->addColumn(
                'customer_group_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Customer Group Id'
            )->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Email'
            )->addColumn(
                'customer_dob',
                Table::TYPE_DATETIME,
                null,
                [],
                'Customer Dob'
            )->addColumn(
                'customer_fullname',
                Table::TYPE_TEXT,
                32,
                [],
                'Customer Full Name'
            )->addColumn(
                'customer_prefix',
                Table::TYPE_TEXT,
                32,
                [],
                'Customer Prefix'
            )->addColumn(
                'customer_firstname',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Firstname'
            )->addColumn(
                'customer_middlename',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Middlename'
            )->addColumn(
                'customer_lastname',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Lastname'
            )->addColumn(
                'customer_suffix',
                Table::TYPE_TEXT,
                32,
                [],
                'Customer Suffix'
            )->addColumn(
                'customer_is_guest',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true],
                'Customer Is Guest'
            )->addColumn(
                'shipping_method',
                Table::TYPE_TEXT,
                40,
                [],
                'Shipping Method'
            )->addColumn(
                'shipping_description',
                Table::TYPE_TEXT,
                255,
                [],
                'Shipping Description'
            )->addColumn(
                'global_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Global Currency Code'
            )->addColumn(
                'base_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Base Currency Code'
            )->addColumn(
                'profile_currency_code',
                Table::TYPE_TEXT,
                255,
                [],
                'Profile Currency Code'
            )->addColumn(
                'base_to_global_rate',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base To Global Rate'
            )->addColumn(
                'base_to_profile_rate',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base To Profile Rate'
            )->addColumn(
                'grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Grand Total'
            )->addColumn(
                'base_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Grand Total'
            )->addColumn(
                'subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Subtotal'
            )->addColumn(
                'base_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Subtotal'
            )->addColumn(
                'trial_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Trial Subtotal'
            )->addColumn(
                'base_trial_subtotal',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Trial Subtotal'
            )->addColumn(
                'tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Tax Amount'
            )->addColumn(
                'base_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Tax Amount'
            )->addColumn(
                'trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Tax Amount'
            )->addColumn(
                'base_trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Tax Amount'
            )->addColumn(
                'initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Initial Fee'
            )->addColumn(
                'base_initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Initial Fee'
            )->addColumn(
                'shipping_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Shipping Amount'
            )->addColumn(
                'base_shipping_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Shipping Amount'
            )->addColumn(
                'engine_code',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Engine Code'
            )->addColumn(
                'last_order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Last Order Id'
            )->addColumn(
                'last_order_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => true, 'default' => null],
                'Last Order Date'
            )->addIndex(
                $installer->getIdxName('aw_sarp_profile', ['reference_id']),
                ['reference_id']
            )->addForeignKey(
                $installer->getFkName('aw_sarp_profile', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Recurring Profile');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_profile_item'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp_profile_item'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Item Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Profile Id'
            )->addColumn(
                'parent_item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Parent Item Id'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                [],
                'Name'
            )->addColumn(
                'sku',
                Table::TYPE_TEXT,
                255,
                [],
                'Sku'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Qty'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )->addColumn(
                'buy_request',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Buy Request Serialized'
            )->addColumn(
                'product_options',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Product Options Serialized'
            )->addColumn(
                'regular_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Regular Price'
            )->addColumn(
                'base_regular_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Regular Price'
            )->addColumn(
                'regular_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Regular Price Incl Tax'
            )->addColumn(
                'base_regular_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Regular Price Incl Tax'
            )->addColumn(
                'initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Initial Fee'
            )->addColumn(
                'base_initial_fee',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Initial Fee'
            )->addColumn(
                'trial_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Price'
            )->addColumn(
                'base_trial_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Price'
            )->addColumn(
                'trial_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Price Incl Tax'
            )->addColumn(
                'base_trial_price_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Price Incl Tax'
            )->addColumn(
                'row_weight',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Row Weight'
            )->addColumn(
                'row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Row Total'
            )->addColumn(
                'base_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Row Total'
            )->addColumn(
                'row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Row Total Incl Tax'
            )->addColumn(
                'base_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Row Total Incl Tax'
            )->addColumn(
                'tax_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Tax Percent'
            )->addColumn(
                'tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Tax Amount'
            )->addColumn(
                'base_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Tax Amount'
            )->addColumn(
                'trial_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Row Total'
            )->addColumn(
                'base_trial_row_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Row Total'
            )->addColumn(
                'trial_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Trial Row Total Incl Tax'
            )->addColumn(
                'base_trial_row_total_incl_tax',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Base Trial Row Total Incl Tax'
            )->addColumn(
                'trial_tax_percent',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Trial Tax Percent'
            )->addColumn(
                'trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Trial Tax Amount'
            )->addColumn(
                'base_trial_tax_amount',
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000'],
                'Base Trial Tax Amount'
            )->addIndex(
                $installer->getIdxName('aw_sarp_profile_item', ['profile_id']),
                ['profile_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp_profile_item',
                    'profile_id',
                    'aw_sarp_profile',
                    'profile_id'
                ),
                'profile_id',
                $installer->getTable('aw_sarp_profile'),
                'profile_id',
                Table::ACTION_CASCADE
            )->setComment('Recurring Profile Item');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_profile_address'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sarp_profile_address'))
            ->addColumn(
                'address_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Address Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Cart Id'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addColumn(
                'address_type',
                Table::TYPE_TEXT,
                10,
                [],
                'Address Type'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )->addColumn(
                'customer_address_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Address Id'
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                255,
                [],
                'Email'
            )->addColumn(
                'prefix',
                Table::TYPE_TEXT,
                40,
                [],
                'Prefix'
            )->addColumn(
                'firstname',
                Table::TYPE_TEXT,
                20,
                [],
                'Firstname'
            )->addColumn(
                'middlename',
                Table::TYPE_TEXT,
                20,
                [],
                'Middlename'
            )->addColumn(
                'lastname',
                Table::TYPE_TEXT,
                20,
                [],
                'Lastname'
            )->addColumn(
                'suffix',
                Table::TYPE_TEXT,
                40,
                [],
                'Suffix'
            )->addColumn(
                'company',
                Table::TYPE_TEXT,
                255,
                [],
                'Company'
            )->addColumn(
                'street',
                Table::TYPE_TEXT,
                40,
                [],
                'Street'
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                40,
                [],
                'City'
            )->addColumn(
                'region',
                Table::TYPE_TEXT,
                40,
                [],
                'Region'
            )->addColumn(
                'region_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Region Id'
            )->addColumn(
                'postcode',
                Table::TYPE_TEXT,
                20,
                [],
                'Postcode'
            )->addColumn(
                'country_id',
                Table::TYPE_TEXT,
                30,
                [],
                'Country Id'
            )->addColumn(
                'telephone',
                Table::TYPE_TEXT,
                20,
                [],
                'Phone Number'
            )->addColumn(
                'fax',
                Table::TYPE_TEXT,
                20,
                [],
                'Fax'
            )->addIndex(
                $installer->getIdxName('aw_sarp_profile_address', ['profile_id']),
                ['profile_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sarp_profile_address',
                    'profile_id',
                    'aw_sarp_profile',
                    'profile_id'
                ),
                'profile_id',
                $installer->getTable('aw_sarp_profile'),
                'profile_id',
                Table::ACTION_CASCADE
            )->setComment('Recurring Profile Address');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_profile_order'
         */
        $table = $installer->getConnection(self::CONNECTION_FOR_SALES_RELATED_TABLES)
            ->newTable($installer->getTable('aw_sarp_profile_order'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Order Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Profile Id'
            )->addIndex(
                $installer->getIdxName('aw_sarp_profile_order', ['profile_id']),
                ['profile_id']
            )->addForeignKey(
                $installer->getFkName('aw_sarp_profile_order', 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment('Recurring Profile Order');
        $installer->getConnection(self::CONNECTION_FOR_SALES_RELATED_TABLES)->createTable($table);

        $installer->startSetup();
        $installer->endSetup();
    }
}
