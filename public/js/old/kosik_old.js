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
	var sleva_cena = dojo.byId('kos-sleva-cena');
	var sleva_cenadph = dojo.byId('kos-sleva-cenadph');
	//cyklus pres jednotlive polozky
	var i = 0;
	var xcelk = 0;
	var xcelkdph = 0;
	while(dojo.byId('kos-cena-' + i)) {
		console.debug('radek ' + i);
		//alert(Number(dojo.byId('kos-cena-' + i).innerHTML.replace(/ /, '').replace(/,/,'.')));
		xcelk += NaCislo(dojo.byId('kos-cena-' + i).innerHTML);
		//alert(xcelk);
		xcelkdph += NaCislo(dojo.byId('kos-cenadph-' + i).innerHTML);
		i++;
	}
	cena_mezi.innerHTML = FormatCisla(xcelk, 2);	
	cenadph_mezi.innerHTML = FormatCisla(xcelkdph, 2);
	sleva_cena.innerHTML = FormatCisla((NaCislo(sleva.innerHTML)/100) * NaCislo(cena_mezi.innerHTML), 2);
	sleva_cenadph.innerHTML = FormatCisla((NaCislo(sleva.innerHTML)/100) * NaCislo(cenadph_mezi.innerHTML), 2);
	cena_celk.innerHTML = FormatCisla(NaCislo(cena_mezi.innerHTML) - NaCislo(sleva_cena.innerHTML), 2);
	cenadph_celk.innerHTML = FormatCisla(NaCislo(cenadph_mezi.innerHTML) - NaCislo(sleva_cenadph.innerHTML), 2);
	Nepracuji();
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

function PridejZbozi(e) {
	Pracuji();
	if (e.target.id == 'pridej1') {
		var kc = dojo.byId('katcislo1').value;
		var ks = Number(NaCislo(dojo.byId('ks1').value));
	} else {
		var kc = dojo.byId('katcislo2').value;
		var ks = Number(NaCislo(dojo.byId('ks2').value));
	}
	console.debug('Kat.cislo: ' + kc);
	console.debug('Mnozstvi: ' + ks);
	var isok = true;
	if (kc.trim() == '') {
		alert('Zadejte, prosím, katalogové číslo zboží, které chcete přidat.')
		isok = false;
	}
	if (ks == 0) {
		alert('Zadejte, prosím, množství zboží, které chcete přidat.')
		isok = false;
	}
	if (!dojo.byId('zboziok').value.trim() == 'true') {
		alert('Zboží s tímto katalogovým číslem neexistuje. Zkontrolujte a opravte, prosím, zadané katalogové číslo.')
		isok = false;
	}
	dojo.stopEvent(e);
	if (isok) {
		var adr = '/obchod/kosik/pridej/kat_id/' + kc + '/mnozstvi/' + ks;
		console.debug('Adresa pro pridani do kosiku: ' + adr);
		//window.location = "http://" + adr;
		window.location = adr;
	}
	Nepracuji();
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

function OdesliDopravu() {
	if (dojo.byId('zpusobdopravy')) {
		dojo.byId('zpusobdopravy').submit();
	}
}

dojo.addOnLoad(function(){
	var i = 0;
	while (dojo.byId('kos-bal-' + i)) {
		dojo.connect(dojo.byId('kos-bal-' + i), 'onchange', 'UpravKusy');
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
});