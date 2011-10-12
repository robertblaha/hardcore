<?php

class Obchod_PristupController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function loginAction()
    {
        // action body
        //nejprve overim, jestli uz je MD5 heslo
        $pripojeno = false;
        $zakodovano = false;
        $vysl = readData('login', array('web_user' => $_POST['user'], 'web_pwd' => md5($_POST['password'])));
        if ((int) $vysl->p->row->pocet == 1) {
        	$pripojeno = true;
        	$zakodovano = true;
        }
        //pokud ne, tak zkus nezakodovane heslo
        if (!$pripojeno) {
	        $vysl = readData('login', array('web_user' => $_POST['user'], 'web_pwd' => $_POST['password']));
	        //echo ukazXML($_SESSION['vracene_xml']);echo print_r($_POST);die();
	        if ((int) $vysl->p->row->pocet == 1) {
	        	$pripojeno = true;
	        }
        }
        if ($pripojeno) {
        	$_SESSION['login']['zprava'] = 'Přihlášení OK';
        	$_SESSION['uzivatel']['firma'] = (string) $vysl->s->row->firma;
        	$_SESSION['uzivatel']['firma_sleva'] = (string) $vysl->s->row->firma_sleva;
        	$_SESSION['uzivatel']['firma_skup'] = (string) $vysl->s->row->skupina_firmy;
        	$_SESSION['uzivatel']['firma_nazev'] = (string) $vysl->s->row->nazev_firmy;
        	$_SESSION['uzivatel']['login'] = (string) $vysl->s->row->login;
        	$_SESSION['uzivatel']['jmeno'] = (string) $vysl->s->row->uzivatel;
        	$_SESSION['uzivatel']['e_mail'] = (string) $vysl->s->row->e_mail;
        	$_SESSION['uzivatel']['kredit_patro'] = (string) $vysl->s->row->kredit_patro;

        	//typ firmy pro zobrazeni cen
        	if (strtolower($_SESSION['uzivatel']['firma_skup']) == 'malo') {
        		$_SESSION['uzivatel']['firma_typ'] = 'VO';
        	} else {
        		$_SESSION['uzivatel']['firma_typ'] = 'MO';
        	}

        	if (strtolower($_SESSION['uzivatel']['firma_skup']) == strtolower(WEB_SKUPINA)) {
        		$_SESSION['uzivatel']['cenik'] = WEB_CENIK;
        	} else {
        		$_SESSION['uzivatel']['cenik'] = STD_CENIK;
        	}
        	//nacteni kosiku - pokud neni ulozen, ponecha se stavajici
        	$vymazano = false;
        	$poradi = 1;
        	foreach($vysl->k->row as $r) {
        		if (!$vymazano) {//pokud se tudy jde, je neco ulozeno v kosiku; pred prvnim vlozenim tedy vymazu stavajici kosik
        			$_SESSION['kosik'] = array();
        			$vymazano = true;
        		}
        		$_SESSION['kosik'][$poradi] = array(
		        	'produkt' => (string) $r->produkt,
		        	'nazev' => (string) $r->nazev,
		        	'mj' => (string) $r->mj,
		        	'mj_v_bal' => (string) $r->mj_v_bal,
		        	'mnozstvi' => (string) $r->mnozstvi,
		        	'sdph' => (string) $r->sdph,
		        	'cena' => (string) $r->cena,
		        	'sleva' => (string) $r->sleva,
		        	'mj_evid' => (string) $r->mj_evid,
		        	'zakl_cena' => (string) $r->zakl_cena,
		        	'cena_radek' => (string) $r->cena_radek,
		        	'cena_radek_dph' => (string) $r->cena_radek_dph
        		);
        		$poradi++;
        	}
        	$_SESSION['kosik_max_pol'] = $poradi - 1;
        	//if ($vymazano) {echo 'pica';} else {echo 'kurva';}
        	//print_r($vymazano);die();
        	if (!$vymazano) {//zustal stavajici kosik, musi se tedy prepocitat --------------------------------------- prepocet kosiku
    			foreach($_SESSION['kosik'] as $poradi => $radek) {
    				//echo 'xx' . $radek['produkt'] . 'xx';
		        	$vysl = readData('dokosiku', array('kat_id' => $radek['produkt'], 'mnozstvi' => $radek['mnozstvi'], 'cenik' => $_SESSION['uzivatel']['cenik'], 'firma_sleva' => $_SESSION['uzivatel']['firma_sleva']));
			        //die($pars['cenamj']);
			        //die(str_replace('_', '.', $pars['cenamj']));
		            foreach($vysl->r->row as $r) {
		            	//echo (string) $r->kat_id . '-';
				        //vyhodnoceni nasobku a minimalniho mnozstvi
				        if ((int) $r->nasobky == 0) {//neprodava se po nasobcich kusu, jen overim, jestli je prodano alespon minimalni mnozstvi
				        	if ((int) $radek['mnozstvi'] < (int) $r->min_mnozstvi) {//musim doplnit na minimalni
				        		$xmnozstvi = (int) $r->min_mnozstvi;
				        	} else {
				        		$xmnozstvi = (int) $radek['mnozstvi'];
				        	}
				        	$xmjvbal = 1;
				        } else {//prodava se po nasobcich
				        	$xmjvbal = (int) $r->min_mnozstvi;
				        	$xmnozstvi = (int) $radek['mnozstvi'];
				        }
				        #die("x: $xmjvbal m: $xmnozstvi");
		            	$cena = (double) $r->cenamj - ((double) $r->cenamj * (double) $r->sleva / 100);
		            	//usporadam pole dle polozek
			        	$_SESSION['kosik'][$poradi]['zakl_cena'] = (double) $r->cenamj;
			        	$_SESSION['kosik'][$poradi]['cena'] = $cena;
			        	$_SESSION['kosik'][$poradi]['sleva'] = (string) $r->sleva;
			        	$_SESSION['kosik'][$poradi]['cena_radek'] = round((double)$xmjvbal * (double) $xmnozstvi * $cena, 2);
			        	$_SESSION['kosik'][$poradi]['cena_radek_dph'] = round((double)(string) $xmjvbal * (double) $xmnozstvi * (double) $cena * (1 + (double) $r->sdph/100), 2);
				        //a pridam do kosiku v DB
				        $par = $_SESSION['kosik'][$poradi];
				        $par['osoba'] = $_SESSION['uzivatel']['jmeno'];
				        readData('pridej_do_kosiku', $par);
		            }
    			}
	        	$_SESSION['kosik_max_pol'] = sizeof($_SESSION['kosik']);
				//die('nacteno');
        	} // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ prepocet kosiku
        	#nacteni objemovych slev
        	$_SESSION['slevy'] = array();
       		//objemove slevy maji jen webovi zakaznici
       		if (strtoupper($_SESSION['uzivatel']['firma_skup']) == 'KWEB') {
	        	$i = 1;
	        	foreach($vysl->sl->row as $r) {
	        		$_SESSION['slevy'][$i] = array('castka' => (int) $r->castka, 'procento' => (float) $r->procento);
	        		$i++;
	        	}
       		}
        } else {
        	$_SESSION['login']['zprava'] = 'Nesprávné přihlašovací údaje!';
        	$_SESSION['uzivatel'] = array('firma' => '', 'firma_skup' => '');
        }
        //pokud neni zakodovano heslo v DB, zakoduji
        if (!$zakodovano && $pripojeno) {
        	$vysl = readData('koduj_heslo', array('osoba' => $_SESSION['uzivatel']['jmeno'], 'firma' => $_SESSION['uzivatel']['firma'], 'heslo' => md5($_POST['password'])));
        }
        //print_r($_SESSION);die();
        $zdroj = parse_url($_SERVER[HTTP_REFERER]);
        $this->_Redirect($zdroj['path']);
    }

    public function objednavkyAction()
    {
       	$vysl = readData('objednavky', array('uziv' => $_SESSION['uzivatel']['jmeno']));
       	$this->view->objednavky = array();
		foreach($vysl->o->row as $r) {
			$this->view->objednavky[] = array('poradi' => (string) $r->poradi, 'cena' => (double) $r->cena,
				'datum' => substr((string) $r->datum, 8, 2) . '.' . substr((string) $r->datum, 5, 2) . '.' . substr((string) $r->datum, 0, 4));
		}
        $this->view->strom = stromek(0, $this->_getAllParams());
    }

    public function logoutAction()
    {
        // action body
        $_SESSION['login']['zprava'] = '';
		$_SESSION['uzivatel'] = array();
		$_SESSION['kosik'] = array();
		$_SESSION['objednavka'] = array();
        $zdroj = parse_url('/');
        $this->_Redirect($zdroj['path']);
    }

    public function registraceAction()
    {
        $this->view->strom = stromek(0, $this->_getAllParams());
    }

    public function zpracujregistraciAction() {
    	//overim, jestli firma neexistuje
    	$data = array();
    	foreach($_POST as $k => $h) {
    		$data[$k] = textToDB($h);
    	}
    	
    	$cele_jmeno = trim($data['prijmeni']);
    	if (trim($data[jmeno]) != '') {
    		$cele_jmeno .= ' ' . trim($data['jmeno']);
    	}

    	//napleneni informaci o firme
		$pars = array();
		if (trim($_POST['firma']) == '') {
    		$pars['xfirma'] = substr($cele_jmeno, 0, 30);
			$pars['nazev_firmy'] = $cele_jmeno;
		} else {
    		$pars['xfirma'] = substr(textToDB(trim($_POST['firma'])), 0, 30);
			$pars['nazev_firmy'] = textToDB(trim($_POST['firma']));
		}
		$pars['kniha'] = 'Adresář';
		$pars['ulice'] = textToDB(trim($_POST['ulice']));
		$pars['mesto'] = textToDB(trim($_POST['mesto']));
		$pars['psc'] = textToDB(trim($_POST['psc']));
		$pars['adresa'] = $pars['ulice'] . ' ' . $pars['psc'] . ' ' . $pars['mesto'];
		switch (trim($_POST['stat'])) {
			case 'sk':
				$pars['stat'] = 'Slovenská republika';
				break;
			default:
				$pars['stat'] = 'Česká republika';
				break;
		}
		$pars['telefon'] = textToDB(trim($_POST['telefon']));
		$pars['email'] = textToDB(trim($_POST['email']));
		$pars['ic'] = textToDB(trim($_POST['ic']));
		$pars['dic'] = textToDB(trim($_POST['dic']));
		if ($pars['dic'] != '') {
			$pars['platce_dph'] = '-1';
		} else {
			$pars['platce_dph'] = '0';
		}
		if ($pars['ic'] != '') {
			$pars['pravnicka_osoba'] = '-1';
		} else {
			$pars['pravnicka_osoba'] = '0';
		}
		$pars['udaj_1'] = WEB_SKUPINA;
			$pars['osoba'] = $cele_jmeno;
			$pars['ujmeno'] = textToDB(trim($_POST['ujmeno']));
			$pars['heslo'] = textToDB(trim($_POST['heslo']));
		if (trim($_POST['firma2']) == '') {
			$pars['dodani_nazev_firmy'] = textToDB(trim($_POST['jmeno2']));
		} else {
			$pars['dodani_nazev_firmy'] = textToDB(trim($_POST['firma2']));
		}
		$pars['dodani_ulice'] = textToDB(trim($_POST['ulice2']));
		$pars['dodani_mesto'] = textToDB(trim($_POST['mesto2']));
		$pars['dodani_psc'] = textToDB(trim($_POST['psc2']));
		$pars['dodani_adresa'] = $pars['dodani_ulice'] . ' ' . $pars['dodani_psc'] . ' ' . $pars['dodani_mesto'];

    	//print_r($data);
    	//overeni existence duplicitni firmy
    	$parso = array();
    	$parso['jmeno'] = ($cele_jmeno == '') ? 'xxxxx' : $cele_jmeno;
		if (trim($data['firma']) == '') {
    		$parso['xfirma'] = ($cele_jmeno == '') ? 'xxxxx' : $cele_jmeno;
		} else {
    		$parso['xfirma'] = (trim($data['firma']) == '') ? 'xxxxx' : trim($data['firma']);
		}
    	$parso['frmpk'] = (trim($data['firma']) == '') ? 'xxxxx' : substr(trim($data['firma'], 0, 30));
    	$parso['email'] = (trim($data['email']) == '') ? 'xxxxx' : trim($data['email']);
    	$parso['dic'] = (trim($data['dic']) == '') ? 'xxxxx' : trim($data['dic']);
    	$parso['ic'] = (trim($data['ic']) == '') ? 'xxxxx' : trim($data['ic']);
    	#print_r($pars);#die();
    	$vysl = readData('duplicitafirmy', $parso);
    	#echo "\n\nPOSLEDNI XML:\n";
		#echo ukazXML($_SESSION['vracene_xml']);
		#die();
    	if (isset($vysl->c->row) || isset($vysl->s->row)) {//nalezena nejaka jina firma
    		#echo 'Nalezena jina firma';
    		//$this->_Redirect('/web/texty/zobraz/idt/reg_ok');
    		$texty = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'dup_reg_zak','dup_reg_obch'")));
    		//mail zakaznikovi
    		mailuj($data['email'], 'MFP shop - zprava o registraci',
    			str_replace('#TEL#', TEL_OBCHODNIK, str_replace('#MAIL#', EMAIL_OBCHODNIK, $texty['dup_reg_zak'])));
$porizeno = "Pořízené údaje:
===============
";
		foreach($_POST as $k => $h) {
			$porizeno .= "$k: $h\r\n";
		}
$nalezeno = "
Nalezené firmy:
===============
";
			if (isset($vysl->c->row)) {//ceske firmy
				$nalezeno .= "Česká databáze:\r\n";
				foreach($vysl->c->row as $r) {
					$nalezeno .= (string) $r->kod_firmy . "\r\n";
				}
			}
			if (isset($vysl->s->row)) {//slovenske firmy
				$nalezeno .= "\r\nSlovenská databáze:\r\n";
				foreach($vysl->s->row as $r) {
					$nalezeno .= (string) $r->kod_firmy . "\r\n";
				}
			}
	    	$vysl = readData('registruj_duplicitni_firmu', $pars);
    		mailuj(EMAIL_OBCHODNIK, 'MFP shop - zprava o registraci duplicitniho zakaznika',
    			str_replace('#NALEZENO#', $nalezeno, str_replace('#PORIZENO#', $porizeno, str_replace('#CAS#', date('H:i'), str_replace('#DATUM#', date('d.m.Y'), $texty['dup_reg_obch'])))));
    		unset($pars);
    		unset($parso);
    		$this->_Redirect('/web/texty/zobraz/idt/dup_reg_zak_txt');
    	} else { //firma nalezena nebyla, takze se muze zalozit
	    	$vysl = readData('registruj_firmu', $pars);
    		#echo "<pre>\nPars:\n";print_r($pars);
    		#echo "\nzalozeno firem: " . (string) $vysl->f->row->pocet_firem;
    		#echo "\nzalozeno uzivatelu: " . (string) $vysl->f->row->pocet_uzivatelu;

    		$texty = zpracujTexty(readData('texty', array('jazyk' => $_SESSION['jazyk'], 'kody' => "'reg_zak','reg_obch'")));
    		//mail zakaznikovi
    		mailuj($data['email'], 'MFP shop - zprava o registraci',
    			str_replace('#JMENO#', $pars['ujmeno'], str_replace('#HESLO#', $pars['heslo'], $texty['reg_zak'])));
			$porizeno = "Pořízené údaje:\r\n===============\r\n";
			foreach($pars as $k => $h) {
				$porizeno .= "$k: $h\r\n";
			}
    		mailuj(EMAIL_OBCHODNIK, 'MFP shop - zprava o registraci zakaznika',
    			str_replace('#PORIZENO#', $porizeno, str_replace('#CAS#', date('H:i'), str_replace('#DATUM#', date('d.m.Y'), $texty['reg_obch']))));
    		unset($parso);
    		unset($pars);
    		$this->_Redirect('/web/texty/zobraz/idt/reg_zak_txt');
    	}
    	#echo "\n\n<pre>POST:\n";print_r($_POST);
    	#die();
    }

    public function editaceAction()
    {
        $this->view->strom = stromek(0, $this->_getAllParams());
        $vysl = readData('uzivatel_info', array('firma' => $_SESSION['uzivatel']['firma'], 'osoba' => $_SESSION['uzivatel']['jmeno']));
       	$this->view->firma = array('nazev_firmy' => trim((string) $vysl->f->row->nazev_firmy), 'ulice' => trim((string) $vysl->f->row->ulice),
       			'psc' => trim((string) $vysl->f->row->psc), 'mesto' => trim((string) $vysl->f->row->mesto), 'stat' => trim((string) $vysl->f->row->stat),
       			'telefon' => trim((string) $vysl->f->row->telefon), 'ico' => trim((string) $vysl->f->row->ico), 'dic' => trim((string) $vysl->f->row->dic));
       	$this->view->osoba = array('ujmeno' => trim((string) $vysl->f->row->ujmeno), 'osoba' => trim((string) $vysl->f->row->osoba),
       			'email' => trim((string) $vysl->f->row->u_e_mail),
       			'nazev_firmy' => trim((string) $vysl->f->row->nazev_firmy), 'ulice' => trim((string) $vysl->f->row->ulice),
       			'psc' => trim((string) $vysl->f->row->psc), 'mesto' => trim((string) $vysl->f->row->mesto), 'stat' => trim((string) $vysl->f->row->stat),
       			'telefon' => trim((string) $vysl->f->row->u_telefon), 'ico' => trim((string) $vysl->f->row->ico), 'dic' => trim((string) $vysl->f->row->dic));
       	//dorucovaci adresa - jen pokud se lisi od fakturacni
       	$this->view->doruceni = array('osoba' => trim((string) $vysl->f->row->osoba), 'nazev_firmy' => trim((string) $vysl->f->row->dodani_nazev_firmy),
       		'ulice' => trim((string) $vysl->f->row->dodani_ulice), 'psc' => trim((string) $vysl->f->row->dodani_psc), 'mesto' => trim((string) $vysl->f->row->dodani_mesto));
		$this->view->pars = $this->_getAllParams();
		if (!isset($this->view->pars['fromobj'])) {
			$this->view->pars['fromobj'] = 'ne';
		}
    }

    public function zpracujeditaciAction() {
    	//overim, jestli firma neexistuje
    	$data = array();
    	foreach($_POST as $k => $h) {
    		$data[$k] = textToDB($h);
    	}
    	#print_r($data);
    	#die();
		//ted nactu puvodni udaje:
		$vysl = readData('uzivatel_info', array('firma' => $_SESSION['uzivatel']['firma'], 'osoba' => $_SESSION['uzivatel']['jmeno']));
		$puv = array(
       		'nazev_firmy' => trim((string) $vysl->f->row->nazev_firmy), 'ulice' => trim((string) $vysl->f->row->ulice),
       			'psc' => trim((string) $vysl->f->row->psc), 'mesto' => trim((string) $vysl->f->row->mesto), 'stat' => trim((string) $vysl->f->row->stat),
       			'telefon' => trim((string) $vysl->f->row->telefon), 'ico' => trim((string) $vysl->f->row->ico), 'dic' => trim((string) $vysl->f->row->dic),
       			'ujmeno' => trim((string) $vysl->f->row->ujmeno), 'osoba' => trim((string) $vysl->f->row->osoba),
       			'email' => trim((string) $vysl->f->row->u_e_mail),
       			'doruceni_nazev_firmy' => trim((string) $vysl->f->row->dodani_nazev_firmy),
       			'doruceni_ulice' => trim((string) $vysl->f->row->dodani_ulice), 'doruceni_psc' => trim((string) $vysl->f->row->dodani_psc),
       			'doruceni_mesto' => trim((string) $vysl->f->row->dodani_mesto)
       	);
    	$pars = array('skupina_oprav' => WEB_SKUPINA);
   		$pars['xfirma'] = $_SESSION['uzivatel']['firma'];
		if (trim($_POST['firma']) == '') {
			$pars['nazev_firmy'] = textToDB(trim($_POST['jmeno']));
		} else {
			$pars['nazev_firmy'] = textToDB(trim($_POST['firma']));
		}
		$pars['ujmeno'] = $_SESSION['uzivatel']['login'];
		$pars['ulice'] = textToDB(trim($_POST['ulice']));
		$pars['mesto'] = textToDB(trim($_POST['mesto']));
		$pars['psc'] = textToDB(trim($_POST['psc']));
		$pars['adresa'] = $pars['ulice'] . ' ' . $pars['psc'] . ' ' . $pars['mesto'];
		switch (trim($_POST['stat'])) {
			case 'sk':
				$pars['stat'] = 'Slovenská republika';
				break;
			default:
				$pars['stat'] = 'Česká republika';
				break;
		}
		$pars['telefon'] = textToDB(trim($_POST['telefon']));
		$pars['email'] = textToDB(trim($_POST['email']));
		$pars['ic'] = textToDB(trim($_POST['ic']));
		$pars['dic'] = textToDB(trim($_POST['dic']));
		$pars['osoba'] = textToDB(trim($_POST['jmeno']));
		$pars['heslo'] = textToDB(trim($_POST['heslo']));
		if (trim($_POST['firma2']) == '') {
			$pars['dodani_nazev_firmy'] = textToDB(trim($_POST['jmeno2']));
		} else {
			$pars['dodani_nazev_firmy'] = textToDB(trim($_POST['firma2']));
		}
		$pars['dodani_ulice'] = textToDB(trim($_POST['ulice2']));
		$pars['dodani_mesto'] = textToDB(trim($_POST['mesto2']));
		$pars['dodani_psc'] = textToDB(trim($_POST['psc2']));
		$pars['dodani_adresa'] = $pars['dodani_ulice'] . ' ' . $pars['dodani_psc'] . ' ' . $pars['dodani_mesto'];
		#print_r($pars);die();
    	$vysl = readData('edituj_firmu', $pars);
    	#a jeste musim opravit udaje v session
    	$_SESSION['uzivatel']['nazev_firmy'] = $pars['nazev_firmy'];
    	$_SESSION['uzivatel']['jmeno'] = $pars['osoba'];
    	$_SESSION['uzivatel']['e_mail'] = $pars['email'];
		#echo "<pre>\nPars:\n";print_r($pars);
		#echo "\nzalozeno firem: " . (string) $vysl->f->row->pocet_firem;
		#echo "\nzalozeno uzivatelu: " . (string) $vysl->f->row->pocet_uzivatelu;

		#odeslani mailu se zpravou o zmene
		$zprava = 'Došlo ke změně údajů partnera.' . "\r\n\r\n";
		$zprava .= "Původní údaje:\r\n";
		$zprava .= "==============\r\n";
		foreach($puv as $k => $h) {
			$zprava .= "$k: $h\r\n";
		}
		$zprava .= "\r\nNové údaje:\r\n";
		$zprava .= "===========\r\n";
		foreach($pars as $k => $h) {
			$zprava .= "$k: $h\r\n";
		}
		@mailuj(EMAIL_OBCHODNIK, 'MFP Shop - zmena udaju zakaznika', iconv('UTF-8', 'iso-8859-2//TRANSLIT', $zprava), 'iso-8859-2');
		unset($pars);
		unset($puv);
		if (isset($_POST['fromobj']) && trim(strtolower($_POST['fromobj'])) == 'ano') {
			$this->_Redirect('/obchod/kosik/doruceni');
		} else {
			$this->_Redirect('/');
		}
    	#echo "\n\n<pre>POST:\n";print_r($_POST);
    	#die();
    }

    function zobrazobjAction() {
    	if (is_numeric($this->_getParam('poradi'))) {
    		$cobj = trim($this->_getParam('poradi'));
    	} else {
    		$cobj = 0;
    	}
    	$vysl = readData('obj_info', array('cobj' => $cobj, 'login' => $_SESSION['uzivatel']['login']));
    	$this->view->hl = $vysl->hl->row;
    	$this->view->pol = $vysl->pol;
        $this->view->strom = stromek(0, $this->_getAllParams());
    }

}

