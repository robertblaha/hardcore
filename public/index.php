<?php
session_start();

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    'd:\Dropbox\dokumenty\wwwroot\apache\ZendFramework-1.11.10-minimal',
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';  
require_once 'Zend/Translate.php';  
require_once 'Zend/Log.php';  
require_once 'Zend/Log/Writer/Stream.php';  
require_once 'Zend/Controller/Front.php';

/** Definice zavisle na lokaci **/
require_once 'defs.php';
/** Knihovny CShopu **/
require_once 'cshop/cshop_lib.php';
require_once 'cshop/classTextile.php';


//Inicializace promennych
if (!isset($_SESSION['kosik']))
	$_SESSION['kosik'] = array();
if (!isset($_SESSION['url']['katalog']))
	$_SESSION['url']['katalog'] = '/katalog/index/index';
if (!isset($_SESSION['katalog']['per_page']))
	$_SESSION['katalog']['per_page'] = 12;
if (!isset($_SESSION['uzivatel']['firma']))
	$_SESSION['uzivatel']['firma'] = '';
if (!isset($_SESSION['uzivatel']['firma_sleva']))
	$_SESSION['uzivatel']['firma_sleva'] = '';
if (!isset($_SESSION['uzivatel']['firma_skup']))
	$_SESSION['uzivatel']['firma_skup'] = '';
if (!isset($_SESSION['uzivatel']['firma_typ']))
	$_SESSION['uzivatel']['firma_typ'] = 'mo';
if (!isset($_SESSION['uzivatel']['cenik']))
	$_SESSION['uzivatel']['cenik'] = WEB_CENIK;
if (!isset($_SESSION['katalog']['pars']['jenskladem']))
	$_SESSION['katalog']['pars']['jenskladem'] = 1;
if (!isset($_SESSION['katalog']['pars']['jenmfp']))
	$_SESSION['katalog']['pars']['jenmfp'] = 1;
if (!isset($_SESSION['katalog']['akce'])) {
	$_SESSION['katalog']['akce'] = 0;
}
if (!isset($_SESSION['katalog']['novy'])) {
	$_SESSION['katalog']['novy'] = 0;
}
if (!isset($_SESSION['katalog']['sezona'])) {
	$_SESSION['katalog']['sezona'] = 0;
}
if (!isset($_SESSION['katalog']['sleva'])) {
	$_SESSION['katalog']['sleva'] = 0;
}


if (!isset($_SESSION['jazyk'])) {
	if (strtolower(substr($_SERVER['HTTP_HOST'], -2)) == 'sk' || (isset($_GET['jazyk']) && $_GET['jazyk'] == 'sk')) {
		$_SESSION['jazyk'] = 'sk';
	} else {
		$_SESSION['jazyk'] = 'cs';
	}
}


    	

$front = Zend_Controller_Front::getInstance();

//Nastaveni cest k modulum
$front->setControllerDirectory(array(
    'default' => CESTA . '/application/default/controllers',
    'katalog'    => CESTA . '/application/katalog/controllers',
    'admin'    => CESTA . '/application/admin/controllers',
    'web'    => CESTA . '/application/web/controllers',
    'obchod'    => CESTA . '/application/obchod/controllers'
));

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV, 
    APPLICATION_PATH . '/configs/application.ini'
);
//$application->bootstrap()
//            ->run();
            
//defince konstant

$front->dispatch();