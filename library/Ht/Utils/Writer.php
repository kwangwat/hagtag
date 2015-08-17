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
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Stream.php 8064 2008-02-16 10:58:39Z thomas $
 */

/** Zend_Log_Writer_Abstract */
require_once "Ht/Utils/Writer/Abstract.php";

/**
 * @category   Ht
 * @package    Ht_Writer
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Stream.php 8064 2008-02-16 10:58:39Z thomas $
 */
class Ht_Utils_Writer extends Ht_Utils_Writer_Abstract {
    /**
     * Holds the PHP stream to log to.
     * @var null|stream
     */
    protected $_stream = null;

    /**
     * Class Constructor
     *
     * @param  streamOrUrl     Stream or URL to open as a stream
     * @param  mode            Mode, only applicable if a URL is given
     */
    public function __construct($streamOrUrl, $mode = "a") {
        if(is_resource($streamOrUrl)) {
            $this->_checkOptions($streamOrUrl, $mode);
            $this->_stream = $streamOrUrl;
        } else {
            if(! $this->_stream = fopen($streamOrUrl, $mode, false)) {
                $msg = "\"$streamOrUrl\" cannot be opened with mode \"$mode\"";
                throw new Ht_Writer_Exception($msg);
            }
        }

    }

    protected function _checkOptions($streamOrUrl, $mode) {
        if(get_resource_type($streamOrUrl) != "stream") {
            throw new Ht_Writer_Exception("Resource is not a stream");
        }

        if($mode != "a") {
            throw new Ht_Writer_Exception("Mode cannot be changed on existing streams");
        }
    }

    /**
     * Close the stream resource.
     *
     * @return void
     */
    public function shutdown() {
        if(is_resource($this->_stream)) {
            fclose($this->_stream);
        }
    }

    /**
     * Write a message to the file.
     *
     * @param  array  $data  event data
     * @return void
     */
    protected function _write($data) {
        if(false === fwrite($this->_stream, $data)) {
            throw new Ht_Writer_Exception("Unable to write to stream");
        }
    }

    /**
     * Write directory with path.
     *
     * @param  string  $dir  directory path
     * @param  number  $mod  write directory mode
     * @return void
     */
    public static function mkdir($dir, $mod = 0750) {
        mkdir($dir, 0755, true);
        chmod($dir, $mod);
    }
}
