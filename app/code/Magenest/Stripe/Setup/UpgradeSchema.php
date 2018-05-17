<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 12/07/2016
 * Time: 15:00
 */

namespace Magenest\Stripe\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $setup->getConnection()->dropColumn(
                $setup->getTable('magenest_stripe_product_attribute'),
                'store_id'
            );
            $setup->getConnection()->dropColumn(
                $setup->getTable('magenest_stripe_product_attribute'),
                'attribute_id'
            );
        }

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_subscription'),
                'total_cycles',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'comment' => 'Total Cycles'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_subscription'),
                'sequence_order_ids',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Sequence Order IDs'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $table = $setup->getConnection()->newTable($setup->getTable('magenest_stripe_card'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'magento_customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Customer ID'
                )->addColumn(
                    'card_id',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Card ID'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    10,
                    ['default' => 'active'],
                    'Status'
                )
                ->addColumn(
                    'brand',
                    Table::TYPE_TEXT,
                    20,
                    [],
                    'Card Brand'
                )
                ->addColumn(
                    'last4',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Card last 4 digit'
                )
                ->addColumn(
                    'exp_month',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Exp month'
                )
                ->addColumn(
                    'exp_year',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Exp year'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    'null',
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('Card Table');

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_card'),
                'threed_secure',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => '3d secure status'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('magenest_stripe_card'),
                'threed_secure',
                'three_d_secure',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'comment' => '3d secure status'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_product_attribute'),
                'product_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'comment' => 'Stripe product id'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_product_attribute'),
                'is_enabled',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Is enabled'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.1.1') < 0) {
            $table = $setup->getConnection()->newTable($setup->getTable('magenest_stripe_subscription_item'))
                ->addColumn(
                    'id',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'subscription_id',
                    Table::TYPE_TEXT,
                    50,
                    [],
                    'Subscription ID'
                )->addColumn(
                    'plan',
                    Table::TYPE_TEXT,
                    1000,
                    [],
                    'Hash Plan'
                )
                ->addColumn(
                    'quantity',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Quantity'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    'null',
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('subscription item Table');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_subscription'),
                'cancel_at_period_end',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Cancel at period end'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_subscription'),
                'canceled_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Cancel at period end'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_subscription'),
                'created',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Created at'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_subscription'),
                'trial_start',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Trial Start'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_subscription'),
                'trial_end',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Trial End'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_subscription'),
                'ended_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'End At'
                ]
            );
        }

        $setup->endSetup();
    }
}
