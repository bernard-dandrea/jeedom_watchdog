// Last Modified : 2025/09/04 11:32:22


function generer_expression() {

    var eldebut = $(this);
    var el = $(this).closest('.info').find('.cmdAttr[data-l1key=configuration][data-l2key=controle]');
    var el_name = $(this).closest('.info').find('.cmdAttr[data-l1key=name]');


    var chaineExpressionTest = "";

    tempo1 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo1]').value() + " secondes";
    if (tempo1 == " secondes") tempo1 = 'à configurer';
    tempo2 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo2]').value() + " secondes";
    if (tempo2 == " secondes") tempo2 = 'à configurer';
    tempo3 = $('.eqLogicAttr[data-l1key=configuration][data-l2key=tempo3]').value() + " secondes";
    if (tempo3 == " secondes") tempo3 = 'à configurer';

    message = dialogHTML_Head('Sélection type action') + dialogHTML_1();

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

                        jeedom.eqLogic.getSelectModal({}, function (result) {

                            //vient de desktop/js/scenario.js
                            // Texte de l'écran numéro 3/3	

                            // utilisé pour donner le nom au controle
                            var controlname = extractName(result.human);

                            message = dialogHTML_Head('Sélection action pour équipement') + dialogHTML_11();
                            message = remplaceTout(message, '_tempo1_', 'tempo1 (' + tempo1 + ')');
                            message = remplaceTout(message, '_tempo2_', 'tempo2 (' + tempo2 + ')');
                            message = remplaceTout(message, '_tempo3_', 'tempo3 (' + tempo3 + ')');
                            message = remplaceTout(message, '_controlname_', controlname);
                            message = remplaceTout(message, '_control_', result.human);

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

                                                condition = '(#timestamp# - strtotime(lastCommunication(' + condition + "))) > " +  '_tempo'+$('.conditionAttr[data-l1key=choixtempo]').value()+'_';
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
                            }); 
                        });		
                    }

                    else if ($('#r12').value() == "1") {

                        //------------L'utilisateur demande a choisir la commande de l'équipement --
                        jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {

                            // utilisé pour donner le nom au controle
                            var controlname = extractName(result.human);

                            message = dialogHTML_Head('Sélection action pour commande') + dialogHTML_21();
                            message = remplaceTout(message, '_tempo1_', 'tempo1 (' + tempo1 + ')');
                            message = remplaceTout(message, '_tempo2_', 'tempo2 (' + tempo2 + ')');
                            message = remplaceTout(message, '_tempo3_', 'tempo3 (' + tempo3 + ')');
                            message = remplaceTout(message, '_controlname_', controlname);
                            message = remplaceTout(message, '_control_', result.human);


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

                                            if ($('#r11').value() == '1') {     // Commande : tester le changement d'état dernière communication
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
                                                condition = '(age(' + condition + ") > " + '_tempo'+$('.conditionAttr[data-l1key=choixtempo]').value()+'_' + ') ou (age(' + condition + ') < 0)';
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
                            }); 
                        });	
                    }
                    else {
                        //------------L'utilisateur demande a choisir le controle de l'IP --
                        var currentLocationhostname = window.location.hostname;
                        el.atCaret('insert', '#internalAddr# = "' + currentLocationhostname + '"');
                    }
                }
            },
        }
    });
}

function remplaceTout(chaine, aRemplacer, remplacement) {
    // Échapper les caractères spéciaux dans la chaîne à remplacer pour les expressions régulières
    const motif = aRemplacer.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(motif, 'g');
    return chaine.replace(regex, remplacement);
}


function dialogHTML_Head(title) {

    // demandé par https://ralfvanveen.com/fr/tools/validateur-html/

    $html = `
<!DOCTYPE html>
<html lang="fr-FR">
<head>
	<title>` ;
    $html += title;
    $html += `
    </title>
</head> 
`;
    return $html;

}

function dialogHTML_1() {

    // corrigé par https://www.htmlcorrector.com/ 

    return `
<form class="form-horizontal" onsubmit="return false;">
  <div class="panel-group" id="accordion">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r11" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value="2" name="choix" checked="checked" required=""> Un équipement </label></h4>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r12" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r12" value="1" name="choix" required=""> La commande d'un équipement </label></h4>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r13" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r13" value="3" name="choix" required=""> La config IP de Jeedom</label></h4>
      </div>
    </div>
  </div>
</form>
`;

}




function dialogHTML_11() {

    // corrigé par https://www.htmlcorrector.com/ 

    return `
<form class="form-horizontal" onsubmit="return false;">
  <div class="panel-group" id="accordion">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r11" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value="2" name="choix" checked="checked" required=""> Tester la dernière communication avec l'équipement<a data-toggle="collapse" data-parent="#accordion" href="#collapse1"></a></label></h4>
      </div>
      <div id="collapse1" class="panel-collapse collapse in">
        <div class="panel-body">
          <p>Tester si le délai depuis la dernière communication avec<br>
          <b>_control_</b> est supérieur à :</p>
          <div class="col-xs-7">
            <select class="conditionAttr form-control" data-l1key="choixtempo">
              <option value="1">
                _tempo1_
              </option>
              <option value="2">
                _tempo2_
              </option>
              <option value="3">
                _tempo3_
              </option>
            </select>
          </div>
        </div>
      </div>
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r12" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r12" value="3" name="choix" required=""> Tester que cet équipement est actif<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"></a></label></h4>
      </div>
      <div id="collapse2" class="panel-collapse collapse in"></div>
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r13" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r13" value="4" name="choix" required=""> Utiliser la macro sur cet équipement<a data-toggle="collapse" data-parent="#accordion" href="#collapse3"></a></label></h4>
      </div>
      <div id="collapse3" class="panel-collapse collapse in"></div>
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r14" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r14" value="5" name="choix" required=""> Insérer le nom de cet équipement<a data-toggle="collapse" data-parent="#accordion" href="#collapse4"></a></label></h4>
      </div>
      <div id="collapse4" class="panel-collapse collapse in"></div>
    </div>
  </div>
  <script>
        $("#r11").on("click", function () { $(this).parent().find("a").trigger("click") });
        $("#r12").on("click", function () { $(this).parent().find("a").trigger("click") });
        $("#r13").on("click", function () { $(this).parent().find("a").trigger("click") });
        $("#r14").on("click", function () { $(this).parent().find("a").trigger("click") });
  </script>
  <div class="form-group">
    <div class="col-xs-12">
      <input type="checkbox" checked style="margin-top : 11px;margin-right : 10px;" class="conditionAttr" data-l1key="configuration" data-l2key="assistName"> Mettre <b>_controlname_</b> comme nom au contrôle
    </div>
  </div>
  <hr>
  <div class="form-group">
    <label class="col-xs-5 control-label">Ensuite</label>
    <div class="col-xs-3">
      <select class="conditionAttr form-control" data-l1key="next">
        <option value="">
          rien
        </option>
        <option value="ET">
          et
        </option>
        <option value="OU">
          ou
        </option>
      </select>
    </div>
  </div>
</form>
`;

}



function dialogHTML_21() {

    // corrigé par https://www.htmlcorrector.com/ 

    return `

<form class="form-horizontal" onsubmit="return false;">
  <div class="panel-group" id="accordion">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r11" style="width: 100%;"><input type="radio" class="conditionAttr" data-l1key="radio" id="r11" value="2" name="choix" checked="checked" required=""> Tester un changement d'état de la commande<a data-toggle="collapse" data-parent="#accordion" href="#collapse1"></a></label></h4>
      </div>
      <div id="collapse1" class="panel-collapse collapse in">
        <div class="panel-body">
          <p>Tester si <b>_control_</b> est</p>
          <div class="col-xs-7">
            <input class="conditionAttr" data-l1key="operator" value="==" style="display : none;"> <select class="conditionAttr form-control" data-l1key="operande">
              <option value="1">
                Ouvert
              </option>
              <option value="0">
                Fermé
              </option>
              <option value="1">
                Allumé
              </option>
              <option value="0">
                Eteint
              </option>
              <option value="1">
                Déclenché
              </option>
              <option value="0">
                Au repos
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r12" style="width: 100%;"><input type="radio" id="r12" value="1" name="choix" required=""> Tester la date de la dernière collecte de cette commande<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"></a></label></h4>
      </div>
      <div id="collapse2" class="panel-collapse collapse">
        <div class="panel-body">
          <p>Tester si le délai depuis la dernière mise à jour de<br>
          <b>_control_</b> est supérieur à :</p>
          <div class="col-xs-7">
            <select class="conditionAttr form-control" data-l1key="choixtempo">
              <option value="1">
                _tempo1_
              </option>
              <option value="2">
                _tempo2_
              </option>
              <option value="3">
                _tempo3_
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r13" style="width: 100%;"><input type="radio" id="r13" value="3" name="choix" required=""> Utiliser la macro sur cette commande<a data-toggle="collapse" data-parent="#accordion" href="#collapse3"></a></label></h4>
      </div>
      <div id="collapse3" class="panel-collapse collapse">
        <div class="panel-body"></div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><label for="r14" style="width: 100%;"><input type="radio" id="r14" value="4" name="choix" required=""> Insérer le nom de cette commande<a data-toggle="collapse" data-parent="#accordion" href="#collapse4"></a></label></h4>
      </div>
      <div id="collapse4" class="panel-collapse collapse">
        <div class="panel-body"></div>
      </div>
    </div>
    <script>
            $("#r11").on("click", function () { $(this).parent().find("a").trigger("click") }); 
            $("#r12").on("click", function () { $(this).parent().find("a").trigger("click") }); 
            $("#r13").on("click", function () { $(this).parent().find("a").trigger("click") }); 
            $("#r14").on("click", function () { $(this).parent().find("a").trigger("click") });
    </script>
    <div class="form-group">
      <div class="col-xs-12">
        <input type="checkbox" checked style="margin-top : 11px;margin-right : 10px;" class="conditionAttr" data-l1key="configuration" data-l2key="assistName"> Mettre <b>_controlname_</b> comme nom au contrôle
      </div>
    </div>
    <hr>
    <div class="form-group">
      <label class="col-xs-5 control-label">Ensuite</label>
      <div class="col-xs-3">
        <select class="conditionAttr form-control" data-l1key="next">
          <option value="">
            rien
          </option>
          <option value="ET">
            et
          </option>
          <option value="OU">
            ou
          </option>
        </select>
      </div>
    </div>
  </div>
</form>
`;

}

