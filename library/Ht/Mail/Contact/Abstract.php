<?php
abstract class Ht_Mail_Contact_Abstract {

    const CONTACT_FIRSTNAME = 'firstname';
    const CONTACT_LASTNAME = 'lastname';
    const CONTACT_NICKNAME = 'nickname';
    const CONTACT_EMAIL = 'email';
    const CONTACT_TELNUMBER = 'tel';
    const CONTACT_MOBILE = 'mobile';

    protected $_firstname;
    protected $_lastname;
    protected $_nickname;
    protected $_email;
    protected $_tel;
    protected $_mobile;

    public function __construct($data = array()) {
        if (is_array($data)) {
            $this->_setConfig($data);
        }
    }

    protected function _setConfig($data) {
        foreach ($data as $key => $val) {
            $function = "set" . ucfirst($key);
            $this->$function($val);
        }
    }

    /**
     * @return string
     */
    public function getFirstname() {
        return $this->_firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname) {
        $this->_firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname() {
        return $this->_lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname) {
        $this->_lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getNickname() {
        return $this->_nickname;
    }

    /**
     * @param string $_nickname
     */
    public function setNickname($nickname) {
        $this->_nickname = $nickname;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->_email;
    }

    /**
     * @param field_type $email
     */
    public function setEmail($email) {
        $this->_email = $email;
    }

    /**
     * @return string
     */
    public function getTelephone() {
        return $this->_tel;
    }

    /**
     * @param string $tel
     */
    public function setTel($tel) {
        $this->_tel = $tel;
    }

    /**
     * @return string
     */
    public function getMobile() {
        return $this->_mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile) {
        $this->_mobile = $mobile;
    }

    public function __get($method) {
        $prob = "_" . strtolower(str_replace("get", "", $method));
        if (isset($this->$prob)) {
            return $this->$prob;
        }

        return null;
    }

    public function __set($method, $params) {
        $prob = "_" . strtolower(str_replace("set", "", $method));
        if (isset($this->$prob)) {
            $this->$prob = is_array($params) ? current($params) : $params;
        }
    }

    public function __toString() {
        return $this->_lastname . " " . $this->_lastname . "<" . $this->_email . ">";
    }

    public function toArray() {
        $vars = $this->getConstants();
        $data = array();
        foreach ($vars as $val) {
            $prob = "_" . $val;
            $data[$val] = $this->$prob;
        }
        return $data;
    }

    public function getConstants() {
        $reflect = new ReflectionClass(get_class($this));
        return $reflect->getConstants();
    }

}
