<?php
require_once 'Ht/Utils/Generater/Exception.php';
abstract class Ht_Utils_Generater {
    const GENERATED_TYPE_ID           = 'id';
    const GENERATED_TYPE_PASSWORD     = 'password';
    const GENERATED_TYPE_GUID         = 'guid';
    const GENERATED_TYPE_UUID         = 'uuid';

    const GENERATED_DIGIT8           = 8;
    const GENERATED_DIGIT16           = 16;
    const GENERATED_DIGIT32           = 32;
    const GENERATED_DIGIT36           = 36;
    const GENERATED_INCLUDE_NUMBER    = TRUE;
    const GENERATED_EXCLUDE_NUMBER    = FALSE;
    const GENERATED_USE_LOWERLETTER   = TRUE;
    const GENERATED_UNUSE_LOWERLETTER = FALSE;
    const GENERATED_USE_UPPERLETTER   = TRUE;
    const GENERATED_UNUSE_UPPERLETTER = FALSE;

    /**
     * Id of this Generater
     * @var string
     */
    protected $_generatedId;
    protected static $_generater = null;

    /******************************************************************************
     * Abstract Methods
     *****************************************************************************/

    /**
     * Ht_Utils_Generater and its subclasses cannot be instantiated directly.
     * Use Ht_Utils_Generater::factory() to return a new Ht_Utils_Generater object.
     */
    abstract public function __construct($typeSpecific = array());

    /**
     * Return a string representation of this generated Id.
     *
     * @return string
     */
    abstract public function getGeneratedId();

    /**
     * Convenience function, checks that a $uri string is well-formed
     * by validating it but not returning an object.  Returns TRUE if
     * $uri is a well-formed generated, or FALSE otherwise.
     *
     * @param string $type
     * @param string $specification
     * @return boolean|string
     */
    public static function get($type, $specification = array()) {
        try {
            $type = self::factory($type, $specification);
        } catch(Exception $e) {
            echo $e->getMessage();
            return false;
        }

        return $type->getGeneratedId();
    }
    /**
     * Create a new Ht_Utils_Generater object for a generated id.  If building a new generated id.
     * Otherwise, supply $id with the complete generated id.
     *
     * @param string $type
     * @throws Ht_Utils_Generater_Exception
     * @return Ht_Utils_Generater
     */
    public static function factory($type = Ht_Utils_Generater::GENERATED_TYPE_ID, $specification = array()) {
        if(! strlen($type)) {
            throw new Ht_Utils_Generater_Exception('An empty string was supplied for the generater type.');
        }

        $type = strtolower($type);

        /**
         * Create a new Ht_Utils_Generater object for the $type. If a subclass of Ht_Utils_Generater exists for the
         * scheme, return an instance of that class. Otherwise, a Ht_Utils_Generater_Exception is thrown.
         */
        switch($type) {
            case self::GENERATED_TYPE_ID:
                $className = 'Ht_Utils_Generater_Id';
                break;
            case self::GENERATED_TYPE_PASSWORD:
                $className = 'Ht_Utils_Generater_Password';
                break;
            case self::GENERATED_TYPE_GUID:
                $className = 'Ht_Utils_Generater_Guid';
                break;
            case self::GENERATED_TYPE_UUID:
                $className = 'Ht_Utils_Generater_Uuid';
                break;
            default:
                throw new Ht_Utils_Generater_Exception("Type \"$type\" is not supported");
        }
        Zend_Loader::loadClass($className);
        self::$_generater = new $className($specification);

        return self::$_generater;
    }

    /**
     * Get the ID's spacific
     *
     * @return string|false Id generater or false if no spacific is set.
     */

    public function getGenerater() {
        if(! empty(self::$_generater)) {
            return self::$_generater;
        } else {
            return false;
        }
    }

    /**
     * Check digit in ISBN-10
     *
     * The 2001 edition of the official manual of the International ISBN Agency says that the ISBN-10 check
     * digit — which is the last digit of the ten-digit ISBN — must range from 0 to 10
     * (the symbol X is used instead of 10)
     * and must be such that the sum of all the ten digits, each multiplied by the integer weight, descending
     * from 10 to 1, is a multiple of the number 11. Modular arithmetic is convenient for calculating the check digit
     * using modulus 11. Each of the first nine digits of the ten-digit ISBN — excluding the check digit,
     * itself — is multiplied by a number in a sequence from 10 to 2, and the remainder of the sum,
     * with respect to 11, is computed. The resulting remainder, plus the check digit, must equal 11;
     * therefore, the check digit is 11 minus the remainder of the sum of the products.
     *
     * For example, the check digit for an ISBN-10 of 0-306-40615-? is calculated as follows:
     * s = 0×10 + 3×9 + 0×8 + 6×7 + 4×6 + 0×5 + 6×4 + 1×3 + 5×2
     * =    0 +  27 +   0 +  42 +  24 +   0 +  24 +   3 +  10
     * = 130
     * 130 / 11 = 11 remainder 9
     * 11 - 9   = 2
     *
     * Thus, the check digit is 2, and the complete sequence is ISBN 0-306-40615-2.
     * Formally, the check digit calculation is:
     *
     * x_{10} = 11 - (10x_1 + 9x_2 + 8x_3 + 7x_4 + 6x_5 + 5x_6 + 4x_7 + 3x_8 + 2x_9) \, (\bmod 11) .
     *
     * The two most common errors in handling an ISBN (e.g., typing or writing it) are an altered digit or
     * the transposition of adjacent digits. Since 11 is a prime number,
     * the ISBN check digit method ensures that
     * these two errors will always be detected. However,
     * if the error occurs in the publishing house and goes undetected,
     * the book will be issued with an invalid ISBN.
     *
     * @param int $check_number Number to check it
     * @return int Check degit number
     */
    public function getCheckDegit($check_number) {
        $check_number = self::parse_number($check_number);
        $degit = str_split((string)$check_number);
        #_print($degit);
        $_sumcheck = 10;
        $result = 0;
        foreach($degit as $key=>$number) {
            $result += ((int)$number * ($_sumcheck - $key));
        }
        $check_degit = (11 - ($result % 11));
        if($check_degit > 10) {
            $check_degit = substr($check_degit, - 1);
        }
        return $check_degit;
    }

    public function parse_number($str_number) {
        return preg_replace('/[\-\_\:\|a-zA-Z\ ]/i', "", $str_number);
    }
}
