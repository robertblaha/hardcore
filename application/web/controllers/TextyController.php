<?php

class Web_TextyController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function zobrazAction()
    {
    	$textile = new Textile();
    	$pars = $this->_getAllParams();
    	if (isset($pars['idt']) && trim($this->_getParam('idt')) != '') {
    		$this->view->kod = trim($this->_getParam('idt'));
        	$this->view->txt = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'" . trim($this->_getParam('idt')) . "'")));
        	//print_r($this->view->nacteny);
        	#print_r($this->view->txt);
        	#echo "\n\nPOSLEDNI XML:\n";
			#echo ukazXML($_SESSION['vracene_xml']);
			#die();
        	if (sizeof($this->view->txt) == 0 || !isset($this->view->txt[$this->view->kod])) {
        		$this->view->txt = array($this->view->kod => $textile->TextileThis('Text s kódem *' . $this->view->kod . '* nebyl nalezen.'));
        	} else {
        		$this->view->txt[$this->view->kod] = $textile->TextileThis($this->view->txt[$this->view->kod]);
        	}
    	} else {
    		$this->view->kod = 'info';
        	$this->view->txt = array('info' => 'Nebyl specifikován požadovaný text.');
    	}

        $this->view->strom = stromek(0, $this->_getAllParams());
    }
}

