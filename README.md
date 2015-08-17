# Hagtag Thailand
![Logo](http://framework.zend.com/images/logos/ZendFramework-logo.png)

# Description #
This project is an hagtag thailand

## Instructions ##
### Step 1 ###

To clone this repository, do the following:

~~~
git clone --recursive  git@github.com:kangza/hagtag.git DESTINATION
cd DESTINATION
~~~

If you have PHP version < 5.3 (no namespace support):

~~~
git checkout no_namespaces
git submodule update
~~~

### Step 2 ###
You also have to download the Zend framework. I only tested the application with Zend Framework version 1.11.11 ([link](http://framework.zend.com/releases/ZendFramework-1.11.11/ZendFramework-1.11.11-minimal.zip)), but it should work with older versions. If you have any trouble place a [comment](http://thebestsolution.org/zend-login-with-facebook-twitter-and-google/#respond).

Unpack the archive and copy the Zend folder to the library folder of the checked out repository folder.

### Step 3 ###
Change the `application.ini` (can be found in application/configs), to your configuration.

Installation
------------
% zf create project quickstart

    quickstart
    |-- application
    |   |-- Bootstrap.php
    |   |-- configs
    |   |   `-- application.ini
    |   |-- controllers
    |   |   |-- ErrorController.php
    |   |   `-- IndexController.php
    |   |-- models
    |   `-- views
    |       |-- helpers
    |       `-- scripts
    |           |-- error
    |           |   `-- error.phtml
    |           `-- index
    |               `-- index.phtml
    |-- library
    |-- public
    |   |-- .htaccess
    |   `-- index.php
    `-- tests
        |-- application
        |   `-- bootstrap.php
        |-- library
        |   `-- bootstrap.php
        `-- phpunit.xml

Web Server Setup
----------------

### PHP CLI Server

The simplest way to get started if you are using PHP 5.4 or above is to start the internal PHP cli-server in the root directory:

    php -S 0.0.0.0:8080 -t public/ public/index.php

This will start the cli-server on port 8080, and bind it to all network
interfaces.

**Note: ** The built-in CLI server is *for development only*.

### Apache Setup

To setup apache, setup a virtual host to point to the public/ directory of the
project and you should be ready to go! It should look something like below:

    <VirtualHost *:80>
        ServerName quickstart.local
        DocumentRoot /path/to/quickstart/public
     
        SetEnv APPLICATION_ENV "development"
     
        <Directory /path/to/quickstart/public>
            DirectoryIndex index.php
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
    </VirtualHost>
