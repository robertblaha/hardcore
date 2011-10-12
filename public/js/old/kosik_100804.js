function PrepoctiKosik() {
	Pracuji();
	console.debug('prepocitavam');
	var cena_mezi = dojo.byId('kos-cena-mezi');
	var cenadph_mezi = dojo.byId('kos-cenadph-mezi');
	var cena_celk = dojo.byId('kos-cena-celk');
	var cenadph_celk = dojo.byId('kos-cenadph-celk');
	var cena_mezi = dojo.byId('kos-cena-mezi');
	var cenadph_mezi = dojo.byId('kos-cenadph-mezi');
	var sleva = dojo.byId('kos-sleva');
	var sleva_proc = dojo.byId('kos-sleva');
	var sleva_cena = dojo.byId('kos-sleva-cena');
	var sleva_cenadph = dojo.byId('kos-sleva-cenadph');
	//cyklus pres jednotlive polozky
	var i = 1;
	var xcelk = 0;
	var xcelkdph = 0;
	while(i <= kosik_max_pol) {
		if (dojo.byId('kos-cena-' + i)) {
			console.debug('radek ' + i);
			//alert(Number(dojo.byId('kos-cena-' + i).innerHTML.replace(/ /, '').replace(/,/,'.')));
			xcelk += NaCislo(dojo.byId('kos-cena-' + i).innerHTML);
			//alert(xcelk);
			xcelkdph += NaCislo(dojo.byId('kos-cenadph-' + i).innerHTML);
		}
		i++;
	}
	//zjisteni spravne ceny
	i = 1;
	sleva.innerHTML = '0';
	sleva_proc.value = 0;
	while(slc[i]) {
		if (slc[i] < xcelk) {
			sleva.innerHTML = (slp[i]) * 100;
			sleva_proc.value = (slp[i]) * 100;
		}
		i++;
	}
	cena_mezi.innerHTML = FormatCisla(xcelk, 2);	
	cenadph_mezi.innerHTML = FormatCisla(xcelkdph, 2);
	sleva_cena.innerHTML = FormatCisla((NaCislo(sleva.innerHTML)/100) * NaCislo(cena_mezi.innerHTML), 2);
	sleva_cenadph.innerHTML = FormatCisla((NaCislo(sleva.innerHTML)/100) * NaCislo(cenadph_mezi.innerHTML), 2);
	cena_celk.innerHTML = FormatCisla(NaCislo(cena_mezi.innerHTML) - NaCislo(sleva_cena.innerHTML), 2);
	cenadph_celk.innerHTML = FormatCisla(NaCislo(cenadph_mezi.innerHTML) - NaCislo(sleva_cenadph.innerHTML), 2);
	Nepracuji();
	NastavSlevu(sleva_proc.value);
}

function UpravKusy(e) {
	Pracuji();
	e.target.value = CeskeCislo(e.target.value);
	var poradi = e.target.id.substr(8);
	//alert('kos-prepocet-' + poradi);	
	var prepocet = dojo.byId('kos-prepocet-' + poradi);
	var jcena = dojo.byId('kos-jcena-' + poradi);
	var dph = dojo.byId('kos-dph-' + poradi);
	var cena = dojo.byId('kos-cena-' + poradi);
	var cenadph = dojo.byId('kos-cenadph-' + poradi);
	var ks = dojo.byId('kos-ks-' + poradi);
	console.debug('JCena: ' + Number(jcena.value))
	ks.value = FormatCisla(e.target.value * prepocet.value, 0);
	cena.innerHTML = FormatCisla(Math.round(Number(e.target.value) * prepocet.value * Number(jcena.value), 2), 2);
	cenadph.innerHTML = FormatCisla(Math.round(NaCislo(cena.innerHTML) * (1 + (Number(dph.value) * 0.01)), 2), 2);
	PrepoctiKosik();
	UpravMnozstviKosiku(poradi, Number(e.target.value))
	Nepracuji();
}

function UpravMnozstviKosiku(poradi, mnozstvi) {
	Pracuji();
	dojo.xhrGet({
		url: "/obchod/kosik/uprav/pol/" + poradi + "/mnozstvi/" + mnozstvi,
		handleAs: "text",
		load: function(data,args){
			console.debug('Vysledek upravy kosiku: ' + data);
			if (data == 'OK') {
				x = 'je to ok';
			} else {
				alert('Aktualizace košíku se nezdařila, omlouváme se. Upravte, prosím, množství znovu.');
			}
			Nepracuji();
		},
		// if any error occurs, it goes here:
		error: function(error,args){
			console.warn("Chyba pri uprave mnozstvi v kosiku: ",error);
			Nepracuji();
		}
	});
}

//function PridejZbozi(e) {
function PridejZbozi() {
	Pracuji();
	//alert(dojo.byId('zboziok').value);
	//Pracuji();
	//ted uz tam je jen jeden radek
	//if (e.target.id == 'pridej1') {
		var kc = dojo.byId('katcislo1').value;
		var ks = Number(NaCislo(dojo.byId('ks1').value));
	//} else {
	//	var kc = dojo.byId('katcislo2').value;
	//	var ks = Number(NaCislo(dojo.byId('ks2').value));
	//}
	console.debug('Kat.cislo: ' + kc);
	console.debug('Mnozstvi: ' + ks);
	var isok = true;
	if (dojo.trim(kc) == '') {
		alert('Zadejte, prosím, katalogové číslo zboží, které chcete přidat.')
		isok = false;
	}
	if (ks == 0) {
		alert('Zadejte, prosím, množství zboží, které chcete přidat.')
		isok = false;
	}
	if (dojo.byId('zboziok').value != 'true') {
		alert('Zboží s tímto katalogovým číslem neexistuje. Zkontrolujte a opravte, prosím, zadané katalogové číslo.')
		isok = false;
	}
	//dojo.stopEvent(e);
	if (isok) {
		var adr = '/obchod/kosik/pridej/kat_id/' + kc + '/mnozstvi/' + ks;
		console.debug('Adresa pro pridani do kosiku: ' + adr);
		//window.location = "http://" + adr;
		window.location = adr;
	} else {
		Nepracuji();
	}
}

function OverZbozi(e) {
	Pracuji();
	var kat_id = e.target.value;
	dojo.xhrGet({
		url: "/admin/ajax/nacti/prikaz/overzbozi/pn1/kat_id/ph1/" + kat_id,
		handleAs: "json",
		load: function(data,args){
			console.debug('Nactena data: ' + data);
			if (data.r.row.pocet != '1') {
				alert('Zboží s tímto katalogovým číslem neexistuje. Zkontrolujte a opravte, prosím, zadané katalogové číslo.');
				dojo.byId('zboziok').value = 'false';
			} else {
				dojo.byId('zboziok').value = 'true';
			}
			Nepracuji();
		},
		// if any error occurs, it goes here:
		error: function(error,args){
			console.warn("Chyba pri overovani mnozstvi v kosiku: ",error);
			Nepracuji();
		}
	});
}

function NastavSlevu(s) {
	//Pracuji();
	//var kat_id = e.target.value;
	dojo.xhrGet({
		url: "/obchod/kosik/nastavslevu/sleva/" + s,
		handleAs: "text",
		load: function(data,args){
			console.debug('Nactena data: ' + data);
			//Nepracuji();
		},
		// if any error occurs, it goes here:
		error: function(error,args){
			console.warn("Chyba pri nastavovani slevy: ",error);
			//Nepracuji();
		}
	});
}

function OdesliDopravu() {
	//nejprve musim overit, ze je zadana doprava
	var zadana_doprava = false;
	dojo.query("input.doprava").forEach(
    	function(cil) {
    		if (dojo.attr(cil, 'checked')) {
        		zadana_doprava = true;
    		}
		}
	);
	var zadana_platba = false;
	dojo.query("input.platba").forEach(
    	function(cil) {
    		if (dojo.attr(cil, 'checked')) {
        		zadana_platba = true;
    		}
		}
	);
	if (zadana_doprava && zadana_platba) {
		if (dojo.byId('zpusobdopravy')) {
			dojo.byId('zpusobdopravy').submit();
		}
	} else {
		alert('Je nutné uvést způsob dopravy a platby.');
	}
}

function stisknuto(e) {
	//alert('--' + e.keyCode);
	if (e.keyCode == '13' && dojo.trim(dojo.byId('katcislo1').value) != '' && dojo.trim(dojo.byId('ks1').value) != '') {
		//alert('pridam');
		dojo.stopEvent(e);
		//OverZbozi(e);
		PridejZbozi();
	}
}

function ZobrazPlatby(e) {
	//zjistim zaskrtlou dopravu
	var doprava = '';
	dojo.query("input.doprava").forEach(
    	function(cil) {
    		if (dojo.attr(cil, 'checked')) {
        		//alert(cil.value);
        		doprava = cil.value.replace(' ', '_');
    		}
		}
	);
	dojo.query("tr.platba").forEach(
    	function(cil) {
    		//alert(dojo.attr(cil, 'value'));
        	cil.style.display = 'none';
		}
	);
	//je zaskrtnuta platna?
	var platna = false;
	dojo.query("tr." + doprava).forEach(
    	function(cil) {
    		//alert(dojo.attr(cil, 'value'));
        	cil.style.display = 'table-row';
        	if (dojo.byId('pl-' + cil.id.substr(3)).checked) {
        		//alert('pl-' + cil.id.substr(3));
        		platna = true;
        	}
		}
	);
	//zvolena neni platna, takze zaskrtla neni zadna, takze zaskrtnu prvni
	var uz = false;
	if (!platna) {
		//alert('jdu sem');
		dojo.query("input.platba").forEach(
	    	function(cil) {
	    		//alert(dojo.attr(cil, 'value'));
	    		if (!uz && dojo.byId('tr-' + cil.id.substr(3)).style.display == 'table-row') {
	    			cil.checked = true;
	    			uz = true;
	    		} else {
	        		cil.checked = false;	
	    		}
			}
		);
	}
}

dojo.addOnLoad(function(){
	var i = 1;
	while (i <= kosik_max_pol) {
		if (dojo.byId('kos-bal-' + i)) {
			dojo.connect(dojo.byId('kos-bal-' + i), 'onchange', 'UpravKusy');
		}
		i++;
	}
	i = 1;
	while(i < 3 && dojo.byId('pridej' + i)) {
		dojo.connect(dojo.byId('pridej' + i), 'onclick', 'PridejZbozi');
		dojo.connect(dojo.byId('katcislo' + i), 'onchange', 'OverZbozi');
		i++;
	}
	if (dojo.byId('dopravapokladna')) {
		dojo.connect(dojo.byId('dopravapokladna'), 'click', OdesliDopravu);
	}
	if (dojo.byId('katcislo1')) {
		dojo.connect(dojo.byId('katcislo1'),'onkeypress',stisknuto);
	}
	if (dojo.byId('ks1')) {
		dojo.connect(dojo.byId('ks1'),'onkeypress',stisknuto);
	}
	dojo.query("input.doprava").forEach(
    	function(e) {
    		//alert(dojo.attr(e, 'id').substr(2));
        	dojo.connect(e, 'onclick', ZobrazPlatby);
		}
	);
	ZobrazPlatby();
});