<?php
/**
 * @see Ht_Mail_Contact_Abstract
 */
require_once 'Ht/Mail/Contact/Abstract.php';

class Ht_Mail_Contact extends Ht_Mail_Contact_Abstract {

    public function getDisplayName() {
        return $this->_firstname . ' ' . $this->_lastname;
    }

}