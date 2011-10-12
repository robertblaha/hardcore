//skript, ktery se nacita na vsech stranach - obsahuje spolecne funkce a funkce pro zahlavi a zapati
function osetreniLoginu(event) {
	//plneni hodnot do poli pri jejich osetreni
	if (dojo.byId('user') && dojo.byId('user_preklad') && dojo.byId('password') && dojo.byId('password_preklad')) {
		var prvek = dojo.byId(event.target.id);
		if (event.type == 'blur') {
			if (prvek.value == '') {
				prvek.value = dojo.byId(event.target.id + '_preklad').value;
			}
		}
		if (event.type == 'focus') {
			if (prvek.value == dojo.byId(event.target.id + '_preklad').value) {
				prvek.value = '';
			}
		}
	}
}

function osetreniHledani(event) {
	//plneni hodnot do poli pri jejich osetreni
	if (dojo.byId('hledej') && dojo.byId('hledej_preklad')) {
		var prvek = dojo.byId('hledej');
		if (event.type == 'blur') {
			if (prvek.value == '') {
				prvek.value = dojo.byId('hledej_preklad').value + ' ...';
			}
		}
		if (event.type == 'focus') {
			if (prvek.value == dojo.byId('hledej_preklad').value + ' ...') {
				prvek.value = '';
			}
		}
	}
}

function spustHledani(event) {
	if (dojo.byId('hledej') && dojo.byId('hledej_preklad')) {
		var prvek = dojo.byId('hledej');
		if (prvek.value != '' && prvek.value != dojo.byId('hledej_preklad').value + ' ...') {
			Pracuji();
			window.location = dojo.byId('tento_server').value + '/katalog/index/index/u1/0/nkat/vse/hledani/' + prvek.value;
			dojo.stopEvent(event);
		}
	}
}
function NaCislo(s) {
	return Number(CeskeCislo(s).replace(/,/, '.').replace(/ /, ''));
}

function FormatCisla(c, p) {
	return c.toFixed(p).toString().replace(/\./, ',');
}

function CeskeCislo(cestina) {
    //alert(cestina + '---');
    return cestina.toLowerCase().replace(/ /g, '').replace(/\+/g, '1').replace(/ě/g, '2').replace(/š/g, '3').replace(/č/g, '4').replace(/ř/g, '5').replace(/ž/g, '6').replace(/ý/g, '7').replace(/á/g, '8').replace(/í/g, '9').replace(/é/g, '0');
}

function CeskeCisloEvent(event) {
	var cestina = event.target.value;
    //alert(cestina + '---');
    event.target.value = cestina.toLowerCase().replace(/ /g, '').replace(/\+/g, '1').replace(/ě/g, '2').replace(/š/g, '3').replace(/č/g, '4').replace(/ř/g, '5').replace(/ž/g, '6').replace(/ý/g, '7').replace(/á/g, '8').replace(/í/g, '9').replace(/é/g, '0');
}

function Pracuji() {
	var makosi = dojo.byId('makosi');
	console.debug('Makosi se chystaji: ' + makosi.value);
	makosi.value = Number(makosi.value) + 1;
	console.debug('Makosi makaji: ' + makosi.value);
	if (makosi.value > 0) {
		dojo.byId('ajax-loader-obr').style.display = 'block';
	}
}

function Nepracuji() {
	var makosi = dojo.byId('makosi');
	console.debug('Uz to bude: ' + makosi.value);
	makosi.value = Number(makosi.value - 1);
	console.debug('Po praci: ' + makosi.value);
	if (makosi.value <= 0) {
		dojo.byId('ajax-loader-obr').style.display = 'none';
		//makosi.value = 0;
	}
}

function ZavriLoader() {
	var makosi = dojo.byId('makosi');
	makosi.value = 0;
	dojo.byId('ajax-loader-obr').style.display = 'none';
}

function ZavriInfo() {
	dojo.byId('info-kosik').style.display = 'none';
}

function PotvrdVysypani(e) {
	Nepracuji();
	if (confirm('Opravdu chcete vyprázdnit košík?\r\n'  + 'Pokud ano, stiskněte OK.')) {
		Pracuji();
	} else {
		dojo.stopEvent(e);
	}
}

dojo.addOnLoad(function(){
	console.debug('Verze Dojo: ' + dojo.version);
	if (dojo.byId('user')) {
		dojo.connect(dojo.byId('user'), 'onblur', osetreniLoginu);
		dojo.connect(dojo.byId('user'), 'onfocus', osetreniLoginu);
	}
	if (dojo.byId('password')) {
		dojo.connect(dojo.byId('password'), 'onblur', osetreniLoginu);
		dojo.connect(dojo.byId('password'), 'onfocus', osetreniLoginu);
	}
	if (dojo.byId('hledej')) {
		dojo.connect(dojo.byId('hledej'), 'onblur', osetreniHledani);
		dojo.connect(dojo.byId('hledej'), 'onfocus', osetreniHledani);
	}
	if (dojo.byId('go')) {
		dojo.connect(dojo.byId('go'), 'onclick', spustHledani);
	}
	if (dojo.byId('info-kosik-zavri')) {
		dojo.connect(dojo.byId('info-kosik-zavri'), 'onclick', ZavriInfo);
	}
	if (dojo.byId('ajax-loader-obr')) {
		dojo.connect(dojo.byId('ajax-loader-obr'), 'onclick', ZavriLoader);
	}
	if (dojo.byId('vysypat')) {
		dojo.connect(dojo.byId('vysypat'), 'onclick', PotvrdVysypani);
	}
	dojo.query("a:not(.fotka):not(.noloadinfo)").forEach(
    	function(e) {
        	dojo.connect(e, 'onclick', Pracuji);
		}
	);
 });

