<?php
/**
 * @see Ht_Utils_Mail
 */
require_once 'Ht/Utils/Mail.php';

/**
 * @see Zend_Mail_Transport_Smtp
 */
require_once 'Zend/Mail/Transport/Smtp.php';

/**
 * @see Zend_Mail_Protocol_Smtp
 */
require_once 'Zend/Mail/Protocol/Smtp.php';

/**
 * @see Zend_Mime_Part
 */
require_once 'Zend/Mime/Part.php';

class Ht_Mail {

    protected static $_instance = null;
    protected static $_transport = null;
    protected static $_protocol = null;
    protected $_host = "localhost";
    protected $_from = array();
    protected $_to = array();
    protected $_cc = array();
    protected $_bcc = array();
    protected $_systemName = "Hagtag";
    protected $_config = array();
    protected $_fileAttachment = array();
    protected $_charset = "UTF-8";

    /**
     * Email priority (1 = High, 3 = Normal, 5 = low).
     * @var int
     */
    protected $_priority = 3;
    protected $_port = '25';

    public function __construct($host = "", $port = null, array $config = array()) {
        if ($host) {
            $this->setHost($host);
        }

        if ($port) {
            $this->_port = $port;
        }

        if ($config) {
            $this->_config = $config;
        }

        $this->setProtocol();
    }

    /**
     * Returns an instance of Ht_Mail
     *
     * Singleton pattern implementation
     *
     * @return Ht_Mail Provides a fluent interface
     */
    public static function getInstance($host = "", $port = null, $config = array()) {
        if (null === self::$_instance) {
            self::$_instance = new self($host, $port, $config);
        }

        return self::$_instance;
    }

    public function setCharset($charset) {
        $this->_charset = isset($charset) ? $charset : $this->_charset;
    }

    public function getCharset() {
        return $this->_charset;
    }

    public function setHtName($name) {
        if (!empty($name)) {
            $this->_systemName = $name;
        }
    }

    public function getHtName() {
        return $this->_systemName;
    }

    public function setHost($host) {
        if (!$host) {
            throw new Ht_Exception("Host to set is blank!");
        }
        $this->_host = $host;
    }

    public function setPriority($priority = 3) {
        $this->_priority = $priority;
    }

    public function getPriority() {
        return $this->_priority;
    }

    public function getTransport() {
        if (self::$_transport === null) {
            $this->setTransport();
        }
        return self::$_transport;
    }

    public function setTransport() {
        if (!isset($this->_config['port'])) {
            $this->_config['port'] = $this->_port;
        }
        self::$_transport = new Zend_Mail_Transport_Smtp($this->_host, $this->_config);
        return $this;
    }

    public function getProtocol() {
        if (self::$_protocol === null) {
            $this->setProtocol();
        }
        return self::$_protocol;
    }

    public function setProtocol() {
        self::$_protocol = new Zend_Mail_Protocol_Smtp($this->_host, $this->_port, $this->_config);
    }

    public function addTo($toMail, $toName = null) {
        if (!$toMail) {
            return;
        }

        if (is_array($toMail)) {
            $_to = array();
            foreach ($toMail as $index => $_m) {
                $_to[$index]["email"] = $_m;
                if (!empty($toName[$index])) {
                    $_to[$index]["name"] = $toName[$index];
                }
            }
            $this->_to = array_merge($this->_to, $_to);
        } else {
            $_to["email"] = $toMail;
            if ($toName) {
                $_to["name"] = $toName;
            }
            array_push($this->_to, $_to);
        }
    }

    public function addCc($ccMail, $ccName = null) {
        if ($ccMail == "" or empty($ccMail) or $ccMail === null) {
            return;
        }

        if (is_array($ccMail)) {
            $_cc = array();
            foreach ($ccMail as $index => $_m) {
                $_cc[$index]["email"] = $_m;
                if (!empty($ccName[$index])) {
                    $_cc[$index]["name"] = $ccName[$index];
                }
            }
            $this->_cc = array_merge($this->_cc, $_cc);
        } else {
            $_cc["email"] = $ccMail;
            if ($ccName) {
                $_cc["name"] = $ccName;
            }

            array_push($this->_cc, $_cc);
        }
    }

    public function addBcc($bccMail, $bccName = null) {
        if ($bccMail == "" or empty($bccMail) or $bccMail === null) {
            return;
        }
        $_bcc = array();
        if (is_array($bccMail)) {
            foreach ($bccMail as $index => $_m) {
                $_bcc[$index]["email"] = $_m;
                if (!empty($bccName[$index])) {
                    $_bcc[$index]["name"] = $bccName[$index];
                }
            }
            $this->_bcc = array_merge($this->_bcc, $_bcc);
        } else {
            $_bcc["email"] = $bccMail;
            if ($bccName) {
                $_bcc["name"] = $bccName;
            }
            array_push($this->_bcc, $_bcc);
        }
    }

    public function setFrom($fromMail, $fromName = "") {
        if (!$fromMail) {
            return;
        }
        $this->_from["email"] = $fromMail;

        if ($fromName) {
            $this->_from["name"] = $fromName;
        }
    }

    public function resetTo() {
        $this->_to = array();
    }

    public function resetCc() {
        $this->_cc = array();
    }

    public function resetBcc() {
        $this->_bcc = array();
    }

    public function resetFrom() {
        $this->_from = array();
    }

    public function addAttachment($file) {

        if (is_array($file) && array_key_exists("tmp_name", $file)) {
            $file = (object)$file;
            $fileName = $file->tmp_name;
        } else {
            $fileName = str_replace("\\\\", "/", $file);
        }

        if (!file_exists($fileName)) {
            return;
        }

        $fp = fopen($fileName, "r");
        //$content = file_get_contents($file["tmp_name"]);
        $at = new Zend_Mime_Part($fp);
        $at->type = isset($file->type) ? $file->type : $at->type;
        //$at->disposition = Zend_Mime::DISPOSITION_INLINE;
        $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        //$at->encoding    = Zend_Mime::ENCODING_8BIT;
        $at->encoding = Zend_Mime::ENCODING_BASE64;
        $at->filename = isset($file->name) ? $file->name : end(explode("/", $fileName));

        $this->_fileAttachment[] = $at;

        //$at = null;
        unset($at);
    }

    public function sentMail($subject, $message, $to = null) {
        self::addTo($to);

        $protocol = $this->getProtocol();
        $protocol->connect();
        $protocol->helo($this->_host);

        $transport = $this->getTransport();
        $transport->setConnection($protocol);

        // Loop through messages
        foreach ($this->_to as $to) {
            $mail = new Ht_Utils_Mail($this->_charset);
            $mail->addHeader('X-Priority', $this->_priority);
            $mail->addHeader('X-MailGenerator', $this->_systemName);
            $mail->addTo($to["email"], $to["name"]);
            $mail->setFrom($this->_from["email"], $this->_from["name"]);
            $mail->setReturnPath($this->_from["email"], $this->_from["name"]);
            $mail->setSubject($subject);
            $mail->setBodyText(strip_tags($message), $this->_charset, Zend_Mime::ENCODING_8BIT);
            $mail->setBodyHtml($message, $this->_charset, Zend_Mime::ENCODING_BASE64);

            if (count($this->_cc) > 0) {
                foreach ($this->_cc as $cc) {
                    $mail->addCc($cc["email"], $cc["name"]);
                }
            }

            if (count($this->_bcc) > 0) {
                foreach ($this->_bcc as $bcc) {
                    $mail->addBcc($bcc["email"], $bcc["name"]);
                }
            }

            if (count($this->_fileAttachment) > 0) {
                foreach ($this->_fileAttachment as $at) {
                    if (!$at instanceof Zend_Mime_Part) {
                        continue;
                    }
                    $mail->addAttachment($at);
                }
            }

            // Manually control the connection
            $protocol->rset();
            $mail->send($transport);

            //$mail = null;
            unset($mail);
        }
        $protocol->quit();
        $protocol->disconnect();
        return true;
    }

    public function sentMailForAll($subject, $message, $to = null) {
        self::addTo($to);

        $protocol = $this->getProtocol();
        $protocol->connect();
        $protocol->helo($this->_host);

        $transport = $this->getTransport();

        $transport->setConnection($protocol);

        $mail = new Ht_Utils_Mail($this->_charset);
        $mail->addHeader('X-Priority', $this->_priority);
        $mail->addHeader('X-MailGenerator', $this->_systemName);

        foreach ($this->_to as $to) {
            $mail->addTo($to["email"], $to["name"]);
        }

        $mail->setFrom($this->_from["email"], $this->_from["name"]);
        $mail->setReturnPath($this->_from["email"], $this->_from["name"]);
        $mail->setSubject($subject);
        $mail->setBodyText(strip_tags($message), $this->_charset, Zend_Mime::ENCODING_8BIT);
        $mail->setBodyHtml($message, $this->_charset, Zend_Mime::ENCODING_BASE64);

        if (count($this->_cc) > 0) {
            foreach ($this->_cc as $cc) {
                $mail->addCc($cc["email"], $cc["name"]);
            }
        }

        if (count($this->_bcc) > 0) {
            foreach ($this->_bcc as $bcc) {
                $mail->addBcc($bcc["email"], $bcc["name"]);
            }
        }

        if (count($this->_fileAttachment) > 0) {
            foreach ($this->_fileAttachment as $at) {
                if (!$at instanceof Zend_Mime_Part) {
                    continue;
                }
                $mail->addAttachment($at);
            }
        }

        // Manually control the connection
        $protocol->rset();
        $mail->send($transport);
        $protocol->quit();
        $protocol->disconnect();

        return true;
    }

    protected function _encodeHeader($value) {
        if (Zend_Mime::isPrintable($value)) {
            return $value;
        } else {
            $mime = Zend_Mime::encodeQuotedPrintable($value);
            $quotedValue = str_replace(array('?', ' '), array('=3F', '=20'), $mime);
            return '=?' . $this->_charset . '?Q?' . $quotedValue . '?=';
        }
    }

}