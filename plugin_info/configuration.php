<?php
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

    <legend><i class="icon kiko-check-line" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Résultat Global}}</span></legend>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Contrôle OK lorsque le Résultat Global est égal à }}</label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="sel_ResultatGlobalOK" class="configKey form-control" data-l1key="ResultatGlobalOK">
                <option value="">{{True}}</option>
                <option value="0">{{False}}</option>
            </select>
        </div>
    </div> <br>

    <legend><i class="icon kiko-book-open" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Reporting}}</span></legend>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Virtuel pour le reporting}}</label>
        <div class="col-sm-3">
            <div class="input-group">
                <input class="configKey form-control" data-l1key="VirtualReport" />
                <span class="input-group-btn">
                    <a class="btn btn-default cursor" title="Rechercher un équipement" id="VirtualReportGlobal"><i class="fas fa-list-alt"></i></a>
                </span>
            </div>
        </div>
    </div> <br>
    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Afficher seulement les watchdogs non OK}}</label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="sel_ReportOnlyNonOK" class="configKey form-control" data-l1key="ReportOnlyNonOK">
                <option value="">{{Oui}}</option>
                <option value="0">{{Non}}</option>
            </select>
        </div>
    </div>
    <br>

    <?php
    $widgetDashboard = cmd::getSelectOptionsByTypeAndSubtype('info', 'binary', 'dashboard', cmd::availableWidget('dashboard'));
    $widgetMobile = cmd::getSelectOptionsByTypeAndSubtype('info', 'binary', 'dashboard', cmd::availableWidget('mobile'));
    ?>

    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget Resultat Global dashboard}}</label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_resultatglobal_dashboard" class="configKey form-control" data-l1key="template_resultatglobal_dashboard">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetDashboard;
                ?>
            </select>
        </div>
    </div>
    <br>

    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget Resultat Global mobile}}</label>
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

    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget Reporting dashboard}}</label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_reporting_dashboard" class="configKey form-control" data-l1key="template_reporting_dashboard">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetDashboard;
                ?>
            </select>
        </div>
    </div>
    <br>

    <div class=" form-group">
        <label class="col-sm-3 control-label">{{Widget Reporting mobile}}</label>
        <div class="col-sm-3">
            <select style="width: 150px;" id="template_reporting_mobile" class="configKey form-control" data-l1key="template_reporting_mobile">
                <option value="">{{Défaut}}</option>
                <?php
                echo $widgetMobile;
                ?>
            </select>
        </div>
    </div>
    <br>



</form>



<?php include_file('desktop', 'watchdog', 'js', 'watchdog'); ?>
