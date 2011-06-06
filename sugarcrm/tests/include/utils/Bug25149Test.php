<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'modules/Products/Product.php';

/**
 * @group 25149
 */
class Bug25149Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testExportAllProductFields()
    {
        $product = new Product();

        $order_by = array();
        $where = '';
        $query = $product->create_export_query($order_by, $where);

        $db = DBManagerFactory::getInstance();
        $result = $db->limitQuery($query, 1, 1, true, '');
        $export_fields = $db->getFieldsArray($result, true);

        $query = 'SELECT * FROM '.$product->table_name;
        $result = $db->limitQuery($query, 1, 1, true, '');
        $table_fields = $db->getFieldsArray($result, true);

        $this->assertGreaterThanOrEqual(count($table_fields), count($export_fields));
    }
}
