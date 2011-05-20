<?php
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
        $query = $product->create_export_query($order_by, $where) . ' LIMIT 1';

        $db = DBManagerFactory::getInstance();
        $result = $db->query($query, true, '');
        $fields = $db->getFieldsArray($result, true);

        $query = 'DESCRIBE '.$product->table_name;
        $result = $db->query($query, true, '');

        $this->assertGreaterThanOrEqual($db->getRowCount($result), count($fields));
    }
}
