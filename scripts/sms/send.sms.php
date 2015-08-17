<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)."/../../application"));

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', 'development');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../vendor/ZendFramework/library'),
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'helper.inc.php';
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// Initialize Zend_Application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$bootstrap = $application->getBootstrap()->bootstrap('db');
$db = $bootstrap->getResource('db');

$sql = "SELECT use_name,use_lastname,use_phone FROM `ht_user`";
$stmt = $db->query($sql);
while($record = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $message[$record['use_name']] = array(
        'sender'   => 'Hagtag',
        'message'  => "Prepair for test seding Hagtag System. ^^!",
        'receivers' => array($record['use_phone'])
    );
}
Zend_Loader::loadClass('Ht_Utils_Sms');
$sms = Ht_Utils_Sms::getInstance();
foreach($message as $n => $to){

    $sms->setMessage($to['message'])
        ->setSender($to['sender'])
        ->setReceivers($to['receivers']);

    $results = $sms->send();
    echo "Sent to khun `".$n."` number ".current($to['receivers'])." ".current($results)."\n";
    sleep(2);
}

$bootstrap->getResource('db')->closeConnection();

exit();
