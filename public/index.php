<?php
ob_start('initial_output');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
	realpath(APPLICATION_PATH . '/../vendor'),
	realpath(APPLICATION_PATH . '/../vendor/ZendFramework/library'),
    realpath(APPLICATION_PATH . '/../vendor/minify-2.1.7/min/lib'),
	realpath(APPLICATION_PATH . '/../vendor/phpsniff-2.1.3'),
    get_include_path(),
)));

require_once APPLICATION_PATH . '/../vendor/facebook-php-sdk-v4/src/Facebook/autoload.php';

require_once 'helper.inc.php';
/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();

function initial_output($buffer) {
    require_once('Minify/HTML.php');
    require_once('Minify/CSS.php');
    require_once('JSMin.php');
    $buffer = Minify_HTML::minify($buffer, array(
            'cssMinifier' => array('Minify_CSS', 'minify'),
            'jsMinifier' => array('JSMin', 'minify')
    ));
    return $buffer;
}