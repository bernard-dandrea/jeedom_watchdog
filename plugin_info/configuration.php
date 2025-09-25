<?php

// Last Modified : 2025/09/02 18:57:19

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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>

<form class="form-horizontal">

    <legend><i class="icon kiko-check-line" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Résultat}}</span></legend>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Contrôle OK lorsque le résultat est égal à }}
            <sup><i class="fas fa-question-circle tooltips" title="{{Ne s'applique qu'au mode 'Action sur chaque contrôe indépendamment'. Dans les modes ET/OU, le résultat global est toujours affiché.}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="sel_ResultatOK" class="configKey form-control" data-l1key="ResultatOK">
                <option value="">{{True}}</option>
                <option value="0">{{False}}</option>
            </select>
        </div>
    </div>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Historique}}</label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="sel_ResultatHistory" class="configKey form-control" data-l1key="ResultatHistory">
                <option value="">{{Aucun}}</option>
                <option value="/">{{Défaut}}</option>
                <option value="-1 day">{{1 jour}}</option>
                <option value="-7 days">{{7 jours}}</option>
                <option value="-1 month">{{1 mois}}</option>
                <option value="-3 month">{{3 mois}}</option>
                <option value="-6 month">{{6 mois}}</option>
                <option value="-1 year">{{1 an}}</option>
                <option value="-2 years">{{2 ans}}</option>
                <option value="-3 years">{{3 ans}}</option>
                <option value="never">{{Pas de purge}}</option>
            </select>
        </div>
    </div>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Afficher seulement les résultats non OK}} </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="sel_DisplayOnlyConditionNonOK" class="configKey form-control" data-l1key="DisplayOnlyConditionNonOK">
                <option value="">{{Oui}}</option>
                <option value="0">{{Non}}</option>
            </select>
        </div>
    </div>
    <?php
    $widgetDashboard = cmd::getSelectOptionsByTypeAndSubtype('info', 'binary', 'dashboard', cmd::availableWidget('dashboard'));
    $widgetMobile = cmd::getSelectOptionsByTypeAndSubtype('info', 'binary', 'dashboard', cmd::availableWidget('mobile'));
    ?>

    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget dashboard}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le(s) résultat(s) dans la tuile du watchdog en mode dashboard}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_resultat_dashboard" class="configKey form-control" data-l1key="template_resultat_dashboard">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetDashboard;
                ?>
            </select>
        </div>
    </div>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget mobile}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le(s) résultat(s) dans la tuile du watchdog en mode mobile}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_resultat_mobile" class="configKey form-control" data-l1key="template_resultat_mobile">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetMobile;
                ?>
            </select>
        </div>
    </div>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget global dashboard}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le résultat global dans la tuile du watchdog en mode dashboard}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_resultatglobal_dashboard" class="configKey form-control" data-l1key="template_resultatglobal_dashboard">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetDashboard;
                ?>
            </select>
        </div>
    </div>

    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget global mobile}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le résultat global dans la tuile du watchdog en mode mobile}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_resultatglobal_mobile" class="configKey form-control" data-l1key="template_resultatglobal_mobile">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetMobile;
                ?>
            </select>
        </div>
    </div>
    <br>


    <legend><i class="icon kiko-book-open" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Reporting}}</span></legend>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Virtuel pour le reporting}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Les résultats des contrôles seront enregistrés dans ce virtuel, ce qui permet d'avoir une vue globale de l'état des watchdogs.}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <div class="input-group">
                <input class="configKey form-control" data-l1key="VirtualReport" />
                <span class="input-group-btn">
                    <a class="btn btn-default cursor" title="Rechercher un virtuel" id="VirtualReportPlugin"><i class="fas fa-list-alt"></i></a>
                </span>
            </div>
        </div>
    </div>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Historique}}</label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="sel_ReportingHistory" class="configKey form-control" data-l1key="ReportingHistory">
                <option value="">{{Aucun}}</option>
                <option value="/">{{Défaut}}</option>
                <option value="-1 day">{{1 jour}}</option>
                <option value="-7 days">{{7 jours}}</option>
                <option value="-1 month">{{1 mois}}</option>
                <option value="-3 month">{{3 mois}}</option>
                <option value="-6 month">{{6 mois}}</option>
                <option value="-1 year">{{1 an}}</option>
                <option value="-2 years">{{2 ans}}</option>
                <option value="-3 years">{{3 ans}}</option>
                <option value="never">{{Pas de purge}}</option>
            </select>
        </div>
    </div>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Afficher seulement les résultats non OK}}</label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="sel_DisplayOnlyReportingNonOK" class="configKey form-control" data-l1key="DisplayOnlyReportingNonOK">
                <option value="">{{Oui}}</option>
                <option value="0">{{Non}}</option>
            </select>
        </div>
    </div>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget dashboard}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le(s) résultat(s) dans la tuile du virtuel en mode dashboard}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_reporting_dashboard" class="configKey form-control" data-l1key="template_reporting_dashboard">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetDashboard;
                ?>
            </select>
        </div>
    </div>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget mobile}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le(s) résultat(s) dans la tuile du virtuel en mode mobile}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_reporting_mobile" class="configKey form-control" data-l1key="template_reporting_mobile">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetMobile;
                ?>
            </select>
        </div>
    </div>

    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Suppression automatique}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Suppression dans le reporting si la condition ou le watchdog est supprimé}}"></i></sup>
        </label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="sel_ReportingSuppressionAutomatique" class="configKey form-control" data-l1key="ReportingSuppressionAutomatique">
                <option value="">{{Oui}}</option>
                <option value="0">{{Non}}</option>
            </select>
        </div>
    </div>


</form>



<?php include_file('desktop', 'watchdog', 'js', 'watchdog'); ?>