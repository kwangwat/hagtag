<?php
class Admin_Model_ProductImages extends MainModel {
    
    public function getProductImages() {
        $db = $this->getAdapter();
        $sql = $db->select()->from("ht_category",array("cat_name","cat_desc"))
                            ->joinLeft("ht_product", "ht_product.cat_id=ht_category.cat_id", array("*"))
                            ->group(array("cat_name"));
        $stmt = $sql->query();
        return $stmt->fetchAll();
    }
    
}