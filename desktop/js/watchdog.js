/*  Last Modified : 2025/09/02 18:32:43
 *
 * This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */



$("#table_controles").sortable({ axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });
$("#table_actions").sortable({ axis: "y", cursor: "move", items: ".watchdogAction", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });

$(document).ready(function () { //lancé quand toute la page est chargée
	$(".bt_masqueCalculs").hide();
});

// bt_reporting_maintenance
$('#bt_reporting_maintenance').off('click').on('click', function () {
	jeedom.eqLogic.getSelectModal({ eqLogic: { eqType_name: 'virtual' } }, function (result) {

		$.ajax({
			type: "POST",
			url: "plugins/watchdog/core/ajax/watchdog.ajax.php",
			data:
			{
				action: "reporting_maintenance",
				id: result.id
			},
			dataType: 'json',
			error: function (request, status, error) {
			},
			success: function (data) {
			}
		});
	});
});


// Affiche/Cache l'aide
$('.bt_help').off('click').on('click', function () {
	set_help_state('toggle');
});

function set_help_state(action = 'toggle') {

	var help_state = localStorage.getItem('watchdog_help_state');
	if (help_state == null)
		help_state = 'block'; // visible

	if (action == 'toggle') {
		if (help_state == 'block')
			help_state = 'none';
		else
			help_state = 'block';
	}

	const help_fields = document.querySelectorAll('.help_field');
	help_fields.forEach(myField => {
		myField.style.display = help_state;
	});
	if (help_state == 'block') {
		localStorage.removeItem('watchdog_help_state');
	}
	else {
		localStorage.setItem('watchdog_help_state', help_state);
	}

}

// BOUTONS -------------
// Selection du virtuel utilise pour le reporting pour l'equipement

$('#VirtualReport').off('click').on('click', function () {
	jeedom.eqLogic.getSelectModal({ eqLogic: { eqType_name: 'virtual' } }, function (result) {
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=VirtualReport]').value(result.human);
	});
});

// Selection du virtuel utilise pour le reporting pour le plugin
$('#VirtualReportPlugin').off('click').on('click', function () {
	jeedom.eqLogic.getSelectModal({ eqLogic: { eqType_name: 'virtual' } }, function (result) {
		$('.configKey[data-l1key=VirtualReport]').value(result.human);
	});
});

// Selection de l'équipement utilisé en parametre equipX
$('#equip1').off('click').on('click', function () {
	jeedom.eqLogic.getSelectModal({ eqLogic: { eqType_name: '' } }, function (result) {
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=equip1]').value(result.human);
	});
});

$('#equip2').off('click').on('click', function () {
	jeedom.eqLogic.getSelectModal({ eqLogic: { eqType_name: '' } }, function (result) {
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=equip2]').value(result.human);
	});
});

$('#equip3').off('click').on('click', function () {
	jeedom.eqLogic.getSelectModal({ eqLogic: { eqType_name: '' } }, function (result) {
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=equip3]').value(result.human);
	});
});

$('.bt_afficheCalculs').off('click').on('click', function () {
	$(".calcul").show();
	$(".bt_masqueCalculs").show();
	$(".bt_afficheCalculs").hide();
});

$('.bt_masqueCalculs').off('click').on('click', function () {
	$(".calcul").hide();
	$(".bt_masqueCalculs").hide();
	$(".bt_afficheCalculs").show();
});

$('#bt_cronGenerator').off('click').on('click', function () {
	jeedom.getCronSelectModal({}, function (result) {
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=autorefresh]').value(result.value);
	});
});

$('.bt_plugin_view_log').off('click').on('click', function () {

	var logfile = 'watchdog';
	if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=logspecifique]').value() == '1') {
		logfile = logfile + '_' + $('.eqLogicAttr[data-l1key=id]').value();
	}
	jeeDialog.dialog({
		id: 'jee_modal2',
		title: "{{Log de }}" + $('.eqLogicAttr[data-l1key=name]').value() + " (" + logfile + ")",
		contentUrl: 'index.php?v=d&modal=log.display&log=' + logfile
	})

});

// ajout d'une condition
$('.bt_addControle').off('click').on('click', function () {
	addCmdToTable({}, 'info');
});

/*
 Remplit la table des conditions
 */
function addCmdToTable(_cmd, type) {

	//On ignore resultatglobal et refresh
	if ((init(_cmd.logicalId) == 'refresh')) return;
	//On ignore resultatglobal si on n'est pas sur une condition ET/OU
	if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=typeControl]').value() == '') {
		if ((init(_cmd.logicalId) == 'resultatglobal')) return;
	}

	var ResultatGlobal = false;
	if (init(_cmd.logicalId) == 'resultatglobal') {
		ResultatGlobal = true;
	}
	var typecontrole = '';
	const selectElementByName = document.querySelector('select[name="typecontrole"]');
	if (selectElementByName) { // Il est toujours bon de vérifier si l'élément existe
		typecontrole = selectElementByName.value;
	} else {
		console.error("L'élément avec le nom 'typecontrole' n'a pas été trouvé.");
	}

	if (!isset(_cmd))
		var _cmd = {};
	if (!isset(_cmd.configuration))
		_cmd.configuration = {};
	if (!isset(_cmd.display))
		_cmd.display = {};
	if (isset(type))
		_cmd.type = type;
	if (!isset(_cmd.subType))
		// dans la version initiale du plugin, le subtype était watchdog qui n est pas un subType supporté par jeedom et ne permettait pas un affichage correct
		// changé en binary à partir de aout 2025
		_cmd.subType = "binary";
	// met en forme le resultat
	var resultatOK = _cmd.configuration.resultat;
	if (resultatOK == '1') {
		resultatOK = 'True';
	} else {
		if (resultatOK == '0') {
			resultatOK = 'False';
		}
	}

	var invertBinary = _cmd.display.invertBinary;
	if (invertBinary == '1') {
		if (resultatOK == 'True') {
			resultatOK = 'False';
		}
		else {
			if (resultatOK == 'False') {
				resultatOK = 'True';
			}
		}
	}

	var couleur = 'info';
	var icon = '';
	var data_title = '';
	switch (resultatOK) {
		case 'True':
			if (typecontrole == '' || ResultatGlobal == true) {
				var couleur = 'success';
				var icon = '<i class="far fa-thumbs-up"  style="color: #ffffff!important;"></i>';
			}
			break;
		case 'False':
			if (typecontrole == '' || ResultatGlobal == true) {
				var couleur = 'warning';
				var icon = '<i class="far fa-thumbs-down" style="color: #ffffff!important;"></i>';
			}
			break;
		default:
			var data_title = '{{Problème avec la condition. Vous pouvez tester l\'expression avec le testeur d\'expression (3ème bouton à droite de l\'expression).}}';
			var couleur = 'danger';
			var icon = '<i class="far fa-question-circle" style="color: #ffffff!important;"></i>';
	}

	// met en forme le resultat avant
	var resultatAvantOK = _cmd.configuration.resultatAvant;
	if (resultatAvantOK == '1') {
		resultatAvantOK = 'True';
	} else {
		if (resultatAvantOK == '0') {
			resultatAvantOK = 'False';
		}
	}

	if (invertBinary == '1') {
		if (resultatAvantOK == 'True') {
			resultatAvantOK = 'False';
		}
		else {
			if (resultatAvantOK == 'False') {
				resultatAvantOK = 'True';
			}
		}
	}

	var couleurAvant = 'info';
	var iconAvant = '';
	switch (resultatAvantOK) {
		case 'True':
			if (typecontrole == '' || ResultatGlobal == true) {
				var couleurAvant = 'success';
				var iconAvant = '<i class="far fa-thumbs-up"  style="color: #ffffff!important;"></i>';
			}
			break;
		case 'False':
			if (typecontrole == '' || ResultatGlobal == true) {
				var couleurAvant = 'warning';
				var iconAvant = '<i class="far fa-thumbs-down" style="color: #ffffff!important;"></i>';
			}
			break;
		default:
			var couleurAvant = 'danger';
			var iconAvant = '<i class="far fa-question-circle" style="color: #ffffff!important;"></i>';
	}

	var tr = '<tr class="cmd info" >';
	tr += '<td width=30>';
	if (ResultatGlobal == false) {
		tr += '<input type="checkbox" style="" class="cmdAttr" data-l1key="configuration" data-l2key="disable"  title="{{Cocher pour désactiver la condition}}" />';
	}
	tr += '</td>';

	tr += '<td width=160>';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
	tr += '<span style="display:none;" class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
	tr += '<input class="cmdAttr form-control" type="hidden" data-l1key="subType" value="binary">';
	tr += '</td>';
	tr += '<td >';

	tr += ' <input class="cmdAttr form-control input-sm"  data-type="' + _cmd.type + '" data-l1key="configuration" data-l2key="controle" ';
	if (ResultatGlobal == true) {
		tr += ' disabled ';
	}
	tr += ' style="margin-bottom : 5px;width : 80%; display : inline-block;" >';
	tr += ' <input class="cmdAttr form-control "  data-l1key="id"  style="display: none;" >';
	if (ResultatGlobal == false) {
		tr += '<a class="btn btn-info btn-sm cursor generer_expression" data-type="generer_expression"  style="margin-left : 5px;"><i class="far fa-star"  title="{{Générer expression}}" style="color: #ffffff!important;"></i></a>';
		tr += '<a class="btn btn-info btn-sm cursor macro" data-type="macro"  style="margin-left : 5px;"><i class="fab fa-medium-m" title="{{Convertir en macro}}" style="color: #ffffff!important;"></i></a>';
		tr += '<a class="btn btn-info btn-sm cursor testexpression" data-type="testexpression"  style="margin-left : 5px;"><i class="fas fa-check" title="{{Tester l\'expression}}" style="color: #ffffff!important;"></i></a>';

		tr += '<div hidden class="calcul"><small><i>';
		tr += '<span style="margin-top : 9px; margin-left: 10px; " class="cmdAttr" data-l1key="configuration" data-l2key="calcul"></span></i></small></div>';
	}

	tr += '</td>';
	tr += '<td width=150 align=center>'
	var resultatAvant = _cmd.configuration.resultatAvant;
	if (resultatAvant != null) {
		tr += '<span class="cmdAttr label label-' + couleurAvant + '" >' + iconAvant + '</span>';
		tr += '<span class="cmdAttr label label-' + couleurAvant + '" style="font-weight: bold; ' + '" > '
		if (resultatAvant == '1') {
			tr += '{{True}}';
		}
		else {
			if (resultatAvant == '0') {
				tr += '{{False}}';
			}
			else {
				tr += '{{' + resultatAvant + '}}';
			}
		}
		tr += "</span>";
		tr += '</span>';
	}
	tr += '</td>';
	tr += '<td width=150 align=center>';
	if (_cmd.configuration.resultat != null) {
		tr += '<span class="cmdAttr label label-' + couleur + '" >' + icon + '</span>';
		tr += '<span class="cmdAttr label label-' + couleur + '  style="font-weight: bold;"';
		if (data_title != '')
			tr += ' title=" ' + data_title + '" ';

		tr += "> ";
		var resultat = _cmd.configuration.resultat;
		if (resultat != null) {
			if (resultat == '1') {
				tr += '{{True}}';
			}
			else {
				if (resultat == '0') {
					tr += '{{False}}';
				}
				else {
					tr += '{{' + resultat + '}}';
				}
			}
		}
	}
	tr += '</td>';

	tr += '<td width=120 align=center>';

	if (is_numeric(_cmd.id)) {
		if (_cmd.isHistorized == '1') {
			console.log(_cmd.isHistorized);
			tr += '<a class="btn  btn-sm cursor historique" data-type="historique"  style="margin-left : 5px;"><i class="far fa-chart-bar" title="{{historique}}" style="color: #ffffff!important;"></i></a>';
		}
		tr += '<a class="btn  btn-sm cursor configure" data-type="configure"  style="margin-left : 5px;"><i class="fas fa-cogs" title="{{configure}}" style="color: #ffffff!important;"></i></a>';
	}
	if (ResultatGlobal == false) {
		tr += '<span style="font-size: 1.75em;"><i class="fas fa-minus-circle  cmdAction cursor"  data-action="remove"  style="margin-left : 5px;" title="{{Supprimer le contrôle}}"></i></span>';
	}

	tr += '</td>';


	tr += '</tr>';
	//tr += '<tr class="bg-warning"><td class="bg-warning">frgthjkl</td></tr>';
	$('#table_controles tbody').append(tr);
	$('#table_controles tbody tr:last').setValues(_cmd, '.cmdAttr');
	if (isset(_cmd.type)) {
		$('#table_controles tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
	}
	//Affiche le sous-type et n'affiche que les champs concernés par le type
	jeedom.cmd.changeType($('#table_controles tbody tr:last'), init(_cmd.subType));

}


// Test d'une action
$('#div_pageContainer').off('click').on('click', '.cmdAction[data-action=testaction]', function (event) {
	var id_action = $(this).attr('id_action');

	$.ajax({
		type: "POST",
		url: "plugins/watchdog/core/ajax/watchdog.ajax.php",
		data:
		{
			action: "testaction",
			id: $('.eqLogicAttr[data-l1key=id]').value(),
			id_action: id_action
		},
		dataType: 'json',
		error: function (request, status, error) {
		},
		success: function (data) {
		}
	});
});

// bouton ajout d'une action
$('.bt_addAction').off('click').on('click', function () {
	$('#table_actions').append('</center><legend><i class="fa fa-cogs" style="font-size : 2em;color:#a15bf7;"></i><span style="color:#a15bf7"> {{Nouvelle action}}</span></legend><center>');
	var typeControl = '';
	var selectElementByName = document.querySelector('select[name="typecontrole"]');
	if (selectElementByName) {
		typeControl = selectElementByName.value;
	}
	var typeAction = '';
	var selectElementByName = document.querySelector('select[name="typeAction"]');
	if (selectElementByName) {
		typeAction = selectElementByName.value;
	}
	addAction(typeControl, typeAction, {}, "watchdogAction", "Nouvelle");
});




function addAction(typeControl, typeAction, _action, type, id_action = "") {

	if (!isset(_action)) {
		_action = {};
	}
	if (!isset(_action.options)) {
		_action.options = {};
	}

	switch (type) {
		case 'True':
			var couleur = 'success';
			break;
		case 'False':
			var couleur = 'warning';
			break;
		default:
			var couleur = 'info';
	}

	var div = '<div class="watchdogAction  alert-' + couleur + '">';
	div += '<div class="form-group ">';
	div += '<div class="col-sm-2">';
	div += '<input type="checkbox" style="margin-top : 11px;margin-right : 5px;margin-left : 5px;" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}" />';
	div += '<input type="checkbox" style="margin-top : 11px;margin-right : 5px;" class="expressionAttr" data-l1key="options" data-l2key="background" title="Cocher pour que la commande s\'exécute en parallèle des autres actions" />';
	div += '<input type="checkbox" class="expressionAttr tooltipstered" style="margin-top : 11px;margin-right : 5px;" data-l1key="options" data-l2key="log" checked title="Cocher pour que l\'action soit enregistrée dans le fichier log \'watchdog_actions\'" />';
	if (typeControl == '' && (type == 'True' || type == 'False') && (type == typeAction || typeAction == 'ALL')) {
		div += '<input type="checkbox" style="margin-top : 11px;margin-right : 5px;margin-left : 5px;" class="expressionAttr" data-l1key="options" data-l2key="seulement_si_changement"  title="{{Exécuter l\'action sune seule fois}}" />';
	}
	div += '<select class="expressionAttr form-control input-sm" data-l1key="actionType" style="margin-bottom: 10px;width:calc(100% - 100px);display:inline-block">';
	div += '<option style="background: #d9edf7; color: #00000;" value="Avant">{{Avant le(s) contrôle(s)}}</option>';
	var msg = ((typeAction == 'True' || typeAction == 'ALL') ? 'Est égal à True' : 'Passe à True')
	div += '<option style="background: #dff0d8; color: #00000;" value="True">' + msg + '</option>';
	var msg = ((typeAction == 'False' || typeAction == 'ALL') ? 'est égal à False' : 'Passe à False')
	div += '<option style="background: #dff0d8; color: #00000;" value="False">' + msg + '</option>';
	div += '<option style="background: #d9edf7; color: #00000;" value="Apres">{{Après le(s) contrôle(s)}}</option>';
	div += '</select>';
	div += '</div>';
	div += '<div class="col-sm-5" style="margin-top : 5px;">';
	div += '<div class="input-group" >';
	div += '<span class="input-group-btn">';
	div += '<a class="btn removeAction btn-sm" data-type="removeAction"><i class="fa fa-minus-circle"></i></a>';
	div += '</span>';
	div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="watchdogAction" />';
	div += '<span class="input-group-btn">';
	div += '<a class="btn btn-primary btn-sm listAction" data-type="listAction" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
	div += '<a class="btn btn-primary btn-sm listCmdAction" data-type="listCmdAction"  title="{{Sélectionner la commande}}" ><i class="fa fa-list-alt"></i></a>';
	div += '</span>';
	div += '</div>';

	// La commande c'est : _action.cmd
	if (is_numeric(id_action)) {
		div += '<a class="btn btn-primary btn-xs cmdAction" data-action="testaction" id_action=' + id_action + '  title="{{Tester la commande}}" ><i class="fa fa-rss"></i> Tester</a>';
	}

	div += '</div>';
	var actionOption_id = jeedomUtils.uniqId();

	div += '<div style="margin-top : 5px;margin-bottom : 5px; margin-left : -5px;" class="col-sm-5  actionOptions" id="' + actionOption_id + '">';
	div += '</div>';
	div += '</div>';

	$('#table_actions').append(div);
	$('#table_actions .watchdogAction:last').setValues(_action, '.expressionAttr');

	actionOptions.push({
		expression: init(_action.cmd, ''),
		options: _action.options,
		id: actionOption_id
	});

}

// fonction javascript appelée avant le save
function saveEqLogic(_eqLogic) {

	if (!isset(_eqLogic.configuration)) {
		_eqLogic.configuration = {};
	}
	//Sauvegarde des commandes , façon scénario, avec ses options (pour les commandes qui ont un titre ou autres options)
	_eqLogic.configuration.watchdogAction = $('#table_actions .watchdogAction').getValues('.expressionAttr');
	return _eqLogic;
}


// fontion appelée au chargement de l eqlogic
function printEqLogic(_eqLogic) {

	typeControl = _eqLogic.configuration.typeControl;

	// ligne des titres
	$('#table_controlesTitre').empty();

	dernierLancement = "Aucun";
	if (typeof _eqLogic.configuration.dernierLancement !== 'undefined') {
		dernierLancement = _eqLogic.configuration.dernierLancement.replace('CRON ', '');
		dernierLancement = dernierLancement.replace('SAVE ', '');
		dernierLancement = dernierLancement.replace('REFRESH ', '');
	}

	avantDernierLancement = "Aucun";
	if (typeof _eqLogic.configuration.avantDernierLancement !== 'undefined') {
		avantDernierLancement = _eqLogic.configuration.avantDernierLancement.replace('CRON ', '');
		avantDernierLancement = avantDernierLancement.replace('SAVE ', '');
		avantDernierLancement = avantDernierLancement.replace('REFRESH ', '');
	}

	$titreCondition = ' <tr><th style="width: 40px;">{{Inactif}}</th><th style="width: 160px;">{{  Nom}}</th><th>{{  Contrôle}}</th><th class="text-center" style="width:200px;">Avant-dernier Résultat<br><small>' + avantDernierLancement + '</small></th><th class="text-center" style="width:200px;">Dernier Résultat<br><small>' + dernierLancement + '</small></th><th style="width:120px;"></th></tr>';

	$('#table_controlesTitre').append($titreCondition);

	// On remplit la table_actions
	actionOptions = [];
	var typeAction = _eqLogic.configuration.typeAction;
	var LancementActionsAvantApres = _eqLogic.configuration.LancementActionsAvantApres;
	$('#table_actions').empty();
	if (isset(_eqLogic.configuration.watchdogAction)) {

		//   actions qui se déclencheront avant de lancer les controles
		var msg_LancementActionsAvantApres = (LancementActionsAvantApres != 'ALL' ? 'l\'ENSEMBLE des contrôles' : 'CHAQUE contrôle')
		$('#table_actions').append('<legend><i class="fa fa-cogs" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Actions à exécuter AVANT de lancer }}' + msg_LancementActionsAvantApres + '</span>'
			+ '<sup><i style="color:#a15bf7" class="fas fa-question-circle tooltips" title="{{Suivant la configuration du plugin, ces actions ne sont lancées qu\'une seule fois ou alors avant le lancement de chaque contrôle.}}"></i></sup>'
			+ '</legend>');
		for (var i in _eqLogic.configuration.watchdogAction) {
			if (_eqLogic.configuration.watchdogAction[i].actionType == "Avant")
				addAction(_eqLogic.configuration.typeControl, _eqLogic.configuration.typeAction, _eqLogic.configuration.watchdogAction[i], "Avant", i)
		}

		// actions qui se déclencheront quand on passera de false à true
		var msg1 = (typeControl != '' ? 'le résultat global' : 'le résultat du contrôle')

		var msg2 = (typeAction == 'True' || (typeAction == 'ALL') ? 'est égal à True' : 'passe à True')
		$('#table_actions').append('<legend><i class="far fa-thumbs-up" style="font-size : 2em;color:#a15bf7;"></i><span style="color:#a15bf7"> Actions à exécuter quand ' + msg1 + ' ' + msg2 + '</span></legend>');

		for (var i in _eqLogic.configuration.watchdogAction) {
			if (_eqLogic.configuration.watchdogAction[i].actionType == "True") {
				addAction(_eqLogic.configuration.typeControl, _eqLogic.configuration.typeAction, _eqLogic.configuration.watchdogAction[i], "True", i)
			}
		}
		//   actions qui se déclencheront quand on passera de true à false
		var msg2 = (typeAction == 'False' || (typeAction == 'ALL') ? 'est égal à False' : 'passe à False')
		$('#table_actions').append('<legend><i class="far fa-thumbs-up" style="font-size : 2em;color:#a15bf7;"></i><span style="color:#a15bf7"> Actions à exécuter quand ' + msg1 + ' ' + msg2 + '</span></legend>');

		for (var i in _eqLogic.configuration.watchdogAction) {
			if (_eqLogic.configuration.watchdogAction[i].actionType == "False")
				addAction(_eqLogic.configuration.typeControl, _eqLogic.configuration.typeAction, _eqLogic.configuration.watchdogAction[i], "False", i)
		}

		//   actions qui se déclencheront après les controles
		$('#table_actions').append('<legend><i class="fa fa-cogs" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Actions à exécuter APRES avoir lancé }}' + msg_LancementActionsAvantApres + '</span>'
			+ '<sup><i style="color:#a15bf7" class="fas fa-question-circle tooltips" title="{{Suivant la configuration du plugin, ces actions ne sont lancées qu\'une seule fois ou alors après le lancement de chaque contrôle.}}"></i></sup>'
			+ '</legend>');
		for (var i in _eqLogic.configuration.watchdogAction) {
			if (_eqLogic.configuration.watchdogAction[i].actionType == "Apres")
				addAction(_eqLogic.configuration.typeControl, _eqLogic.configuration.typeAction, _eqLogic.configuration.watchdogAction[i], "Apres", i)
		}

		// ajoutes les options des commandes
		jeedom.cmd.displayActionsOption({
			params: actionOptions,
			async: false,
			error: function (error) {
				$('#div_alert').showAlert({ message: error.message, level: 'danger' });
			},
			success: function (data) {
				for (var i in data) {
					$('#' + data[i].id).append(data[i].html.html);
				}
				taAutosize();
			}
		});
	}
}

// --Boutons pour gérer les boutons associés aux commandes

$("#table_actions").off('click').on('click', ".listAction,.listCmdAction,.removeAction", function () {

	var type = $(this).attr('data-type');
	var el = $(this).closest('.watchdogAction').find('.expressionAttr[data-l1key=cmd]');
	if (type == 'listAction') {
		jeedom.getSelectActionModal({}, function (result) {
			el.value(result.human);
			jeedom.cmd.displayActionOption(el.value(), '', function (html) {
				el.closest('.watchdogAction').find('.actionOptions').html(html);
				taAutosize();
			});
		});
	}


	if (type == 'listCmdAction') {
		jeedom.cmd.getSelectModal({ cmd: { type: 'action' } }, function (result) {
			el.value(result.human);
			jeedom.cmd.displayActionOption(el.value(), '', function (html) {
				el.closest('.watchdogAction').find('.actionOptions').html(html);
				jeedomUtils.taAutosize();
			});
		});
	}

	// suppression d'une action
	if (type == 'removeAction') {
		var type = $(this).attr('data-type');
		$(this).closest('.watchdogAction').remove();
	}

});


//-------------------------------------
// Assistant pour remplir facilement le test à faire sur un equipement
// et lancer le test sur l'expression
//-------------------------------------
$("#table_controles").off('click').on('click', ".generer_expression,.testexpression,.macro,.historique,.configure", function () {

	var type = $(this).attr('data-type');
	if (type == 'testexpression') {
		var el = $(this).closest('.info').find('.cmdAttr[data-l1key=configuration][data-l2key=expression]');
		var expression = el.val();

		var el = $(this).closest('.info').find('.cmdAttr[data-l1key=configuration][data-l2key=controle]');
		var condition = el.val();
		var el = $(this).closest('.info').find('.cmdAttr[data-l1key=id]');
		var id = el.val();

		$("#resultatAjax").val('');
		$.ajax({
			type: "POST",
			url: "plugins/watchdog/core/ajax/watchdog.ajax.php",
			data:
			{
				action: "test_expression",
				condition: condition,
				id: id
			},
			async: false,
			dataType: 'json',
			error: function (request, status, error) {
			},
			success: function (data) {
				$("#resultatAjax").val(data.result);
			}
		});

		expression = $("#resultatAjax").val();
		jeeDialog.dialog({
			title: "{{Testeur d'expression}}", // Titre de la modale
			contentUrl: 'index.php?v=d&plugin=watchdog&modal=expression.test&expression=' + encodeURIComponent(expression)
		});
	}

	else if (type == 'historique') {
		var el_controle = $(this).closest('.info').find('.cmdAttr[data-l1key=configuration][data-l2key=controle]');
		var condition = el_controle.val();
		var el_id = $(this).closest('.info').find('.cmdAttr[data-l1key=id]');
		var id = el_id.val();
		jeeDialog.dialog({
			title: "{{Historique}}", // Titre de la modale
			contentUrl: 'index.php?v=d&modal=cmd.history&id=' + id
		});
	}
	else if (type == 'configure') {
		var el_controle = $(this).closest('.info').find('.cmdAttr[data-l1key=configuration][data-l2key=controle]');
		var condition = el_controle.val();
		var el_id = $(this).closest('.info').find('.cmdAttr[data-l1key=id]');
		var id = el_id.val();
		jeeDialog.dialog({
			title: "{{Configure}}", // Titre de la modale
			contentUrl: 'index.php?v=d&modal=cmd.configure&cmd_id=' + id
		});
	}
	else if (type == 'macro') {
		var el_macro = $('.eqLogicAttr[data-l1key=configuration][data-l2key=macro]');
		var macro = el_macro.val();
		if (macro.trim() != '') {
			jeeDialog.alert('{{La macro est déjà définie (mettre la macro à blanc pour la régénérer).}}')
		}
		else {
			var el_controle = $(this).closest('.info').find('.cmdAttr[data-l1key=configuration][data-l2key=controle]');
			var condition = el_controle.val();
			var el_id = $(this).closest('.info').find('.cmdAttr[data-l1key=id]');
			var id = el_id.val();

			$("#resultatAjax").val('');
			$.ajax({
				type: "POST",
				url: "plugins/watchdog/core/ajax/watchdog.ajax.php",
				data:
				{
					action: "cherche_equipement_dans_expression",
					condition: condition,
					id: id
				},
				async: false,
				dataType: 'json',
				error: function (request, status, error) {
				},
				success: function (data) {
					$("#resultatAjax").val(data.result);
				}
			});

			var equip = $("#resultatAjax").val();
			console.log('equip: ' + equip);
			if (equip.trim() != '') {
				if (condition.includes(equip)) {
					// crée la macro en remplacant l equipement trouvé par _arg1_
					var regex = new RegExp(echapperRegex(equip), "gi");
					var macro = condition.replace(regex, "_arg1_");
					regex = new RegExp(echapperRegex(equip), "gi");
					macro = condition.replace(regex, "_arg1_");  // remplace l'équipement ex #[maison][Network1]# -> _arg1_
					macro = macro.replace(equip.slice(0, -1), "#_arg1_"); // remplace les commandes ex #[maison][Network1][Statut]# -> #_arg1_[Statut]#
					el_macro.value(macro);

					// remplace la condition en utilisant la macro
					el_controle.value('_macro_(' + equip + ')');
				}
				else {
					// equipement non trouvé dans la condition --> on va chercher à remplacer la première commande
					$("#resultatAjax").val('');
					$.ajax({
						type: "POST",
						url: "plugins/watchdog/core/ajax/watchdog.ajax.php",
						data:
						{
							action: "cherche_commande_dans_expression",
							condition: condition,
							id: id
						},
						async: false,
						dataType: 'json',
						error: function (request, status, error) {
						},
						success: function (data) {
							$("#resultatAjax").val(data.result);
						}
					});
					var commande = $("#resultatAjax").val();
					if (condition.includes(commande)) {
						// crée la macro en remplacant l commandeement trouvé par _arg1_
						var regex = new RegExp(echapperRegex(commande), "gi");
						var macro = condition.replace(regex, "_arg1_");
						regex = new RegExp(echapperRegex(commande), "gi");
						macro = condition.replace(regex, "_arg1_");
						el_macro.value(macro);
						// remplace la condition en utilisant la macro
						el_controle.value('_macro_(' + commande + ')');
					}

				}
			}
		}
	}
	else if (type == 'generer_expression') {
		var chaineExpressionTest = "";

		var eldebut = $(this);
		var expression = $(this).closest('expression');
		var el = $(this).closest('.info').find('.cmdAttr[data-l1key=configuration][data-l2key=controle]');
		var el_name = $(this).closest('.info').find('.cmdAttr[data-l1key=name]');

		tempo1 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo1]').value() + " secondes";
		if (tempo1 == " secondes") tempo1 = 'à configurer';
		tempo2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo2]').value() + " secondes";
		if (tempo2 == " secondes") tempo2 = 'à configurer';
		tempo3 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo3]').value() + " secondes";
		if (tempo3 == " secondes") tempo3 = 'à configurer';

		message = '<form class="form-horizontal" onsubmit="return false;">';
		message += '	<div class="panel-group" id="accordion">';

		message += '		<div class="panel panel-default">';
		message += '			<div class="panel-heading">';
		message += '				<h4 class="panel-title"><label for="r11" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value=2 name="choix" checked="checked" required/>';
		message += '				Un équipement <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"></a></label></h4>';
		message += '			</div>';
		message += '		</div>';


		message += '		<div class="panel panel-default">';
		message += '			<div class="panel-heading">';
		message += '				<h4 class=panel-title><label for="r12" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r12" value=1 name="choix" required/>';
		message += '				La commande d\'un équipement <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"></a></label></h4>';
		message += '			</div>';
		message += '		</div>';


		message += '		<div class="panel panel-default">';
		message += '			<div class="panel-heading">';
		message += '				<h4 class=panel-title><label for="r13" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r13" value=3 name="choix" required/>';
		message += '				La config IP de Jeedom<a data-toggle="collapse" data-parent="#accordion" href="#collapseTree"></a></label></h4>';
		message += '			</div>';
		message += '		</div>';



		message += '<script>';
		message += '	$("#r11").on("click", function(){  $(this).parent().find("a").trigger("click")});';
		message += '	$("#r12").on("click", function(){  $(this).parent().find("a").trigger("click")});';
		message += '</script>';
		message += '		</div>';
		message += '			</div>';
		message += '</form> ';

		// Lancement de l'écran numéro 1/3	
		bootbox.dialog({
			title: "{{Que voulez-vous tester ?}}",
			message: message,
			buttons: {
				"Annuler": {
					className: "btn-default",
					callback: function () {
					}
				},
				success: {
					label: "Valider",
					className: "btn-primary",
					callback: function () {

						if ($('#r11').value() == "1") {
							//------------L'utilisateur demande a choisir l'équipement --
							// Lancement de l'écran numéro 2/3	
							jeedom.eqLogic.getSelectModal({}, function (result) {
								var date = new Date();

								// utilisé pour donner le nom au controle
								var controlname = extractName(result.human);

								//vient de desktop/js/scenario.js
								// Texte de l'écran numéro 3/3	
								tempo1 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo1]').value() + " secondes";
								if (tempo1 == " secondes") tempo1 = 'à configurer';
								tempo2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo2]').value() + " secondes";
								if (tempo2 == " secondes") tempo2 = 'à configurer';
								tempo3 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo3]').value() + " secondes";
								if (tempo3 == " secondes") tempo3 = 'à configurer';
								message = '<form class="form-horizontal" onsubmit="return false;">  <div class="panel-group" id="accordion">    ';
								message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class="panel-title">            <label for="r11" style="width: 100%;">              <input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value=2 name="choix" checked="checked" required />';
								message += ' Tester la dernière communication avec l\'équipement';
								message += '<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"></a>            </label>        </h4>      </div>      <div id="collapseOne" class="panel-collapse collapse in">';

								message += '<div class="panel-body">          <p>' +
									'Tester si le délai depuis la dernière communication avec <br><b>' + result.human + '</b> est supérieur à :' +
									'            <div class="col-xs-7">' +
									'              <select class="conditionAttr form-control" data-l1key="choixtempo">' +
									'                       <option value="1">Tempo1 (' + tempo1 + ')</option>' +
									'                       <option value="2">Tempo2 (' + tempo2 + ')</option>' +
									'                       <option value="3">Tempo3 (' + tempo3 + ')</option>' +
									'                       </select>' +
									'                    </div>' +
									'</p>        </div>      </div>    </div>';

								message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class=panel-title>            <label for="r12" style="width: 100%;">              <input type="radio" id="r12" value=3 name="choix" required />';
								message += " Tester que cet équipement est actif";
								message += '</p>        </div>      </div>';

								message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class=panel-title>            <label for="r13" style="width: 100%;">              <input type="radio" id="r13" value=4 name="choix" required />';
								message += " Utiliser la macro sur cet équipement";
								message += '</p>        </div>      </div>';

								message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class=panel-title>            <label for="r14" style="width: 100%;">              <input type="radio" id="r14" value=5 name="choix" required />';
								message += " Insérer le nom de cet équipement";
								message += '</p>        </div>      </div>';

								message += '<div class="form-group"> ' +
									'             <div class="col-xs-12">' +
									'  <input type="checkbox" checked="true" style="margin-top : 11px;margin-right : 10px;" class="conditionAttr" data-l1key="configuration" data-l2key="assistName" > Mettre <b>' + controlname + '</b> comme nom au contrôle' +
									'       </div>' +
									'</div><hr>';
								message += '<div class="form-group"> ' +
									'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
									'             <div class="col-xs-3">' +
									'                <select class="conditionAttr form-control" data-l1key="next">' +
									'                  <option value="">{{rien}}</option>' +
									'                  <option value="ET">{{et}}</option>' +
									'                  <option value="OU">{{ou}}</option>' +
									'            </select>' +
									'       </div>' +
									'</div>';
								message += '</div> </div>';
								message += '</form> ';

								// Lancement de l'écran numéro 3/3	

								bootbox.dialog({
									title: "{{Que voulez-vous faire ?}}",
									message: message,
									buttons: {
										"Annuler": {
											className: "btn-default",
											callback: function () {
											}
										},
										success: {
											label: "Valider",
											className: "btn-primary",
											callback: function () {

												var condition = result.human;

												if ($('#r11').value() == '1') {    // Equipement : tester la dernière communication
													//On regarde quel est le tempo sélectionné 
													switch ($('.conditionAttr[data-l1key=choixtempo]').value()) {
														case '2':
															choixtempo = "_tempo2_";
															break;
														case '3':
															choixtempo = "_tempo3_";
															break;
														default:
															choixtempo = "_tempo1_";
													}
													condition = '(#timestamp# - strtotime(lastCommunication(' + condition + "))) > " + choixtempo;
												}

												if ($('#r12').value() == '1') {  // Equipement : tester si l'équipement est actif
													condition = 'eqEnable(' + condition + ") == 1";
												}

												if ($('#r13').value() == '1') {  // // Equipement : macro
													condition = '_macro_(' + condition + ")";
												}

												if ($('#r14').value() == '1') {  // insère le nom de l'équipement
													condition = condition.trim();
												}

												// Ajout du ET / OU à la condition
												condition += ' ' + $('.conditionAttr[data-l1key=next]').value() + ' ';

												valeurprecedente = chaineExpressionTest
												condition = valeurprecedente + condition;
												chaineExpressionTest = condition;

												if ($('.conditionAttr[data-l1key=next]').value() != '') {
													eldebut.click();
													//remplit la Condition(); //on reboucle pour une autre condition
												}
												else {
													el.atCaret('insert', condition);
													// Si la case à cocher qui permet de mettre automatiquement le nom de l'équipement est cochée
													if ($('.conditionAttr[data-l1key=configuration][data-l2key=assistName]').value() == '1')
														el_name.value(controlname);

													chaineExpressionTest = "";

												}
											}
										},
									}
								}); // fin de bootbox.dialog(
							});	// fin de jeedom.cmd.getSelectModal			

						}

						else if ($('#r12').value() == "1") {

							//------------L'utilisateur demande a choisir la commande de l'équipement --
							// Lancement de l'écran numéro 2/3	
							jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {
								var date = new Date();

								// utilisé pour donner le nom au controle
								var controlname = extractName(result.human);

								//vient de desktop/js/scenario.js
								// Texte de l'écran numéro 3/3	

								message = '<form class="form-horizontal" onsubmit="return false;">  <div class="panel-group" id="accordion">    ';
								message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class="panel-title">            <label for="r11" style="width: 100%;">              <input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value=2 name="choix" checked="checked" required />';
								message += ' Tester un changement d\'état de la commande';
								message += '<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"></a>            </label>        </h4>      </div>      <div id="collapseOne" class="panel-collapse collapse in">        <div class="panel-body">          <p>';
								message += 'Tester si <b>' + result.human + ' </b>est' +
									'            <div class="col-xs-7">' +
									'                 <input class="conditionAttr" data-l1key="operator" value="==" style="display : none;" />' +
									'                  <select class="conditionAttr form-control" data-l1key="operande">' +
									'                       <option value="1">{{Ouvert}}</option>' +
									'                       <option value="0">{{Fermé}}</option>' +
									'                       <option value="1">{{Allumé}}</option>' +
									'                       <option value="0">{{Eteint}}</option>' +
									'                       <option value="1">{{Déclenché}}</option>' +
									'                       <option value="0">{{Au repos}}</option>' +
									'                       </select>' +
									'                    </div>' +
									'                 </div>';
								message += '</p>        </div>      </div>   ';
								tempo1 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo1]').value() + " secondes";
								if (tempo1 == " secondes") tempo1 = 'à configurer';
								tempo2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo2]').value() + " secondes";
								if (tempo2 == " secondes") tempo2 = 'à configurer';
								tempo3 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo3]').value() + " secondes";
								if (tempo3 == " secondes") tempo3 = 'à configurer';
								message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class=panel-title>            <label for="r12" style="width: 100%;">              <input type="radio" id="r12" value=1 name="choix" required />';
								message += " Tester la date de la dernière collecte de cette commande";
								message += '<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"></a>            </label>        </h4>      </div>      <div id="collapseTwo" class="panel-collapse collapse">        <div class="panel-body">          <p>' +
									'Tester si le délai depuis la dernière mise à jour de <br><b>' + result.human + '</b> est supérieur à :' +
									'            <div class="col-xs-7">' +
									'              <select class="conditionAttr form-control" data-l1key="choixtempo">' +
									'                       <option value="1">Tempo1 (' + tempo1 + ')</option>' +
									'                       <option value="2">Tempo2 (' + tempo2 + ')</option>' +
									'                       <option value="3">Tempo3 (' + tempo3 + ')</option>' +
									'                       </select>' +
									'                    </div>' +
									'</p>        </div>      </div>    </div>';

								message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class=panel-title>            <label for="r13" style="width: 100%;">              <input type="radio" id="r13" value=3 name="choix" required />';
								message += " Utiliser la macro sur cette commande";
								message += '</p>        </div>      </div>';

								message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class=panel-title>            <label for="r14" style="width: 100%;">              <input type="radio" id="r14" value=4 name="choix" required />';
								message += " Insérer le nom de cette commande";
								message += '</p>        </div>      </div>';


								// instructions script servent à afficher les choix ouvert/fermé, ... t Tempo quand on clique sur l'option
								message += '<script>$("#r11").on("click", function(){  $(this).parent().find("a").trigger("click")});$("#r12").on("click", function(){  $(this).parent().find("a").trigger("click")});$("#r13").on("click", function(){  $(this).parent().find("a").trigger("click")})</script>';

								message += '<div class="form-group"> ' +
									'             <div class="col-xs-12">' +
									'  <input type="checkbox" checked="true" style="margin-top : 11px;margin-right : 10px;" class="conditionAttr" data-l1key="configuration" data-l2key="assistName" > Mettre <b>' + controlname + '</b> comme nom au contrôle' +
									'       </div>' +
									'</div><hr>';
								message += '<div class="form-group"> ' +
									'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
									'             <div class="col-xs-3">' +
									'                <select class="conditionAttr form-control" data-l1key="next">' +
									'                  <option value="">{{rien}}</option>' +
									'                  <option value="ET">{{et}}</option>' +
									'                  <option value="OU">{{ou}}</option>' +
									'            </select>' +
									'       </div>' +
									'</div>';
								message += '</div> </div>';
								message += '</form> ';

								// Lancement de l'écran numéro 3/3	

								bootbox.dialog({
									title: "{{Que voulez-vous faire ?}}",
									message: message,
									buttons: {
										"Annuler": {
											className: "btn-default",
											callback: function () {
											}
										},
										success: {
											label: "Valider",
											className: "btn-primary",
											callback: function () {

												var condition = result.human;
												// BD pas utilisé	var test = result.cmd.subType;

												if ($('#r11').value() == '1') {     // Commande : tester le changement d'éatat dernière communication
													condition += ' ' + $('.conditionAttr[data-l1key=operator]').value();
													if (result.cmd.subType == 'string') {
														if ($('.conditionAttr[data-l1key=operator]').value() == 'matches') {
															condition += ' "/' + $('.conditionAttr[data-l1key=operande]').value() + '/"';
														} else {
															condition += ' "' + $('.conditionAttr[data-l1key=operande]').value() + '"';
														}
													} else {
														condition += ' ' + $('.conditionAttr[data-l1key=operande]').value();
													}
												}

												if ($('#r12').value() == '1') {    // Commande : tester la dernière communication
													switch ($('.conditionAttr[data-l1key=choixtempo]').value()) {
														case '2':
															choixtempo = "_tempo2_";
															break;
														case '3':
															choixtempo = "_tempo3_";
															break;
														default:
															choixtempo = "_tempo1_";
													}

													// On est dans le cas : Tester le délai depuis la dernière mise à jour de xxx ou aucune case
													condition = '(age(' + condition + ") > " + choixtempo + ') ou (age(' + condition + ') < 0)';
												}

												if ($('#r13').value() == '1') {   // Commande : macro
													// générer macro
													condition = '_macro_(' + condition + ")";
												}

												if ($('#r14').value() == '1') {   // Insére le nom de la commande
													condition = condition.trim();
												}

												condition += ' ' + $('.conditionAttr[data-l1key=next]').value() + ' ';

												valeurprecedente = chaineExpressionTest
												condition = valeurprecedente + condition;
												chaineExpressionTest = condition;

												if ($('.conditionAttr[data-l1key=next]').value() != '') {
													eldebut.click();
												}
												else {
													el.atCaret('insert', condition);
													// Si la case à cocher qui permet de mettre automatiquement le nom de l'équipement est cochée
													if ($('.conditionAttr[data-l1key=configuration][data-l2key=assistName]').value() == '1')
														el_name.value(controlname);

													chaineExpressionTest = "";

												}
											}
										},
									}
								}); // fin de bootbox.dialog(
							});	// fin de jeedom.cmd.getSelectModal
						}
						else {
							//------------L'utilisateur demande a choisir le controle de l'IP --
							var currentLocationhostname = window.location.hostname;
							el.atCaret('insert', '#internalAddr# = "' + currentLocationhostname + '"');
														console.log("3");
						}
					}
				},
			}
		});
	}

});


// Fonction pour échapper les caractères spéciaux d'une regex
function echapperRegex(chaine) {
	// Les caractères spéciaux à échapper
	return chaine.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function extractName(chaine) {
	const regex = /\[([^\]]+)\]#/;
	const match = chaine.match(regex);
	return match ? match[1] : null;
}