<?php

namespace Webshopapps\Matrixrate\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->dropTable($installer->getTable('shipping_matrixrate'));
        if (!$installer->tableExists('shipping_matrixrate')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('shipping_matrixrate')
                    )->addColumn(
                            'pk', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'Primary Key'
                    )->addColumn(
                            'website_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0], 'Author Name'
                    )->addColumn(
                            'dest_country_id', Table::TYPE_TEXT, 4, ['nullable' => false, 'default' => 0], 'Destination Country ID'
                    )->addColumn(
                            'dest_region_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0], 'Destination Region ID'
                    )->addColumn(
                            'dest_city', Table::TYPE_TEXT, 30, ['nullable' => false, 'default' => ''], 'Destination City'
                    )->addColumn(
                            'dest_zip', Table::TYPE_TEXT, 10, ['nullable' => false, 'default' => ''], 'Destination Zip'
                    )->addColumn(
                            'dest_zip_to', Table::TYPE_TEXT, 10, ['nullable' => false, 'default' => ''], 'Destination Zip To'
                    )->addColumn(
                            'condition_name', Table::TYPE_TEXT, 20, ['nullable' => false, 'default' => 0], 'Condition Name'
                    )->addColumn(
                            'condition_from_value', Table::TYPE_DECIMAL, [12, 4], ['nullable' => false, 'default' => '0.0000'], 'Condition From Value'
                    )->addColumn(
                            'condition_to_value', Table::TYPE_DECIMAL, [12, 4], ['nullable' => false, 'default' => '0.0000'], 'Condition To Value'
                    )->addColumn(
                            'price', Table::TYPE_DECIMAL, [12, 4], ['nullable' => false, 'default' => '0.0000'], 'Price'
                    )->addColumn(
                            'cost', Table::TYPE_DECIMAL, [12, 4], ['nullable' => false, 'default' => '0.0000'], 'Cost'
                    )->addColumn(
                            'delivery_type', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Cost'
                    )->addIndex(
                    $installer->getIdxName(
                            'unique_index_matrix', [
                        'website_id',
                        'dest_country_id',
                        'dest_region_id',
                        'dest_city',
                        'dest_zip',
                        'dest_zip_to',
                        'condition_name',
                        'condition_from_value', 'condition_to_value', 'delivery_type'
                            ], AdapterInterface::INDEX_TYPE_UNIQUE
                    ), [
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_city',
                'dest_zip',
                'dest_zip_to',
                'condition_name',
                'condition_from_value', 'condition_to_value', 'delivery_type'
                    ], ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }

}
