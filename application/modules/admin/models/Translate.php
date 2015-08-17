<?php
class Admin_Model_Translate extends MainModel {

    protected $_dbTable;
    protected $_type;
    
    public function getMapper() {
        if (null === $this->_mapper) {
            $this->setMapper(new Admin_Model_Translate());
        }
    
        return $this->_mapper;
    }
    
    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Admin_Model_DbTable_Translate');
        }
    
        return $this->_dbTable;
    }
    
    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
    
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
    
        $this->_dbTable = $dbTable;
    
        return;
    }

    public function setLanguageData($data, $key, $code) {
        $db = $this->getAdapter();

        $key_chk = $this->getLanguageDataByCode_Key($code, $key);
        if ($key_chk != "") {
            $arr_insert = array("msgstr" => $data, "modified" => new Zend_Db_Expr("NOW()"));
            $db->update("sys_languages", $arr_insert, "msgid='" . $code . "' AND UPPER(language) = UPPER('" . $key . "')");
        } else {
            $arr_insert = array("msgstr" => $data, "msgid" => $code, "language" => strtolower($key), "modified" => new Zend_Db_Expr("NOW()"));
            $db->insert("sys_languages", $arr_insert);
        }
    }

    public function getLanguageDataByCode_Key($code, $key) {
        $db = $this->getAdapter();
        $select = $db->select()->from("sys_languages", array("msgid"))
                               ->where("msgid = '$code' AND UPPER(language) = UPPER('" . $key . "')");
        return $db->fetchOne($select);
    }

    public function getLanguageDataById($id) {
        $db = $this->getAdapter();
        $select = $db->select()->from("sys_languages", array("msgid"))
            ->where("id = '$id'");
        return $db->fetchOne($select);
    }

    public function getLanguageData($id) {
        $db = $this->getAdapter();
        $row = $this->getLanguageDataById($id);
        $select = $db->select()->from("sys_languages", array("id", "msgid", "language", "msgstr"))
                               ->where("msgid = '$row'");
        return $db->fetchAll($select);
    }

    public function deletedata($id) {
        $db = $this->getAdapter();
        $rs = $db->delete("sys_languages", "msgid='" . $id . "'");
        return $rs ? "true" : "false";
    }

    public function getLabelFromDb($lang = "en") {
        $db = $this->getAdapter();
        $sql = $db->select()->from("sys_languages", array("msgid", "msgstr"))
                ->where("language=?", $lang)
                ->where("msgstr!=''");
        $rs = $db->fetchAll($sql);
        if (is_array($rs)) {
            $data = array();
            foreach ($rs as $f) {
                array_push($data, array("msgid" => $f["msgid"], "msgstr" => $f["msgstr"]));
            }
        }
        return $data;
    }

    public function getDatalistSearch($key) {
        $db = $this->getAdapter();
        $select = $db->select()->from("sys_languages", array("msgid", "msgid"))
                               ->where("msgid LIKE '$key%'");
        return $db->fetchPairs($select);
    }
    
    public function getTranslateList($where, $order) {
        $db = $this->getAdapter();
        $select = $db->select()->from("sys_languages", array("*"));
        if($where){
            $select->where($where);
        }
        if($order){
            $select->order($order);
        }
        return $db->fetchAll($select);
    }

}