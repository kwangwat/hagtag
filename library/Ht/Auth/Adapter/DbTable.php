<?php

require_once 'Zend/Auth/Adapter/DbTable.php';

class Ht_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable {

    /**
     * _authenticateCreateSelect() - This method creates a Zend_Db_Select object that
     * is completely configured to be queried against the database.
     *
     * @return Zend_Db_Select
     */
    protected function _authenticateCreateSelect() {
        // build credential expression
        if (empty($this->_credentialTreatment) || (strpos($this->_credentialTreatment, '?') === false)) {
            $this->_credentialTreatment = '?';
        }

        $credentialExpression = new Zend_Db_Expr(
                '(CASE WHEN ' . $this->_zendDb->quoteInto($this->_zendDb->quoteIdentifier($this->_credentialColumn, true) .
                ' = ' . $this->_credentialTreatment, $this->_credential) . ' THEN 1 ELSE 0 END) AS ' .
                $this->_zendDb->quoteIdentifier($this->_zendDb->foldCase('zend_auth_credential_match'))
        );

        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->_tableName, array('*', $credentialExpression))
            ->where($this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity)
            ->where($this->_tableName . '.grp_id > 0');

        return $dbSelect;
    }

}
