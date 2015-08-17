<?php
class Admin_Model_Category extends MainModel {
    
    
    
    
    public function getCategoryList() {
        $db = $this->getAdapter();
        $sql = $db->select()->from("ht_category",array("*","count"=>"(SELECT COUNT(pro_id) FROM ht_product WHERE ht_product.cat_id=ht_category.cat_id)"))
                            ->order(array("cat_name"));
        $stmt = $sql->query();
        return $stmt->fetchAll();
    }
    
    
    
    
}