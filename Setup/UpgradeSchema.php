<?php

namespace MDevs\Shipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable(
            $setup->getTable('md_shipments')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'orderid',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Order ID'
        )->addColumn(
            'main_barcode',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Main Barcode'
        )->addColumn(
            'tracking_url',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Tracking Url'
        )->addColumn(
            'url_label',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Url Label'
        )->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation Time'
        )->setComment(
            'Shipment created on postbox'
        );

        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
