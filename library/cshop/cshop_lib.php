<?php
date_default_timezone_set('Europe/Prague');

/**
* Spolecne funkce a tridy
*
* @version  1.0
* @author  Robert Blaha  <robert.blaha@rbc.cz>
* @package  CShop_Libs
*/

/**
 * Funkce upravi retezec obsahujici XML tak, aby bylo mozne jej zobrazit v beznem HTML vystupu.
 * Prskticky nahradi znaky vetsi a mensi odpovidajicimi entitami
 *
 * @param string $xml_string retezec obsahujici XML(to ale neni nutnou podminkou. Obecne tedy jakykoliv retezec, ktery ma byt upraven.
 * @return string retezec, v nemz jsou "nezobrazitelne" znaky nahrazeny entitami
 */
function ukazXML($xml_string) {
	return str_replace('>', '&gt;', str_replace('<', '&lt;', $xml_string));
}

/**
 * Komunikace s aplikacnim serverem. Tj. zpracovani prikazu na vzdalenem aplikacnim serveru a overeni
 * validity a ocekavaneho formatu prijateho XML souboru
 *
 * @param string $command retezec obsahujici validni XML s volanim prislusneho prikazu app. serveru a paramatry
 * @return object simple_xml object s nactenym XML, vracenym aplikacnim serverem
 */
function readData($command = '', $pars = array()) {
	$_SESSION['log'][mikrocas()] = '--readData, prikaz: ' . $command . ' START';
	//die(COMMAND_SERVER . '/' . COMMAND_PAGE);
	$client = new Zend_Http_Client(COMMAND_SERVER . '/' . COMMAND_PAGE, array('strict' => false));
			//die('zde');
	$client->setParameterGet('command', $command);
	foreach($pars as $klic => $hodnota) {
		$client->setParameterGet($klic, $hodnota);
	}
	//automaticke doplneni databazi
	$client->setParameterGet('CoreDB', 'Data0001');
	$client->setParameterGet('SkDB', 'Data0003');
	if (strtolower($_SESSION['jazyk']) == 'sk') {
		$client->setParameterGet('LocalDB', 'Data0003');
        	$client->setParameterGet('PriceDB', 'DKMS0003');
        	$client->setParameterGet('ConfDB', 'DeSh0003');
	} else {
		$client->setParameterGet('LocalDB', 'Data0001');
        	$client->setParameterGet('PriceDB', 'DKMS0001');
        	$client->setParameterGet('ConfDB', 'DeSh0001');
	}
	$client->setParameterGet('firma', $_SESSION['uzivatel']['firma']);
	$client->setParameterGet('firma_skup', $_SESSION['uzivatel']['firma_skup']);
	$_SESSION['log'][mikrocas()] = '--readData, prikaz: ' . $command . ' pred odeslanim requestu';
	//$odpoved = bzdecompress($client->request('POST')->getBody());
	$odpoved = $client->request('GET')->getBody();
	if (is_numeric($odpoved))
		die('Chyba dekomprese, vysledek: ' . $odpoved);
	$_SESSION['log'][mikrocas()] = '--readData, prikaz: ' . $command . ' request nacten';
	//nacteni vysledneho xml
	$_SESSION['vracene_xml'] = $odpoved;
	//echo '<pre>' . ukazXML($odpoved);die();
	$xml = simplexml_load_string($odpoved, 'SimpleXMLElement');
	$_SESSION['log'][mikrocas()] = '--readData, prikaz: ' . $command . ' XML parsovano';

	$_SESSION['log'][mikrocas()] = '--readData, prikaz: ' . $command . ' vysledek finalizovan';
	//vraceni chyby v pripade problemu s nactenym XML
	if (!$xml) {
		die('<br/><br/><b>Nepodarilo se nacist vysledne XML!</b><br/><br/><b>XML:</b><br/>' . ukazXML($odpoved) . '<br/><br/>'. var_export($xml, true));
	}
	//finalize_answer($xml, 0, false);
	//die('<br/><br/>FUCKING END!!!!</br>');
	$_SESSION['log'][mikrocas()] = '--readData, prikaz: ' . $command . ' KONEC';
	//die(json_encode($xml));
	return $xml;
}


/**
 * Enter description here...
 *
 * @return string retezec obsahujici aktualni cas vcetne microsekund
 */
function mikrocas() {
	list($tisiciny, $sekundy) = explode(" ",microtime());
	$vysl = date('H:i:s', $sekundy);
	list($nic, $mikro) = explode(".", $tisiciny);
	$vysl .= ".".$mikro;
	return $vysl;
}

/**
 * Prelozeni retezce z UTF-8 do CP1250
 *
 * @param string $co retezec v UTF-8
 * @return string retezenv CP1250
 */
function toCP1250 ($co) {
 return iconv("UTF-8//IGNORE", "CP1250//TRANSLIT", $co);
}

function finalize_answer (&$pole, $depth = 0, $debug = false) {
	if ($debug)
		echo  "<hr/>Pocet deti: ".count($pole->children())."<br/>";
	$citac = 0;
	foreach($pole->children() as $klic=>$prvek) {
		finalize_answer($prvek, $depth+1, $debug);
		$citac++;
	}
	if ($citac == 0) {
		if ($debug) {
			echo "Uroven zanoreni: - $depth<br/> print_r:";var_dump($pole);echo "<br/>";
		}
			//echo "toCP1250 - $depth: ".toCP1250($prvek)."<br/>";echo "<br/>";
		//$pole[0] = textFromDB($pole[0]);
		if ($debug) {
			echo "prelozeno: ".$pole[0]."<br/>";
			echo "<br/>";
		}
	}
}

function 	textToDB($par) {
	return str_replace("'", "''", str_replace(';', ',', $par));
}

/**
 * Prelozeni retezce z UTF-8 do CP1250
 *
 * @param string $co retezec v CP1250
 * @return string retezenv UTF-8
 */
function toUTF8 ($co) {
 return iconv("CP1250//TRANSLIT", "UTF-8//IGNORE", $co);
}

function textFromDB($text) {
	$ret = $text;
	//$ret = trim(iconv("UTF-8//IGNORE", "CP1250//TRANSLIT", $text));
	$ret = str_ireplace('#STREDNIK#', ';', $ret);
	return $ret;
}

function xcesky($co) {
	//return strtr($co, '�����������������������َ���ύҼ����', 'aeeiyouuzscrdtnlaeouAEEIYOUUZSCRDTNLAEOU');
	//return iconv("CP1250//IGNORE", "UTF-8//IGNORE", strtr(iconv("UTF-8//IGNORE", "CP1250//IGNORE", $co), 'áéěíýóúůžščřďťňľäëöüÁÉĚÍÝÓÚŮŽŠČŘĎŤŇĽÄËÖÜ', 'aeeiyouuzscrdtnlaeouAEEIYOUUZSCRDTNLAEOU'));
	//return iconv("CP1250//IGNORE", "UTF-8//IGNORE", strtr(iconv("UTF-8//IGNORE", "CP1250//IGNORE", $co), 'áéěíýóúůžščřďťňľäëöüÁÉĚÍÝÓÚŮŽŠČŘĎŤŇĽÄËÖÜ', 'aeeiyouuzscrdtnlaeouAEEIYOUUZSCRDTNLAEOU'));
	return mb_strstr($co, 'áéěíýóúůžščřďťňľäëöüÁÉĚÍÝÓÚŮŽŠČŘĎŤŇĽÄËÖÜ', 'aeeiyouuzscrdtnlaeouAEEIYOUUZSCRDTNLAEOU', 'UTF-8');
        //return $co;
}

/** Vytvoření přátelského URL
* @param string řetězec v kódování UTF-8, ze kterého se má vytvořit URL
* @return string řetězec obsahující pouze čísla, znaky bez diakritiky, podtržítko a pomlčku
* @copyright Jakub Vrána, http://php.vrana.cz/
*/
function cesky($nadpis) {
    $url = $nadpis;
    $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
    $url = trim($url, "-");
    $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
    $url = strtolower($url);
    $url = preg_replace('~[^-a-z0-9_]+~', '', $url);
    return $url;
}

/**
 * Zjisteni informaci o kosiku - pocet polozek a celkova cena
 *
 * @param string $co retezec v CP1250
 * @return string retezenv UTF-8
 */
function kosikInfo() {
	$ret = array('polozky' => 0, 'kusy' => 0, 'cena' => 0, 'cena_dph' => 0);
	foreach($_SESSION['kosik'] as $r) {
		$ret['polozky']++;
		$ret['kusy'] += (double) $r['mj_evid'];
		$ret['cena'] += (double) $r['cena_radek'];
		$ret['cena_dph'] += (double) $r['cena_radek_dph'];
	}
	return $ret;
}

/**
 * Vrati retezec s prvnim velkym pismenem
 */
function vp($ret) {
	return 	mb_strtoupper(mb_substr($ret, 0 ,1, 'UTF-8'), 'UTF-8') . mb_substr($ret, 1, strlen($ret), 'UTF-8');
}

/**
 * Nacteni stromu kategorii s rozvinutou vetvi
 *
 * @param $ur stromu
 * @return pole obsahujici data prezentujici strom
 */
function stromek($ur, $p = array()) {
	if ($ur == 0)
   		$vysl = readData('strom_n', array('nadrizena' => (int) $ur, 'jenskladem' => $_SESSION['katalog']['pars']['jenskladem'], 'jenmfp' => $_SESSION['katalog']['pars']['jenmfp'], 'novy' => $_SESSION['katalog']['novy'], 'akce' => $_SESSION['katalog']['akce'], 'sleva' => $_SESSION['katalog']['sleva'], 'sezona' => $_SESSION['katalog']['sezona']));
   	else {
   		//$vysl = readData('strom_n', array('nadrizena' => (int) $p['u' . (string) $ur], 'jenskladem' => $_SESSION['katalog']['pars']['jenskladem']));
   		$vysl = readData('strom_n', array('nadrizena' => (int) $_SESSION['katalog']['rozpad']['u' . (string) $ur], 'jenskladem' => $_SESSION['katalog']['pars']['jenskladem'], 'jenmfp' => $_SESSION['katalog']['pars']['jenmfp'], 'novy' => $_SESSION['katalog']['novy'], 'akce' => $_SESSION['katalog']['akce'], 'sleva' => $_SESSION['katalog']['sleva'], 'sezona' => $_SESSION['katalog']['sezona']));
   	}
   	$ret = array();
   	$i = 0;
	//$nav_text = ''; // text pro navigaci po kategoriich do zahlavi seznamu
    foreach($vysl->kats->row as $r) {
		//poskladani navigace
		if (isset($p['u' . (string) ((int) $ur + 1)]) && $p['u' . (string) ((int) $ur + 1)] == (string) $r->id_kategorie) {
			$_SESSION['crumb'][$ur] = array('id' => (string) $r->id_kategorie, 'kat' => (string) $r->kategorie);
			//echo 'jjj';
		}
    	$ret[$i] = array();
    	$ret[$i]['id'] = (string) $r->id_kategorie;
    	$ret[$i]['nazev'] = (string) $r->kategorie;
    	$ret[$i]['pocet'] = (string) $r->pocet;
    	if (isset( $_SESSION['katalog']['rozpad']['u' . (string) ($ur+1)]) && (string)  $_SESSION['katalog']['rozpad']['u' . (string) ($ur+1)] == (string) $r->id_kategorie)
    		$ret[$i]['nasl'] = stromek($ur+1, $p);
    	else
    		$ret[$i]['nasl'] = array();
    	$i++;
    }
	unset($vysl);
		return $ret;
}

function zobrazStrom($s, $uroven = 0 , $pref_url = '', $pref_nazev = '', $pref_crumb = '') {
	$ret = '';
	if ($uroven == 0) {
		$ret .= '<ul id="menu">';
		#$_SESSION['crumb-text'] = '<a href="/katalog/index/index">' . vp($_t->_('uvod_kat')) . '</a>';
	} else {
		$ret .= '<ul>';
	}
	$_SESSION['katalog']['navigace_kategorii'] = '';
	//$_SESSION['katalog']['rozpadla_kategorie'] = '';
	foreach($s as $k => $h) {
		$adr = '<a href="/katalog/index/index/' . $pref_url . 'u' . ($uroven + 1) . '/' . $h['id'] . '/nkat/' . $pref_nazev . strtolower(cesky($h['nazev'])) . '/jenskladem/' . $_SESSION['katalog']['pars']['jenskladem'] . '/jenmfp/' . $_SESSION['katalog']['pars']['jenmfp'] . '">' . $h['nazev'] . '</a>';
		$adr_ = '<a href="/katalog/index/index/' . $pref_url . 'u' . ($uroven + 1) . '/' . $h['id'] . '/nkat/' . $pref_nazev . strtolower(cesky($h['nazev'])) . '/jenskladem/' . $_SESSION['katalog']['pars']['jenskladem'] . '/jenmfp/' . $_SESSION['katalog']['pars']['jenmfp'] . '">' . str_replace(' ', '&nbsp;', $h['nazev']) . '</a>';
		if (!isset($_SESSION['katalog']['max_uroven'])) {
			$_SESSION['katalog']['max_uroven'] = 0;
		}
		if ($uroven > $_SESSION['katalog']['max_uroven']) {//pokud se dostavam na dalsi uroven / momentalne nejvyssi/ vymazu navigaci
			$_SESSION['katalog']['max_uroven'] = $uroven;
			$_SESSION['katalog']['navigace_kategorii'] = '';
		}

		if (isset($_SESSION['katalog']['pars']['u' . (string) ($uroven+1)]) && $_SESSION['katalog']['pars']['u' . (string) ($uroven+1)] == $h['id']) {
			$ret .= '<li class="active">' . $h['nazev'] . '</li>';
			if (!isset($_SESSION['katalog']['rozpad']['u' . (string) ($uroven+1)])) {
				$_SESSION['katalog']['navigace_kategorii'] .= str_replace(' ', '&nbsp;', $h['nazev']) /*. ' (' . $h['pocet'] . ')*/ .' | ';
			}
		} else {
			$ret .= '<li>' . $adr . '</li>';
			if (!isset($_SESSION['katalog']['rozpad']['u' . (string) ($uroven+1)])) {
				$_SESSION['katalog']['navigace_kategorii'] .= $adr_/* . ' (' . $h['pocet'] */. ' | ';
			}
		}
		//$ret .= '</li>';
		if (isset($_SESSION['crumb'][$uroven]) && $_SESSION['crumb'][$uroven]['id'] == $h['id']) {
			if ($h['id'] == $_SESSION['katalog']['pars']['nkat']) {
				$_SESSION['crumb-text'] .= ' <span class="oddelovac">&raquo;</span> ' . $adr;
			} else {
				if (!isset($_SESSION['crumb-text'])) {
					$_SESSION['crumb-text'] = '';
				}
				$_SESSION['crumb-text'] .= ' <span class="oddelovac">&raquo;</span> <strong>' . $h['nazev'] . '</strong>';
				$_SESSION['katalog']['nazev_kat'] = $h['nazev'];
			}
		}
		if (sizeof($h['nasl']) > 0) {//ma naslednika, takze ho rekurzivne zobrazim
			$_SESSION['katalog']['rozpadla_kategorie'] = $h['nazev'];
			$ret .= zobrazStrom($h['nasl'], $uroven + 1, $pref_url . 'u' . ($uroven + 1) . '/' . $h['id'] . '/', $pref_nazev . strtolower(cesky($h['nazev'])) . '-');
		}
	}
	$ret .= '</ul>';
	return $ret;
}

function zpracujTexty($zdroj) {
	$txt = array();
	if (isset($zdroj->j->row)) {
		foreach($zdroj->j->row as $r) {
			$txt[(string) $r->kod] = (string) $r->txt;
		}
	}
	if (isset($zdroj->c->row)) {
		foreach($zdroj->c->row as $r) {
			if (!isset($txt[(string) $r->kod])) {
				$txt[(string) $r->kod] = (string) $r->txt;
			}
		}
	}
	return $txt;
}

function mailuj($komu, $predmet, $zprava, $kodovani = 'UTF-8') {
    if (substr($komu, -6) == 'mfp.cz') {
        if (THIS_SERVER != 'http://localhost' || THIS_SERVER != 'http://lmfp') {
            @mail($komu, $predmet . ' [' . $_SESSION['jazyk'] . ']', $zprava, 'From: ' . EMAIL_ODESILATEL . "\r\n" . 'Content-Type: text/plain; charset="' . $kodovani . '"' . "\r\n");
        }
    } else {
        if (THIS_SERVER != 'http://localhost' || THIS_SERVER != 'http://lmfp') {
            @mail($komu, $predmet, $zprava, 'From: ' . EMAIL_ODESILATEL . "\r\n" . 'Content-Type: text/plain; charset="' . $kodovani . '"' . "\r\n");
        }
    }
    //kopie spravci serveru
    #mail('mfpshop@rbc.cz', $predmet, 'iso Original pro: ' . iconv('UTF-8', 'iso-8859-2//TRANSLIT', $komu) . "\r\n\r\n" . iconv('UTF-8', 'iso-8859-2//TRANSLIT', $zprava), 'From: ' . EMAIL_ODESILATEL . "\r\n" . 'Content-Type: text/plain; charset="iso-8859-2"' . "\r\n");
    @mail('mfpshop@rbc.cz', $predmet . ' [' . $_SESSION['jazyk'] . ']', 'Original pro: ' . $komu . "\r\n\r\n" . $zprava, 'From: ' . EMAIL_ODESILATEL . "\r\n" . 'Content-Type: text/plain; charset="' . $kodovani . '"' . "\r\n");
}

function maxKlicPole($pole = array()) {
	$ret = 0;
	foreach($pole as $k => $h) {
		if ($k > $ret) {
			$ret = $k;
		}
	}
	return $ret;
}

function getCrumb($uvod_popis) {
    $ret = '<a href="/katalog/index/index">' . $uvod_popis . '</a>';
    $pref = '';
    foreach($_SESSION['crumb'] as $i => $r) {
        $ret .= ' <span class="oddelovac">&raquo;</span> <a href="/katalog/index/index' . $pref . '/u' . (string)((integer) $i + 1) . '/' . $r['id'] . '/nkat/' . $r['kat'] . '">' . $r['kat'] . '</a>';
        $pref .= '/u' . (string)((integer) $i + 1) . '/' . $r['id'];
    }
    return $ret;
}

/*
 * Vrati obrazek s jazykovou koncovkou
 *
 * @parameter src src agrument
 *
 * @todo Vyresit pevny predpoklad, ze pripona obrazku ma 3 znaky
 */
function obrazekJazyk($src) {
    $jazykova_varianta = substr($src, 0, -4) . '_' . $_SESSION['jazyk'] . '.' . substr($src, -3);
    if (is_file(CESTA . '/public/' . $jazykova_varianta)) {
        return $jazykova_varianta;
    } else {
        return $src;
    }
}
