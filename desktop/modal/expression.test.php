<?php

// Last Modified : 2025/09/04 16:02:27

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


// ce programme est une copie du script standard de jeedom pour tester les expressions
// seul le début a été modifié afin d'initialiser l expression à tester


if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div id="md_expressionTest" data-modalType="md_expressionTest">
  <form class="form-horizontal" onsubmit="return false;">
    <div class="input-group input-group-sm" style="width: 100%">
      <span class="input-group-addon roundedLeft" style="width: 100px"><i class="fas fa-random"></i> {{Test}}</span>
      <input class="form-control input-sm" id="in_testExpression">
      <span class="input-group-btn">
        <a class="btn btn-default btn-sm cursor tooltips" id="bt_searchInfoCmd" title="{{Rechercher une commande}}"><i class="fas fa-list-alt"></i>
          <a class="btn btn-default btn-sm cursor tooltips" id="bt_selectGenericExpression" title="{{Rechercher un type générique}}"><i class="fas fa-puzzle-piece"></i>
          </a><a class="btn btn-default btn-sm cursor tooltips" id="bt_searchEqLogic" title="{{Rechercher un équipement}}"><i class="fas fa-cube"></i>
          </a><a class="btn btn-sm btn-default btn-success roundedRight" id="bt_testExpression"><i class="fas fa-bolt"></i> {{Exécuter}}</a>
      </span>
    </div>
  </form>
  </br>
  <legend><i class="fas fa-sign-in-alt"></i> {{Résultat}}</legend>
  <div id="div_expressionTestResult"></div>
  <legend><i class="fas fa-history"></i> {{Historique}}</legend>
  <ul id="ul_expressionHistory"></ul>
</div>

<?php
$expression = init('expression');
$expression = str_replace('"', '\"', $expression);
sendVarToJs('expression', $expression);
?>

<script>
  if (!jeeFrontEnd.md_expressionTest) {
    jeeFrontEnd.md_expressionTest = {
      init: function() {
        document.getElementById('in_testExpression').insertAtCursor(expression)
        jeeFrontEnd.md_expressionTest.testExpression();
      },

      testExpression: function() {
        let expression = document.getElementById('in_testExpression').value
        if (expression == '') {
          jeedomUtils.showAlert({
            attachTo: jeeDialog.get('#md_expressionTest', 'dialog'),
            message: '{{L\'expression de test ne peut être vide}}',
            level: 'danger'
          })
          return
        }

        if (!document.querySelector('.bt_expressionHistory[data-command="' + expression.replace(/"/g, '\\"') + '"]')) {
          let li = '<li class="cursor list-group-item list-group-item-success bt_expressionHistory"  data-command="' + expression.replace(/"/g, '\\"') + '"><a>' + expression + '</a></li>'
          document.getElementById('ul_expressionHistory').insertAdjacentHTML('afterbegin', li)
        }
        jeedom.scenario.testExpression({
          expression: expression,
          error: function(error) {
            jeedomUtils.showAlert({
              attachTo: jeeDialog.get('#md_expressionTest', 'dialog'),
              message: error.message,
              level: 'danger'
            });
          },
          success: function(data) {
            let divResult = document.getElementById('div_expressionTestResult')
            divResult.empty()
            var html = '<ul><div class="alert alert-info">'
            if (data.correct == 'nok') {
              html += '<strong>{{Attention : il doit y avoir un souci, car le résultat est le même que l\'expression}}</strong><br\>'
            }
            html += '{{Je vais évaluer :}} <strong>' + data.evaluate + '</strong><br/>'
            html += '{{Résultat :}} <strong>' + data.result + '</strong>'
            html += '</div></ul>'
            divResult.insertAdjacentHTML('beforeend', html)
          }
        })
      },
    }
  }

  (function() { // Self Isolation!
    var jeeM = jeeFrontEnd.md_expressionTest
    jeeM.init()

    //Manage events outside parents delegations:
    document.getElementById('in_testExpression')?.addEventListener('keypress', function(event) {
      if (event.which == 13) {
        jeeFrontEnd.md_expressionTest.testExpression()
      }
    })

    /*Events delegations
     */
    document.getElementById('md_expressionTest')?.addEventListener('click', function(event) {
      var _target = null
      if (_target = event.target.closest('#bt_searchInfoCmd')) {
        jeedom.cmd.getSelectModal({
          cmd: {
            type: 'info'
          }
        }, function(result) {
          document.getElementById('in_testExpression').insertAtCursor(result.human)
        })
        return
      }

      if (_target = event.target.closest('#bt_searchScenario')) {
        jeedom.scenario.getSelectModal({}, function(result) {
          document.getElementById('in_testExpression').insertAtCursor(result.human)
        })
        return
      }

      if (_target = event.target.closest('#bt_searchEqLogic')) {
        jeedom.eqLogic.getSelectModal({}, function(result) {
          document.getElementById('in_testExpression').insertAtCursor(result.human)
        })
        return
      }

      if (_target = event.target.closest('#bt_selectGenericExpression')) {
        jeedom.config.getGenericTypeModal({
          type: 'info',
          object: true
        }, function(result) {
          document.getElementById('in_testExpression').insertAtCursor(result.human)
        })
        return
      }

      if (_target = event.target.closest('#bt_testExpression')) {
        jeeFrontEnd.md_expressionTest.testExpression()
        return
      }

      if (_target = event.target.closest('.bt_expressionHistory')) {
        document.getElementById('in_testExpression').value = _target.getAttribute('data-command')
        jeeFrontEnd.md_expressionTest.testExpression()
        return
      }
    })
  })()
  testExpression = function(_params) {

    var paramsRequired = ['expression']
    var paramsSpecifics = {}
    try {
      jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
    } catch (e) {
      (_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
      return
    }
    var params = domUtils.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
    var paramsAJAX = jeedom.private.getParamsAJAX(params)
    paramsAJAX.url = 'core/ajax/scenario.ajax.php'
    paramsAJAX.data = {
      action: 'testExpression',
      expression: _params.expression
    }
    domUtils.ajax(paramsAJAX)
  }
</script>

