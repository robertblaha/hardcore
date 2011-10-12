function razeniChange(event) {
	Pracuji();
	var radit = dojo.byId('radit');
	var vzestupne = dojo.byId('vzestupne');
	var sestupne = dojo.byId('sestupne');
	var vyrobce = dojo.byId('vyrobce');
	var skladem = dojo.byId('skladem');
	var jenmfp = dojo.byId('jenmfp');
	var kolik = dojo.byId('howmany');
	var adresa = cesta_razeni + '/strana/1/razeni/' + radit.value + '/nastranu/' + kolik.value;
	if (vyrobce && vyrobce.value != '') {
		adresa += '/vyrobce/' + vyrobce.value;
	}
	if (skladem && skladem.checked) {
		adresa += '/jenskladem/1';
	}
	if (jenmfp && jenmfp.checked) {
		adresa += '/jenmfp/1';
	}
	if (event.target.id == 'vzestupne') {
		adresa += '/smer/asc';
	} else if (event.target.id == 'sestupne') {
		adresa += '/smer/desc';
	} else {
		adresa += '/smer/' + stavajici_razeni;
	}
	//alert(adresa);
	window.location = "http://" + adresa;
	dojo.stopEvent(event);
}

function xdoKosiku(e) {
	var pol = e.target.id.substr(6);
	console.debug('Poradi pridavaneho zbozi: ' + pol);
	var adresa = ts + '/obchod/kosik/pridej/kat_id/' + dojo.byId('produkt' + pol).value + '/mnozstvi/' + CeskeCislo(dojo.byId('ks' + pol).value);
	console.debug('Adresa pro pridani do kosiku: ' + adresa);
	//alert(adresa);
	//window.location.href = adresa;
	dojo.stopEvent(e);
}

function doKosiku(e) {
	//Pracuji();
	dojo.stopEvent(e);
	var cil = e.target.id, id = '';
	if (cil.substring(0,2) == 'ks') {
		id = cil.substring(2);
	} else {
		id = cil.substring(6);
	}
	//alert(e.target.id);
	var produkt = dojo.byId('produkt' + id).value, ks = CeskeCislo(dojo.byId('ks' + id).value), url = '/obchod/kosik/pridej/kat_id/' + produkt + '/mnozstvi/' + ks + '/ajax/1';
	console.debug('URL pro pridani do kosiku: ' + url);
	dojo.xhrGet({
		url: url,
		handleAs: "json",
		load: function(data,args){
			console.debug('Nactena data: ' + data);
			var txt = dojo.byId('info-kosik-text');
			dojo.byId('kos-txt-pol').innerHTML = data.kosik.polozky;
			switch (data.kosik.polozky) {
				case 1:
					dojo.byId('kos-txt-pol-ozn').innerHTML = kosik_preklad['pol_1'];
					break;
				case 2:
				case 3:
				case 4:
					dojo.byId('kos-txt-pol-ozn').innerHTML = kosik_preklad['pol_2'];
					break;
				default:
					dojo.byId('kos-txt-pol-ozn').innerHTML = kosik_preklad['pol_5'];
					break;
			}
			txt.innerHTML = kosik_preklad['pridano'] + '<br/><br/>';
			txt.innerHTML += data.polozka.mj_evid + ' ' + kosik_preklad['ks'] + ' ' + data.polozka.nazev + '<br/>';
			txt.innerHTML += kosik_preklad['cena'] + ': ';
			if (dojo.byId('typ_firmy').value == 'vo') {
				txt.innerHTML += data.polozka.cena_radek + ' ' + kosik_preklad['bez dph'];
				dojo.byId('kos-txt-cena').innerHTML = data.kosik.cena;
			} else {
				txt.innerHTML += data.polozka.cena_radek_dph + ' ' + kosik_preklad['s dph'];
				dojo.byId('kos-txt-cena').innerHTML = data.kosik.cena_dph;
			}
			txt.innerHTML += '<br/>';
			//if (data.r.row.pocet != '1') {
			//	alert('Zboží s tímto katalogovým číslem neexistuje. Zkontrolujte a opravte, prosím, zadané katalogové číslo.');
			//	dojo.byId('zboziok').value = 'false';
			//} else {
			//	dojo.byId('zboziok').value = 'true';
			//}
			kosik_max_pol = data.kosik_max_pol;
			Nepracuji();
			dojo.byId('info-kosik').style.display = 'block';
			setTimeout('ZavriInfo()', 5000);
		},
		// if any error occurs, it goes here:
		error: function(error,args){
			console.warn("Chyba pri overovani mnozstvi v kosiku: ",error);
			Nepracuji();
			dojo.byId('info-kosik').style.display = 'block';
		}
	});
}

function stisknuto(e) {
	//alert('--' + e.keyCode);
	if (e.keyCode == '13') {
		if (e.target.value != 0) {
			//alert('pridam ' + e.target.value);
			Pracuji();
			doKosiku(e);
		} else {
			dojo.stopEvent(e);
		}
	}
}


dojo.addOnLoad(function(){
	var radit = dojo.byId('radit');
	var vzestupne = dojo.byId('vzestupne');
	var sestupne = dojo.byId('sestupne');
	var vyrobce = dojo.byId('vyrobce');
	var skladem = dojo.byId('skladem');
	var jenmfp = dojo.byId('jenmfp');
	var kolik = dojo.byId('howmany');
	var vyrobce = dojo.byId('vyrobce');
	if (radit) {
		dojo.connect(radit, 'onchange', 'razeniChange');
	}
	if (vzestupne) {
		dojo.connect(vzestupne, 'onclick', 'razeniChange');
	}
	if (sestupne) {
		dojo.connect(sestupne, 'onclick', 'razeniChange');
	}
	if (vyrobce) {
		dojo.connect(vyrobce, 'onchange', 'razeniChange');
	}
	if (skladem) {
		dojo.connect(skladem, 'onclick', 'razeniChange');
	}
	if (jenmfp) {
		dojo.connect(jenmfp, 'onclick', 'razeniChange');
	}
	if (kolik) {
		dojo.connect(kolik, 'onchange', 'razeniChange');
	}
	dojo.query("a.pridani").forEach(
    	function(e) {
    		//alert(dojo.attr(e, 'id').substr(2));
        	dojo.connect(e, 'onclick', doKosiku);
		}
	);
	var i = 1;
	while (dojo.byId('ks' + i)) {
		dojo.connect(dojo.byId('ks' + i), 'onkeypress', 'stisknuto');
		i++;
	}
	
});