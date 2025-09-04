/*  Last Modified : 2025/09/04 16:20:42
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

	// met le titre du bouton d aide sur la log specifique
	const logspecifique_tooltip = document.getElementById('logspecifique_tooltip');
	if (logspecifique_tooltip)
		logspecifique_tooltip.title = "{{Si cette option est activée, les traces de ce watchdog seront enregistrées dans watchdog_ suivi de l'Id de l'eqLogic, ici watchdog_}}"+_eqLogic.id+". "+"{{Vous pouvez consulter directement la log en cliquant sur le bouton correspondant.}}";


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

		// on passe par une script externe pour plus de lisibilité du code		
		generer_expression.call($(this));

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