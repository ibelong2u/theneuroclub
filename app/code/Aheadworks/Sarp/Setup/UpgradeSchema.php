<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Aheadworks\Sarp\Setup
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addCoreEngineTables($setup);
            $this->addRemoteIpColumns($setup);
            $this->addPaymentMethodColumns($setup);
            $this->addLogTable($setup);
        }
    }

    /**
     * Add core engine tables
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addCoreEngineTables(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'aw_sarp_core_subscription'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_sarp_core_subscription'))
            ->addColumn(
                'subscription_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Core Subscription Id'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Profile Id'
            )->addColumn(
                'is_initial_paid',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Initial Payment Paid'
            )->addColumn(
                'trial_payments_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Trial Payments Count'
            )->addColumn(
                'regular_payments_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Regular Payments Count'
            )->addColumn(
                'payment_failures_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Payment Failures Count'
            )->addColumn(
                'payment_data',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Payment Data'
            )->addColumn(
                'is_reactivated',
                Table::TYPE_BOOLEAN,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Reactivated'
            )->addIndex(
                $setup->getIdxName('aw_sarp_core_subscription', ['profile_id']),
                ['profile_id']
            )->addForeignKey(
                $setup->getFkName(
                    'aw_sarp_core_subscription',
                    'profile_id',
                    'aw_sarp_profile',
                    'profile_id'
                ),
                'profile_id',
                $setup->getTable('aw_sarp_profile'),
                'profile_id',
                Table::ACTION_CASCADE
            )->setComment('Core Subscription');
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'aw_sarp_core_payment'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_sarp_core_payment'))
            ->addColumn(
                'payment_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Core Payment Id'
            )->addColumn(
                'subscription_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Core Subscription Id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Order Id'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Payment Type'
            )->addColumn(
                'scheduled_at',
                Table::TYPE_DATE,
                null,
                ['nullable' => false],
                'Scheduled At'
            )->addColumn(
                'retry_at',
                Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'Retry At'
            )->addColumn(
                'retries_count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Retries Count'
            )->addIndex(
                $setup->getIdxName('aw_sarp_core_payment', ['subscription_id']),
                ['subscription_id']
            )->addForeignKey(
                $setup->getFkName(
                    'aw_sarp_core_payment',
                    'subscription_id',
                    'aw_sarp_core_subscription',
                    'subscription_id'
                ),
                'subscription_id',
                $setup->getTable('aw_sarp_core_subscription'),
                'subscription_id',
                Table::ACTION_CASCADE
            )->setComment('Core Subscription Payment');
        $setup->getConnection()->createTable($table);
    }

    /**
     * Add remote IP address columns
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addRemoteIpColumns(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('aw_sarp_subscriptions_cart'),
            'remote_ip',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Remote Ip'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('aw_sarp_profile'),
            'remote_ip',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Remote Ip'
            ]
        );
    }

    /**
     * Add payment method code column to aw_sarp_profile table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addPaymentMethodColumns(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('aw_sarp_profile'),
            'payment_method_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'default' => null,
                'comment' => 'Payment Method Code'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('aw_sarp_profile'),
            'payment_method_title',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'Payment Method Title'
            ]
        );
    }

    /**
     * Add log tables
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function addLogTable(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'aw_sarp_log'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_sarp_log'))
            ->addColumn(
                'log_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'identity' => true, 'primary' => true],
                'Log ID'
            )->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Profile Id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Customer Id'
            )->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                128,
                [],
                'Customer Email'
            )->addColumn(
                'customer_fullname',
                Table::TYPE_TEXT,
                512,
                [],
                'Customer Fullname'
            )->addColumn(
                'level',
                Table::TYPE_TEXT,
                32,
                ['nullable' => false],
                'Level'
            )->addColumn(
                'date_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Entry Date Time'
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Title'
            )->addColumn(
                'details',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Details'
            )->addColumn(
                'error_details',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Error Details'
            )->addColumn(
                'engine_code',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Engine Code'
            )->setComment('Log');
        $setup->getConnection()->createTable($table);
    }
}
