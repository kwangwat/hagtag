<?php
/**
 * Converts gettext translation '.po' files to binary '.mo' files in PHP Using System tools.
 *
 * @author 
 * @since
 *
 * Usage:
 * <?php
 * Ht_Utils_MoConverter::convert( 'input.po', [ 'output.mo' ] );
 * ?>
 */
class Ht_Utils_MoConverter {

    const PHP_SERVER_WINDOWS = 'WINDOWS';
    const PHP_SERVER_UNIX = 'UNIX';

    protected static $_minimum_percentage = 100;

    public static function convert($inputFile, $outputfile = null) {
        if (!$outputfile) {
            $outputfile = str_replace('.po', '.mo', $inputFile);
        }

        $os = self::_getServerOs();
        if (self::PHP_SERVER_UNIX == $os) {
            return self::_convertWithUnixTool($inputFile, $outputfile);
        } elseif (self::PHP_SERVER_WINDOWS == $os) {
            return self::_convertWithWindowsTool($inputFile, $outputfile);
        } else {
            throw new Exception("Operation not support on `" . $os . "` os!");
        }

        return false;
    }

    protected static function _convertWithUnixTool($po, $mo) {
        $msgfmt_output = `msgfmt --statistics $po -o $mo 2>&1`;
        try {
            self::_verifySuccess($msgfmt_output);
            return true;
        } catch (Exception $e) {
            unlink($mo);
            echo $e->getMessage();
            return false;
        }
    }

    protected static function _convertWithWindowsTool($po, $mo) {
        $msgfmt = realpath(APPLICATION_PATH . '/../vendor/GnuWin32/bin/msgfmt.exe');
        $msgfmt_output = system("$msgfmt --statistics $po -o $mo");
        try {
            self::_verifySuccess($msgfmt_output);
            return true;
        } catch (Exception $e) {
            unlink($mo);
            echo $e->getMessage();
            return false;
        }
    }

    protected static function _verifySuccess($msgfmt_output) {
        $matches = array();
        preg_match('/(\d+) translated messages(?:\.|, (\d+) untranslated messages)/', $msgfmt_output, $matches);
        if (isset($matches[2])) {
            $translated_percentage = $matches[1] / ($matches[1] + $matches[2]) * 100;
            if ($translated_percentage < self::$_minimum_percentage) {
                throw new Exception("Translation has only $translated_percentage% translated, " . self::$_minimum_percentage . "% are required.");
            }
        }
        return true;
    }

    protected static function _getServerOs() {
        switch (strtolower(PHP_OS)) {
            case 'win32':
            case 'windows':
            case 'winnt':
                return self::PHP_SERVER_WINDOWS;
                break;
            case 'linux':
            case 'unix':
            case 'freebsd':
            case 'hp-ux':
            case 'irix64':
            case 'netbsd':
            case 'openbsd':
            case 'superior operating system':
                return self::PHP_SERVER_UNIX;
                break;
            default:
                return PHP_OS;
                break;
        }
    }

}
