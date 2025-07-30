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

    public function remove()
    {
        $watchdog = $this;
        $_key = 'log::level::watchdog_' . $watchdog->getId();
        if (config::byKey($_key) != '')
            config::remove($_key);
        return parent::remove();
    }
    public function preSave()
    {
        $watchdog = $this;
        log::add('watchdog', 'info', '┌──────────────────────[Sauvegarde du Watchdog ' . $watchdog->getName() . ']────────────────────────────────────────────────────────────────────────────────────');

        // -----------------------------------------------------
        // Niveau de log pour les logs spécifiques
        // il est nécessaire de le définir sinon les entrées dans la log spécifique risquent d être ignorées 
        // si la config par défaut de jeedon n'est pas sur info
        // -----------------------------------------------------
        $_key = 'log::level::watchdog_' . $watchdog->getId();
        if (($watchdog->getConfiguration('logspecifique', '0') != '0')  && $watchdog->getIsEnable() == 1) {
            if (config::byKey($_key) == '') {
                $loglevel =  array("100" => "1", "200" => "0", "300" => "0", "400" => "0", "1000" => "0", "default" => "0"); // niveau debug pour afficher tous les niveaux de messages
                log::add('watchdog', 'info', 'Creation de la configuration ' . $_key . ' avec "' . print_r($loglevel, true) . '"');
                config::save($_key, $loglevel);
            }
        }
        if (($watchdog->getConfiguration('logspecifique', '0') == '0')  || $watchdog->getIsEnable() == 0) {
            if (config::byKey($_key) != '') { {
                    log::add('watchdog', 'info', 'Suppression de la configuration ' . $_key . ' avec "' . print_r($loglevel, true) . '"');
                    config::remove($_key);
                }
            }
        }

        // -----------------------------------------------------
        // Niveau de log pour les logs spécifiques sur les Actions
        // -----------------------------------------------------
        $_key = 'log::level::watchdog_actions';
        if (config::byKey($_key) == '') {
            $loglevel =  array("100" => "1", "200" => "0", "300" => "0", "400" => "0", "1000" => "0", "default" => "0"); // niveau debug pour afficher tous les niveaux de messages
            log::add('watchdog', 'info', 'Creation de la configuration ' . $_key . ' avec "' . print_r($loglevel, true) . '"');
            config::save($_key, $loglevel);
        }


        if ((substr($watchdog->getConfiguration('dernierLancement'), 0, 7)) == "PRECRON") {  // PRECON c'est pour signaler que le CRON va etre sauvegarder
            $watchdog->setConfiguration('dernierLancement', 'CRON ' . date("d.m.Y") . " " . date("H:i:s"));
        } else {
            $watchdog->setConfiguration('dernierLancement', 'SAVE ' . date("d.m.Y") . " " . date("H:i:s"));
        }

        // Resultat OK si égal au parametre
        $ResultatOK = config::byKey('ResultatOK', 'watchdog', '1');
        $ResultatOK = $watchdog->getConfiguration("ResultatOK", $ResultatOK);
        $watchdog->setConfiguration("ResultatOK_Courant", $ResultatOK);

        // Historique demandé ou non 
        $ResultatHistory = config::byKey('ResultatHistory', 'watchdog', '');
        $ResultatHistory = $watchdog->getConfiguration("ResultatHistory", $ResultatHistory);
        $watchdog->setConfiguration("ResultatHistory_Courant", $ResultatHistory);

        // affiche ou non seulement les conditions non OK dans la tuile 
        // ne concerne pas les modes ET / OU pour lesquels le résultat global est toujours affiché
        $DisplayOnlyConditionNonOK = config::byKey('DisplayOnlyConditionNonOK', 'watchdog', '1');
        $DisplayOnlyConditionNonOK = $watchdog->getConfiguration("DisplayOnlyConditionNonOK", $DisplayOnlyConditionNonOK);
        $watchdog->setConfiguration("DisplayOnlyConditionNonOK_Courant", $DisplayOnlyConditionNonOK);

        $template_resultat_dashboard = config::byKey('template_resultat_dashboard', 'watchdog', 'core::default');
        $template_resultat_dashboard = $watchdog->getConfiguration("template_resultat_dashboard", $template_resultat_dashboard);
        $watchdog->setConfiguration("template_resultat_dashboard_Courant", $template_resultat_dashboard);

        $template_resultat_mobile = config::byKey('template_resultat_mobile', 'watchdog', 'core::default');
        $template_resultat_mobile = $watchdog->getConfiguration("template_resultat_mobile", $template_resultat_mobile);
        $watchdog->setConfiguration("template_resultat_mobile_Courant", $template_resultat_mobile);

        $VirtualReport = trim($watchdog->getConfiguration("VirtualReport", ''));
        if (trim($VirtualReport) == '')
            $VirtualReport = trim(config::byKey('VirtualReport', 'watchdog', ''));
        if (trim($VirtualReport) == '/')
            $VirtualReport = '';
        $watchdog->setConfiguration("VirtualReport_Courant", $VirtualReport);

        $ReportingHistory = config::byKey('ReportingHistory', 'watchdog', '');
        $ReportingHistory = $watchdog->getConfiguration("ReportingHistory", $ReportingHistory);
        $watchdog->setConfiguration("ReportingHistory_Courant", $ReportingHistory);

        // affiche ou non seulement les résultats globaux/conditions non OK dans le virtuel du reporting 
        $DisplayOnlyReportingNonOK = config::byKey('DisplayOnlyReportingNonOK', 'watchdog', '1');
        $DisplayOnlyReportingNonOK = $watchdog->getConfiguration("DisplayOnlyReportingNonOK", $DisplayOnlyReportingNonOK);
        $watchdog->setConfiguration("DisplayOnlyReportingNonOK_Courant", $DisplayOnlyReportingNonOK);

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
        $cmdRefresh = $watchdog->getCmd(null, 'refresh');
        if (!is_object($cmdRefresh)) {
            log::add('watchdog', 'debug', '╠═══> Ajout de la commande action refresh à ' . $watchdog->getName());
            $cmdRefresh = new watchdogCmd();
            $cmdRefresh->setName('Refresh');
            $cmdRefresh->setEqLogic_id($watchdog->getId());
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
        $cmdResultatGlobal = $watchdog->getCmd(null, "resultatglobal");
        if (!is_object($cmdResultatGlobal)) {
            log::add('watchdog', 'debug', '╠═══> Ajout de la commande info resultatglobal à ' . $watchdog->getName());
            $cmdResultatGlobal = new watchdogCmd();
            $cmdResultatGlobal->setType('info');
            $cmdResultatGlobal->setLogicalId("resultatglobal");
            $cmdResultatGlobal->setSubType('binary');
            $cmdResultatGlobal->setEqLogic_id($watchdog->getId());
            $cmdResultatGlobal->setName("Résultat Global");
            $cmdResultatGlobal->setIsVisible(1);
            $cmdResultatGlobal->save();
        }

        // ne gère pas le Resultat Global si pas de condition ET / OU
        $typeControl = $watchdog->getConfiguration('typeControl');
        if ($typeControl == '') {
            if ($cmdResultatGlobal->getIsVisible() == '1') {
                $cmdResultatGlobal->setIsVisible(0);
                $cmdResultatGlobal->save();
            }
        } else {

            $update_cmdResultatGlobal = false; // pour savoir si il faut faire une MAJ

            // affiche le résultat global dans le widget
            if ($cmdResultatGlobal->getIsVisible() == '0') {
                $update_cmdResultatGlobal = true;
                $cmdResultatGlobal->setIsVisible(1);
            }

            // pas utile d'afficher le nom de la commande
            if ($cmdResultatGlobal->getDisplay("showNameOndashboard", '1') == '1') {
                $cmdResultatGlobal->setDisplay("showNameOndashboard", '0');
                $update_cmdResultatGlobal = true;
            }

            if ($cmdResultatGlobal->getDisplay("showNameOnmobile", '1') == '1') {
                $cmdResultatGlobal->setDisplay("showNameOnmobile", '0');
                $update_cmdResultatGlobal = true;
            }

            // historique 
            $ResultatHistory = $watchdog->getConfiguration("ResultatHistory_Courant", '');
            if ($ResultatHistory <> '') {  // historique demandé
                if ($cmdResultatGlobal->getIsHistorized() == 0) {
                    $cmdResultatGlobal->setIsHistorized(1);
                    $update_cmdResultatGlobal = true;
                }
                $historyPurge = $ResultatHistory;
                if ($historyPurge == '/') $historyPurge = '';

                if ($historyPurge != $cmdResultatGlobal->getConfiguration('historyPurge', '')) {
                    $cmdResultatGlobal->setConfiguration('historyPurge', $historyPurge);
                    $update_cmdResultatGlobal = true;
                }
            } else { // pas d historique demandé
                if ($cmdResultatGlobal->getIsHistorized() == 1) {
                    $cmdResultatGlobal->setIsHistorized(0);
                    $update_cmdResultatGlobal = true;
                }
            }

            $cmdResultatGlobal->setIsHistorized(1);
            $cmdResultatGlobal->setConfiguration('historizeMode', 'none');
            $cmdResultatGlobal->setConfiguration('historyPurge', '-7 day');

            // applique les templates à résultat global
            $template_resultat_dashboard = $watchdog->getConfiguration("template_resultat_dashboard_Courant");
            if ($template_resultat_dashboard <> $cmdResultatGlobal->getTemplate("dashboard", "core::default")) {
                $cmdResultatGlobal->setTemplate("dashboard", $template_resultat_dashboard);
                $update_cmdResultatGlobal = true;
            }

            $template_resultat_mobile = $watchdog->getConfiguration("template_resultat_mobile_Courant");
            if ($template_resultat_mobile <> $cmdResultatGlobal->getTemplate("mobile", "core::default")) {
                $cmdResultatGlobal->setTemplate("mobile", $template_resultat_mobile);
                $update_cmdResultatGlobal = true;
            }

            // Applique l'affichage inversé si nécessaire
            $ResultatOK = $watchdog->getConfiguration("ResultatOK_Courant", "1");
            if ($ResultatOK == '1') {
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
        }
        // on fait la mise à jour du reporting dans tous les cas pour forcer la mise à jour de l'affichage si on change de type de controle
        $cmdResultatGlobal->report_cmd();

        log::add('watchdog', 'info', "└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
    }

    // Lancement des watchdogs éligibles (selon les paramètres du CRON)
    public static function update()  // procédure appelée par le CRON
    {
        $cron_update = false;  // pour afficher les messages d entete une seule fois

        foreach (self::byType('watchdog') as $watchdog) {
            $autorefresh = $watchdog->getConfiguration('autorefresh');
            if ($watchdog->getIsEnable() == 1 && $autorefresh != '') {
                try {
                    $c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                    if ($c->isDue()) {
                        if ($cron_update == false) {
                            $cron_update = true;
                            log::add('watchdog', 'info', '╔══════════════════════[Lancement des Watchdogs par le CRON ]═══════════════════════════════════════════');
                            log::add('watchdog', 'info', "╚═══════════════════════════════════════════════════════════════════════════════════════════════════════");
                        }
                        $watchdog->whatchdog_Update();
                    }
                } catch (Exception $exc) {
                    log::add('watchdog', 'error', __('Expression cron non valide pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $autorefresh);
                }
            }
        }
        if ($cron_update == true) {
            log::add('watchdog', 'info', '╔══════════════════════[Fin du lancement des Watchdogs par le CRON ]════════════════════════════════════');
            log::add('watchdog', 'info', "╚═══════════════════════════════════════════════════════════════════════════════════════════════════════");
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

            if ($watchdog->getConfiguration('logspecifique', '') == '1') {
                log::add('watchdog', 'info', '║                     Enregistrement de la log de ce watchdog dans watchdog_' . $watchdog->getId());
                $watchdog->log('info', '╔══════════════════════[Lancement du Watchdog ' . $watchdog->getName() . ']════════════════════════════════════════════════════════════════════════════');
            }
            $watchdog->lancerControle();
            log::add('watchdog', 'info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");
            if ($watchdog->getConfiguration('logspecifique', '') == '1') {
                $watchdog->log('info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");
            }
        } catch (Exception $exc) {
            log::add('watchdog', 'error', __('Erreur pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $exc->getMessage());
        }
        $watchdog->save();
    }
    public function lancerControle()
    {

        $watchdog = $this;
        $watchdogID = $watchdog->getId();

        $watchdog->log('debug', "╠════> Avant de lancer le contrôle on lance les actions d'avant contrôle (s'il y en a).");


        foreach ($watchdog->getConfiguration("watchdogAction") as $action) {

            $options = [];
            if (isset($action['options'])) $options = $action['options'];
            if (($action['actionType'] == "Avant") && $options['enable'] == '1') {
                // On va remplacer les variables dans tous les champs du array "options"
                foreach ($options as $key => $option) {
                    $option = $watchdog->remplace_parametres($option);
                    $options[$key] = $option;
                }
                $commande_action = $watchdog->remplace_parametres($action['cmd']);  // remplace les paramètres dans la commande
                $watchdog->log('debug', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                if ($options['log'] == '1') {
                    log::add('watchdog_actions', 'info', 'Watchdog "' . $watchdog->getHumanName() .  '"  Mode AVANT - Commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                }
                try {
                    scenarioExpression::createAndExec('action', $commande_action, $options);
                } catch (Exception $e) {
                    $watchdog->log('error', __('function lancerControle : Erreur lors de l\'éxecution de ', __FILE__) . $commande_action . __('. Détails : ', __FILE__) . $e->getMessage());
                    if ($options['log'] == '1') {
                        log::add('watchdog_actions', 'info', 'Watchdog "' . $watchdog->getHumanName() . '" Commande ' . jeedom::toHumanReadable($commande_action) . '" Erreur lors de l\'éxecution de ' . $commande_action . " : " . $e->getMessage());
                    }
                }
            }
        }

        $watchdog->log('debug', '╠════> On lance les contrôles :');

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
            //   $watchdog->log('debug', '╠═╦══> Calcul du résultat Global :');

            $watchdog->log('debug', '║ ─────────────────────────[ Calcul du résultat Global ]────────────────────────────────────────────────────────────────────────────────────');

            $typeAction = $watchdog->getConfiguration('typeAction');

            if ($typeControl == "ET") {
                $leResultatdelaBoucle = true;
            } else {
                $leResultatdelaBoucle = false;
            }

            //On évalue toutes les commandes du watchdog pour calculer le résultat global des tests
            foreach ($watchdog->getCmd('info') as $controle) {
                if ($controle->getLogicalId() != "resultatglobal") { // on ignore resultatglobal
                    $leResultat = $controle->getConfiguration('resultat');
                    $watchdog->log('debug', '║ ╚═══>[' . $typeControl . "] " . $leResultat . ' (contrôle "' . $controle->getName() . '")');

                    if ($leResultat == "1" || $leResultat == "0") {
                        //Résultat valide, on continue le test
                        if ($typeControl == "ET") {
                            if ($leResultat == "0")    $leResultatdelaBoucle = false; // On est sur une fonction ET
                        } else {
                            if ($leResultat == "1")    $leResultatdelaBoucle = true; // On est sur une fonction OU
                        }
                    }
                }
            }

            if ($leResultatdelaBoucle) $leResultatdelaBoucle = 'True';
            else $leResultatdelaBoucle = 'False';
            $watchdog->log('debug', "║ ╚═════>[==] " . $leResultatdelaBoucle);

            //---------------------------------------------------
            // On va chercher si on est en SAUVEGARDE ou en CRON/REFRESH
            //---------------------------------------------------

            $resultatPrecedent = $watchdog->getConfiguration('dernierEtat');
            $watchdog->setConfiguration('dernierEtat', $leResultatdelaBoucle);
            //Pour que le resultat soit accessible dans une commande info, on copie dernierEtat dans resultatglobal
            $watchdog->checkAndUpdateCmd('resultatglobal', $leResultatdelaBoucle);


            if ($typeAction == 'ALL') { // Mode "Lancer les actions même si le résultat n'a pas changé de valeur"
                $resultatPrecedent = ""; // Reset du Résultat Précédent pour forcer le lancement de l'action
            }

            // On est ici sur le résultat général des controles
            // on ne fait rien si on est en mode "Actions sur chaque controle indépendamment" car le lancement des actions est traité dans le presave
            $typeControl = $watchdog->getConfiguration('typeControl');
            if ($typeControl != "") {  // Mode ET/OU
                $watchdog->log('info', '║ ─────────────────────────[  Bilan global ]────────────────────────────────────────────────────────────────────────────────────');
                if ($resultatPrecedent != $leResultatdelaBoucle) {
                    if ($typeAction == 'ALL')
                        $watchdog->log('debug', '╠═════> Mode "Lancer les actions même si le résultat n\'a pas changé de valeur" --> On lance les actions correspondant au résultat global à ' . $leResultatdelaBoucle);
                    else
                        $watchdog->log('debug', '╠═════> [Résultat Précédent=' . $resultatPrecedent . '] [Nouveau Résultat=' . $leResultatdelaBoucle . ']-> On lance les actions correspondant au résultat global à ' . $leResultatdelaBoucle);
                    $watchdog::LanceActions($leResultatdelaBoucle);
                } else {
                    $watchdog->log('debug', '╠═════> [Résultat Précédent=' . $resultatPrecedent . '] [Nouveau Résultat=' . $leResultatdelaBoucle . ']-> Pas de changement du résultat global -> On ne fait rien');
                }
            }
        }
    }


    // lance les actions correspondant au changement de résultat du watchdog si $condition vide ou autrement de la condition
    public function LanceActions($_resultat, $condition = '')
    {
        // La fonction LanceActions ne doit être appellée que sur le résultat général des controles, on ne fait rien si on est en mode "Actions sur chaque controle indépendamment"
        $watchdog = $this;

        if ($_resultat == '0') $_resultat = 'False';
        if ($_resultat == '1') $_resultat = 'True';


        if ($condition == '')
            $watchdog->log('debug', '╠═════> On lance les actions qui correspondent au passage de [' . $watchdog->getName() . '] à ' . $_resultat);
        else
            $watchdog->log('debug', '╠═════> On lance les actions qui correspondent au passage de [' . $condition->getName() . '] à ' . $_resultat);

        foreach ($watchdog->getConfiguration("watchdogAction") as $action) {
            $options = [];
            if (isset($action['options'])) $options = $action['options'];
            if (($action['actionType'] == $_resultat) && $options['enable'] == '1') {

                // On va remplacer les variables dans tous les champs du array "options"
                foreach ($options as $key => $option) {

                    if ($condition != '') {
                        $option = str_ireplace("#controlname#", $condition->getName(), $option);
                        // remplace _equip_ par le premier équipement référencé dans la condition
                        if ((stripos(' ' . $option, '_equip_') > 0) && ($equip <> ''))
                            $option = str_ireplace("_equip_", $equip, $option);
                    }
                    $option = $watchdog->remplace_parametres($option, $key);  // remplace les parametres utilisés dans les commandes action
                    $options[$key] = $option;
                }

                if ($condition != '') {
                    // remplace _equip_ par le premier équipement référencé dans la condition
                    if ((stripos(' ' . $commande_action, '_equip_') > 0) && ($equip <> ''))
                        $commande_action = str_ireplace("_equip_", $equip, $commande_action);
                }
                $commande_action = $watchdog->remplace_parametres($action['cmd']);  // remplace les paramètres dans la commande

                $watchdog->log('debug', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                if ($options['log'] == '1') {
                    $action_env = "";
                    if ($condition != '')
                        $action_env = '" Condition : "' .  $condition->getName() . '" Résultat Controle :' . $_resultat;
                    else
                        $action_env =  '" Résultat Global : ' . $_resultat;

                    log::add('watchdog_actions', 'info', 'Watchdog "' . $watchdog->getHumanName() . $action_env . ' Commande "' . jeedom::toHumanReadable($commande_action) . '" avec comme option(s) : "' . json_encode($options) . '"');
                }
                try {
                    scenarioExpression::createAndExec('action', $commande_action, $options);
                } catch (Exception $e) {
                    $watchdog->log('error', __('function LanceActions : Erreur lors de l\'éxecution de ', __FILE__) . $commande_action . __('. Détails : ', __FILE__) . $e->getMessage());
                    if ($options['log'] == '1') {
                        log::add('watchdog_actions', 'error', 'Watchdog "' . $watchdog->getHumanName() . '"' . $action_env . ' Erreur lors de l\'éxecution de ' . $commande_action . " : " . $e->getMessage());
                    }
                }
            }
        }
    }



    // Remplace les parametres dans les expressions et options
    public function remplace_parametres($_string, $_key = '')
    {
        // ignore les options liés au lancement des actions
        if (strpos(';enable;background;log;',  $_key . ';') > 0)
            return $_string;

        $watchdog = $this;


        // remplace l expression
        $expr = $watchdog->getConfiguration('expr', '');
        if ($expr != '') {
            $parsedCommand1 = parseFunctionCall(trim($_string));
            if ($parsedCommand1) {
                $_string = $expr;
                $i = 0;
                foreach ($parsedCommand1['arguments'] as $arg) {
                    $_string = str_ireplace("_arg" . (string)$i . "_", trim($arg), $_string);
                    $i++;
                    if ($i == 2) break;
                }
                $_string = str_ireplace("_expr_(" . "", trim($arg), $_string);
            }
        }

        // remplace title par le nom du watchdog
        $_string = str_ireplace("#title#", $watchdog->getName(), $_string);

        // Remplace les valeurs de var 1 2 et 3
        for ($i = 1; $i <= 3; $i++) {
            $_string = str_ireplace("_var_" . (string)$i, trim($watchdog->getConfiguration("var" . (string)$i, '')), $_string);
        }

        // Remplace les valeurs de tempo 1 2 et 3
        for ($i = 1; $i <= 3; $i++) {
            $_string = str_ireplace("#tempo" . (string)$i . "#", trim($watchdog->getConfiguration('tempo' . (string)$i, '')), $_string);
            $_string = str_ireplace("_tempo" . (string)$i . "_", trim($watchdog->getConfiguration('tempo' . (string)$i, '')), $_string);
        }

        // Remplacer les valeurs de eqlogic 1 2 et 3
        for ($i = 1; $i <= 3; $i++) {
            // on doit décaler d un caractere car le résultat est 0 (soit false) si _equipX_ est en tête de la chaine
            if (stripos(' ' . $_string, "_equip" . (string)$i . "_") <> false) {
                unset($eqlogic);
                $eqlogicName = trim($watchdog->getConfiguration('equip' . (string)$i), '');
                if ($eqlogicName == '') {
                    try {
                        $eqlogic = eqLogic::byString($eqlogic1Name);
                    } catch (Exception $e) {
                        $watchdog->log('warning', '╠════' . $watchdog->getName() . ' ════> parametre _equip' . (string)$i . '_ non défini ' . $eqlogicName);
                    }
                    if (is_object($eqlogic)) {
                        $eqlogicName = $eqlogic->getHumanName();
                        $_string = str_ireplace("_equip" . (string)$i . "_", $eqlogicName, $_string);
                    }
                }
            }
        }

        return $_string;
    }

    // utiliser cette fonction si on veut diriger les logs
    // vers la log spécifique de l'équipement
    public function log($_mode, $_message)
    {
        $watchdog = $this;

        $logfile = 'watchdog';
        if ($watchdog->getConfiguration('logspecifique', '0') != '0') {
            $logfile = 'watchdog_' . $watchdog->getId();
        }
        log::add($logfile, $_mode, $_message);
    }
}

class watchdogCmd extends cmd
{

    public function preInsert()
    {

        if ($this->getType() == 'action') return; //On ne fait rien le test si c'est une Commande Action		
        if ($this->getLogicalId() == 'resultatglobal') return; //On ne rien si c'est la commande 	resultatglobal	

        $condition = $this;
        $watchdog = $this->getEqLogic();

        log::add('watchdog', 'info', '║ ┌──────────────────────[Création Contrôle ' . $condition->getName() . ']────────────────────────────────────────────────────────────────────────────────────');
        $this->preSave();

        $_string = $condition->getConfiguration('controle');
        $resultat = self::TesteCondition($_string);
        $condition->setConfiguration('resultat', $resultat);

        // applique l'affichage inversé
        $condition->setDisplay("invertBinary", $condition->getDisplay("invertBinary", '0'));

        log::add('watchdog', 'info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");
    }

    // c'est ici que sont effectués les controles
    // la commande info contient les éléments du controle
    public function preSave()
    {

        if ($this->getType() == 'action') return; //On ne fait pas le test si c'est une Commande Action		
        if ($this->getLogicalId() == 'resultatglobal') return; //On ne fait pas le test si c'est la commande 	resultatglobal	
        if ($this->getID() == '') return; // on arrete si c est un insert

        $condition = $this;
        $watchdog = $this->getEqLogic();

        $watchdog->log('info', '║ ┌──────────────────────[Contrôle ' . $condition->getName() . ']────────────────────────────────────────────────────────────────────────────────────');

        $resultatPrecedent = $condition->getConfiguration('resultat');

        $_string = $condition->getConfiguration('controle');
        $resultat = $condition->TesteCondition($_string);
        $watchdog->log('debug', '║ │ ╚═══> Resultat : ' . $resultat);

        // sauve le resultat dans la condition
        // $watchdog->checkAndUpdateCmd($condition, $resultat);

        // Si le résultat a changé, il faut actualiser le calcul du résultat global, pour cela, on utilise la variable cmd.configuration.aChange qui traitera le calcul dans postSave
        if ($resultatPrecedent  != $resultat) {
            $aChange = True;
            $condition->setConfiguration('resultat', $resultat);
        } else {
            $aChange = False;
        }

        $condition->setConfiguration('aChange', $aChange);

        if ($watchdog->getConfiguration('typeControl') == '') {
            // Etablit l'affichage inversé en cas de SAVE afin que le controle s'affiche correctement
            if ($condition->getDisplay("showNameOndashboard", '1') == '0') {
                $condition->setDisplay("showNameOndashboard", '1');
            }

            if ($condition->getDisplay("showNameOnmobile", '1') == '0') {
                $condition->setDisplay("showNameOnmobile", '1');
            }

            // indique quelle est la valeur du Resultat pour laquelle le controle est OK
            if ($watchdog->getConfiguration("ResultatOK_Courant", '0') == '0') {
                $ResultatOK = '0';
                $invertBinary = '1';
            } else {
                $ResultatOK = '1';
                $invertBinary = '0';
            }
            // applique l'affichage inversé
            $invertBinaryCurrent = $condition->getDisplay("invertBinary", '0');
            if ($invertBinary <> $invertBinaryCurrent) {
                $condition->setDisplay("invertBinary", $invertBinary);
            }

            $DisplayOnlyConditionNonOK = $watchdog->getConfiguration("DisplayOnlyConditionNonOK_Courant");
            // affiche le controle quelque soit le résultat
            if ($DisplayOnlyConditionNonOK <> '1') {
                $condition->setIsVisible(1);
            } else {
                // Affiche ou non la commande associée au watchdog en fonction de l'état du watchdog
                if ($resultat == $ResultatOK) {
                    $condition->setIsVisible(0);
                }
                if ($resultat != $whatcResultatOKhdog_ok) {
                    $condition->setIsVisible(1);
                }
            }

            // historique 
            $ResultatHistory = $watchdog->getConfiguration("ResultatHistory_Courant", '');
            if ($ResultatHistory <> '') {  // historique demandé
                if ($condition->getIsHistorized() == 0) {
                    $condition->setIsHistorized(1);
                }
                $historyPurge = $ResultatHistory;
                if ($historyPurge == '/') $historyPurge = '';

                if ($historyPurge != $condition->getConfiguration('historyPurge', '')) {
                    $condition->setConfiguration('historyPurge', $historyPurge);
                }
            } else { // pas d historique demandé
                if ($condition->getIsHistorized() == 1) {
                    $condition->setIsHistorized(0);
                }
            }

            // applique les templates à la condition
            $template_resultat_dashboard = $watchdog->getConfiguration("template_resultat_dashboard_Courant");
            if ($template_resultat_dashboard <> $condition->getTemplate("dashboard", "core::default")) {
                $condition->setTemplate("dashboard", $template_resultat_dashboard);
            }

            $template_resultat_mobile = $watchdog->getConfiguration("template_resultat_mobile_Courant");
            if ($template_resultat_mobile <> $condition->getTemplate("mobile", "core::default")) {
                $condition->setTemplate("mobile", $template_resultat_mobile);
            }
        } else {
            // désactivage l'affichage car on n'affiche que le résultat global
            $condition->setIsVisible(0);
        }

        //On ne va lancer les actions que si on est en mode CRON/REFRESH et pas si on est en mode SAVE
        // On va chercher si on est en SAUVEGARDE ou en CRON/REFRESH
        $dernierLancement = $watchdog->getConfiguration('dernierLancement');
        $dernierLancement = substr($dernierLancement, 0, 4);
        if ($dernierLancement != "SAVE") {
            // uniquement si on est en mode "Actions sur chaque controle indépendamment"
            if ($watchdog->getConfiguration('typeControl') == '') {

                // gère le type d action
                if ($aChange == True || $watchdog->getConfiguration('typeAction', '') == 'ALL') {
                    //   $condition->LanceActionSurChaqueControleIndependamment($resultat);
                    $watchdog->LanceActions($resultat, $condition);
                } else {
                    $watchdog->log('info', '╠═════> Résultat du contrôle OK ]-> On ne fait rien');
                }
            }
        }
    }

    public function TesteCondition($_string)
    {
        $scenario = null;
        $condition = $this;
        $watchdog = $this->getEqLogic();

        // remplace les parametres
        $_string = $watchdog->remplace_parametres($_string);

        $_string = str_replace("#internalAddr#", '"' . config::byKey('internalAddr') . '"', $_string);  // généré par l'assistant sur controle IP de jeedom

        // Recherche et sauve le 1er équipement dans l expression pour l utiliser dans les actions lancées dans le mode 'Lancer action sur chaque controle
        $condition->setConfiguration("equip", $condition->cherche_equipement_dans_expression($_string));

        $fromHumanReadable = jeedom::fromHumanReadable($_string);
        $toHumanReadable = jeedom::toHumanReadable($_string);
        // $watchdog->log('debug', '║ │ ║ ╚═╦═>   ' . $fromHumanReadable);
        $watchdog->log('debug', '║ │ ╦═>   ' . $toHumanReadable);


        $condition->setConfiguration('calcul', scenarioExpression::setTags($fromHumanReadable));  // stocke les valeurs du calcul

        $return = evaluate(scenarioExpression::setTags($fromHumanReadable, $scenario, true)); // apparemment, setTags permet de récupérer les valeurs des variables dans la formule

        if (is_bool($return)) {
            if ($return == true) $return = '1';
            else $return = '0';
        } else {
            $watchdog->log('warning', '║ │ ╦═══> Problème avec l\'expresion:    ' . $fromHumanReadable);
            $return = $toHumanReadable; // si erreur, renvoie l expression soumise afin de pouvoir identifier le problème
        }

        return $return;
    }

    // recherche le premier Eqlogic dans la formule

    function cherche_equipement_dans_expression($text)
    {

        // recherche le 1er équipement
        preg_match_all("/#eqLogic([0-9]*)#/", $text, $matches);
        foreach ($matches[1] as $eqLogic_id) {
            if (is_numeric($eqLogic_id)) {
                $eqLogic = eqLogic::byId($eqLogic_id);
                if (is_object($eqLogic)) {
                    return $eqLogic->getHumanName();
                }
            }
        }

        // si pas trouvé, recherche la 1ere commande et renvoie l'équipement correspondant
        preg_match_all("/#[0-9]*#/", $text, $matches);
        foreach ($matches[0] as $cmd_id) {
            $cmd_id = str_replace('#', '', $cmd_id);
            if (is_numeric($cmd_id)) {
                $cmd = cmd::byId($cmd_id);
                if (is_object($cmd)) {
                    return $cmd->getEqLogic()->getHumanName();
                }
            }
        }

        // rien trouvé
        return "";
    }
    /*
    // Lance les actions de l'équipement
    public function LanceActionSurChaqueControleIndependamment($resultat)
    {

        if ($resultat == '0') $resultat = 'False';
        if ($resultat == '1') $resultat = 'True';

        $condition = $this;
        $watchdog = $condition->getEqLogic();
        $typeControl = $watchdog->getConfiguration('typeControl');
        $watchdogID = $watchdog->getId();

        $watchdog->log('debug', '╠═════> On lance les actions qui correspondent au passage de [' . $condition->getName() . '] à ' . $resultat);

        foreach ($watchdog->getConfiguration("watchdogAction") as $action) {

            $options = [];
            if (isset($action['options'])) $options = $action['options'];

            $equip = $condition->getConfiguration('equip', ''); // parametre _equip_ lié à la condition

            if (($action['actionType'] == $resultat) && $options['enable'] == '1') {

                // On va remplacer les variables dans tous les champs du array "options"
                foreach ($options as $key => $option) {
                    $option = str_ireplace("#controlname#", $condition->getName(), $option);
                    $option = $watchdog->remplace_parametres($option, $key);  // remplace les parametres utilisés dans les commandes action

                    // remplace _equip_ par le premier équipement référencé dans la condition
                    if ((stripos(' ' . $option, '_equip_') > 0) && ($equip <> ''))
                        $option = str_ireplace("_equip_", $equip, $option);

                    $options[$key] = $option;
                }
                $commande_action = $watchdog->remplace_parametres($action['cmd']);  // remplace les paramètres dans la commande
                // remplace _equip_ par le premier équipement référencé dans la condition
                if ((stripos(' ' . $commande_action, '_equip_') > 0) && ($equip <> ''))
                    $commande_action = str_ireplace("_equip_", $equip, $commande_action);

                $watchdog->log('debug', '╠══════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                if ($options['log'] == '1') {
                    log::add('watchdog_actions', 'info', 'Watchdog "' . $watchdog->getHumanName() . '" Condition "' . $condition->getName() . '" Résultat Controle "' . $resultat . '" Commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
                }
                try {
                    scenarioExpression::createAndExec('action', $commande_action, $options);
                } catch (Exception $e) {
                    $watchdog->log('error', __('function LanceActionSurChaqueControleIndependamment : Erreur lors de l\'éxecution de ', __FILE__) . $commande_action . __('. Détails : ', __FILE__) . $e->getMessage());
                    if ($options['log'] == '1') {
                        log::add('watchdog_actions', 'info', 'Watchdog "' . $watchdog->getHumanName() . '" Condition "' . $condition->getName() . '" Commande ' . jeedom::toHumanReadable($commande_action) . '" Erreur lors de l\'éxecution de ' . $commande_action . " : " . $e->getMessage());
                    }
                }
            }
        }

        $watchdog->log('info', '╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════');
    }
*/
    public function postSave()
    {
        if ($this->getType() == 'action') return; //On ne fait pas le test si c'est une Commande Action		

        $condition = $this;
        $watchdog = $this->getEqLogic();

        if ($this->getLogicalId() != 'resultatglobal') { //On ne fait pas le test si c'est la commande 	resultatglobal	
            if ($condition->getConfiguration('aChange')) {
                // Cette boucle est déclenchée quand le résultat du controle a changé, il faut ainsi relancer le save du resultat global
                $condition->setConfiguration('aChange', false);
                $condition->save();
                $condition->getEqLogic()->save(); //enregistre l'équipement entier (et donc le resultat global des controles)
            }
        }
        // Reporting de la condition dans le virtuel 
        // On va chercher si on est en SAUVEGARDE ou en CRON/REFRESH
        $dernierLancement = $watchdog->getConfiguration('dernierLancement');
        $dernierLancement = substr($dernierLancement, 0, 4);
        //On ne va lancer les actions que si on est en mode CRON/REFRESH et pas si on est en mode SAVE
        if ($dernierLancement != "SAVE") {
            $condition->report_cmd();
        }

        // log::add('watchdog', 'info', "║ └──Postsave────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
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
        $condition = $this;
        $watchdog = $this->getEqLogic();

        if (!is_object($watchdog) || $watchdog->getIsEnable() != 1) {
            throw new \Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }

        if ($condition->getLogicalId() == 'refresh') {
            log::add('watchdog', 'info', '┌──────────────────────[Refresh de ' . $watchdog->getName() . ']────────────────────────────────────────────────────────────────────────────────────');
            log::add('watchdog', 'info', "└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
            $watchdog->whatchdog_Update();
            return true;
        }
    }

    public function toHtml($_version = 'dashboard', $_options = '')
    {
        // on est obligé d'utiliser une logique spécifique pour l affichage
        // car le sous-type watchdog n est pas géré en standard
        if (($this->getType() == 'info') && ($this->getSubType() == 'watchdog')) {
            $this->setSubType('binary');
            $parentHtml = parent::toHtml($_version, $_options);
            $this->setSubType('watchdog');
            return $parentHtml;
        } else {
            $parentHtml = parent::toHtml($_version, $_options);
            return $parentHtml;
        }
    }

    // -----------------------------------------------------
    // Reporting du résultat global ou de la condition 
    // -----------------------------------------------------
    public function report_cmd()
    {

        $cmdresult = $this;
        $watchdog = $this->getEqLogic();
        $VirtualReportName = $watchdog->getConfiguration("VirtualReport_Courant");

        if (trim($VirtualReportName) <> '') {

            $DisplayOnlyReportingNonOK = $watchdog->getConfiguration("DisplayOnlyReportingNonOK_Courant");

            $whatchdog_ok = '0'; // indique quelle est la valeur de Resultat Global/Condition pour laquelle le whatchdog est OK
            if ($watchdog->getConfiguration("ResultatOK_Courant", '0') == '1')
                $whatchdog_ok = '1';
            unset($eqVirtualReport);
            try {
                $eqVirtualReport = eqLogic::byString($VirtualReportName);
            } catch (Exception $e) {
                $watchdog->log('warning', '╠═══> Virtuel nécessaire pour le reporting non défini ' . $VirtualReportName);
            }
            if (is_object($eqVirtualReport)) {

                $VirtualReportName = $eqVirtualReport->getHumanName();
                $eqVirtualReportId = $eqVirtualReport->getId();

                // récupère la commande correspondant à l'Id de la condition
                $cmdresultId = $cmdresult->getId();

                unset($cmdReportWatchdog);
                $cmdReportWatchdog = cmd::byEqLogicIdAndLogicalId($eqVirtualReportId, $cmdresultId);
                if (!is_object($cmdReportWatchdog)) {
                    if (($watchdog->getConfiguration('typeControl') == '' && $cmdresult->getLogicalID() == 'resultatglobal') ||
                        ($watchdog->getConfiguration('typeControl') != '' && $cmdresult->getLogicalID() != 'resultatglobal')
                    )  return;

                    // crée la commande correspondant au watchdog dans le virtuel du reporting
                    $watchdog->log('debug', '╠═══> Création dans le virtuel de reporting ' . $VirtualReportName . ' de la commande ' . $cmdresult->getName() . ' du watchdog ' . $watchdog->getName());
                    $cmdReportWatchdog = new virtualCmd();
                    $name = $watchdog->getName();
                    if ($cmdresult->getLogicalId() == 'resultatglobal')
                        $name = $watchdog->getName();
                    else
                        $name = $watchdog->getName() . ': ' . $cmdresult->getName();
                    // teste si le nom de la commande est déjà attribué    
                    // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
                    if (is_object(cmd::byEqLogicIdCmdName($eqVirtualReportId, $name))) {
                        $count = 1;
                        while (is_object(cmd::byEqLogicIdCmdName($eqVirtualReportId, substr($name, 0, 100) . "..." . $count))) {
                            $count++;
                        }
                        $name = substr($name, 0, 100) . "..." . $count;
                    }
                    $cmdReportWatchdog->setName($name);
                    $cmdReportWatchdog->setEqLogic_id($eqVirtualReportId);
                    $cmdReportWatchdog->setLogicalId($cmdresultId);
                    $cmdReportWatchdog->setConfiguration('historizeMode', 'none');
                    $cmdReportWatchdog->setConfiguration('repeatEventManagement', 'never');
                    $cmdReportWatchdog->setType('info');
                    $cmdReportWatchdog->setUnite('');
                    $cmdReportWatchdog->setSubType('binary');
                    $cmdReportWatchdog->setDisplay('generic_type', 'GENERIC_INFO');
                    $cmdReportWatchdog->setDisplay('graphType', 'column');
                    $cmdReportWatchdog->save();
                } else {
                    if (($watchdog->getConfiguration('typeControl') == '' && $cmdresult->getLogicalID() == 'resultatglobal') ||
                        ($watchdog->getConfiguration('typeControl') != '' && $cmdresult->getLogicalID() != 'resultatglobal')
                    ) {
                        $cmdReportWatchdog->setIsVisible(0);
                        $cmdReportWatchdog->save();
                        return;
                    }
                }

                // enregistre le résultat
                $eqVirtualReport->checkAndUpdateCmd($cmdReportWatchdog, $cmdresult->execCmd());


                $update_cmdReportWatchdog = false; // pour savoir si il faut faire une MAJ
                // historique 
                $ReportHistory = $watchdog->getConfiguration("ReportingHistory_Courant", '');
                if ($ReportingHistory <> '') {  // historique demandé
                    if ($cmdReportWatchdog->getIsHistorized() == 0) {
                        $cmdReportWatchdog->setIsHistorized(1);
                        $update_cmdReportWatchdog = true;
                    }
                    $historyPurge = $ReportHistory;
                    if ($historyPurge == '/') $historyPurge = ''; // valeur par défaut

                    if ($historyPurge != $cmdReportWatchdog->getConfiguration('historyPurge', '')) {
                        $cmdReportWatchdog->setConfiguration('historyPurge', $historyPurge);
                        $update_cmdReportWatchdog = true;
                    }
                } else { // pas d historique demandé
                    if ($cmdReportWatchdog->getIsHistorized() == 1) {
                        $cmdReportWatchdog->setIsHistorized(0);
                        $update_cmdReportWatchdog = true;
                    }
                }

                // applique les templates à la commande info dans le virtuel
                $template_reporting_dashboard = $watchdog->getConfiguration("template_reporting_dashboard_Courant");
                if ($template_reporting_dashboard <> $cmdReportWatchdog->getTemplate("dashboard", "core::default")) {
                    $cmdReportWatchdog->setTemplate("dashboard", $template_reporting_dashboard);
                    $update_cmdReportWatchdog = true;
                }

                $template_reporting_mobile = $watchdog->getConfiguration("template_reporting_mobile_Courant");
                if ($template_reporting_mobile <> $cmdReportWatchdog->getTemplate("mobile", "core::default")) {
                    $cmdReportWatchdog->setTemplate("mobile", $template_reporting_mobile);
                    $update_cmdReportWatchdog = true;
                }

                // aligne l affichage inversé sur celui du Resultat 
                $invertBinaryResultat = $cmdresult->getDisplay("invertBinary", '0');
                $invertBinary = $cmdReportWatchdog->getDisplay("invertBinary", '0');
                if ($invertBinary <> $invertBinaryResultat) {
                    $cmdReportWatchdog->setDisplay("invertBinary", $invertBinaryResultat);
                    $update_cmdReportWatchdog = true;
                }

                // affiche le controle quelque soit le résultat
                if ($DisplayOnlyReportingNonOK <> '1' && $cmdReportWatchdog->getIsVisible() == '0') {
                    $cmdReportWatchdog->setIsVisible(1);
                    $update_cmdReportWatchdog = true;
                } else {
                    // récupère le résultat de la commande Resultat 
                    $WatchdogResultat = $cmdresult->execCmd();
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
}

function parseFunctionCall(string $functionCallString): ?array
{
    // Regex pour capturer le nom de la fonction et le contenu entre parenthèses
    // Gère les noms de fonctions avec des caractères alphanumériques et underscores,
    // et les espaces optionnels autour des parenthèses.
    // Le pattern `([^()]*)` capture tout ce qui se trouve à l'intérieur des parenthèses
    // (les arguments potentiels).
    $pattern = '/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\((.*)\)$/';

    if (preg_match($pattern, $functionCallString, $matches)) {
        $functionName = $matches[1];
        $argumentsString = $matches[2];

        $arguments = [];

        // Si la chaîne d'arguments n'est pas vide
        if (!empty(trim($argumentsString))) {
            // Tentative de diviser les arguments. C'est la partie la plus délicate.
            // Cette regex est une tentative simple et peut ne pas gérer tous les cas :
            // - les strings avec virgules (ex: "un,deux")
            // - les arrays imbriqués ou les objets
            // - les fonctions imbriquées comme arguments
            // - les arguments nommés
            // Pour des cas plus complexes, une véritable analyse lexicale/syntaxique serait nécessaire.
            // Ici, on se base sur les virgules comme séparateurs, en essayant de ne pas diviser
            // à l'intérieur des guillemets (non implémenté ici pour la simplicité,
            // mais ce serait une amélioration majeure).

            // Cette version simple divise par la virgule suivie d'espaces optionnels
            $argsRaw = explode(',', $argumentsString);

            foreach ($argsRaw as $arg) {
                $arg = trim($arg);

                // Tentative de déterminer le type de l'argument (simplifié)
                if (is_numeric($arg)) {
                    $arguments[] = (strpos($arg, '.') !== false) ? (float)$arg : (int)$arg;
                } elseif (in_array(strtolower($arg), ['true', 'false'])) {
                    $arguments[] = (strtolower($arg) === 'true');
                } elseif (strtolower($arg) === 'null') {
                    $arguments[] = null;
                } elseif (preg_match('/^[\'"](.*)[\'"]$/', $arg, $stringMatch)) {
                    // Supprime les guillemets simples ou doubles pour les chaînes
                    $arguments[] = $stringMatch[1];
                } else {
                    // Pour tout le reste, on le traite comme une chaîne (par exemple, des constantes non définies,
                    // des noms de variables sans leur valeur réelle, etc.).
                    // Dans un vrai parseur, on devrait essayer de résoudre ces "valeurs".
                    $arguments[] = $arg;
                }
            }
        }

        return [
            'functionName' => $functionName,
            'arguments' => $arguments
        ];
    }

    return null; // Retourne null si la chaîne ne correspond pas à un appel de fonction
}
