<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	#vynulovani rozpadu stromu
    	$_SESSION['katalog']['pars']['u1'] = 0;
    	$_SESSION['katalog']['rozpad'] = 0;
        $this->view->strom = stromek(0, $this->_getAllParams());
        $pom_txt = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'uvod_top'")));
        $vysl = readData('index', array());
        $this->view->kategorie = array();
        foreach($vysl->k->row as $r) {
        	$this->view->kategorie[(string) $r->idk] = (string) $r->kat;
        }
		//definice stylu pro kategorie
		$css = '';
		$index = 1;
		foreach($this->view->kategorie as $id => $kat) {
			$css .= '#rozcestnik li#kat-' . trim($id) . ' a, #rozcestnik li#kat-' . trim($id) . ' a:visited {' . "\n";
			$css .= 'background: #162983 url("/grafika/kat_' . trim($id) . '.jpg");' . "\n";
			if ($index == $index) {
				$css .= "height: 41px;\n";
				$css .= "padding-top: 37px;\n";
			}
			$css .= "}\n";
			$css .= '#rozcestnik li#kat-' . trim($id) . ' a:hover, #rozcestnik li#kat-' . trim($id) . ' a:focus, #rozcestnik li#kat-' . trim($id) . ' a:active {' . "\n";
			$css .= 'background: #5da6dc url("/grafika/kat_' . trim($id) . '_ho.jpg");' . "\n";
			$css .= "}\n";
			if ($index >= 3) {
				$index = 0;
			}
			$index++;
		}
		//$this->view->css_defs = array(0 => $css);
		$textile = new Textile();
		$this->view->txt = array('uvod_top' => $textile->TextileThis($pom_txt['uvod_top']));
    }

	public function jazykAction() {
		//die($this->_getParam('jazyk'));
		$_SESSION['jazyk'] = strtolower($this->_getParam('jazyk'));
		$zdroj = parse_url($_SERVER[HTTP_REFERER]);
        $this->_Redirect($zdroj['path']);
	}
}

