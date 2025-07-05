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


/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class watchdog extends eqLogic
{

    public function preInsert()
    {
        $watchdog = $this;  
        $watchdog->setIsEnable(1);
        $watchdog->setIsVisible(1);
        $watchdog->setConfiguration('autorefresh', '*/5 * * * *');
    }

    public function preSave()
    {
        $watchdog = $this;  
        log::add('watchdog', 'info', '┌──────────────────────[Sauvegarde du Watchdog ' . $watchdog->getName() . ']────────────────────────────────────────────────────────────────────────────────────');

        if ((substr($watchdog->getConfiguration('dernierLancement'), 0, 7)) == "PRECRON") {
            $watchdog->setConfiguration('dernierLancement', 'CRON ' . date("d.m.Y") . " " . date("H:i:s"));
        } else {
            $watchdog->setConfiguration('dernierLancement', 'SAVE ' . date("d.m.Y") . " " . date("H:i:s"));
        }

        $ResultatGlobalOK = config::byKey('ResultatGlobalOK', 'watchdog', '1');
        $ResultatGlobalOK = $watchdog->getConfiguration("ResultatGlobalOK", $ResultatGlobalOK);
        $watchdog->setConfiguration("ResultatGlobalOK_Courant", $ResultatGlobalOK);

        $VirtualReport = trim($watchdog->getConfiguration("VirtualReport", ''));
        if ($VirtualReport == '')
            $VirtualReport = trim(config::byKey('VirtualReport', 'watchdog', ''));
        $watchdog->setConfiguration("VirtualReport_Courant", $VirtualReport);

        $ReportOnlyNonOK = config::byKey('VirtualReport', 'ReportOnlyNonOK', '1');
        $ReportOnlyNonOK = $watchdog->getConfiguration("ReportOnlyNonOK", $ReportOnlyNonOK);
        $watchdog->setConfiguration("ReportOnlyNonOK_Courant", $ReportOnlyNonOK);

        $template_resultatglobal_dashboard = config::byKey('template_resultatglobal_dashboard', 'watchdog', 'core::default');
        $template_resultatglobal_dashboard = $watchdog->getConfiguration("template_resultatglobal_dashboard", $template_resultatglobal_dashboard);
        $watchdog->setConfiguration("template_resultatglobal_dashboard_Courant", $template_resultatglobal_dashboard);

        $template_resultatglobal_mobile = config::byKey('template_resultatglobal_mobile', 'watchdog', 'core::default');
        $template_resultatglobal_mobile = $watchdog->getConfiguration("template_resultatglobal_mobile", $template_resultatglobal_mobile);
        $watchdog->setConfiguration("template_resultatglobal_mobile_Courant", $template_resultatglobal_mobile);

        $template_reporting_dashboard = config::byKey('template_reporting_dashboard', 'watchdog', 'core::default');
        $template_reporting_dashboard = $watchdog->getConfiguration("template_reporting_dashboard", $template_reporting_dashboard);
        $watchdog->setConfiguration("template_reporting_dashboard_Courant", $template_reporting_dashboard);

        $template_reporting_mobile = config::byKey('template_reporting_mobile', 'watchdog', 'core::default');
        $template_reporting_mobile = $watchdog->getConfiguration("template_reporting_mobile", $template_reporting_mobile);
        $watchdog->setConfiguration("template_reporting_mobile_Courant", $template_reporting_mobile);
    }

    public function postSave()
    {

        $watchdog = $this; 
        
        // -----------------------------------------------------
        // Commande refresh
        // -----------------------------------------------------

        unset($cmdRefresh);
        $cmdRefresh = $this->getCmd(null, 'refresh');
        if (!is_object($cmdRefresh)) {
            log::add('watchdog', 'debug', '╠═══> Ajout de la commande action refresh à ' . $this->getName());
            $cmdRefresh = new watchdogCmd();
            $cmdRefresh->setName('Refresh');
            $cmdRefresh->setEqLogic_id($this->getId());
            $cmdRefresh->setType('action');
            $cmdRefresh->setSubType('other');
            $cmdRefresh->setLogicalId('refresh');
            $cmdRefresh->setIsVisible(1);
            $cmdRefresh->setDisplay('generic_type', 'GENERIC_INFO');
            $cmdRefresh->save();
        }

        // -----------------------------------------------------
        // Commande Resultat Global
        // -----------------------------------------------------
        unset($cmdResultatGlobal);
        $cmdResultatGlobal = $this->getCmd(null, "resultatglobal");
        if (!is_object($cmdResultatGlobal)) {
            log::add('watchdog', 'debug', '╠═══> Ajout de la commande info resultatglobal à ' . $this->getName());
            $cmdResultatGlobal = new watchdogCmd();
            $cmdResultatGlobal->setType('info');
            $cmdResultatGlobal->setLogicalId("resultatglobal");
            $cmdResultatGlobal->setSubType('binary');
            $cmdResultatGlobal->setEqLogic_id($this->getId());
            $cmdResultatGlobal->setName("Résultat Global");
            $cmdResultatGlobal->setIsVisible(1);
            $cmdResultatGlobal->save();
        }

        // ne gère pas le Resultat Global si pas de condition ET / OU
        $typeControl = $this->getConfiguration('typeControl');
        if ($typeControl <> 'ET' && $typeControl <> 'OU') {
            if ($cmdResultatGlobal->getIsVisible() <> '1') {
                $cmdResultatGlobal->setIsVisible(0);
                $cmdResultatGlobal->save();
            }
        } else {
            $update_cmdResultatGlobal = false; // pour savoir si il faut faire une MAJ

            // pas utile d'afficher le nom de la commande
            if ($cmdResultatGlobal->getDisplay("showNameOndashboard", '1') == '1') {
                $cmdResultatGlobal->setDisplay("showNameOndashboard", '0');
                $update_cmdResultatGlobal = true;
            }

            if ($cmdResultatGlobal->getDisplay("showNameOnmobile", '1') == '1') {
                $cmdResultatGlobal->setDisplay("showNameOnmobile", '0');
                $update_cmdResultatGlobal = true;
            }

            // applique les templates à résultat global
            $template_resultatglobal_dashboard = $this->getConfiguration("template_resultatglobal_dashboard_Courant");
            if ($template_resultatglobal_dashboard <> $cmdResultatGlobal->getTemplate("dashboard", "core::default")) {
                $cmdResultatGlobal->setTemplate("dashboard", $template_resultatglobal_dashboard);
                $update_cmdResultatGlobal = true;
            }

            $template_resultatglobal_mobile = $this->getConfiguration("template_resultatglobal_mobile_Courant");
            if ($template_resultatglobal_mobile <> $cmdResultatGlobal->getTemplate("mobile", "core::default")) {
                $cmdResultatGlobal->setTemplate("mobile", $template_resultatglobal_mobile);
                $update_cmdResultatGlobal = true;
            }

            // Applique l'affichage inversé si nécessaire
            $ResultatGlobalOK = $this->getConfiguration("ResultatGlobalOK_Courant", "1");
            if ($ResultatGlobalOK == '1') {
                $invertBinary = '0';
            } else {
                $invertBinary = '1';
            }
            $invertBinaryCurrent = $cmdResultatGlobal->getDisplay("invertBinary", '0');
            if ($invertBinary <> $invertBinaryCurrent) {
                $cmdResultatGlobal->setDisplay("invertBinary", $invertBinary);
                $update_cmdResultatGlobal = true;
            }

            if ($update_cmdResultatGlobal == true)
                $cmdResultatGlobal->save();

            // -----------------------------------------------------
            // Reporting des watchdogs
            // -----------------------------------------------------
            $VirtualReportName = $this->getConfiguration("VirtualReport_Courant");

            if (trim($VirtualReportName) <> '') {

                $ReportOnlyNonOK = $this->getConfiguration("ReportOnlyNonOK_Courant");

                $whatchdog_ok = 1; // indique quelle est la valeur de ResultatGlobal pour laquelle le whatchdog est OK
                if ($this->getConfiguration("invertBinary", '0') == '1')
                    $whatchdog_ok = '0';

                unset($eqVirtualReport);
                try {
                    $eqVirtualReport = eqLogic::byString($VirtualReportName);
                } catch (Exception $e) {
                    log::add('watchdog', 'warning', '╠═══> Virtuel nécessaire pour le reporting non défini ' . $VirtualReportName);
                }
                if (is_object($eqVirtualReport)) {

                    $VirtualReportName = $eqVirtualReport->getHumanName();
                    $eqVirtualReportId = $eqVirtualReport->getId();

                    // récupère la commande correspondant à l'Id du watchdog
                    $eqLogicId = $this->getId();
                    unset($cmdReportWatchdog);
                    $cmdReportWatchdog = cmd::byEqLogicIdAndLogicalId($eqVirtualReportId, $eqLogicId);
                    if (!is_object($cmdReportWatchdog)) {
                        // crée la commande correspondant au watchdog dans le virtuel du reporting
                        log::add('watchdog', 'debug', '╠═══> Création dans le virtuel de reporting ' . $VirtualReportName . ' de la commande correspondant au watchdog ' . $this->getName());
                        $cmdReportWatchdog = new virtualCmd();
                        $cmdReportWatchdog->setName($this->getName());
                        $cmdReportWatchdog->setEqLogic_id($eqVirtualReportId);
                        $cmdReportWatchdog->setLogicalId($eqLogicId);
                        $cmdReportWatchdog->setIsHistorized(1);
                        $cmdReportWatchdog->setConfiguration('historizeMode', 'none');
                        $cmdReportWatchdog->setConfiguration('historyPurge', '-7 day');
                        $cmdReportWatchdog->setConfiguration('repeatEventManagement', 'never');
                        $cmdReportWatchdog->setType('info');
                        $cmdReportWatchdog->setUnite('');
                        $cmdReportWatchdog->setSubType('binary');
                        $cmdReportWatchdog->setDisplay('generic_type', 'GENERIC_INFO');
                        $cmdReportWatchdog->setDisplay('graphType', 'column');
                        $cmdReportWatchdog->save();
                    }

                    $update_cmdReportWatchdog = false; // pour savoir si il faut faire une MAJ

                    // applique les templates à la commande info dans le virtuel
                    $template_reporting_dashboard = $this->getConfiguration("template_reporting_dashboard_Courant");
                    if ($template_reporting_dashboard <> $cmdReportWatchdog->getTemplate("dashboard", "core::default")) {
                        $cmdReportWatchdog->setTemplate("dashboard", $template_reporting_dashboard);
                        $update_cmdReportWatchdog = true;
                    }

                    $template_reporting_mobile = $this->getConfiguration("template_reporting_mobile_Courant");
                    if ($template_reporting_mobile <> $cmdReportWatchdog->getTemplate("mobile", "core::default")) {
                        $cmdReportWatchdog->setTemplate("mobile", $template_reporting_mobile);
                        $update_cmdReportWatchdog = true;
                    }

                    // aligne l affichage inversé sur celui du Resultat Global
                    $invertBinaryResultatGlobal = $cmdResultatGlobal->getDisplay("invertBinary", '0');
                    $invertBinary = $cmdReportWatchdog->getDisplay("invertBinary", '0');
                    if ($invertBinary <> $invertBinaryResultatGlobal) {
                        $cmdReportWatchdog->setDisplay("invertBinary", $invertBinaryResultatGlobal);
                        $update_cmdReportWatchdog = true;
                    }

                    // récupère le résultat de la commande Resultat global et MAJ la valeur dans le reporting
                    $WatchdogResultat = $cmdResultatGlobal->execCmd();
                    if ($WatchdogResultat <> $cmdReportWatchdog->execCmd()) {
                        $cmdReportWatchdog->event($WatchdogResultat);
                        $update_cmdReportWatchdog = true;
                    }

                    // affiche le controle quelque soit le résultat
                    if ($ReportOnlyNonOK <> '1' && $cmdReportWatchdog->getIsVisible() == '0') {
                        $cmdReportWatchdog->setIsVisible(1);
                        $update_cmdReportWatchdog = true;
                    } else {
                        // récupère le résultat de la commande Resultat global
                        $WatchdogResultat = $cmdResultatGlobal->execCmd();
                        // Affiche ou non la commande associée au watchdog en fonction de l'état du watchdog
                        if ($WatchdogResultat == $whatchdog_ok && $cmdReportWatchdog->getIsVisible() == 1) {
                            $cmdReportWatchdog->setIsVisible(0);
                            $update_cmdReportWatchdog = true;
                        }
                        if ($WatchdogResultat != $whatchdog_ok && $cmdReportWatchdog->getIsVisible() == 0) {
                            $cmdReportWatchdog->setIsVisible(1);
                            $update_cmdReportWatchdog = true;
                        }
                    }

                    if ($update_cmdReportWatchdog == true) {
                        $cmdReportWatchdog->save();
                    }
                }
            }
        }
        log::add('watchdog', 'info', "└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
    }

    public function lancerControle()
    {

        log::add('watchdog', 'debug', "╠════> Avant de lancer le contrôle on lance les actions d'avant contrôle (s'il y en a).");

        $watchdog = $this;
        $watchdogID = $watchdog->getId();

        foreach ($watchdog->getConfiguration("watchdogAction") as $action) {

            $options = [];
            if (isset($action['options'])) $options = $action['options'];
            if (($action['actionType'] == "Avant") && $options['enable'] == '1') {
                // On va remplacer les variables dans tous les champs du array "options"
                foreach ($options as $key => $option) {
                    $option = str_ireplace("#controlname#", $watchdog->getName(), $option);
                    $option = $watchdog->remplace_parametres($option);
                    $options[$key] = $option;
                }
                $commande_action = $watchdog->remplace_parametres($action['cmd']);  // remplace les paramètres dans la commande
                log::add('watchdog', 'debug', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                if ($options['log'] == '1') {
                    log::add('watchdog_' . $watchdogID, 'info', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                }
                try {
                    scenarioExpression::createAndExec('action', $commande_action, $options);
                } catch (Exception $e) {
                    log::add('watchdog', 'error', __('function lancerControle : Erreur lors de l\'éxecution de ', __FILE__) . $commande_action . __('. Détails : ', __FILE__) . $e->getMessage());
                }
            }
        }

        log::add('watchdog', 'debug', '╠════> On lance les contrôles :');

        foreach ($watchdog->getCmd('info') as $controle) {
            // On sauvegarde le dernier résultat dans AvantdernierResultat
            $controle->setConfiguration('resultatAvant', $controle->getConfiguration('resultat'));
            if ($controle->getLogicalId() != "resultatglobal") { // on ignore resultatglobal
                $controle->save();     // le calcul est effectué dans la procédure presave de la classe watchdogcmd
            }
        }

        // On va faire le test GLOBAL
        $typeControl = $watchdog->getConfiguration('typeControl');
        if ($typeControl != "") {    // Que si en OU ou en ET
            log::add('watchdog', 'debug', '╠═╦══> Calcul du résultat Global :');

            $typeAction = $watchdog->getConfiguration('typeAction');

            $traceleCalcul = "Calcul : Init à ";

            if ($typeControl == "ET") {
                $leResultatdelaBoucle = true;
            } else {
                $leResultatdelaBoucle = false;
            }

            //On évalue toutes les commandes du watchdog pour calculer le résultat global des tests
            foreach ($watchdog->getCmd('info') as $controle) {
                if ($controle->getLogicalId() != "resultatglobal") { // on ignore resultatglobal
                    $leResultat = $controle->getConfiguration('resultat');
                    log::add('watchdog', 'debug', '║ ╚═══>[' . $typeControl . "] " . $leResultat . ' (' . $controle->getName() . ')');

                    if ($leResultat == "True" || $leResultat == "False") {
                        //Résultat valide, on continue le test
                        if ($typeControl == "ET") {
                            if ($leResultat == "False")    $leResultatdelaBoucle = false; // On est sur une fonction ET
                        } else {
                            if ($leResultat == "True")    $leResultatdelaBoucle = true; // On est sur une fonction OU
                        }
                    }
                }
            }

            if ($leResultatdelaBoucle) $leResultatdelaBoucle = 'True';
            else $leResultatdelaBoucle = 'False';
            log::add('watchdog', 'debug', "║ ╚═══>[==] " . $leResultatdelaBoucle);

            //---------------------------------------------------
            // On va chercher si on est en SAUVEGARDE ou en CRON
            //---------------------------------------------------

            $resultatPrecedent = $watchdog->getConfiguration('dernierEtat');
            $watchdog->setConfiguration('dernierEtat', $leResultatdelaBoucle);
            //Pour que le resultat soit accessible dans une commande info, on copie dernierEtat dans resultatglobal
            $watchdog->checkAndUpdateCmd('resultatglobal', $leResultatdelaBoucle);


            if ($typeAction == 'ALL') {
                $resultatPrecedent = "";
                log::add('watchdog', 'debug', 'Mode action à chaque contrôle : Désactivation du Résultat Précédent');
            }

            // On est ici sur le résultat général des controles, on ne fait rien si on est en mode "Actions sur chaque controle indépendamment"
            $typeControl = $watchdog->getConfiguration('typeControl');
            if ($typeControl != "") {
                if ($resultatPrecedent != $leResultatdelaBoucle) {
                    log::add('watchdog', 'debug', '╠═════> Bilan global : [Résultat Précédent=' . $resultatPrecedent . '] [Nouveau Résultat=' . $leResultatdelaBoucle . ']-> On lance les actions correspondant au changement du resultat global');
                    $watchdog::LanceActionsResultatGlobal($leResultatdelaBoucle);
                } else {
                    log::add('watchdog', 'debug', '╠═════> Bilan global : [Résultat Précédent=' . $resultatPrecedent . '] [Nouveau Résultat=' . $leResultatdelaBoucle . ']-> On ne fait rien');
                }
            }
        }
    }

    public function LanceActionsResultatGlobal($_ResultatGlobal)
    {
        // La fonction LanceActionsResultatGlobal ne doit être appellée que sur le résultat général des controles, on ne fait rien si on est en mode "Actions sur chaque controle indépendamment"
        $watchdog = $this;
        $watchdogID = $watchdog->getId();

        log::add('watchdog', 'debug', '╠═════> On lance les actions qui correspondent au passage de [' . $watchdog->getName() . '] à ' . $_ResultatGlobal);
        foreach ($watchdog->getConfiguration("watchdogAction") as $action) {
            $options = [];
            if (isset($action['options'])) $options = $action['options'];
            if (($action['actionType'] == $_ResultatGlobal) && $options['enable'] == '1') {

                // On va remplacer les variables dans tous les champs du array "options"
                foreach ($options as $key => $option) {
                    $option = $watchdog->remplace_parametres($option, $key);
                    $options[$key] = $option;
                }
                $commande_action = $watchdog->remplace_parametres($action['cmd']);  // remplace les paramètres dans la commande
                log::add('watchdog', 'debug', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                if ($options['log'] == '1') {
                    log::add('watchdog_' . $watchdogID, 'info', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                }
                try {
                    scenarioExpression::createAndExec('action', $commande_action, $options);
                } catch (Exception $e) {
                    log::add('watchdog', 'error', __('function LanceActionsResultatGlobal : Erreur lors de l\'éxecution de ', __FILE__) . $commande_action . __('. Détails : ', __FILE__) . $e->getMessage());
                }
            }
        }
    }
    // Lancement des watchdogs éligibles (selon les paramètres du CRON)
    public static function update()
    {
        foreach (self::byType('watchdog') as $watchdog) {
            $autorefresh = $watchdog->getConfiguration('autorefresh');
            if ($watchdog->getIsEnable() == 1 && $autorefresh != '') {
                try {
                    $c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                    if ($c->isDue()) {
                        $watchdog->whatchdog_Update();
                    }
                } catch (Exception $exc) {
                    log::add('watchdog', 'error', __('Expression cron non valide pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $autorefresh);
                }
            }
        }
    }

    // Exécution des controles et actions d'un watchdog suite à fonction update ou refresh
    public function whatchdog_Update()
    {
        $watchdog = $this;
        try {
            $watchdog->setConfiguration('avantDernierLancement', $watchdog->getConfiguration('dernierLancement'));
            $watchdog->setConfiguration('dernierLancement', 'PRECRON ' . date("d.m.Y") . " " . date("H:i:s")); // PRECON c'est pour signaler que le CRON va etre sauvegarder

            log::add('watchdog', 'info', '╔══════════════════════[Lancement du Watchdog ' . $watchdog->getName() . ']════════════════════════════════════════════════════════════════════════════');
            $watchdog->lancerControle();
            log::add('watchdog', 'info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");
        } catch (Exception $exc) {
            log::add('watchdog', 'error', __('Erreur pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $exc->getMessage());
        }
        $watchdog->save();
    }
    // Remplace les parametres dans les expressions et options
    public function remplace_parametres($_string, $_key = '')
    {
        // ignore les options liés au lancement du scenario
        if (strpos(';enable;background;log;',  $_key . ';') > 0)
            return $_string;

        $watchdog = $this;

        // remplace title par le nom du watchdog
        $_string = str_ireplace("#title#", $watchdog->getName(), $_string);

        // Remplace les valeurs de tempo 1 2 et 3
        $_string = str_ireplace("#tempo1#", trim($watchdog->getConfiguration('tempo1', '')), $_string);
        $_string = str_ireplace("#tempo2#", trim($watchdog->getConfiguration('tempo2', '')), $_string);
        $_string = str_ireplace("#tempo3#", trim($watchdog->getConfiguration('tempo3', '')), $_string);
        //---------------------------------------------------

        // Remplacer les valeurs de eqlogic 1 2 et 3
        // on doit décaler d un caractere car le résultat est 0 (soit false) si #equip1# est en tête de la chaine
        if (stripos(' ' . $_string, "#equip1#") <> false) {
            unset($eqlogic1);
            $eqlogic1Name = trim($watchdog->getConfiguration('equip1'), '');
            if ($eqlogic1Name != '') {
                try {
                    $eqlogic1 = eqLogic::byString($eqlogic1Name);
                } catch (Exception $e) {
                    log::add('watchdog', 'warning', '╠════' . $watchdog->getName() . ' ════> parametre #equip1# non défini ' . $eqlogic1Name);
                }
                if (is_object($eqlogic1)) {
                    $eqlogic1Name = $eqlogic1->getHumanName();
                    $_string = str_ireplace("#equip1#", $eqlogic1Name, $_string);
                }
            }
        }
        if (stripos(' ' . $_string, "#equip2#") <> false) {
            unset($eqlogic2);
            $eqlogic2Name = trim($watchdog->getConfiguration('equip2'), '');
            if ($eqlogic2Name != '') {
                try {
                    $eqlogic2 = eqLogic::byString($eqlogic2Name);
                } catch (Exception $e) {
                    log::add('watchdog', 'warning', '╠════' . $watchdog->getName() . ' ════> parametre #equip2# non défini ' . $eqlogic2Name);
                }
                if (is_object($eqlogic2)) {
                    $eqlogic2Name = $eqlogic2->getHumanName();
                    $_string = str_ireplace("#equip2#", $eqlogic2Name, $_string);
                }
            }
        }
        if (stripos(' ' . $_string, "#equip3#") <> false) {
            unset($eqlogic3);
            $eqlogic3Name = trim($watchdog->getConfiguration('equip3'), '');
            if ($eqlogic3Name != '') {
                try {
                    $eqlogic3 = eqLogic::byString($eqlogic3Name);
                } catch (Exception $e) {
                    log::add('watchdog', 'warning', '╠════' . $watchdog->getName() . ' ════> parametre #equip3# non défini ' . $eqlogic3Name);
                }
                if (is_object($eqlogic3)) {
                    $eqlogic3Name = $eqlogic3->getHumanName();
                    $_string = str_ireplace("#equip3#", $eqlogic3Name, $_string);
                }
            }
        }

        return $_string;
    }
}

class watchdogCmd extends cmd
{

    // Lance les actions de l'équipement
    public function LanceActionSurChaqueControleIndependamment($resultat)
    {        
        $condition = $this;  
        $watchdog = $condition->getEqLogic();
        $typeControl = $watchdog->getConfiguration('typeControl');
        $watchdogID = $watchdog->getId();

        // La fonction est appellée sur le résultat général des controles, on ne fait rien si on n'est pas en mode "Actions sur chaque controle indépendamment"
        if ($typeControl == "") {

            log::add('watchdog', 'debug', '╠═════> On lance les actions qui correspondent au passage de [' . $condition->getName() . '] à ' . $resultat);

            if ($watchdog->getConfiguration('logspecifique'))
                log::add('watchdog_' . $watchdogID, 'info', '╔══════════════════════[' . $condition->getName() . ' est passé à ' . $resultat . ']════════════════════════════════════════════════════════════════════════════');

            foreach ($watchdog->getConfiguration("watchdogAction") as $action) {

                $options = [];
                if (isset($action['options'])) $options = $action['options'];
                if (($action['actionType'] == $resultat) && $options['enable'] == '1') {

                    // On va remplacer les variables dans tous les champs du array "options"
                    foreach ($options as $key => $option) {
                        $option = str_ireplace("#controlname#", $this->getName(), $option);
                        $option = $watchdog->remplace_parametres($option, $key);  // remplace les parametres utilisés dans les commandes action
                        $options[$key] = $option;
                    }
                    $commande_action = $watchdog->remplace_parametres($action['cmd']);  // remplace les paramètres dans la commande

                    log::add('watchdog', 'debug', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                    if ($options['log'] == '1') {
                        log::add('watchdog_' . $watchdogID, 'info', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                    }
                    try {
                        scenarioExpression::createAndExec('action', $commande_action, $options);
                    } catch (Exception $e) {
                        log::add('watchdog', 'error', __('function LanceActionSurChaqueControleIndependamment : Erreur lors de l\'éxecution de ', __FILE__) . $commande_action . __('. Détails : ', __FILE__) . $e->getMessage());
                    }
                }
            }
            if ($watchdog->getConfiguration('logspecifique'))
                log::add('watchdog_' . $watchdogID, 'info', '╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════');
        }
    }
    // c'est ici que sont effectués les controles
    // la commande info contient les éléments du controle
    public function preSave()
    {
        if ($this->getType() == 'action') return; //On ne fait pas le test si c'est une Commande Action		
        if ($this->getLogicalId() == 'resultatglobal') return; //On ne fait pas le test si c'est la commande 	resultatglobal	
        
        $condition = $this;  
        $watchdog = $this->getEqLogic();
  
        log::add('watchdog', 'info', '║ ┌──────────────────────[Contrôle ' . $watchdog->getName() . ']────────────────────────────────────────────────────────────────────────────────────');
                // On va chercher si on est en SAUVEGARDE ou en CRON
        $dernierLancement = $watchdog->getConfiguration('dernierLancement');
        $dernierLancement = substr($dernierLancement, 0, 4);

        $resultatPrecedent = $condition->getConfiguration('resultat');

        log::add('watchdog', 'debug', '║ │ ╠═╦═>     Execution du contrôle [' . $condition->getName() . ']');
        log::add('watchdog', 'debug', '║ │ ║ ╚═╦═>   ' . jeedom::toHumanReadable($condition->getConfiguration('controle')));
        $resultat = self::TesteCondition($condition->getConfiguration('controle'));
        log::add('watchdog', 'debug', '║ │ ║   ╚═══> Resultat : ' . $resultat);

        $_string = $condition->getConfiguration('controle');

        if ($resultatPrecedent != $resultat) {
            $condition->setConfiguration('resultat', $resultat);

            // Si le résultat a changé, il faut actualiser le calcul du résultat global, pour cela, on utilise la variable cmd.configuration.aChange qui traitera le calcul dans postSave
            $condition->setConfiguration('aChange', true);
            //On ne va lancer les actions que si on est en mode CRON/REFRESH et pas si on est en mode SAVE
            // et uniquement si on est en mode "Actions sur chaque controle indépendamment"
            if (($dernierLancement == "CRON") || ($dernierLancement == "PREC"))
                $condition->LanceActionSurChaqueControleIndependamment($resultat);
        }
    }

    public function TesteCondition($_string)
    {
        $scenario = null;        
        $condition = $this;  
        $watchdog = $this->getEqLogic();

        // remplace les parametres
        $_string = $watchdog->remplace_parametres($_string);
        $_string = str_replace("#internalAddr#", '"' . config::byKey('internalAddr') . '"', $_string);  // généré par l'assistant sur controle IP

        $fromHumanReadable = jeedom::fromHumanReadable($_string);
        $toHumanReadable = jeedom::toHumanReadable($_string);     
        log::add('watchdog', 'debug', '║ │ ║ ╚═╦═>   ' . $toHumanReadable);

        $condition->setConfiguration('calcul', scenarioExpression::setTags($fromHumanReadable));  // stocke les valeurs du calcul

        $return = evaluate(scenarioExpression::setTags($fromHumanReadable, $scenario, true)); // apparemment, setTags permet de récupérer les valeurs des variables dans la formule
        if (is_bool($return)) {
            if ($return) $return = 'True';
            else $return = 'False';
        } else {
            log::add('watchdog', 'warning', '║ │ ║ ╚═╦═══> Problème avec l\'expresion:    ' . $toHumanReadable);
            $return = $toHumanReadable; // si erreur, renvoie l expression soumise afin de pouvoir identifier le problème
        }

        return $return;
    }

    public function postSave()
    {
        if ($this->getType() == 'action') return; //On ne fait pas le test si c'est une Commande Action		
        if ($this->getLogicalId() == 'resultatglobal') return; //On ne fait pas le test si c'est la commande 	resultatglobal	

        $condition = $this;  
        if ($condition->getConfiguration('aChange')) {
            // Cette boucle est déclenchée quand le résultat du controle a changé, il faut ainsi relancer le save du resultat global
            $condition->setConfiguration('aChange', false);
            $condition->save();
            $condition->getEqLogic()->save(); //enregistre l'équipement entier (et donc le resultat global des controles)
        }

        log::add('watchdog', 'info', "║ └──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
    }


    public function dontRemoveCmd()
    {
        if ($this->getLogicalId() == 'resultatglobal' or $this->getLogicalId() == 'refresh') {
            return true;
        } else {
            return false;
        }
    }

    public function execute($_options = array())
    {
        // Refresh du watchdog
        $watchdog = $this->getEqLogic();

        if (!is_object($watchdog) || $watchdog->getIsEnable() != 1) {
            throw new \Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }

        if ($this->getLogicalId() == 'refresh') {
            log::add('watchdog', 'info', '┌──────────────────────[Refresh de ' . $watchdog->getName() . ']────────────────────────────────────────────────────────────────────────────────────');
            log::add('watchdog', 'info', "└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
            $watchdog->whatchdog_Update();
            return true;
        }
    }
}
