<?php
require_once ('SysAccessMapper.php');
require_once ('MainModel.php');

/**
 * Add your description here
 *
 * @copyright ZF model generator
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 */
class Default_Model_SysAccess extends MainModel {

    /**
     * mysql var type varchar(16)
     *
     * @var string
     */
    protected $_AccessId;

    /**
     * mysql var type varchar(10)
     *
     * @var string
     */
    protected $_AccId;

    /**
     * mysql var type datetime
     *
     * @var datetime
     */
    protected $_LoginTime;

    /**
     * mysql var type datetime
     *
     * @var datetime
     */
    protected $_LogoutTime;

    /**
     * mysql var type varchar(15)
     *
     * @var string
     */
    protected $_AccIp;

    /**
     * mysql var type varchar(255)
     *
     * @var string
     */
    protected $_AccAgent;

    /**
     * mysql var type varchar(25)
     *
     * @var string
     */
    protected $_AccBrowser;

    /**
     * mysql var type varchar(25)
     *
     * @var string
     */
    protected $_AccOs;

    function __construct() {
        $this->setColumnsList(array(
            'access_id' => 'AccessId',
            'acc_id' => 'AccId',
            'login_time' => 'LoginTime',
            'logout_time' => 'LogoutTime',
            'acc_ip' => 'AccIp',
            'acc_agent' => 'AccAgent',
            'acc_browser' => 'AccBrowser',
            'acc_os' => 'AccOs'
        ));
    }

    /**
     * sets column access_id type varchar(16)
     *
     * @param string $data
     * @return Default_Model_SysAccess
     *
     * */
    public function setAccessId($data) {
        $this->_AccessId = $data;
        return $this;
    }

    /**
     * gets column access_id type varchar(16)
     * @return string
     */
    public function getAccessId() {
        return $this->_AccessId;
    }

    /**
     * sets column acc_id type varchar(10)
     *
     * @param string $data
     * @return Default_Model_SysAccess
     *
     * */
    public function setAccId($data) {
        $this->_AccId = $data;
        return $this;
    }

    /**
     * gets column acc_id type varchar(10)
     * @return string
     */
    public function getAccId() {
        return $this->_AccId;
    }

    /**
     * sets column login_time type datetime
     *
     * @param datetime $data
     * @return Default_Model_SysAccess
     *
     * */
    public function setLoginTime($data) {
        $this->_LoginTime = $data;
        return $this;
    }

    /**
     * gets column login_time type datetime
     * @return datetime
     */
    public function getLoginTime() {
        return $this->_LoginTime;
    }

    /**
     * sets column logout_time type datetime
     *
     * @param datetime $data
     * @return Default_Model_SysAccess
     *
     * */
    public function setLogoutTime($data) {
        $this->_LogoutTime = $data;
        return $this;
    }

    /**
     * gets column logout_time type datetime
     * @return datetime
     */
    public function getLogoutTime() {
        return $this->_LogoutTime;
    }

    /**
     * sets column acc_ip type varchar(15)
     *
     * @param string $data
     * @return Default_Model_SysAccess
     *
     * */
    public function setAccIp($data) {
        $this->_AccIp = $data;
        return $this;
    }

    /**
     * gets column acc_ip type varchar(15)
     * @return string
     */
    public function getAccIp() {
        return $this->_AccIp;
    }

    /**
     * sets column acc_agent type varchar(255)
     *
     * @param string $data
     * @return Default_Model_SysAccess
     *
     * */
    public function setAccAgent($data) {
        $this->_AccAgent = $data;
        return $this;
    }

    /**
     * gets column acc_agent type varchar(255)
     * @return string
     */
    public function getAccAgent() {
        return $this->_AccAgent;
    }

    /**
     * sets column acc_browser type varchar(25)
     *
     * @param string $data
     * @return Default_Model_SysAccess
     *
     * */
    public function setAccBrowser($data) {
        $this->_AccBrowser = $data;
        return $this;
    }

    /**
     * gets column acc_browser type varchar(25)
     * @return string
     */
    public function getAccBrowser() {
        return $this->_AccBrowser;
    }

    /**
     * sets column acc_os type varchar(25)
     *
     * @param string $data
     * @return Default_Model_SysAccess
     *
     * */
    public function setAccOs($data) {
        $this->_AccOs = $data;
        return $this;
    }

    /**
     * gets column acc_os type varchar(25)
     * @return string
     */
    public function getAccOs() {
        return $this->_AccOs;
    }

    /**
     * returns the mapper class
     *
     * @return Default_Model_SysAccessMapper
     *
     */
    public function getMapper() {
        if (null === $this->_mapper) {
            $this->setMapper(new Default_Model_SysAccessMapper());
        }
        return $this->_mapper;
    }

    /**
     * deletes current row by deleting a row that matches the primary key
     *
     * @return int
     */
    public function deleteRowByPrimaryKey() {
        if (!$this->getAccessId()) {
            throw new Exception('Primary Key does not contain a value');
        }
        return $this->getMapper()->getDbTable()->delete('access_id = ' . $this->getAccessId());
    }

}