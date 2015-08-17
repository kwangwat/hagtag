<?php
class Admin_Model_Product extends MainModel {
    
    
    
    
    public function getProductList() {
        $db = $this->getAdapter();
        $sql = $db->select()->from("ht_product","*")
                            ->joinLeft("ht_category", "ht_product.cat_id=ht_category.cat_id", array("cat_name","cat_desc"));
        $stmt = $sql->query();
        return $stmt->fetchAll();
    }
    
    
    
    
}