<?php

class Katalog_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	//ulozeni posledniho URL katalogu pro pripadny navrat
    	$_SESSION['crumb'] = array();
    	$_SESSION['url']['katalog'] = $_SERVER['REQUEST_URI'];
        $pars = $this->_getAllParams();

        if (!isset($pars['vyrobce'])) {
        	$pars['vyrobce'] = '';
        }
        if (!isset($pars['jenskladem'])) {
        	$pars['jenskladem'] = '0';
        }
        if (!isset($pars['jenmfp'])) {
        	$pars['jenmfp'] = '0';
        }
        //parametry katalogu pro pouziti ve zpracovani
        $_SESSION['katalog']['pars'] = $pars;

        $_SESSION['katalog']['rozpad'] = array();

        $i = 1;
        $nalezeno = true;
        $this->view->detail_url = '/katalog/detail/ukaz';
        while ($nalezeno) {
        	if (isset($pars['u' . (string) $i])) {
        		$nalezeno = true;
        		$this->view->detail_url .= '/' . 'u' . (string) $i . '/' . $pars['u' . (string) $i];
        		$_SESSION['katalog']['rozpad']['u' . (string) $i] = $pars['u' . (string) $i];
        	} else {
        		$nalezeno = false;
        	}
        	$i++;
        }

        //nalezeni posledni kategorie rozpadu
        $i = 1;
        $max_kat = 0;
        while(isset($pars['u' . (string) $i])) {
        	$max_kat = $pars['u' . (string) $i];
        	$i++;
        }

        //podklady pro strankovani
        $this->view->pager = array();
        if (!isset($pars['strana'])) {
        	$strana = 1;
        } else {
        	$strana = $pars['strana'];
        }
        if (isset($pars['nastranu']) && is_numeric($pars['nastranu'])) {
        	$_SESSION['katalog']['per_page'] = (int) $pars['nastranu'];
        	//die('pico');
        }/* else if (!isset($_SESSION['katalog']['pre_page'])) {
        	$_SESSION['katalog']['pre_page'] = 12;
        }*/
        $this->view->pager['strana'] = $strana;

        //razeni
        if (!isset($pars['razeni'])) {
        	$razeni = 'nazev';
        	$pars['razeni'] = 'nazev';
        } else {
        	$razeni = $pars['razeni'];
        }

        if (!isset($pars['smer'])) {
        	$smer = 'asc';
        } else {
        	$smer = $pars['smer'];
        }

        if (!isset($pars['akce'])) {
        	$pars['akce'] = 0;
        }

        if (!isset($pars['novy'])) {
        	$pars['novy'] = 0;
        }

        if (!isset($pars['sleva'])) {
        	$pars['sleva'] = 0;
        }

        if (!isset($pars['sezona'])) {
        	$pars['sezona'] = 0;
        }

        if (!isset($pars['hledani'])) {
        	if (isset($_GET['hledej']) && trim($_GET['hledej']) <> '') {
        		$pars['hledani'] = trim($_GET['hledej']);
        	} else {
        		$pars['hledani'] = '';
        	}
        }

        $_SESSION['katalog']['akce'] = $pars['akce'];
        $_SESSION['katalog']['novy'] = $pars['novy'];
        $_SESSION['katalog']['sleva'] = $pars['sleva'];
        $_SESSION['katalog']['sezona'] = $pars['sezona'];
        $_SESSION['katalog']['hledani'] = $pars['hledani'];

        switch ($razeni) {
        	case 'nazev':
        		$radit_dle = 'k.Popis';
        		break;
        	case 'cena':
        		$radit_dle = 'c.Cena';
        		break;
        	case 'kod':
        		$radit_dle ='k.Produkt';
        		break;
        	default:
        		$radit_dle = 'k.Popis';
        }

        if (strtolower($smer) == 'desc') {
        	$radit_dle .= ' desc';
        } else {
        	$radit_dle .= ' asc';
        }

        //je potreba urcit cenik
        $vysl = readData('zbozi_kategorie',
        	array(
        		'kategorie' => textToDB($max_kat),
        		'strana' => textToDB($this->view->pager['strana']),
        		'nastranu' => $_SESSION['katalog']['per_page'],
        		'cenik' => $_SESSION['uzivatel']['cenik'],
        		'razeni' => $radit_dle,
        		'vyrobce' => textToDB($pars['vyrobce']),
        		'jenskladem' => textToDB($pars['jenskladem']),
        		'jenmfp' => textToDB($pars['jenmfp']),
        		'akce' => $pars['akce'],
        		'novy' => $pars['novy'],
        		'sleva' => $pars['sleva'],
        		'sezona' => $pars['sezona'],
        		'hledani' => $pars['hledani'],
        		'firma_sleva' => $_SESSION['uzivatel']['firma_sleva']
        	)
        );
        //print_r($_SESSION['vracene_xml']);
        $this->view->strom = stromek(0, $this->_getAllParams());
        $this->view->script_files = array('/js/katalog_100822_1.js');
        $this->view->zbozi = array();
        foreach($vysl->z->row as $r) {
        	$this->view->zbozi[] = array('produkt' => (string) $r->produkt, 'popis' => (string) $r->popis, 'sdph' => (double) $r->sdph,
        	 'cena' => (double) $r->cena, 'vshop_id' => (string) $r->vshop_id, 'baleni' => (string) $r->baleni, 'skladem' => (int) $r->skladem,
        	 'text' => $r->xtext, 'sleva' => (double) $r->sleva, 'akce' => (int) $r->akce, 'sezona' => (int) $r->sezona,
        	 'novinka' => (int) $r->novinka, 'doprodej' => (int) $r->doprodej, 'min_mnozstvi' => (int) $r->min_mnozstvi,
        	 'nasobky' => (int) $r->nasobky);
        }
		//seznam dodavatelu
		$this->view->dodavatele = array();
        /*foreach($vysl->d->row as $r) {
        	$this->view->dodavatele[] = (string) $r->dodavatel;
        }*/

        $this->view->pager['pocet_polozek'] = (float) $vysl->p->row->pocet;
        $this->view->pager['pocet_stran'] = ceil((float) $vysl->p->row->pocet/$_SESSION['katalog']['per_page']);
        //sestaveni odkazu bez stranky a bez razeni
        $this->view->pager['url'] = '/' . $pars['module'] . '/' . $pars['controller'] . '/' . $pars['action'];
        $this->view->razeni_url = '/' . $pars['module'] . '/' . $pars['controller'] . '/' . $pars['action'];
        foreach($pars as $k => $h) {
        	if ($k != 'strana' && $k != 'module' && $k != 'controller' && $k != 'action') {
        		$this->view->pager['url'] .= '/' . $k . '/' . $h;
        	}
        	if ($k != 'razeni' && $k != 'smer' && $k != 'strana' && $k != 'module' && $k != 'controller'
        			&& $k != 'action' && $k != 'nastranu' && $k != 'vyrobce' && $k != 'jenskladem' && $k != 'jenmfp') {
        		$this->view->razeni_url .= '/' . $k . '/' . $h;
        	}
        }
        if ($strana > 1) {
        	$this->view->pager['prvni'] = $this->view->pager['url'] . '/strana/1';
        	$this->view->pager['predchozi'] = $this->view->pager['url'] . '/strana/' . ($strana - 1);
        } else {
        	$this->view->pager['prvni'] = '';
        	$this->view->pager['predchozi'] = '';
        }
        if ($strana < $this->view->pager['pocet_stran']) {
        	$this->view->pager['posledni'] = $this->view->pager['url'] . '/strana/' . $this->view->pager['pocet_stran'];
        	$this->view->pager['nasledujici'] = $this->view->pager['url'] . '/strana/' . ($strana + 1);
        } else {
        	$this->view->pager['posledni'] = '';
        	$this->view->pager['nasledujici'] = '';
        }
        $this->view->header_script = "
	cesta_razeni = '" . $_SERVER['SERVER_NAME'] . '/' . $this->view->razeni_url . "';
        ";
        $this->view->radit_dle = $pars['razeni'];
        if (isset($pars['smer'])) {
        	if (strtolower($pars['smer']) == 'desc') {
        		$this->view->header_script .= "stavajici_razeni = 'desc';";
        	} else {
        		$this->view->header_script .= "stavajici_razeni = 'asc';";
        	}
        } else {
        	$this->view->header_script .= "stavajici_razeni = 'asc';";
        }
        //echo $vysl->p->row->pocet;
        //print_r($this->view->pager['url']);
    }

}

