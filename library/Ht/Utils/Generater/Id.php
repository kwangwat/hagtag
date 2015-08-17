<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Ht_Utils_Generate
 * @package    Ht_Utils_Generate_Id
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Uri.php 5492 2007-06-29 00:51:43Z bkarwin $
 */
/**
 * @see Ht_Utils_Generater
 */
require_once 'Ht/Utils/Generater.php';

/**
 * @see Ht_Utils_Generater_Exception
 */
require_once 'Ht/Utils/Generater/Exception.php';

/**
 * @category   Ht_Utils_Generate
 * @package    Ht_Utils_Generate_Id
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ht_Utils_Generater_Id extends Ht_Utils_Generater {

    protected $digit = 12;
    protected $_number = true;
    protected $_lower_letter = true;
    protected $_upper_letter = true;

    public function __construct($specific = array()) {
        // Parse the specification id generate into the instance variables.
        $this->_parseIdSpecific($specific);

        // Set the Id
        if (!$this->setGeneratedId()) {
            throw new Ht_Utils_Generater_Exception('Can\'t Generate supplied Id');
        }
    }

    /**
     * Parse the id-specific portion of the Id and place its parts into instance variables.
     *
     * @param string $specific
     * @return void
     */
    protected function _parseIdSpecific($specific) {
        // Failed decomposition; no further processing needed
        if (!$specific) {
            return;
        }

        if (is_array($specific)) {
            // Save Id Specification that need no further decomposition
            $this->digit = isset($specific[0]) ? $specific[0] : $this->digit;
            $this->_number = isset($specific[1]) ? $specific[1] : $this->_number;
            $this->_lower_letter = isset($specific[2]) ? $specific[2] : $this->_lower_letter;
            $this->_upper_letter = isset($specific[3]) ? $specific[3] : $this->_upper_letter;
        }
    }

    /**
     * Return a string representation of this generated Id.
     *
     * @return string
     */
    public function getGeneratedId() {
        if (isset($this->_generatedId)) {
            return $this->_generatedId;
        }
        return false;
    }

    public function setGeneratedId() {
        $this->_generatedId = $this->__generate();
        if (isset($this->_generatedId)) {
            return true;
        }
        return false;
    }

    protected function __generate() {
        //init Id
        $id = "";
        //$last_character = "";
        //while Id length < number of characters
        while (strlen($id) < $this->digit) {
            //seed the random number generator
            srand($this->makeSeed());
            $ch_type = (rand() % 2);
            $character = $this->verifyCharacter($ch_type);
            //if ch_type == 0 generate number

            if ($character != "") {
                $id .= $character;
            }
        }
        return $id;
    }

    public function verifyCharacter($charType) {
        $character = '';
        if ($charType == 0 && $this->_number === true) {
            $character = $this->generateNumber();
        } else {
            $character = $this->randCharacter();
        }
        return $character;
    }

    public function randCharacter() {
        $character = '';
        mt_srand($this->makeSeed());
        $rand = mt_rand(0, 9);
        $letter_type = $rand % 2;
        //generate upper case letter
        $character = $this->_generateUpperCaseLetter($character, $letter_type);

        //generate lower case letter
        $character = $this->_generateLowerCaseLetter($character, $letter_type);

        //include number = 0,
        //include upper = 0, include lower = 0
        if ($character == "" && $this->_number === false) {
            $character = $this->generateLetter($letter_type);
        }

        return $character;
    }

    protected function _generateUpperCaseLetter($character, $letter_type) {
        if (($letter_type == 0 && $this->_upper_letter === true)
            || ($this->_lower_letter == 0 && $this->_upper_letter === true)) {
            $character = $this->generateLetter(0);
        }
        return $character;
    }

    protected function _generateLowerCaseLetter($character, $letter_type) {
        if (($letter_type == 1 && $this->_lower_letter === true)
            || ($this->_lower_letter === true && $this->_upper_letter === true)) {
            $character = $this->generateLetter(1);
        }
        return $character;
    }
    /**
     * generate number
     *
     * @return character 0-9
     */
    protected function generateNumber() {
        mt_srand($this->makeSeed());
        $character = mt_rand(0, 9);
        return $character;
    }

    //end generateNumber method

    /**
     * generate lower or upper case letter
     *
     * @param integer_type $letter_type,
     * 0 - upper case, 1 - lower case, 2 - random
     * @return character a-zA-Z
     */
    protected function generateLetter($letter_type = "2") {
        //make seed
        mt_srand($this->makeSeed());
        //if letter_type == '2', either lower case or upper case
        if ($letter_type == "2") {
            $rand = mt_rand(0, 9);
            $letter_type = $rand % 2;
        }
        //generate lower case letter
        if ($letter_type == 1) {
            mt_srand($this->makeSeed());
            $character = mt_rand(97, 122);
        }
        //generate upper case letter
        if ($letter_type == 0) {
            mt_srand($this->makeSeed());
            $character = mt_rand(65, 90);
        }
        $character = chr($character);
        return $character;
    }

    /**
     * makeSeed
     *
     * @return float
     */
    protected function makeSeed() {
        list($usec, $sec) = explode(" ", microtime());
        return (float)$sec + ((float)$usec * 100000);
    }

}
