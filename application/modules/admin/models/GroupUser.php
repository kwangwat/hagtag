<?php
class Admin_Model_GroupUser extends MainModel {
    
    public function getGroupUser() {
        $db = $this->getAdapter();
        $sql = $db->select()->from("ht_groupuser","*");
        $stmt = $sql->query();
        return $stmt->fetchAll();
    }
    
}