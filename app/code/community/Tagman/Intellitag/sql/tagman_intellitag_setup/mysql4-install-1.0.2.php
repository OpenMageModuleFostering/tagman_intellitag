<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
 
		
$table_variables = $installer->getConnection()

    ->newTable($installer->getTable('tagman_intellitag/variables'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_CLOB, 0, array(
        'nullable'  => false,
    ), 'Name')
    ->addColumn('is_static', Varien_Db_Ddl_Table::TYPE_BOOLEAN, 1, array(
        'nullable'  => false,
        ),'Type of Value')
    ->addColumn('static_value', Varien_Db_Ddl_Table::TYPE_CLOB, 0, array(
        'nullable'  => false,
    ), 'Static Value')
    ->addColumn('magento_value', Varien_Db_Ddl_Table::TYPE_CLOB, 0, array(
        'nullable'  => false,
    ), 'Magento Value')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_CLOB, 0, array(
    'nullable'  => false,
    ), 'Value');
		
$installer->getConnection()->createTable($table_variables);

$installer->endSetup();