<?php
require_once ('DbTable/SysAccess.php');

/**
 * Add your description here
 *
 * @copyright ZF model generator
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 */
class Default_Model_SysAccessMapper {

    /**
     * $_dbTable - instance of Default_Model_DbTable_SysAccess
     *
     * @var Default_Model_DbTable_SysAccess
     */
    protected $_dbTable;

    /**
     * finds a row where $field equals $value
     *
     * @param string $field
     * @param mixed $value
     * @param Default_Model_SysAccess $cls
     */
    public function findOneByField($field, $value, $cls) {
        $table = $this->getDbTable();
        $select = $table->select();

        $row = $table->fetchRow($select->where("{$field} = ?", $value));
        if (0 == count($row)) {
            return;
        }

        $cls->setAccessId($row->access_id)
            ->setAccId($row->acc_id)
            ->setLoginTime($row->login_time)
            ->setLogoutTime($row->logout_time)
            ->setAccIp($row->acc_ip)
            ->setAccAgent($row->acc_agent)
            ->setAccBrowser($row->acc_browser)
            ->setAccOs($row->acc_os);
        return $cls;
    }

    /**
     * returns an array, keys are the field names.
     *
     * @param new Default_Model_SysAccess $cls
     * @return array
     *
     */
    public function toArray($cls) {
        $result = array(
            'access_id' => $cls->getAccessId(),
            'acc_id' => $cls->getAccId(),
            'login_time' => $cls->getLoginTime(),
            'logout_time' => $cls->getLogoutTime(),
            'acc_ip' => $cls->getAccIp(),
            'acc_agent' => $cls->getAccAgent(),
            'acc_browser' => $cls->getAccBrowser(),
            'acc_os' => $cls->getAccOs()
        );
        return $result;
    }

    /**
     * finds rows where $field equals $value
     *
     * @param string $field
     * @param mixed $value
     * @param Default_Model_SysAccess $cls
     * @return array
     */
    public function findByField($field, $value, $cls) {
        $table = $this->getDbTable();
        $select = $table->select();
        $result = array();

        $rows = $table->fetchAll($select->where("{$field} = ?", $value));
        foreach ($rows as $row) {
            $cls = new Default_Model_SysAccess();
            $result[] = $cls;
            $cls->setAccessId($row->access_id)
                ->setAccId($row->acc_id)
                ->setLoginTime($row->login_time)
                ->setLogoutTime($row->logout_time)
                ->setAccIp($row->acc_ip)
                ->setAccAgent($row->acc_agent)
                ->setAccBrowser($row->acc_browser)
                ->setAccOs($row->acc_os);
        }
        return $result;
    }

    /**
     * sets the dbTable class
     *
     * @param Default_Model_DbTable_SysAccess $dbTable
     * @return Default_Model_SysAccessMapper
     *
     */
    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * returns the dbTable class
     *
     * @return Default_Model_DbTable_SysAccess
     */
    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_SysAccess');
        }
        return $this->_dbTable;
    }

    /**
     * saves current row
     *
     * @param Default_Model_SysAccess $cls
     *
     */
    public function save(Default_Model_SysAccess $cls, $ignoreEmptyValuesOnUpdate = true) {
        if ($ignoreEmptyValuesOnUpdate) {
            $data = $cls->toArray();
            foreach ($data as $key => $value) {
                if (is_null($value) or $value == '') {
                    unset($data[$key]);
                }
            }
        }

        if (null === ($id = $cls->getAccessId())) {
            unset($data['access_id']);
            $id = $this->getDbTable()->insert($data);
            $cls->setAccessId($id);
        } else {
            if ($ignoreEmptyValuesOnUpdate) {
                $data = $cls->toArray();
                foreach ($data as $key => $value) {
                    if (is_null($value) or $value == '') {
                        unset($data[$key]);
                    }
                }
            }

            $this->getDbTable()->update($data, array(
                'access_id = ?' => $id
            ));
        }
    }

    /**
     * finds row by primary key
     *
     * @param string $id
     * @param Default_Model_SysAccess $cls
     */
    public function find($id, Default_Model_SysAccess $cls) {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }

        $row = $result->current();

        $cls->setAccessId($row->access_id)
            ->setAccId($row->acc_id)
            ->setLoginTime($row->login_time)
            ->setLogoutTime($row->logout_time)
            ->setAccIp($row->acc_ip)
            ->setAccAgent($row->acc_agent)
            ->setAccBrowser($row->acc_browser)
            ->setAccOs($row->acc_os);
    }

    /**
     * fetches all rows
     *
     * @return array
     */
    public function fetchAll() {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Default_Model_SysAccess();
            $entry->setAccessId($row->access_id)
                  ->setAccId($row->acc_id)
                  ->setLoginTime($row->login_time)
                  ->setLogoutTime($row->logout_time)
                  ->setAccIp($row->acc_ip)
                  ->setAccAgent($row->acc_agent)
                  ->setAccBrowser($row->acc_browser)
                  ->setAccOs($row->acc_os)
                  ->setMapper($this);
            $entries[] = $entry;
        }
        return $entries;
    }

    /**
     * fetches all rows optionally filtered by where,order,count and offset
     *
     * @param string $where
     * @param string $order
     * @param int $count
     * @param int $offset
     *
     */
    public function fetchList($where = null, $order = null, $count = null, $offset = null) {
        $resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Default_Model_SysAccess();
            $entry->setAccessId($row->access_id)
                  ->setAccId($row->acc_id)
                  ->setLoginTime($row->login_time)
                  ->setLogoutTime($row->logout_time)
                  ->setAccIp($row->acc_ip)
                  ->setAccAgent($row->acc_agent)
                  ->setAccBrowser($row->acc_browser)
                  ->setAccOs($row->acc_os)
                  ->setMapper($this);
            $entries[] = $entry;
        }
        return $entries;
    }

}
