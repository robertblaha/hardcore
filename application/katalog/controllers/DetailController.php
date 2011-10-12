<?php

class Katalog_DetailController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function ukazAction()
    {
    	//ulozeni posledniho URL katalogu pro pripadny navrat
    	$_SESSION['crumb'] = array();
    	$_SESSION['url']['detail'] = $_SERVER['REQUEST_URI'];
        $pars = $this->_getAllParams();
        
        //parametry katalogu pro pouziti ve zpracovani
        $_SESSION['detail']['pars'] = $pars;
        
        $this->view->strom = stromek(0, $this->_getAllParams());

        //nalezeni posledni kategorie rozpadu
        $i = 1;
        $max_kat = 0;
        while(isset($pars['u' . (string) $i])) {
        	$max_kat = $pars['u' . (string) $i];
        	$i++;
        }
        
        $vysl = readData('detail_zbozi', 
        	array(
        		'kc' => textToDB($pars['kc']),
        		'cenik' => $_SESSION['uzivatel']['cenik'],
        		'firma_sleva' => $_SESSION['uzivatel']['firma_sleva'],
        		'ahoj' => 'ahoj'
        	)
        );
        //print_r($_SESSION['vracene_xml']);
        //detail zbozi
        foreach($vysl->z->row as $r) {
        	$this->view->zbozi = array('produkt' => (string) $r->produkt, 'popis' => (string) $r->popis, 'sdph' => (double) $r->sdph,
        	 'cena' => (double) $r->cena, 'baleni' => (string) $r->baleni, 'text' => $r->xtext, 'dodavatel' => (string) $r->dodavatel,
        	 'kc' => (string) $r->kc, 'carovy_kod' => (string) $r->carovy_kod, 'carovy_kod_vlastni' => (string) $r->carovy_kod_vlastni,
        	 'skladem' => (float) $r->skladem, 'sleva' => (double) $r->sleva, 'mez_1' =>(float) $r->mez_1, 'mez_2' =>(float) $r->mez_2,
        	 'min_mnozstvi' => (integer) $r->min_mnozstvi, 'nasobky' => (integer) $r->nasobky);
        }
        //souvisejici zbozi
        $this->view->souv = array();
        foreach($vysl->s->row as $r) {
        	$this->view->souv[] = array('produkt' => (string) $r->produkt, 'popis' => (string) $r->popis, 'sdph' => (double) $r->sdph,
        	 'cena' => (double) $r->cena, 'vshop_id' => (string) $r->vshop_id, 'baleni' => (string) $r->baleni, 'skladem' => (int) $r->skladem,
        	 'text' => $r->xtext, 'sleva' => (double) $r->sleva, 'akce' => (int) $r->akce, 'sezona' => (int) $r->sezona,
        	 'novinka' => (int) $r->novinka, 'doprodej' => (int) $r->doprodej, 'min_mnozstvi' => (int) $r->min_mnozstvi,
        	 'nasobky' => (int) $r->nasobky);
        }
        foreach($vysl->sr->row as $r) {
        	$this->view->souv[] = array('produkt' => (string) $r->produkt, 'popis' => (string) $r->popis, 'sdph' => (double) $r->sdph,
        	 'cena' => (double) $r->cena, 'vshop_id' => (string) $r->vshop_id, 'baleni' => (string) $r->baleni, 'skladem' => (int) $r->skladem,
        	 'text' => $r->xtext, 'sleva' => (double) $r->sleva, 'akce' => (int) $r->akce, 'sezona' => (int) $r->sezona,
        	 'novinka' => (int) $r->novinka, 'doprodej' => (int) $r->doprodej, 'min_mnozstvi' => (int) $r->min_mnozstvi,
        	 'nasobky' => (int) $r->nasobky);
        }
        //souvisejici zbozi
        $this->view->alter = array();
        foreach($vysl->a->row as $r) {
        	$this->view->alter[] = array('produkt' => (string) $r->produkt, 'popis' => (string) $r->popis, 'sdph' => (double) $r->sdph,
        	 'cena' => (double) $r->cena, 'vshop_id' => (string) $r->vshop_id, 'baleni' => (string) $r->baleni, 'skladem' => (int) $r->skladem,
        	 'text' => $r->xtext, 'sleva' => (double) $r->sleva, 'akce' => (int) $r->akce, 'sezona' => (int) $r->sezona,
        	 'novinka' => (int) $r->novinka, 'doprodej' => (int) $r->doprodej, 'min_mnozstvi' => (int) $r->min_mnozstvi,
        	 'nasobky' => (int) $r->nasobky);
        }
        foreach($vysl->ar->row as $r) {
        	$this->view->alter[] = array('produkt' => (string) $r->produkt, 'popis' => (string) $r->popis, 'sdph' => (double) $r->sdph,
        	 'cena' => (double) $r->cena, 'vshop_id' => (string) $r->vshop_id, 'baleni' => (string) $r->baleni, 'skladem' => (int) $r->skladem,
        	 'text' => $r->xtext, 'sleva' => (double) $r->sleva, 'akce' => (int) $r->akce, 'sezona' => (int) $r->sezona,
        	 'novinka' => (int) $r->novinka, 'doprodej' => (int) $r->doprodej, 'min_mnozstvi' => (int) $r->min_mnozstvi,
        	 'nasobky' => (int) $r->nasobky);
        }
        //print_r($_SESSION['vracene_xml']);
        $this->view->script_files = array('/js/katalog_100822_1.js', '/fancybox/jquery-1.3.2.min.js', '/fancybox/custom.js',
        	'/fancybox/jquery.easing.1.3.js', '/fancybox/jquery.fancybox-1.2.1.pack.js');
        $this->view->css_files = array('/fancybox/jquery.fancybox.css');
        //echo $vysl->p->row->pocet;
        //print_r($this->view->pager['url']);
    }

}

