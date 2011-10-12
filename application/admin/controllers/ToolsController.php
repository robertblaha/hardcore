<?php
/**
* Implementace AJAX controlleru
*
* @version  1.0
* @author  Robert Blaha  <robert.blaha@karatsoftware.cz>
* @package  Admin
*/

/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

/**
 * Controller pro zobrazeni a zpracovani prihlasovaciho dialogu...
 *
 */
class Admin_ToolsController extends Zend_Controller_Action {
	/**
	 * Inicializace controlleru
	 * Zejmena naplneni hlavicek odezvy a inicializace prekladu
	 */
	public function init() {
	}

	public function infoAction() {
		phpinfo();
		die();
	}

	public function jserrorhandlerAction() {
            $to = "chyby.mfp@rbc.cz";
            $subject = 'A javascript error has been detected on '. $_GET['website'];
            $message = 'Error: '. $_GET['message']. '<br />';
            $message .= 'Url: '. $_GET['url']. '<br />';
            $message .= 'Line: '. $_GET['line']. '<br />';
            $message .= 'UserAgent: '. $_GET['userAgent']. '<br />';
            $message .= 'Debug: '. $_GET['debug']. '<br />';

            $headers = "From: ". $_GET['from'] ."\r\n"; // Or sendmail_username@hostname by default
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            @mail($to, $subject, $message, $headers);
            die("message sent");
	}

        public function patroexportAction() {
            $this->view->data = readData('patroexport', array('cenik' => WEB_CENIK));
            //echo '<pre>' . ukazXML($_SESSION['vracene_xml']);die();
        }

        public function patrostromAction() {
            $this->view->data = readData('patrostrom', array('cenik' => WEB_CENIK));
            //die(($_SESSION['vracene_xml']));
        }
}
