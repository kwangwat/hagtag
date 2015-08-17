<?php
/**
 * PO (*.po) file creator
 *
 * @author
 * @since
 */

require_once 'Ht/Utils/Writer.php';

class Ht_Utils_PoCreator {

    const PROPERTY_PROJECT_ID = "Project-Id-Version";
    const PROPERTY_CREATE_DATE = "POT-Creation-Date";
    const PROPERTY_REVISION_DATE = "PO-Revision-Date";
    const PROPERTY_TRANSLATOR = "Last-Translator";
    const PROPERTY_LANGUAGE_TEAM = "Language-Team";
    const PROPERTY_MIME_VERSION = "MIME-Version";
    const PROPERTY_CONTENT_TYPE = "Content-Type";
    const PROPERTY_ENCODING = "Content-Transfer-Encoding";
    const PROPERTY_EOL = "\\n";
    const MESSAGE_ID_KEY = "msgid";
    const MESSAGE_STRING_KEY = "msgstr";
    const LOCAL_DIR_KEY = 'local_dir';
    const ADAPTER_KEY = 'adapter';
    const LANGUAGE_KEY = 'language';
    const DATA_KEY = 'data';
    const MESSAGE_FILE = "messages";
    const EOL = "\n";

    /**
     * Local key
     *
     * @var string
     */
    protected $_language = 'en';

    /**
     * Location path to store po files
     *
     * @var string
     */
    protected $_locales_dir = '';

    /**
     * Table adapter data table
     *
     * @var Zend_Db_Table_Abstract
     */
    protected $_adapter = null;

    /**
     * Manual data setting from external source
     * Ex.
     * $data = array();
     * $data[0] = array(
     *       'msgid'  => 'MESSAGE_ID',
     *       'msgstr' => 'MESSAGE_TEXT'
     * );
     *
     * @var array|mixed
     */
    protected $_data = array();

    /**
     * Property data setting
     *
     * @var array
     */
    protected $_properties = array(
        "Project-Id-Version" => "",
        "POT-Creation-Date" => "",
        "PO-Revision-Date" => "",
        "Last-Translator" => "",
        "Language-Team" => "Hagtag Team",
        "MIME-Version" => "1.0",
        "Content-Type" => "text/plain; charset=UTF-8",
        "Content-Transfer-Encoding" => "8bit"
    );

    /**
     * Constructure and setting configuration
     *
     * @param array $config
     * @throws Ht_Utils_PoCreater_Exception
     */
    public function __construct($config = array()) {
        if (!isset($config[self::LOCAL_DIR_KEY])) {
            throw new Ht_Utils_PoCreater_Exception("Local path not setting !");
        }
        if (realpath($config[self::LOCAL_DIR_KEY])) {
            $this->_locales_dir = realpath($config[self::LOCAL_DIR_KEY]);
        } else {
            throw new Ht_Utils_PoCreater_Exception("Local path setting up is not exists!");
        }

        if (isset($config[self::LANGUAGE_KEY])) {
            if ($config[self::LANGUAGE_KEY] != "") {
                try {
                    $locale = new Zend_Locale($config[self::LANGUAGE_KEY]);
                    $this->_language = $locale->getLanguage();
                } catch (Ht_Utils_PoCreater_Exception $e) {
                    throw $e;
                }
            }
        }

        if (isset($config[self::ADAPTER_KEY]) && ($config[self::ADAPTER_KEY] instanceof Zend_Db_Table_Abstract)) {
            $this->_adapter = $config[self::ADAPTER_KEY];
        }
        if (isset($config[self::DATA_KEY])) {
            $this->_data = $config[self::DATA_KEY];
        }
    }

    public function setProperties($properties = array()) {
        if (!is_array($properties)) {
            return;
        }

        foreach ($properties as $key => $property) {
            if ($property == "") {
                continue;
            }
            switch ($key) {
                case self::PROPERTY_PROJECT_ID:
                    $this->_properties[self::PROPERTY_PROJECT_ID] = $property;
                    break;
                case self::PROPERTY_CREATE_DATE:
                    $this->_properties[self::PROPERTY_CREATE_DATE] = $property;
                    break;
                case self::PROPERTY_REVISION_DATE:
                    $this->_properties[self::PROPERTY_REVISION_DATE] = $property;
                    break;
                case self::PROPERTY_TRANSLATOR:
                    $this->_properties[self::PROPERTY_TRANSLATOR] = $property;
                    break;
                case self::PROPERTY_LANGUAGE_TEAM:
                    $this->_properties[self::PROPERTY_LANGUAGE_TEAM] = $property;
                    break;
                case self::PROPERTY_MIME_VERSION:
                    $this->_properties[self::PROPERTY_MIME_VERSION] = $property;
                    break;
                case self::PROPERTY_CONTENT_TYPE:
                    $this->_properties[self::PROPERTY_CONTENT_TYPE] = $property;
                    break;
                case self::PROPERTY_ENCODING:
                    $this->_properties[self::PROPERTY_ENCODING] = $property;
                    break;
                default:
                    ;
                    break;
            }
        }
    }

    /**
     * Do create po file
     *
     * @return string Name of po file
     */
    public function create() {
        try {
            $pofile = $this->_locales_dir . "/" . self::MESSAGE_FILE . ".po";
            $writer = new Ht_Utils_Writer($pofile, 'w');

            //$data = $this->_parseData();
            $writer->write(self::MESSAGE_ID_KEY . ' ""' . self::EOL);
            $writer->write(self::MESSAGE_STRING_KEY . ' ""' . self::EOL);
            foreach ($this->_properties as $key => $property) {
                $property = $property . self::PROPERTY_EOL;
                $writer->write('"' . $key . ': ' . $property . '"' . self::EOL);
            }
            $writer->write(self::EOL);

            $data = $this->_parseData();
            foreach ($data as $row) {
                $writer->write(self::MESSAGE_ID_KEY . ' "' . $row[self::MESSAGE_ID_KEY] . '"' . self::EOL);
                $writer->write(self::MESSAGE_STRING_KEY . ' "' . $row[self::MESSAGE_STRING_KEY] . '"' . self::EOL . self::EOL);
            }
            $writer->write(self::EOL);
            $writer->shutdown();

            //$writer = null;
            unset($writer);

            return $pofile;
        } catch (Ht_Utils_PoCreater_Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data from database table if avalid
     * @return array
     */
    protected function _parseData() {
        $data = $this->_data;
        if ($this->_adapter) {
            $select = $this->_adapter->select()->where("language=?", $this->_language);
            $stmt = $select->query();
            if ($stmt) {
                while ($row = $stmt->fetch()) {
                    $data[] = array(
                        self::MESSAGE_ID_KEY => $row[self::MESSAGE_ID_KEY],
                        self::MESSAGE_STRING_KEY => $this->replaceEndLineCharacter($row[self::MESSAGE_STRING_KEY])
                    );
                }
                $stmt->closeCursor();
            }
            unset($select, $row, $stmt);
        }

        return $data;
    }

    protected function replaceEndLineCharacter($str) {
        return str_replace(array("\n", "\r\n"), "\\n", $str);
    }

}

/**
 * Ht_Utils_PoCreater_Exception
 */
class Ht_Utils_PoCreater_Exception extends Zend_Exception {

}
