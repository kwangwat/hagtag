<?php
class Admin_Model_Account extends MainModel {
    
    
    
    
    public function getAccountUser() {
        $db = $this->getAdapter();
        $sql = $db->select()->from("ht_user","*")
                            ->joinLeft("ht_groupuser", "ht_user.grp_id=ht_groupuser.grp_id", array("grp_name"));
        $stmt = $sql->query();
        return $stmt->fetchAll();
    }
    
    
    
    
}