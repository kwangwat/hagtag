<?php
/**
 * Model Access Class
 * @author chatupon
 *
 */
class Admin_Model_SystemAccess extends MainModel {
    
    
    
    
    public function getAccesslog() {
        $db = $this->getAdapter();
        $sql = $db->select()->from("sys_access","*")
                            ->joinLeft("ht_user", "use_id=acc_id", array("use_name","use_lastname"));
        $stmt = $sql->query();
        return $stmt->fetchAll();
    }
    
    
    
    
}