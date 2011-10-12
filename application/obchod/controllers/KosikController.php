<?php

class Obchod_KosikController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        #promenna se slevami do zahlavi
        $this->view->header_script = "
        	var slc = new Array(),slp = new Array();
        	";
        $i = 1;
        while(isset($_SESSION['slevy'][$i])) {
        	$this->view->header_script .= "slc[$i] = " . $_SESSION['slevy'][$i]['castka'] . ";\r\n";
        	$this->view->header_script .= "slp[$i] = " . $_SESSION['slevy'][$i]['procento'] . ";\r\n";
        	$i++;
        }
    }

    public function pridejAction()
    {
    	/**
    	 * zpracovava se jako AJAX, tzn. je treba, aby vratil JSON
    	 */
        // action body
        //predane parametry - melo by byt predano katalogove cislo a mnozstvi
        $pars = $this->_getAllParams();
        if (array_key_exists('kat_id', $pars)) {
	        //print_r($pars);	        die('dddj');
        	if (!array_key_exists('mnozstvi', $pars)) {
        		$pars['mnozstvi'] = 1;
        	}
        	$vysl = readData('dokosiku', array('kat_id' => $pars['kat_id'], 'mnozstvi' => $pars['mnozstvi'], 'cenik' => $_SESSION['uzivatel']['cenik'], 'firma_sleva' => $_SESSION['uzivatel']['firma_sleva']));
	        //die($pars['cenamj']);
	        //die(str_replace('_', '.', $pars['cenamj']));
            foreach($vysl->r->row as $r) {
		        //vyhodnoceni nasobku a minimalniho mnozstvi
	        	if ((int) $pars['mnozstvi'] < (int) $r->min_mnozstvi) {//musim doplnit na minimalni
	        		$xmnozstvi = (int) $r->min_mnozstvi;
	        	} else {
	        		$xmnozstvi = (int) $pars['mnozstvi'];
	        	}
		        if ((int) $r->nasobky == 0) {//neprodava se po nasobcich kusu, jen overim, jestli je prodano alespon minimalni mnozstvi
		        	$xmjvbal = 1;
		        } else {//prodava se po nasobcich - momentalne se prodava jen v kusech a hlida to obrazovka :-(
		        	$xmjvbal = (int) $r->min_mnozstvi;
		        }
		        #die("x: $xmjvbal m: $xmnozstvi");
		        if ((double) $r->sleva != 0) {
            		$cena = (double) $r->cenamj - ((double) $r->cenamj * (double) $r->sleva / 100);
		        } else {
            		$cena = (double) $r->cenamj;
		        }
            	//usporadam pole dle polozek
            	ksort($_SESSION['kosik']);
            	$poradi = maxKlicPole(($_SESSION['kosik'])) + 1;
				$_SESSION['kosik'][$poradi] = array(
		        	'produkt' => (string) $r->kat_id,
		        	'nazev' => (string) $r->nazev,
		        	'mj' => (string) $r->mj,
		        	'mj_v_bal' => $xmjvbal,
		        	'mnozstvi' => $xmnozstvi,
		        	'sdph' => (string) $r->sdph,
		        	'zakl_cena' => (double) $r->cenamj,
		        	'cena' => $cena,
		        	'sleva' => (string) $r->sleva, 
		        	#'mj_evid' => (double) $xmjvbal * (double) $xmnozstvi,
		        	'mj_evid' => (double) $xmnozstvi,
		        	#'cena_radek' => round((double)$xmjvbal * (double) $xmnozstvi * $cena, 2),
		        	'cena_radek' => round((double) $xmnozstvi * $cena, 2),
		        	#'cena_radek_dph' => round((double)(string) $xmjvbal * (double) $xmnozstvi * (double) $cena * (1 + (double) $r->sdph/100), 2)
		        	'cena_radek_dph' => round((double) $xmnozstvi * (double) $cena * (1 + (double) $r->sdph/100), 2)
		        );
		        if (isset($_SESSION['uzivatel']['login']) && $_SESSION['uzivatel']['login'] != '') {
			        //zjistim pridavanou polozku
			        $pol = sizeof($_SESSION['kosik']);
			        $par = $_SESSION['kosik'][$pol];
			        $par['osoba'] = $_SESSION['uzivatel']['jmeno'];
			        readData('pridej_do_kosiku', $par);
		        }
            }
        }
        //a ted se pripravim na vraceni informaci do AJAX pozadavku
        //$zdroj = parse_url($_SESSION['referer']);
        $_SESSION['kosik_max_pol'] = $poradi;
        if ($pars['ajax'] == '1') {
	        $vrat = array(
	        	'polozka' => array(
	        					'nazev' => $_SESSION['kosik'][$poradi]['nazev'],
	        					'mj_evid' => $_SESSION['kosik'][$poradi]['mj_evid'],
	        					'cena_radek' => $_SESSION['kosik'][$poradi]['cena_radek'],
	        					'cena_radek_dph' => $_SESSION['kosik'][$poradi]['cena_radek_dph']
	        				),
	        	'kosik' => kosikInfo(),
	        	'kosik_max_pol' => $poradi
	        );
 	       //die(json_encode($vrat, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP));
 	       die(Zend_Json::encode($vrat));
        } else {
        	$this->_Redirect($_SESSION['referer']);
        }
    }


    public function zrusAction()
    {
        // action body
        //jako parametr se predava rusena polozka kosiku
        unset($_SESSION['kosik'][$this->_getParam('pol')]);
        if (isset($_SESSION['uzivatel']['login']) && $_SESSION['uzivatel']['login'] != '') {
        	readData('odeber_z_kosiku', array('osoba' => $_SESSION['uzivatel']['jmeno'], 'polozka' => ((int) $this->_getParam('pol'))));
        }
        //$zdroj = parse_url($_SESSION['referer']);
        $this->_Redirect($_SESSION['referer']);
    }
    
    public function upravAction() {
    	$pol = $this->_getParam('pol');
    	$mnozstvi = $this->_getParam('mnozstvi');
    	$_SESSION['kosik'][$pol]['mnozstvi'] = (double) $mnozstvi;
    	#$_SESSION['kosik'][$pol]['mj_evid'] = (double) $mnozstvi * $_SESSION['kosik'][$pol]['mj_v_bal'];
    	$_SESSION['kosik'][$pol]['mj_evid'] = (double) $mnozstvi;
    	#$_SESSION['kosik'][$pol]['cena_radek'] = round($_SESSION['kosik'][$pol]['mnozstvi'] * $_SESSION['kosik'][$pol]['mj_v_bal'] * $_SESSION['kosik'][$pol]['cena'], 2);
    	$_SESSION['kosik'][$pol]['cena_radek'] = round($_SESSION['kosik'][$pol]['mnozstvi'] * $_SESSION['kosik'][$pol]['cena'], 2);
    	$_SESSION['kosik'][$pol]['cena_radek_dph'] = round($_SESSION['kosik'][$pol]['cena_radek'] * (1 + $_SESSION['kosik'][$pol]['sdph']/100), 2);
    	if (isset($_SESSION['uzivatel']['jmeno']) && trim($_SESSION['uzivatel']['jmeno']) != '') {
	    	$vysl = readData('uprav_polozku_kosiku', array('firma' => $_SESSION['uzivatel']['firma'], 'osoba' => $_SESSION['uzivatel']['jmeno'],
	    		'pol' => $pol, 'mnozstvi' => $_SESSION['kosik'][$pol]['mnozstvi'],
	    		'mj_evid' => $_SESSION['kosik'][$pol]['mj_evid'], 'cena_radek' => $_SESSION['kosik'][$pol]['cena_radek'],
	    		'cena_radek_dph' => $_SESSION['kosik'][$pol]['cena_radek_dph']));
    	}
    	die('OK');
    }

    public function vysypAction()
    {
        // action body
        //jako parametr se predava rusena polozka kosiku
        unset($_SESSION['kosik']);
        $_SESSION['kosik'] = array();
        if (isset($_SESSION['uzivatel']['login']) && $_SESSION['uzivatel']['login'] != '') {
	        readData('vysyp_kosik', array('osoba' => $_SESSION['uzivatel']['jmeno']));
        }
        $_SESSION['kosik_max_pol'] = 0;
        //$zdroj = parse_url($_SESSION['referer']);
        $this->_Redirect($_SESSION['referer']);
    }

	public function ukazAction() {
        $this->view->strom = stromek(0, $this->_getAllParams());
        $this->view->script_files = array('/js/kosik_110516.js');
		$this->view->header_script .= "
		dojo.addOnLoad(function(){
			PrepoctiKosik();
		});";
		//projdu polozky kosiku a pokud nemaji nazev, dohledam ho
		foreach($_SESSION['kosik'] as $k => $r) {
			if (!isset($r['nazev']) || trim($r['nazev']) == '') {
				$vysl = readData('nazevzbozi', array('kat_id' => $r['kat_id']));
				//echo ukazXML($_SESSION['vracene_xml']);
				$_SESSION['kosik'][$k]['nazev'] = (string) $vysl->n->row->popis;
			}
		}
		$this->view->kod_txt = 'kos_ukaz';
		$this->view->txt = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'" . $this->view->kod_txt ."'")));
		if (!isset($this->view->txt[$this->view->kod_txt])) {
			$this->view->txt[$this->view->kod_txt] = 'Neni nadefinovan text s kodem ' . $this->view->kod_txt;
		}
	}
	
	public function dopravaAction() {
        $this->view->strom = stromek(0, $this->_getAllParams());
        $this->view->script_files = array('/js/kosik_110516.js');
       	$kosik = kosikInfo();
       	$vysl = readData('doprava', array('cenik' => $_SESSION['uzivatel']['cenik'], 'cena' => $kosik['cena_dph']));
       	$this->view->zp_dopr = array();
        $prvni = '';
        $je_defa = false; //osetreni neexistujici defa dopravy
       	foreach($vysl->d->row as $r) {
       		$this->view->zp_dopr[] = array('doprava' => (string) $r->doprava, 'popis' => (string) $r->popis,
       			'produkt' => (string) $r->produkt, 'cena' => (double) $r->cena, 'sdph' => (double) $r->sdph);
       		if ($prvni == '') {
       			$prvni = (string) $r->doprava;
       		}
       		if ((string) $r->doprava == DEFA_DOPRAVA) {
       			$je_defa = true;
       		}
       	}
       	$this->view->defa_doprava = $je_defa ? DEFA_DOPRAVA : $prvni;
       	$this->view->zp_plat = array();
       	foreach($vysl->p->row as $r) {
       		$this->view->zp_plat[] = array('platba' => (string) $r->platba, 'popis' => (string) $r->popis,
       			'produkt' => (string) $r->produkt, 'sdph' => (double) $r->sdph, 'cena' => (double) $r->cena);
       	}
		$this->view->kod_txt = 'kos_dopr';
		$this->view->txt = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'" . $this->view->kod_txt ."'")));
		if (!isset($this->view->txt[$this->view->kod_txt])) {
			$this->view->txt[$this->view->kod_txt] = 'Neni nadefinovan text s kodem ' . $this->view->kod_txt;
		}
		//naplneni pripustnych hodnot dopravy
		$this->view->vazby = array();
		foreach($vysl->v->row as $r) {
			$this->view->vazby[trim((string) $r->platba)][trim((string) $r->doprava)] = 1;
		}
		#print_r($this->view->vazby);die();
	}
	
	public function zpracujdopravuAction() {
            // zjistim nazev dopravy a platby z databaze
            $vysl = readData('dopr_pl_info', array('doprava' => $_POST['doprava'], 'platba' => $_POST['platba']));
                    $_SESSION['objednavka']['doprava'] = array('kod' => $_POST['doprava'], 'popis' => trim((string) $vysl->d->row->popis), 'cena' => $_POST['dopr-' . str_replace(' ', '-', $_POST['doprava'])]);
                    $_SESSION['objednavka']['platba'] = array('kod' => $_POST['platba'], 'popis' => trim((string) $vysl->p->row->popis), 'cena' => $_POST['plat-' . str_replace(' ', '-', $_POST['platba'])]);
            #$this->_Redirect(THIS_SERVER . '/obchod/kosik/doruceni');
            $this->_Redirect('/obchod/kosik/doruceni');
	}

	public function doruceniAction() {
        $this->view->strom = stromek(0, $this->_getAllParams());
        $this->view->script_files = array('/js/kosik_110516.js');
       	$vysl = readData('doruceni', array('firma' => $_SESSION['uzivatel']['firma'], 'login' => $_SESSION['uzivatel']['login']));
       	$this->view->firma = array('nazev_firmy' => trim((string) $vysl->f->row->nazev_firmy), 'ulice' => trim((string) $vysl->f->row->ulice),
       			'psc' => trim((string) $vysl->f->row->psc), 'mesto' => trim((string) $vysl->f->row->mesto), 'stat' => trim((string) $vysl->f->row->stat),
       			'telefon' => trim((string) $vysl->f->row->telefon), 'ico' => trim((string) $vysl->f->row->ico), 'dic' => trim((string) $vysl->f->row->dic));
       	$this->view->osoba = array('osoba' => trim((string) $vysl->u->row->osoba), 'email' => trim((string) $vysl->u->row->email), 
       			'nazev_firmy' => trim((string) $vysl->u->row->nazev_firmy), 'ulice' => trim((string) $vysl->u->row->ulice),
       			'psc' => trim((string) $vysl->u->row->psc), 'mesto' => trim((string) $vysl->u->row->mesto), 'stat' => trim((string) $vysl->u->row->stat),
       			'telefon' => trim((string) $vysl->u->row->telefon), 'ico' => trim((string) $vysl->u->row->ico), 'dic' => trim((string) $vysl->u->row->dic));
       	//dorucovaci adresa - jen pokud se lisi od fakturacni
       	$this->view->doruceni = array('nazev_firmy' => '', 'osoba' => '', 'ulice' => '', 'psc' => '', 'mesto' => '');
       	if ((trim($this->view->osoba['nazev_firmy']) != '' && $this->view->firma['nazev_firmy'] <> $this->view->osoba['nazev_firmy'])
			|| (trim($this->view->osoba['ulice']) != '' && $this->view->firma['ulice'] <> $this->view->osoba['ulice'])
       		|| (trim($this->view->osoba['mesto']) != '' && $this->view->firma['mesto'] <> $this->view->osoba['mesto'])
       		) {
       			$this->view->doruceni = $this->view->osoba;
       			$this->view->doruceni['lisise'] = 'ano';
       		} else {
       			$this->view->doruceni['lisise'] = 'ne';
       		}
		$this->view->kod_txt = 'kos_doruc';
		$this->view->txt = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'" . $this->view->kod_txt ."'")));
		if (!isset($this->view->txt[$this->view->kod_txt])) {
			$this->view->txt[$this->view->kod_txt] = 'Neni nadefinovan text s kodem ' . $this->view->kod_txt;
		}
	}
	
	public function zpracujdoruceniAction() {
            $_SESSION['objednavka']['osoba']['jmeno'] = $_POST['jmeno'];
            $_SESSION['objednavka']['osoba']['email'] = $_POST['email'];
            $_SESSION['objednavka']['osoba']['telefon'] = $_POST['telefon'];
            $_SESSION['objednavka']['firma']['firma'] = $_POST['firma'];
            $_SESSION['objednavka']['firma']['ic'] = $_POST['ic'];
            $_SESSION['objednavka']['firma']['dic'] = $_POST['dic'];
            $_SESSION['objednavka']['firma']['ulice'] = $_POST['ulice'];
            $_SESSION['objednavka']['firma']['mesto'] = $_POST['mesto'];
            $_SESSION['objednavka']['firma']['psc'] = $_POST['psc'];
            $_SESSION['objednavka']['firma']['stat'] = $_POST['stat'];
            if ($_POST['lisise'] == 'ano') {
                    $_SESSION['objednavka']['doruceni']['jmeno'] = $_POST['jmeno2'];
                    $_SESSION['objednavka']['doruceni']['firma'] = $_POST['firma2'];
                    $_SESSION['objednavka']['doruceni']['ulice'] = $_POST['ulice2'];
                    $_SESSION['objednavka']['doruceni']['mesto'] = $_POST['mesto2'];
                    $_SESSION['objednavka']['doruceni']['psc'] = $_POST['psc2'];
            } else {
                    $_SESSION['objednavka']['doruceni']['jmeno'] = $_POST['jmeno'];
                    $_SESSION['objednavka']['doruceni']['firma'] = $_POST['firma'];
                    $_SESSION['objednavka']['doruceni']['ulice'] = $_POST['ulice'];
                    $_SESSION['objednavka']['doruceni']['mesto'] = $_POST['mesto'];
                    $_SESSION['objednavka']['doruceni']['psc'] = $_POST['psc'];
            }
            $_SESSION['objednavka']['kupon']['cislo'] = $_POST['kupon'];
            $_SESSION['objednavka']['kupon']['sleva'] = $_POST['kupon-sleva'];
            $_SESSION['objednavka']['ostatni']['poznamky'] = $_POST['poznamky'];
            //print_r($_SESSION['objednavka']);
            //die('doruceni zpracovano');
            //pokud je uvedena kuponova sleva, zrus objemovou
            if ((float) $_SESSION['objednavka']['kupon']['sleva'] != 0) {
                $_SESSION['objemova_sleva'] = 0;
            }
            #$this->_Redirect(THIS_SERVER . '/obchod/kosik/shrnuti');
            $this->_Redirect('/obchod/kosik/shrnuti');
	}
	
	public function shrnutiAction() {
        $this->view->strom = stromek(0, $this->_getAllParams());
		$this->view->kod_txt = 'kos_souhrn';
		$this->view->txt = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'" . $this->view->kod_txt ."'")));
		if (!isset($this->view->txt[$this->view->kod_txt])) {
			$this->view->txt[$this->view->kod_txt] = 'Neni nadefinovan text s kodem ' . $this->view->kod_txt;
		}

	}
	
	public function zpracujobjednavkuAction() {
		$_t = new Zend_Translate('csv', CESTA . '/preklad/mfpshop.cs.csv', 'cs');
		$_t->addTranslation(CESTA . '/preklad/mfpshop.sk.csv', 'sk');
		$_t->addTranslation(CESTA . '/preklad/mfpshop.en.csv', 'en');
		
		// Create a log instance
		$writer_preklad = new Zend_Log_Writer_Stream(CESTA . '/logy/preklad.log');
		$log_preklad = new Zend_Log($writer_preklad);

		/*
		firma, osoba, datum, doprava, platba, poznamka, cena_celkem, cena_celkem_DPH
		*/
		#zjisteni celkove ceny
		$soucet = 0;
		$soucet_dph = 0;
		foreach($_SESSION['kosik'] as $r) {
			$soucet += (double) $r['cena_radek'];
			$soucet_dph += (double) $r['cena_radek_dph'];
		}
                $sleva_obj = $soucet * (float) $_SESSION['objemova_sleva']/100;
                $sleva_obj_dph = $soucet_dph * (float) $_SESSION['objemova_sleva']/100;
                $cena_obj = $soucet - $sleva_obj;
                $cena_obj_dph = $soucet_dph - $sleva_obj_dph;
                $sleva_kupon = $cena_obj * (float) $_SESSION['objednavka']['kupon']['sleva']/100;
                $sleva_kupon_dph = $cena_obj_dph * (float) $_SESSION['objednavka']['kupon']['sleva']/100;
                $cena_kupon = $cena_obj - $sleva_kupon;
                $cena_kupon_dph = $cena_obj_dph - $sleva_kupon_dph;
		$_SESSION['objednavka']['doprava']['kod'] = isset($_SESSION['objednavka']['doprava']['kod']) ? $_SESSION['objednavka']['doprava']['kod'] : '';
		$_SESSION['objednavka']['platba']['kod'] = isset($_SESSION['objednavka']['platba']['kod']) ? $_SESSION['objednavka']['platba']['kod'] : '';
		$vysl = readData('objednavka_hl', array(
			'firma' => $_SESSION['uzivatel']['firma'],
			'osoba' => $_SESSION['uzivatel']['jmeno'],
			'doprava' => $_SESSION['objednavka']['doprava']['kod'],
			'platba' => $_SESSION['objednavka']['platba']['kod'],
			'cena_celkem' => $cena_kupon,
			'cena_celkem_dph' => $cena_kupon_dph,
			'poznamka' => $_SESSION['objednavka']['ostatni']['poznamky'],
			'objemova_sleva' => $_SESSION['objemova_sleva'],
                        'kupon' => $_SESSION['objednavka']['kupon']['cislo'],
                        'kupon_sleva' => $_SESSION['objednavka']['kupon']['sleva']
		));
		#Firma, Poradi, Polozka, Produkt, Mj, Mj_v_bal, Mnozstvi, Cena_cenik, Sleva_proc, Cena_celkem, Cena_celkem_DPH
		#neplneni polozek objednavky - vzdy vlozeni do objednavky
		$rozpis = ''; #rozpis objednavky do mailu
		$rozpis_obch = ''; #rozpis objednavky do mailu
		$rozpis .= "=================================================================================================\r\n";
		$rozpis .= sprintf('|%-15s|%-50s|%6s|%10s|%10s|' . "\r\n", "Produkt", iconv('UTF-8', 'iso-8859-2//TRANSLIT', "Název"), iconv('UTF-8', 'iso-8859-2//TRANSLIT', "Počet"), "Cena", "Cena s DPH");
		$rozpis .= "=================================================================================================\r\n";
		foreach($_SESSION['kosik'] as $r) {
			$vysl_pol = readData('objednavka_pol', array(
				'firma' => $_SESSION['uzivatel']['firma'],
				'poradi' => (integer)  $vysl->o->row->cobj,
				'produkt' => $r['produkt'],
				'mj' => $r['mj'],
				'mj_v_bal' => '1',
				'mnozstvi' => $r['mj_evid'],
				'zakl_cena' => $r['zakl_cena'],
				'cena' => $r['cena'],
				'sleva_proc' => $r['sleva'],
				'cena_celkem' => $r['cena_radek'],
				'cena_celkem_dph' => $r['cena_radek_dph']
			));
			$rozpis .= sprintf('|%-15s|%-50s|%6.0F|%10.2F|%10.2F|' . "\r\n", iconv('UTF-8', 'iso-8859-2//TRANSLIT', $r['produkt']), iconv('UTF-8', 'iso-8859-2//TRANSLIT', $r['nazev']), $r['mj_evid'], $r['cena_radek'], $r['cena_radek_dph']);
		}
                // dokonceni objednavky - rozpusteni slev
                $vysl_pol = readData('dokonci_objednavku', array(
				'firma' => $_SESSION['uzivatel']['firma'],
				'poradi' => (integer)  $vysl->o->row->cobj)
                        );
                $rozpis .= "=================================================================================================\r\n";
		$rozpis .= sprintf('|%-73s|%10.2F|%10.2F|' . "\r\n", "Celkem", $soucet, $soucet_dph);
                if ((float) $_SESSION['objemova_sleva'] != 0) {
                    $rozpis .= "=================================================================================================\r\n";
                    $rozpis .= sprintf('|%-73s|%10.2F|%10.2F|' . "\r\n", iconv('UTF-8', 'iso-8859-2//TRANSLIT', "Objemová sleva: ") . $_SESSION['objemova_sleva'] . "%", $sleva_obj, $sleva_obj_dph);
                }
                if ((float) $_SESSION['objednavka']['kupon']['sleva'] != 0) {
                    $rozpis .= "=================================================================================================\r\n";
                    $rozpis .= sprintf('|%-73s|%10.2F|%10.2F|' . "\r\n", iconv('UTF-8', 'iso-8859-2//TRANSLIT', "Slevový kupon ") . $_SESSION['objednavka']['kupon']['cislo'] . ': ' . $_SESSION['objednavka']['kupon']['sleva'] . "%", $sleva_kupon, $sleva_kupon_dph);
                }
                $rozpis .= "=================================================================================================\r\n";
		$rozpis .= sprintf('|%-73s|%10.2F|%10.2F|' . "\r\n", iconv('UTF-8', 'iso-8859-2//TRANSLIT', "Výsledná cena"), $cena_kupon, $cena_kupon_dph);
		$rozpis .= "=================================================================================================\r\n";
		//$rozpis = ($rozpis);
		$rozpis .= "Doprava: " . iconv('UTF-8', 'iso-8859-2//TRANSLIT' ,$_SESSION['objednavka']['doprava']['popis']) . ", " . "cena: " . number_format($_SESSION['objednavka']['doprava']['cena'], 2, ',', ' ') . iconv('UTF-8', 'iso-8859-2//TRANSLIT', " Kč\r\n");
		$rozpis .= "Platba: " . iconv('UTF-8', 'iso-8859-2//TRANSLIT', $_SESSION['objednavka']['platba']['popis']) . ", " . "cena: " . number_format($_SESSION['objednavka']['platba']['cena'], 2, ',', ' ') . iconv('UTF-8', 'iso-8859-2//TRANSLIT', " Kč\r\n");
		$rozpis .= "=================================================================================================\r\n";
		if (trim($_SESSION['objednavka']['ostatni']['poznamky']) != '') {
			$rozpis .= iconv('UTF-8', 'iso-8859-2//TRANSLIT', "Poznámky:\r\n" . $_SESSION['objednavka']['ostatni']['poznamky'] . "\r\n");
			$rozpis .= "=================================================================================================\r\n";
		}
		$rozpis_obch .= "Objednal:\r\n";
		foreach($_SESSION['objednavka']['osoba'] as $k => $h) {
			$rozpis_obch .= iconv('UTF-8', 'iso-8859-2//TRANSLIT', $_t->_($k) . ": $h\r\n");
		}
		$rozpis_obch .= "=================================================================================================\r\n";
		$rozpis_obch .= "Objednatel:\r\n";
		foreach($_SESSION['objednavka']['firma'] as $k => $h) {
			$rozpis_obch .= iconv('UTF-8', 'iso-8859-2//TRANSLIT', $_t->_($k) . ": $h\r\n");
		}
		$rozpis_obch .= "=================================================================================================\r\n";
		$rozpis_obch .= iconv('UTF-8', 'iso-8859-2//TRANSLIT', "Dodací místo:\r\n");
		foreach($_SESSION['objednavka']['doruceni'] as $k => $h) {
			$rozpis_obch .= iconv('UTF-8', 'iso-8859-2//TRANSLIT', $_t->_($k) . ": $h\r\n");
		}
		$rozpis_obch .= "=================================================================================================\r\n";
		//zjisteni textu a odeslani mailu
   		$texty = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'obj_zak','obj_obch'")));
		#print_r($texty);
		#a nakonec vyprazdneni kosiku
		//echo "<pre>\n";die(str_replace('#COBJ#', (string)  $vysl->o->row->cobj, str_replace('#ROZPIS#', $rozpis, $texty['obj_obch'])));
		//die(str_replace('#COBJ#', (string)  $vysl->o->row->cobj, str_replace('#ROZPIS#', $rozpis, iconv('UTF-8', 'iso-8859-2//TRANSLIT', $texty['obj_zak']))));
                //die("<pre>\n" . str_replace('#ROZPIS#', $rozpis . $rozpis_obch, iconv('UTF-8', 'iso-8859-2//TRANSLIT', $texty['obj_zak'])));
    	@mailuj($_SESSION['uzivatel']['e_mail'], 'MFP shop - potvrzeni objednavky', 
    			str_replace('#COBJ#', (string)  $vysl->o->row->cobj, str_replace('#ROZPIS#', $rozpis . $rozpis_obch, iconv('UTF-8', 'iso-8859-2//TRANSLIT', $texty['obj_zak']))), 'iso-8859-2');
    	@mailuj(EMAIL_OBCHODNIK, 'MFP shop - nova objednavka', 
    			str_replace('#COBJ#', (string)  $vysl->o->row->cobj, str_replace('#ROZPIS#', $rozpis . $rozpis_obch, iconv('UTF-8', 'iso-8859-2//TRANSLIT', $texty['obj_obch']))), 'iso-8859-2');
		//vyprazdnit klientsky kosik
    	$_SESSION['kosik'] = array();
    	$_SESSION['objednavka'] = array();
    	//a jeste kosik na serveru
        readData('vysyp_kosik', array('osoba' => $_SESSION['uzivatel']['jmeno']));
        
		unset($_t);
		unset($writer_preklad);
		unset($log_preklad);

		#$this->_Redirect(THIS_SERVER . '/katalog/index/index');
		$this->_Redirect('/katalog/index/index');
		#echo '<h1>Cislo objednavky: ' . (string) $vysl->o->row->cobj . '</h2>';
		#echo '<pre>';print_r($_SESSION['objednavka']);echo "\n\n";print_r($_SESSION['kosik']);echo "\n\n";print_r($_SESSION['uzivatel']);echo '</pre>';die();
	}
	
	function nastavslevuAction() {
		$_SESSION['objemova_sleva'] = (float) $this->_getParam('sleva');
		die('Sleva: ' . $_SESSION['objemova_sleva']);
	}
}


