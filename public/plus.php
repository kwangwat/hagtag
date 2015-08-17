<?php
// Initialize the application path and autoloading
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . "/../application"));

defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', 'development');

set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../vendor/ZendFramework/library'),
        realpath(APPLICATION_PATH . '/../library'),
        realpath(APPLICATION_PATH . '/../vendor/google-api-php-client/src'),
        get_include_path()
)));

/** Zend_Application */
require_once 'helper.inc.php';
// require_once "Google_Client.php";
// require_once "contrib/Google_PlusService.php";

$code = file_get_contents('php://input');

//$code = $request->getContent();
// $client = new Google_Client();
// $client->setApplicationName("Hagtag");
// $client->setClientId("784155728777-h4iodu2gli7uq4s3gnboo8j2v2d38l8m.apps.googleusercontent.com");
// $client->authenticate($code);

// $client = new Google_Client();
// $client->setAuthConfigFile('client_secret_784155728777-h4iodu2gli7uq4s3gnboo8j2v2d38l8m.apps.googleusercontent.com.json');
// $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
// $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');


    //require_once realpath(dirname(__FILE__).'/../vendor/google-api-php-client/src/Google/autoload.php');

    session_start(); // Create a session

    /**************************
    * Google Client Configuration
    *
    * You may want to consider a modular approach,
    * and do the following in a separate PHP file.
    ***************************/

    //required libraries
    //set_include_path("../src/" . PATH_SEPARATOR . get_include_path());
    require_once 'Google/autoload.php';
    require_once 'Google/Client.php';
    require_once 'Google/Service/Analytics.php';

    /* API client information */
    $clientId = '784155728777-h4iodu2gli7uq4s3gnboo8j2v2d38l8m.apps.googleusercontent.com';
    $clientSecret = 'hzSHpfNhj6o8JUVp0RoLRcXP';
    $redirectUri = 'http://'.$_SERVER["HTTP_HOST"].'/oauth2callback.php';
    $devKey = 'AIzaSyCAHTw5G9A-PbRIxxC3_HE7vkVN0gUW67I';
    // Create a Google Client.
    $client = new Google_Client();
    $client->setApplicationName('Hagtag'); // Set your app name here

    /* Configure the Google Client with your API information */

    // Set Client ID and Secret.
    $client->setClientId($clientId);
    $client->setClientSecret($clientSecret);

    // Set Redirect URL here - this should match the one you supplied.
    $client->setRedirectUri('postmessage');

    // Set Developer Key and your Application Scopes.
    $client->setDeveloperKey($devKey);
    $client->setScopes(
        array('https://www.googleapis.com/auth/userinfo.profile')
    );

    // Create a Google Analytics Service using the configured Google Client.
    $analytics = new Google_Service_Analytics($client);

    // Check if there is a logout request in the URL.
    if (isset($_REQUEST['logout'])) {
        // Clear the access token from the session storage.
        unset($_SESSION['access_token']);
    }

    // Check if there is an authentication code in the URL.
    // The authentication code is appended to the URL after
    // the user is successfully redirected from authentication.
    if (isset($code)) {
        // Exchange the authentication code with the Google Client.
        $client->authenticate($code); 

        // Retrieve the access token from the Google Client.
        // In this example, we are storing the access token in
        // the session storage - you may want to use a database instead.
        $_SESSION['access_token'] = $client->getAccessToken(); 
        
        $google_oauthV2 = new Google_Service_Oauth2($client);
        //_print($google_oauthV2);
        $user_info = $google_oauthV2->userinfo->get();
        var_dump($user_info);
        die();

        // Once the access token is retrieved, you no longer need the
        // authorization code in the URL. Redirect the user to a clean URL.
        header('Location: '.filter_var($redirectUri, FILTER_SANITIZE_URL));
    }

    // If an access token exists in the session storage, you may use it
    // to authenticate the Google Client for authorized usage.
    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
    }

    // If the Google Client does not have an authenticated access token,
    // have the user go through the OAuth2 authentication flow.
    if (!$client->getAccessToken()) {
        // Get the OAuth2 authentication URL.
        $authUrl = $client->createAuthUrl();

        /* Have the user access the URL and authenticate here */

        // Display the authentication URL here.
    }
    
    /**************************
    * OAuth2 Authentication Complete
    *
    * Insert your API calls here 
    ***************************/
    
    exit;
// public function connect()
// {

//     $client = new Google_Client();
//     $client->setApplicationName('API Test');
//     $client->setClientId('my client id');
//     $client->setClientSecret('my client secret');
//     $client->setRedirectUri('postmessage');

//     // Get auth code - this works
//     $code = Input::get('auth');

//     $client->authenticate($code);
//     $token = json_decode($client->getAccessToken());

//     return "Token: $token";
// }