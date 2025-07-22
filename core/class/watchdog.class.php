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
            $watchdog->lancerControle();
            log::add('watchdog', 'info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");
        } catch (Exception $exc) {
            log::add('watchdog', 'error', __('Erreur pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $exc->getMessage());
        }
        $watchdog->save();
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

            // On est ici sur le résultat général des controles
            // on ne fait rien si on est en mode "Actions sur chaque controle indépendamment" car le lancement des actions est traité dans le presave
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
        // on doit décaler d un caractere car le résultat est 0 (soit false) si _equip1_ est en tête de la chaine
        if (stripos(' ' . $_string, "_equip1_") <> false) {
            unset($eqlogic1);
            $eqlogic1Name = trim($watchdog->getConfiguration('equip1'), '');
            if ($eqlogic1Name != '') {
                try {
                    $eqlogic1 = eqLogic::byString($eqlogic1Name);
                } catch (Exception $e) {
                    log::add('watchdog', 'warning', '╠════' . $watchdog->getName() . ' ════> parametre _equip1_ non défini ' . $eqlogic1Name);
                }
                if (is_object($eqlogic1)) {
                    $eqlogic1Name = $eqlogic1->getHumanName();
                    $_string = str_ireplace("_equip1_", $eqlogic1Name, $_string);
                }
            }
        }
        if (stripos(' ' . $_string, "_equip2_") <> false) {
            unset($eqlogic2);
            $eqlogic2Name = trim($watchdog->getConfiguration('equip2'), '');
            if ($eqlogic2Name != '') {
                try {
                    $eqlogic2 = eqLogic::byString($eqlogic2Name);
                } catch (Exception $e) {
                    log::add('watchdog', 'warning', '╠════' . $watchdog->getName() . ' ════> parametre _equip2_ non défini ' . $eqlogic2Name);
                }
                if (is_object($eqlogic2)) {
                    $eqlogic2Name = $eqlogic2->getHumanName();
                    $_string = str_ireplace("_equip2_", $eqlogic2Name, $_string);
                }
            }
        }
        if (stripos(' ' . $_string, "_equip3_") <> false) {
            unset($eqlogic3);
            $eqlogic3Name = trim($watchdog->getConfiguration('equip3'), '');
            if ($eqlogic3Name != '') {
                try {
                    $eqlogic3 = eqLogic::byString($eqlogic3Name);
                } catch (Exception $e) {
                    log::add('watchdog', 'warning', '╠════' . $watchdog->getName() . ' ════> parametre _equip3_ non défini ' . $eqlogic3Name);
                }
                if (is_object($eqlogic3)) {
                    $eqlogic3Name = $eqlogic3->getHumanName();
                    $_string = str_ireplace("_equip3_", $eqlogic3Name, $_string);
                }
            }
        }

        return $_string;
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

        log::add('watchdog', 'info', '║ ┌──────────────────────[Contrôle ' . $condition->getName() . ']────────────────────────────────────────────────────────────────────────────────────');

        $resultatPrecedent = $condition->getConfiguration('resultat');

        $_string = $condition->getConfiguration('controle');
        $resultat = self::TesteCondition($_string);
        log::add('watchdog', 'debug', '║ │ ║   ╚═══> Resultat : ' . $resultat);

        // sauve le resultat dans la condition
        $watchdog->checkAndUpdateCmd($condition, $resultat);

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

            if ($condition->getDisplay("showNameOndashboard", '1') == '0') {
                $condition->setDisplay("showNameOndashboard", '1');
            }

            if ($condition->getDisplay("showNameOnmobile", '1') == '0') {
                $condition->setDisplay("showNameOnmobile", '1');
            }

            $DisplayOnlyConditionNonOK = $watchdog->getConfiguration("DisplayOnlyConditionNonOK_Courant");
            $whatchdog_ok = '0'; // indique quelle est la valeur de Resultat pour laquelle le whatchdog est OK
            if ($watchdog->getConfiguration("ResultatOK_Courant", '0') == '1')
                $whatchdog_ok = '1';

            // affiche le controle quelque soit le résultat
            if ($DisplayOnlyConditionNonOK <> '1') {
                $condition->setIsVisible(1);
            } else {
                // Affiche ou non la commande associée au watchdog en fonction de l'état du watchdog
                if ($resultat == $whatchdog_ok) {
                    $condition->setIsVisible(0);
                }
                if ($resultat != $whatchdog_ok) {
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

            // affichage ou non de la condition dans la tuile
            $DisplayOnlyReportingNonOK = $watchdog->getConfiguration("DisplayOnlyReportingNonOK_Courant");
            $visible = '1';
            if ($DisplayOnlyReportingNonOK == '0') {
                // affiche le controle quelque soit le résultat
            } else {
                // Affiche ou non la commande associée en fonction de l'état de la condition
                if ($resultat == $ResultatOK) {
                    $visible = '0';  // OK
                }
                if ($resultat != $ResultatOK) {
                    // pas OK  --> visible
                }
            }
            if ($condition->getIsVisible() !=  $visible) {
                $condition->setIsVisible($visible);
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
                    $condition->LanceActionSurChaqueControleIndependamment($resultat);
                } else {
                    log::add('watchdog', 'debug', '╠═════> Résultat du contrôle OK ]-> On ne fait rien');
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

        $fromHumanReadable = jeedom::fromHumanReadable($_string);
        $toHumanReadable = jeedom::toHumanReadable($_string);
        log::add('watchdog', 'debug', '║ │ ║ ╚═╦═>   ' . $fromHumanReadable);
        log::add('watchdog', 'debug', '║ │ ║ ╚═╦═>   ' . $toHumanReadable);

        $condition->setConfiguration('calcul', scenarioExpression::setTags($fromHumanReadable));  // stocke les valeurs du calcul

        $return = evaluate(scenarioExpression::setTags($fromHumanReadable, $scenario, true)); // apparemment, setTags permet de récupérer les valeurs des variables dans la formule
        if (is_bool($return)) {
            if ($return == true) $return = '1';
            else $return = '0';
        } else {
            log::add('watchdog', 'warning', '║ │ ║ ╚═╦═══> Problème avec l\'expresion:    ' . $toHumanReadable);
            $return = $toHumanReadable; // si erreur, renvoie l expression soumise afin de pouvoir identifier le problème
        }

        return $return;
    }
    // Lance les actions de l'équipement
    public function LanceActionSurChaqueControleIndependamment($resultat)
    {

        if ($resultat == '0') $resultat = 'False';
        if ($resultat == '1') $resultat = 'True';

        $condition = $this;
        $watchdog = $condition->getEqLogic();
        $typeControl = $watchdog->getConfiguration('typeControl');
        $watchdogID = $watchdog->getId();

        log::add('watchdog', 'debug', '╠═════> On lance les actions qui correspondent au passage de [' . $condition->getName() . '] à ' . $resultat);

        if ($watchdog->getConfiguration('logspecifique'))
            log::add('watchdog_' . $watchdogID, 'info', '╔══════════════════════[' . $condition->getName() . ' est passé à ' . $resultat . ']════════════════════════════════════════════════════════════════════════════');

        foreach ($watchdog->getConfiguration("watchdogAction") as $action) {

            $options = [];
            if (isset($action['options'])) $options = $action['options'];
            if (($action['actionType'] == $resultat) && $options['enable'] == '1') {

                // On va remplacer les variables dans tous les champs du array "options"
                foreach ($options as $key => $option) {
                    $option = str_ireplace("#controlname#", $condition->getName(), $option);
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
                log::add('watchdog', 'warning', '╠═══> Virtuel nécessaire pour le reporting non défini ' . $VirtualReportName);
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
                    log::add('watchdog', 'debug', '╠═══> Création dans le virtuel de reporting ' . $VirtualReportName . ' de la commande ' . $cmdresult->getName() . ' du watchdog ' . $watchdog->getName());
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
