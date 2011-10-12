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
class Admin_AjaxController extends Zend_Controller_Action {
	/**
	 * Inicializace controlleru
	 * Zejmena naplneni hlavicek odezvy a inicializace prekladu
	 */
	public function init() {
	}

	public function nactiAction() {
		$chyba = false;
		//pokud jsou parametry predavane jako POST, tak se s nimi take tak vyrovnej
		$pars = array();
		$p = $this->_getAllParams();
		if (isset($_POST) && sizeof($_POST) > 0) {
			foreach($_POST as $p => $k) {
				$pars[$p] = $k;
			}
		} else {
			$i = 1;
			while(array_key_exists('pn' . $i, $p)) {
				$pars[$p['pn' . $i]] = $p['ph' . $i];
				$i++;
			}
		}
		//print_r($pars);die();
    	$vysledek = readData(trim($p['prikaz']), $pars);
		echo Zend_Json::encode($vysledek);
		//echo json_encode($vysledek);

		unset($vysledek);
		die();
	}
}
