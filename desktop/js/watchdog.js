
/* This file is part of Jeedom.
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
var chaineExpressionTest="";

$("#table_controles").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_actions").sortable({axis: "y", cursor: "move", items: ".watchdogAction", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


$(document).ready(function(){ //lancé quand toute la page est chargée
	$(".bt_masqueCalculs").hide();
	});

//console.log("--------- Lancement ---------");


// BOUTONS -------------

 $("body").delegate('.bt_removeAction', 'click', function () {
//$("bt_removeAction").off('click').on("click", function () {
//console.log("--------- bt_removeAction");
    var type = $(this).attr('data-type');
    $(this).closest('.' + type).remove();
});

$('.bt_addControle').off('click').on('click',function(){
//console.log ("Valeur de data-type:"+$(this).attr('data-type'));
  //addCmdToTable({}, $(this).attr('data-type'));
  addCmdToTable({}, 'info');
});


$('#div_pageContainer').on( 'click','.cmdAction[data-action=testaction]',function (event) {
var id_action = $(this).attr('id_action');

  $.ajax({
      type: "POST", 
      url: "plugins/watchdog/core/ajax/watchdog.ajax.php", 
      data:
      {
          action: "testaction",
		  //albums: json_encode(albums),
		  id: $('.eqLogicAttr[data-l1key=id]').value(),
		  id_action: id_action
      },
      dataType: 'json',
      error: function (request, status, error)
      {
          //window.location.reload();
          //handleAjaxError(request, status, error);
      },
      success: function (data)
      { 
          
		  /*if (data.state != 'ok') {
              $('#div_alert').showAlert({message: data.result, level: 'danger'});
              return;
          }*/
          //window.location.reload();
      }
  });






});

$('.listCmdInfoVacances').off('click').on('click',function(){
    var type = $(this).attr('data-type');
 // var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
	var el = $(this).closest('.' + type).find('.eqLogicAttr[data-l1key=configuration][data-l2key=modevacances]');
//console.log("--------- type");
//console.dir(type);
//console.log("--------- el");
//console.dir(el);
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
        el.value(result.human);
        jeedom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.' + type).find('.actionOptions').html(html);
            taAutosize();
        });
    });
	
	//el.value("coucou !!!!!!!");
//console.log("--------- coucou");
	
	
});
  
$('.bt_afficheCalculs').off('click').on('click',function(){
  $(".calcul").show();
  $(".bt_masqueCalculs").show();
  $(".bt_afficheCalculs").hide();
  });
  
$('.bt_masqueCalculs').off('click').on('click',function(){
  $(".calcul").hide();
  $(".bt_masqueCalculs").hide();
  $(".bt_afficheCalculs").show();
});
$('.bt_addAction').off('click').on('click',function(){
//console.log("--------- bt_addAction");
$('#table_actions').append('</center><legend><i class="fa fa-cogs" style="font-size : 2em;color:#a15bf7;"></i><span style="color:#a15bf7"> {{Nouvelle action}}</span></legend><center>');
  addAction({}, "watchdogAction", "Nouvelle");
  //addLog();
});

$('#bt_cronGenerator').off('click').on('click',function(){
    jeedom.getCronSelectModal({},function (result) {
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=autorefresh]').value(result.value);
    });
});


$('.bt_plugin_view_log').on('click',function(){
   $('#md_modal').dialog({title: "{{Log de }}"+$('.eqLogicAttr[data-l1key=name]').value()});
   $("#md_modal").load('index.php?v=d&modal=log.display&log=watchdog_'+$('.eqLogicAttr[data-l1key=id]').value()).dialog('open');

});

function addAction(_action, type, id_action="") {
	//console.log("--------- _action :");
	//console.dir(_action);
/* CONTENU DE _action

{
  "options": {
    "enable": "1",
    "background": "0",
    "title": "#title#",
    "message": "#controlname# est OK"
  },
  "actionType": "True",
  "cmd": "#[Equipement internes][Pushover Lionel][IconeVerte]#"
}

{*/




    if (!isset(_action)) {
        _action = {};
    }
    if (!isset(_action.options)) {
        _action.options = {};
    }
	
	//console.log("--------- id_action>>>>>>>>>>"+id_action);
	

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

    var input = '';
    var div = '<div class="watchdogAction  alert-'+couleur+'">';
	div += '<div class="form-group ">';
    div += '<div class="col-sm-2">';
	    div += '<input type="checkbox" style="margin-top : 11px;margin-right : 5px;margin-left : 5px;" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}" />';
    div += '<input type="checkbox" style="margin-top : 11px;margin-right : 5px;" class="expressionAttr" data-l1key="options" data-l2key="background" title="Cocher pour que la commande s\'exécute en parallèle des autres actions" />';
    div += '<input type="checkbox" class="expressionAttr tooltipstered" style="margin-top : 11px;margin-right : 5px;" data-l1key="options" data-l2key="log" checked title="Cocher pour que l\'action soit enregistrée dans le log du Watchdog" />';
    div += '<select class="expressionAttr form-control input-sm" data-l1key="actionType" style="margin-bottom: 10px;width:calc(100% - 70px);display:inline-block">';
    div += '<option style="background: #dff0d8; color: #00000;" value="True">{{Passe à True}}</option>';
    div += '<option style="background: #f2dede; color: #00000;" value="False">{{Passe à False}}</option>';
    div += '<option style="background: #d9edf7; color: #00000;" value="Avant">{{Avant le contrôle}}</option>';
    div += '</select>';
    div += '</div>';
    div += '<div class="col-sm-5" style="margin-top : 5px;">';
    div += '<div class="input-group" >';
    div += '<span class="input-group-btn">';

    div += '<a class="btn btn-danger bt_removeAction btn-sm" data-type="watchdogAction"><i class="fa fa-minus-circle"></i></a>';
    div += '</span>';
    div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="watchdogAction" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-primary btn-sm listAction" data-type="watchdogAction" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
    div += '<a class="btn btn-primary btn-sm listCmdAction" data-type="watchdogAction"><i class="fa fa-list-alt"></i></a>';
    div += '</span>';
    div += '</div>';
	
	// La commande c'est : _action.cmd
    if (is_numeric(id_action)) {
		div += '<a class="btn btn-primary btn-xs cmdAction" data-action="testaction" id_action="'+id_action+' title="'+id_action+' controlename="'+id_action+'"><i class="fa fa-rss"></i> Tester</a>';
		}
	//console.log("--------- testaction>>>>>>>>>>"+JSON.stringify(_action));
    div += '</div>';
    var actionOption_id = uniqId();
	//console.log(uniqId());
    div += '<div style="margin-top : 5px;margin-bottom : 5px; margin-left : -5px;" class="col-sm-5  actionOptions" id="'+actionOption_id+'">';
    div += '</div>';
    div += '</div>';

        $('#table_actions').append(div);
        $('#table_actions .watchdogAction:last').setValues(_action, '.expressionAttr');

    actionOptions.push({
        expression : init(_action.cmd, ''),
        options : _action.options,
        id : actionOption_id
    });

}

/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.watchdog
 */
function addCmdToTable(_cmd, type) {
	
	//On ignore resultatglobal
	if ((init(_cmd.logicalId) == 'resultatglobal')) return;
  
 // console.log("coucou1");
//  console.log(_eqLogic.configuration.typeControl);
//  console.log("coucou2");
 //typeControl = _eqLogic.configuration.typeControl;
 
  
	// On est sur les commandes INFO -- Onglet Equipements à surveiller

	// On remplit la table 	table_controles qui correspond à la table des equipements à tester Type=info SType=watchdog
	// On utilise cmdAttr

		//$('#table_controles tbody').append("coucoucoucoucoucoucoucou");

    if (!isset(_cmd)) 
        //var _cmd = {configuration: {}};
        var _cmd = {};
    if (!isset(_cmd.configuration)) 
        _cmd.configuration = {};
	if (isset(type))
		_cmd.type=type;
	if (!isset(_cmd.subType)) 
		_cmd.subType = "watchdog";	

//console.log ("------------------------------------"+_cmd.configuration.resultat);

		//console.log($('.cmdAttr[data-l1key=configuration][data-l2key=resultat]').value());
		//console.log(_cmd.configuration.resultat);
		//var tr = '<tr class="cmd ' + _cmd.type + '" data-cmd_id="' + init(_cmd.id) + '">';
		switch (_cmd.configuration.resultat) {
		  case 'True':
			var couleur = 'success';
			var icon='<i class="far fa-thumbs-up"  style="color: #ffffff!important;"></i>';
			break;
		  case 'False':
			var couleur = 'warning';
			var icon='<i class="far fa-thumbs-down" style="color: #ffffff!important;"></i>';
			break;
		  default:
			var couleur = 'info';
			var icon='<i class="far fa-question-circle" style="color: #ffffff!important;"></i>';
		}
		
		switch (_cmd.configuration.resultatAvant) {
		  case 'True':
			var couleurAvant = 'success';
			var iconAvant='<i class="far fa-thumbs-up"  style="color: #ffffff!important;"></i>';
			break;
		  case 'False':
			var couleurAvant = 'warning';
			var iconAvant='<i class="far fa-thumbs-down" style="color: #ffffff!important;"></i>';
			break;
		  default:
			var couleurAvant = 'info';
			var iconAvant='<i class="far fa-question-circle" style="color: #ffffff!important;"></i>';
		}			
		
		//var tr = '<tr class="cmd info" >';88
			var tr = '<tr class="cmd info" >'; //la couleur ne foncitonne pas à cause de info mais on ne peut pas supprimer info
		
		tr += '<td width=160>';
		
		
		
		// Mettre le bouton moins à gauche
   // tr += '<a class="btn btn-info btn-sm" pull-right data-action="remove" cmdAction><i class="fa fa-minus-circle"></i></a>';
   //*************************************************
   
		tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
		tr += '<span style="display:none;" class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
		tr += '<input class="cmdAttr form-control" type="hidden" data-l1key="subType" value="watchdog">';
		tr += '</td>';
		 tr += '<td >';
		tr += ' <input class="cmdAttr form-control input-sm"  data-type="' + _cmd.type + '" data-l1key="configuration" data-l2key="controle"  style="margin-bottom : 5px;width : 80%; display : inline-block;" >';
		tr += '<a class="btn btn-info btn-sm cursor listCmdInfo" data-type="' + _cmd.type + '"  style="margin-left : 5px;"><i class="fa fa-list-alt" style="color: #ffffff!important;"></i></a>';
		tr += '<div hidden class="calcul"><small><i>';
		tr += '<span style="margin-top : 9px; margin-left: 10px; " class="cmdAttr" data-l1key="configuration" data-l2key="calcul"></span></i></small></div>';
		tr += '</td>';
			if ((_cmd.configuration.resultatAvant != null) && (typeControl == "")) {
				 tr += '<td width=150 align=center><span class="cmdAttr label label-'+couleurAvant+'" >'+iconAvant+'</span>';
				tr += '<span class="cmdAttr label label-'+couleurAvant+'" style="font-weight: bold;" data-l1key="configuration" data-l2key="resultatAvant"></span>';
				tr += '</td>';
			}
			
		tr += '<td width=150 align=center><span class="cmdAttr label label-'+couleur+'" >'+icon+'</span>';
		tr += '<span class="cmdAttr label label-'+couleur+'" style="font-weight: bold;" data-l1key="configuration" data-l2key="resultat"></span>';
		//tr += '<span class="cmdAttr" style="font-weight: bold;" data-l1key="configuration" data-l2key="resultat"></span>';
		tr += '</td>'; 	
		tr += '<td width=40 align=center>';

		tr += '<span style="font-size: 1.5em;"><i class="fa icon_red fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></span>';
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


function saveEqLogic(_eqLogic) {
	
//console.log("--------- saveEqLogic");

    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }
	//Sauvegarde de l'action à effectuer, façon scénario, avec ses options (pour les commandes qui ont un titre ou autres options)
    _eqLogic.configuration.watchdogAction = $('#table_actions .watchdogAction').getValues('.expressionAttr');
			return _eqLogic;
}

function printEqLogic(_eqLogic) {
	//console.log("--------- printEqLogic");
	//console.log("_eqLogic");
	//console.dir (_eqLogic);

/* CONTENU DE _eqLogic
{
  "id": "4661",
  "name": "Controle IP",
  "logicalId": "",
  "generic_type": null,
  "object_id": null,
  "eqType_name": "watchdog",
  "eqReal_id": null,
  "isVisible": "1",
  "isEnable": "1",
  "configuration": {
    "createtime": "2020-04-11 10:14:21",
    "dernierLancement": "SAVE 12.04.2020 09:48:02",
    "dernierEtat": "True",
    "autorefresh": "*5 * * * *",
    "typeControl": "",
    "tempo1": "",
    "tempo2": "",
    "tempo3": "",
    "watchdogAction": [
      {
        "options": {
          "enable": "1",
          "background": "0",
          "title": "#title#",
          "message": "#controlname# est OK"
        },
        "actionType": "True",
        "cmd": "#[Equipement internes][Pushover Lionel][IconeVerte]#"
      },
      {
        "options": {
          "enable": "1",
          "background": "0",
          "title": "#title#",
          "message": "#controlname# est passé à False"
        },
        "actionType": "False",
        "cmd": "#[Equipement internes][Pushover Lionel][IconeRouge]#"
      }
    ],
    "updatetime": "2020-04-12 09:48:02"
  },
  "timeout": null,
  "category": {
    "heating": "0",
    "security": "0",
    "energy": "0",
    "light": "0",
    "automatism": "0",
    "multimedia": "0",
    "default": "0"
  },
  "display": {
    "height": "110px",
    "width": "230px"
  },
  "order": "9999",
  "comment": null,
  "tags": null,
  "status": [],
  "cmd": [
    {
      "id": "59408",
      "logicalId": null,
      "generic_type": null,
      "eqType": "watchdog",
      "name": "IP",
      "order": "0",
      "type": "info",
      "subType": "watchdog",
      "eqLogic_id": "4661",
      "isHistorized": "0",
      "unite": "",
      "configuration": {
        "controle": "#internalAddr# = \"192.168.0.21\"",
        "calcul": "\"192.168.0.21\" = \"192.168.0.21\"",
        "resultat": "True",
        "aChange": false
      },
      "template": {
        "dashboard": "default",
        "mobile": "default"
      },
      "display": {
        "showStatsOnmobile": 0,
        "showStatsOndashboard": 0
      },
      "value": null,
      "isVisible": "1",
      "alert": []
    }
  ]
}*/

//	$('#table_controlesTitre').append('<tr><td>1</td><td>2</td><td>3</td></tr>');




// On remplit la table_log

    actionOptions = [];
    $('#table_log').empty();


$('#table_log').append('<br><div class="bg-success"><legend><i class="fa fa-cogs"></i> {{Coucou titre}}</legend>');

    if (isset(_eqLogic.configuration.watchdogLog)) {
		
$('#table_log').append('<br><div class="bg-success"><legend><i class="fa fa-cogs"></i> {{Coucou titre}}</legend>');
		
		
	}



// On remplit la table_actions
    actionOptions = [];
    $('#table_actions').empty(); 
    if (isset(_eqLogic.configuration.watchdogAction)) {
//console.log("--------- _eqLogic.configuration.watchdogAction")
//console.dir(_eqLogic.configuration.watchdogAction);; 


//typeControl = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo1]').value()+" secondes"; 
typeControl = _eqLogic.configuration.typeControl;
dernierEtat = "(actuellement à "+_eqLogic.configuration.dernierEtat+")";


//console.log ("coucou"+typeControl+tr);

  //  $('#section_resultatGlobal').empty();
//	$('#section_resultatGlobal').append("0000000000000000000000000000000000000000 "+typeControl);

// on remplit la table du résultat global
  //  $('#table_controles_resultat').empty();
		switch (_eqLogic.configuration.dernierEtat) {
		  case 'True':
			var couleur = 'success';
			var icon='<i class="far fa-thumbs-up"  style="color: #ffffff!important;"></i>';
			break;
		  case 'False':
			var couleur = 'warning';
			var icon='<i class="far fa-thumbs-down" style="color: #ffffff!important;"></i>';
			break;
		  default:
			var couleur = 'info';
			var icon='<i class="far fa-question-circle" style="color: #ffffff!important;"></i>';
		}

		var tr = '<br><br><legend><i class="fa loisir-weightlift"></i> {{Résultat global des contrôles, méthode ';
		tr += typeControl;
		tr += '}}</legend>	<table class="table "  >';
		tr += '<tr bgcolor=silver style="width: 100%; border-collapse: collapse; background-color: #e0e2e2;">';
		tr += '<td style="width: 3px;">';
		tr += '</td>';
		tr += "<td style='background-color:#e0e2e2;'><i><small>Cette information peut être utilisée dans vos scénarios, il s'agit de l'info [Résultat Global]</small></i>";
		tr += '</td>';		 
		tr += '<td align=right style="background-color:#e0e2e2;">';
		 
if (_eqLogic.configuration.dernierLancement.substring(0, 4) == "SAVE")
		tr += "<i><small><small>Attention, résultat temporaire, peut être faux, sera actualisé au prochain CRON ---></small></small></i>";
		 
		 
		tr += '</td>';   
		// tr += '<td style="width: 100px; background-color: #26ae5f">';
	//	 tr += '<strong><span style="color: #ffffff;">''</span></strong>'; 
		 
		 
		tr += '<td style="background-color:#e0e2e2;width: 100px;"><span class="label label-'+couleur+'" >'+icon+'</span>';
		tr += '<span class="label label-'+couleur+'" style="font-weight: bold;">'+ _eqLogic.configuration.dernierEtat+ '</span>';

		 
		 
		tr += '</td>'; 	
		tr += '<td style="width: 100px;">';
		tr += '</td>';
		tr += '</tr></table>';
//	$('#table_controles_resultat').append(tr);




$('#section_resultatGlobal').empty();
$('#table_controlesTitre').empty();

dernierLancement="Aucun";
if (typeof _eqLogic.configuration.dernierLancement !== 'undefined') {
	dernierLancement=_eqLogic.configuration.dernierLancement.replace('CRON ', '');
	dernierLancement=dernierLancement.replace('SAVE ', '');
}

avantDernierLancement="Aucun";
if (typeof _eqLogic.configuration.avantDernierLancement !== 'undefined') {
	avantDernierLancement=_eqLogic.configuration.avantDernierLancement.replace('CRON ', '');
	avantDernierLancement=avantDernierLancement.replace('SAVE ', '');
}


$titreModebase=' <tr><th style="width: 160px;">{{  Nom}}</th><th>{{  Contrôle}}</th><th class="text-center" style="width:150px;">Avant-dernier Résultat<br><small>'+avantDernierLancement+'</small></th><th class="text-center" style="width:150px;">Dernier Résultat<br><small>'+dernierLancement+'</small></th><th style="width:40px;"></th></tr>';
$titreModeETOU=' <tr><th style="width: 160px;">{{  Nom}}</th><th>{{  Contrôle}}</th><th class="text-center" style="width:150px;">Dernier Résultat<br><small>'+dernierLancement+'</small></th><th style="width:40px;"></th></tr>';



if (typeControl == "") {
$('#table_actions').append('<legend><i class="far fa-thumbs-up" style="font-size : 2em;color:#a15bf7;"></i><span style="color:#a15bf7"> {{Actions à exécuter quand un des contrôles passe à True}}</span></legend>');
$('#table_controlesTitre').append($titreModebase);
}
else {
$('#table_controlesTitre').append($titreModeETOU);
$('#section_resultatGlobal').html(tr);
$('#table_actions').append('<legend><i class="far fa-thumbs-up" style="font-size : 2em;color:#a15bf7;"></i><span style="color:#a15bf7"> Actions à exécuter quand le résultat global des contrôles passe à True '+dernierEtat+'</span></legend>');
}
        // On va lister en premier les actions qui se déclencheront quand on passera de false à true
		for (var i in _eqLogic.configuration.watchdogAction) {
			if (_eqLogic.configuration.watchdogAction[i].actionType == "True") {
			//_eqLogic.configuration.watchdogAction[i]
			addAction(_eqLogic.configuration.watchdogAction[i], "True", i)
			}
        }
		
//$('#table_actions').append('</div>');
		
if (typeControl == "")
$('#table_actions').append('<legend><i class="far fa-thumbs-down" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Actions à exécuter quand un des contrôles passe à False}}</span></legend>');
else
$('#table_actions').append('<legend><i class="far fa-thumbs-down" style="font-size : 2em;color:#a15bf7;"></i><span style="color:#a15bf7"> Actions à exécuter quand le résultat global des contrôles passe à False '+dernierEtat+'</span></legend>');

        // puis les actions qui se déclencheront quand on passera de true à false
        for (var i in _eqLogic.configuration.watchdogAction) {
			if (_eqLogic.configuration.watchdogAction[i].actionType == "False")
			addAction(_eqLogic.configuration.watchdogAction[i], "False", i)
        } 
		
//$('#table_actions').append('</div>');

$('#table_actions').append('<legend><i class="fa fa-cogs" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Actions à exécuter AVANT d\'effectuer le contrôle}}</span></legend>');

        // puis les actions qui se déclencheront quand on passera de true à false
        for (var i in _eqLogic.configuration.watchdogAction) {
			if (_eqLogic.configuration.watchdogAction[i].actionType == "Avant")
			addAction(_eqLogic.configuration.watchdogAction[i], "Avant", i)
        } 
		
	}
		
    jeedom.cmd.displayActionsOption({
        params : actionOptions,
        async : false,
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success : function(data){
            for(var i in data){
                $('#'+data[i].id).append(data[i].html.html);
            }
            taAutosize();
        }
    });
	
}

// --Boutons
 /**************** Commun ***********/
 $("body").delegate(".listCmdAction", 'click', function () {

//console.log("--------- listCmdAction");
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
 //   var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=configuration][data-l2key=commande]');
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
        el.value(result.human);
        jeedom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.' + type).find('.actionOptions').html(html);
            taAutosize();
        });
    });
});

$("body").delegate(".listAction", 'click', function () {
	//console.log("--------- listAction");
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
	//var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=configuration][data-l2key=commande]');

  jeedom.getSelectActionModal({}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
  });
});
});


//-------------------------------------
// Pour remplir facilement le test à faire sur un equipement
//-------------------------------------


//$("body").delegate(".listCmdInfo", 'click', function() {
$("#table_controles").off('click').on('click', ".listCmdInfo",function() {
		//$('#table_controlesTitre').append("0000000000000000000000000000000000000000 ");

	//console.log("--------- listCmdInfo");

  var eldebut = $(this);
  var expression = $(this).closest('expressionn');
  	var type = $(this).attr('data-type');	  
  	var el = $(this).closest('.' + type).find('.cmdAttr[data-l1key=configuration][data-l2key=controle]');
  	var el_name = $(this).closest('.' + type).find('.cmdAttr[data-l1key=name]');


  
  if (expression.find('.cmdAttr[data-l1key=type]').value() == 'action') {
    type = 'action';
  }

							tempo1 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo1]').value()+" secondes"; 
							if (tempo1==" secondes") tempo1='à configurer';
							tempo2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo2]').value()+" secondes"; 
							if (tempo2==" secondes") tempo2='à configurer';
							tempo3 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo3]').value()+" secondes"; 
							if (tempo3==" secondes") tempo3='à configurer';


							message = '<form class="form-horizontal" onsubmit="return false;">';
							message += '	<div class="panel-group" id="accordion">';
							
							message += '		<div class="panel panel-default">';
							message += '			<div class="panel-heading">';
							message += '				<h4 class="panel-title"><label for="r11" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value=2 name="choix" checked="checked" required/>';
							message += '				Surveiller un équipement <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"></a></label></h4>';
							message += '			</div>';
							message += '			<div id="collapseOne" class="panel-collapse collapse in">';
							message += '				<div class="panel-body">';
							message += '					<p>';
							message += '						 Par exemple : <b>[Cuisine][Détecteur de Présence]</b>';
							message += '					</div>';
							message += '				</p>';
							message += '			</div>';
							message += '		</div>';
							
						
							message += '		<div class="panel panel-default">';
							message += '			<div class="panel-heading">';
							message += '				<h4 class=panel-title><label for="r12" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r12" value=1 name="choix" required/>';
							message += '				Surveiller la commande d\'un équipement <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"></a></label></h4>';
							message += '			</div>';
							message += '			<div id="collapseTwo" class="panel-collapse collapse">';
							message += '				<div class="panel-body">';
							message += '					<p>';
							message += '						'+ 'Par exemple : <b>[Cuisine][Détecteur de Présence][Présent]</b>';
							message += '					</div>';
							message += '				</p>';
							message += '			</div>';
							message += '		</div>';
							
							
							message += '		<div class="panel panel-default">';
							message += '			<div class="panel-heading">';
							message += '				<h4 class=panel-title><label for="r13" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r13" value=3 name="choix" required/>';
							message += '				Surveiller la config IP de Jeedom<a data-toggle="collapse" data-parent="#accordion" href="#collapseTree"></a></label></h4>';
							message += '			</div>';
							message += '		</div>';							
							
						
							
							message += '<script>';
							message += '	$("#r11").on("click", function(){  $(this).parent().find("a").trigger("click")});';
							message += '	$("#r12").on("click", function(){  $(this).parent().find("a").trigger("click")});';
							message += '</script>';
							message += '		</div>' ;
							message += '			</div>' ;
							message += '</form> ';

// Lancement de l'écran numéro 1/3	
				  bootbox.dialog({
					title: "{{Que voulez-vous surveiller ?}}",
					message: message,
					buttons: {
					  "Annuler": {
						className: "btn-default",
						callback: function () {
						  //expression.find('.watchdogMasterEqLogicAttr[data-l1key=expression]').atCaret('insert', result.human);
						 //el.closest('.watchdogMasterEqLogic').find('.watchdogMasterEqLogicAttr[data-l1key=eqLogic]').value(result.human); C'est pour mettre la résultat précédent
						}
					  },
					  success: {
						label: "Valider",
						className: "btn-primary",
						callback: function () {
							
	//if ($('.conditionAttr[data-l1key=radio]').value() == "1")
	if ($('#r11').value() == "1")
		{
		//------------L'utilisateur demande a choisir l'équipement --
		// Lancement de l'écran numéro 2/3	
			 jeedom.eqLogic.getSelectModal({cmd: {}}, function (result) {
					var date = new Date();

					// on va trouver le nom de l'équipement dans result.human
					// exemple :
					// #[Controle Installation][Apple TV]# doit donner Apple TV

					chaine=result.human;
					for (var i = 0; i < chaine.length; i++) {
						test=chaine.substring(i, i+2);
					  if (test=="]["){
						  chaine=chaine.substring(i+2);
						  break;
					  }}
					for (var i = 0; i < chaine.length; i++) {
						test=chaine.substring(i, i+2);
					  if (test=="]#"){
						  chaine=chaine.substring(0,i);
						  break;
					  }}
						
					//vient de desktop/js/scneario.js
					// Texte de l'écran numéro 3/3	
									tempo1 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo1]').value()+" secondes"; 
									if (tempo1==" secondes") tempo1='à configurer';
									tempo2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo2]').value()+" secondes"; 
									if (tempo2==" secondes") tempo2='à configurer';
									tempo3 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo3]').value()+" secondes"; 
									if (tempo3==" secondes") tempo3='à configurer';							 
									message = '<form class="form-horizontal" onsubmit="return false;">  <div class="panel-group" id="accordion">    ';
									message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class="panel-title">            <label for="r13" style="width: 100%;">              <input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value=2 name="choix" checked="checked" required />';
									message += ' Tester la dernière communication avec l\'équipement';
									message += '<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"></a>            </label>        </h4>      </div>      <div id="collapseOne" class="panel-collapse collapse in">';
									
									//message += '</p>        </div>      </div>   '; 

									//message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class=panel-title>            <label for="r12" style="width: 100%;">              <input type="radio" id="r12" value=1 name="choix" required />';
									//message += " Tester la dernière communication avec l\'équipement";
									//message += '<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"></a>            </label>        </h4>      </div>      <div id="collapseTwo" class="panel-collapse collapse">';
									message += '<div class="panel-body">          <p>'+
									'Tester si le délai depuis la dernière communicvation avec <br><b>'+result.human+'</b> est supérieur à :'+
									'            <div class="col-xs-7">' +
									'              <select class="conditionAttr form-control" data-l1key="choixtempo">' +
									'                       <option value="1">Tempo1 ('+tempo1+')</option>' +
									'                       <option value="2">Tempo2 ('+tempo2+')</option>' +
									'                       <option value="3">Tempo3 ('+tempo3+')</option>' +
									'                       </select>' +
									'                    </div>' +
									'</p>        </div>      </div>    </div>';
									//message += '<script>$("#r11").on("click", function(){  $(this).parent().find("a").trigger("click")});$("#r12").on("click", function(){  $(this).parent().find("a").trigger("click")})</script>';
									message += '<div class="form-group"> ' +
									'             <div class="col-xs-12">' +
									'  <input type="checkbox" style="margin-top : 11px;margin-right : 10px;" class="conditionAttr" data-l1key="configuration" data-l2key="assistName" > Mettre <b>'+chaine+'</b> comme nom au contrôle' +
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
									message += '</div> </div>' ;
									message += '</form> ';

					// Lancement de l'écran numéro 3/3	

						  
						  bootbox.dialog({
							title: "{{Quel test faire ?}}",
							message: message,
							buttons: {
							  "Annuler": {
								className: "btn-default",
								callback: function () {
								  //expression.find('.watchdogMasterEqLogicAttr[data-l1key=expression]').atCaret('insert', result.human);
								 //el.closest('.watchdogMasterEqLogic').find('.watchdogMasterEqLogicAttr[data-l1key=eqLogic]').value(result.human); C'est pour mettre la résultat précédent
								}
							  },
							  success: {
								label: "Valider",
								className: "btn-primary",
								callback: function () {
									
								  var condition = result.human;
								  //var test=result.cmd.subType;
								  
								  //console.dir($('.conditionAttr[data-l1key=radio]').value());
								  
									  
									  //On regarde quel ets le tempo sélectionné 
									  switch ($('.conditionAttr[data-l1key=choixtempo]').value()) {
											  case '2':
												choixtempo="#tempo2#";
												break;
											  case '3':
												choixtempo="#tempo3#";
												break;
											  default:
												choixtempo="#tempo1#";
											}
									  
									  
									  // On est dans le cas : Tester le délai depuis la dernière mise à jour de xxx ou aucune case
								  condition = '(#timestamp# - strtotime(lastCommunication(' + condition+"))) > "+choixtempo;

								

								valeurprecedente = chaineExpressionTest
								//valeurprecedente = $('.eqLogicAttr[data-l1key=configuration][data-l2key=nameOn]').value();
								condition=valeurprecedente+condition;
								chaineExpressionTest=condition;
								//$('.eqLogicAttr[data-l1key=configuration][data-l2key=nameOn]').value(condition);				  
								
								  if($('.conditionAttr[data-l1key=next]').value() != ''){
									eldebut.click();
									//rempliCondition(); //on reboucle pour une autre condition
								  }
								  else
								  {
									 // console.log("condition="+condition);
									 // console.log("chaine="+chaine);
									  el.value(condition);
									// Si la case à cocher qui permet de mettre automatiquement le nom de l'équipement est cochée
									if($('.conditionAttr[data-l1key=configuration][data-l2key=assistName]').value() == '1')
									  el_name.value(chaine);

									  chaineExpressionTest="";
								  

								  }
								  
								  //el.value(condition);
							   }
							  },
							}
						  }); // fin de bootbox.dialog(
					  });	// fin de jeedom.cmd.getSelectModal			
			
		}
			
	else if ($('#r12').value() == "1")

	{

 		//------------L'utilisateur demande a choisir la commande de l'équipement --
		// Lancement de l'écran numéro 2/3	
			 jeedom.cmd.getSelectModal({cmd: {}}, function (result) {
					var date = new Date();

					// on va trouver le nom de l'équipement dans result.human
					// exemple :
					// #[Controle Installation][Apple TV][Statut]# doit donner Apple TV

					chaine=result.human;
					for (var i = 0; i < chaine.length; i++) {
						test=chaine.substring(i, i+2);
					  if (test=="]["){
						  chaine=chaine.substring(i+2);
						  break;
					  }}
					for (var i = 0; i < chaine.length; i++) {
						test=chaine.substring(i, i+2);
					  if (test=="]["){
						  chaine=chaine.substring(0,i);
						  break;
					  }}
						
					//vient de desktop/js/scneario.js
					// Texte de l'écran numéro 3/3	
							 
									message = '<form class="form-horizontal" onsubmit="return false;">  <div class="panel-group" id="accordion">    ';
									message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class="panel-title">            <label for="r11" style="width: 100%;">              <input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value=2 name="choix" checked="checked" required />';
									message += ' Tester un changement d\'état de la commande';
									message += '<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"></a>            </label>        </h4>      </div>      <div id="collapseOne" class="panel-collapse collapse in">        <div class="panel-body">          <p>';
									message += 'Tester si <b>'+result.human+' </b>est'+
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
									tempo1 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo1]').value()+" secondes"; 
									if (tempo1==" secondes") tempo1='à configurer';
									tempo2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo2]').value()+" secondes"; 
									if (tempo2==" secondes") tempo2='à configurer';
									tempo3 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo3]').value()+" secondes"; 
									if (tempo3==" secondes") tempo3='à configurer';
									message += '<div class="panel panel-default">      <div class="panel-heading">        <h4 class=panel-title>            <label for="r12" style="width: 100%;">              <input type="radio" id="r12" value=1 name="choix" required />';
									message += " Tester la date de la dernière collecte";
									message += '<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"></a>            </label>        </h4>      </div>      <div id="collapseTwo" class="panel-collapse collapse">        <div class="panel-body">          <p>'+
									'Tester si le délai depuis la dernière mise à jour de <br><b>'+result.human+'</b> est supérieur à :'+
									'            <div class="col-xs-7">' +
									'              <select class="conditionAttr form-control" data-l1key="choixtempo">' +
									'                       <option value="1">Tempo1 ('+tempo1+')</option>' +
									'                       <option value="2">Tempo2 ('+tempo2+')</option>' +
									'                       <option value="3">Tempo3 ('+tempo3+')</option>' +
									'                       </select>' +
									'                    </div>' +
									'</p>        </div>      </div>    </div>';
									message += '<script>$("#r11").on("click", function(){  $(this).parent().find("a").trigger("click")});$("#r12").on("click", function(){  $(this).parent().find("a").trigger("click")})</script>';
									message += '<div class="form-group"> ' +
									'             <div class="col-xs-12">' +
									'  <input type="checkbox" style="margin-top : 11px;margin-right : 10px;" class="conditionAttr" data-l1key="configuration" data-l2key="assistName" > Mettre <b>'+chaine+'</b> comme nom au contrôle' +
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
									message += '</div> </div>' ;
									message += '</form> ';

					// Lancement de l'écran numéro 3/3	

						  
						  bootbox.dialog({
							title: "{{Quel test faire ?}}",
							message: message,
							buttons: {
							  "Annuler": {
								className: "btn-default",
								callback: function () {
								  //expression.find('.watchdogMasterEqLogicAttr[data-l1key=expression]').atCaret('insert', result.human);
								 //el.closest('.watchdogMasterEqLogic').find('.watchdogMasterEqLogicAttr[data-l1key=eqLogic]').value(result.human); C'est pour mettre la résultat précédent
								}
							  },
							  success: {
								label: "Valider",
								className: "btn-primary",
								callback: function () {
									
								  var condition = result.human;
								  var test=result.cmd.subType;
								  
								 // console.dir($('.conditionAttr[data-l1key=radio]').value());
								  
								  if($('.conditionAttr[data-l1key=radio]').value() != '1'){
									  
									  //On regarde quel ets le tempo sélectionné 
									  switch ($('.conditionAttr[data-l1key=choixtempo]').value()) {
											  case '2':
												choixtempo="#tempo2#";
												break;
											  case '3':
												choixtempo="#tempo3#";
												break;
											  default:
												choixtempo="#tempo1#";
											}
									  
									  
									  // On est dans le cas : Tester le délai depuis la dernière mise à jour de xxx ou aucune case
								  condition = '(#timestamp# - strtotime(collectdate(' + condition+"))) > "+choixtempo;
								  }
								  else
								  {		  
									  condition += ' ' + $('.conditionAttr[data-l1key=operator]').value();
									  if(result.cmd.subType == 'string'){
										if($('.conditionAttr[data-l1key=operator]').value() == 'matches'){
										  condition += ' "/' + $('.conditionAttr[data-l1key=operande]').value()+'/"';
										}else{
										  condition += ' "' + $('.conditionAttr[data-l1key=operande]').value()+'"';
										}
									  }else{
										condition += ' ' + $('.conditionAttr[data-l1key=operande]').value();
									  }
									  condition += ' ' + $('.conditionAttr[data-l1key=next]').value()+' ';
								  }
								

								valeurprecedente = chaineExpressionTest
								//valeurprecedente = $('.eqLogicAttr[data-l1key=configuration][data-l2key=nameOn]').value();
								condition=valeurprecedente+condition;
								chaineExpressionTest=condition;
								//$('.eqLogicAttr[data-l1key=configuration][data-l2key=nameOn]').value(condition);				  
								
								  if($('.conditionAttr[data-l1key=next]').value() != ''){
									eldebut.click();
									//rempliCondition(); //on reboucle pour une autre condition
								  }
								  else
								  {
									  el.value(condition);
									// Si la case à cocher qui permet de mettre automatiquement le nom de l'équipement est cochée
									if($('.conditionAttr[data-l1key=configuration][data-l2key=assistName]').value() == '1')
									  el_name.value(chaine);

									  chaineExpressionTest="";
								  

								  }
								  
								  //el.value(condition);
							   }
							  },
							}
						  }); // fin de bootbox.dialog(
					  });	// fin de jeedom.cmd.getSelectModal
		}
		else {
		//------------L'utilisateur demande a choisir le controle de l'IP --
		var currentLocationhostname = window.location.hostname;
			el.value('#internalAddr# = "'+currentLocationhostname+'"');
		}
  
  // Début Fermeture des parenthèses et accolades du premier bootbox.dialog
					   }
					  },
					}
				  });
// Fin Fermeture des parenthèses et accolades du premier bootbox.dialog
  
  
  });



