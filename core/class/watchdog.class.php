<?php

// Last Modified : 2026/01/21 18:22:44

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

    public function preRemove()
    {

        $watchdog = $this;

        // supprime la config pour les logs specifiques
        $_key = 'log::level::watchdog_' . $watchdog->getId();
        if (config::byKey($_key) != '')
            config::remove($_key);

        // supprime les résultats dans le virtuel du reporting
        if ($watchdog->getConfiguration("ReportingSuppressionAutomatique_Courant", '1') == '1') {
            foreach ($watchdog->getCmd('info') as $condition) {
                $condition->report_cmd_delete();
            }
        }

        return true;
    }
    public function preSave()
    {
        $watchdog = $this;
        //    log::add('watchdog', 'info', '┌──────────────────────[Sauvegarde du Watchdog ' . $watchdog->getName() . ']────────────────────────────────────────────────────────────────────────────────────');

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
                    log::add('watchdog', 'info', 'Suppression de la configuration ' . $_key );
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

        // -----------------------------------------------------
        // Calcul des configurations courantes du watchdog
        // -----------------------------------------------------
        $ResultatOK = config::byKey('ResultatOK', 'watchdog', '1');
        $ResultatOK = $watchdog->getConfiguration("ResultatOK", $ResultatOK);
        $watchdog->setConfiguration("ResultatOK_Courant", $ResultatOK);

        // Historique demandé ou non 
        $ResultatHistory = config::byKey('ResultatHistory', 'watchdog', '');
        $ResultatHistory = $watchdog->getConfiguration("ResultatHistory", $ResultatHistory);
        if (trim($ResultatHistory) == '/')
            $ResultatHistory = '';
        $watchdog->setConfiguration("ResultatHistory_Courant", $ResultatHistory);

        // affiche ou non seulement les conditions non OK dans la tuile 
        // ne concerne pas les modes ET / OU pour lesquels le résultat global est toujours affiché
        $DisplayOnlyConditionNonOK = config::byKey('DisplayOnlyConditionNonOK', 'watchdog', '1');
        $DisplayOnlyConditionNonOK = $watchdog->getConfiguration("DisplayOnlyConditionNonOK", $DisplayOnlyConditionNonOK);
        $watchdog->setConfiguration("DisplayOnlyConditionNonOK_Courant", $DisplayOnlyConditionNonOK);

        $template_resultat_dashboard = config::byKey('template_resultat_dashboard', 'watchdog', 'core::line');
        $template_resultat_dashboard = $watchdog->getConfiguration("template_resultat_dashboard", $template_resultat_dashboard);
        $watchdog->setConfiguration("template_resultat_dashboard_Courant", $template_resultat_dashboard);

        $template_resultat_mobile = config::byKey('template_resultat_mobile', 'watchdog', 'core::line');
        $template_resultat_mobile = $watchdog->getConfiguration("template_resultat_mobile", $template_resultat_mobile);
        $watchdog->setConfiguration("template_resultat_mobile_Courant", $template_resultat_mobile);

        $template_resultatglobal_dashboard = config::byKey('template_resultatglobal_dashboard', 'watchdog', 'core::default');
        $template_resultatglobal_dashboard = $watchdog->getConfiguration("template_resultatglobal_dashboard", $template_resultatglobal_dashboard);
        $watchdog->setConfiguration("template_resultatglobal_dashboard_Courant", $template_resultatglobal_dashboard);

        $template_resultatglobal_mobile = config::byKey('template_resultatglobal_mobile', 'watchdog', 'core::default');
        $template_resultatglobal_mobile = $watchdog->getConfiguration("template_resultatglobal_mobile", $template_resultatglobal_mobile);
        $watchdog->setConfiguration("template_resultatglobal_mobile_Courant", $template_resultatglobal_mobile);

        $VirtualReport = trim($watchdog->getConfiguration("VirtualReport", ''));
        if (trim($VirtualReport) == '')
            $VirtualReport = trim(config::byKey('VirtualReport', 'watchdog', ''));
        if (trim($VirtualReport) == '/')
            $VirtualReport = '';
        $watchdog->setConfiguration("VirtualReport_Courant", $VirtualReport);

        $ReportingHistory = config::byKey('ReportingHistory', 'watchdog', '');
        $ReportingHistory = $watchdog->getConfiguration("ReportingHistory", $ReportingHistory);
        if (trim($ReportingHistory) == '/')
            $ReportingHistory = '';
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

        $ReportingSuppressionAutomatique = config::byKey('ReportingSuppressionAutomatique', 'watchdog', '1');
        $ReportingSuppressionAutomatique = $watchdog->getConfiguration("ReportingSuppressionAutomatique", $ReportingSuppressionAutomatique);
        $watchdog->setConfiguration("ReportingSuppressionAutomatique_Courant", $ReportingSuppressionAutomatique);
    }

    public function postSave()
    {
        $watchdog = $this;

        // -----------------------------------------------------
        // Cree la Commande refresh
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
        // Cree la Commande Resultat Global
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
        }
        // -----------------------------------------------------------------------------------------------
        // si l'equipement est desactivé, on supprime l'affichage des commandes info dans le reporting
        // -----------------------------------------------------------------------------------------------
        if ($watchdog->getIsEnable() == '0') {
            foreach ($watchdog->getCmd('info') as $controle) {
                $controle->report_cmd_notVisible();
            }
        }

        // log::add('watchdog', 'info', "└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
    }

    // postAjax appelé après la MAJ de l EQLOGIC et des commandes dans l'interface utilisateur
    // on lance alors le controle des commandes info en mode SAVE
    public function postAjax()
    {
        $watchdog = $this;
        $watchdog->watchdog_Update('SAVE');
    }


    // Procedure lancée par le cron défini lors de l'activation du plugin
    // Lancement des watchdogs éligibles (selon les paramètres du CRON)
    public static function update()  // procédure appelée par le CRON
    {

        $cron_update = false;  // pour afficher les messages d entete une seule fois et seulement si il y a des watchdog à traiter

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
                        $watchdog->watchdog_Update('CRON');
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

    // Exécution des controles et actions d'un watchdog suite à fonction update, refresh ou save
    public function watchdog_Update($_contexte)
    {
        $watchdog = $this;
        try {
            log::add('watchdog', 'info', '╔══════════════════════[Lancement du Watchdog ' . $watchdog->getName() . '] en mode ' . $_contexte . ' ════════════════════════════════════════════════════════════════════════════');

            if ($watchdog->getConfiguration('logspecifique', '') == '1') {
                log::add('watchdog', 'info', '║                     Enregistrement de la log de ce watchdog dans watchdog_' . $watchdog->getId());
                $watchdog->log('info', '╔══════════════════════[Lancement du Watchdog ' . $watchdog->getName() . ']════════════════════════════════════════════════════════════════════════════');
            }
            $watchdog->lanceControles($_contexte);
            // log::add('watchdog', 'info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");
            if ($watchdog->getConfiguration('logspecifique', '') == '1') {
                $watchdog->log('info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");
            }
        } catch (Exception $exc) {
            log::add('watchdog', 'error', __('Erreur pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $exc->getMessage());
        }
        // On sauvegarde l'avant dernier lancement si l'on est pas en mode SAVE
        if ($_contexte != "SAVE") {
            $watchdog->setConfiguration('avantDernierLancement', $watchdog->getConfiguration('dernierLancement', ''));
        }
        $watchdog->setConfiguration('dernierLancement', $_contexte . ' ' . date("d.m.Y") . " " . date("H:i:s"));
        $watchdog->save();
        log::add('watchdog', 'info', '╚══════════════════════[Fin du Watchdog ' . $watchdog->getName() . '] en mode ' . $_contexte . ' ════════════════════════════════════════════════════════════════════════════');
    }
    public function lanceControles($_contexte)
    {

        $watchdog = $this;

        $typeControl = $watchdog->getConfiguration('typeControl');
        $LancementActionsAvantApres = $watchdog->getConfiguration('LancementActionsAvantApres', '');
        // en mode lancer les actions Avant/Apres une seule fois
        if ($LancementActionsAvantApres != 'ALL' || $typeControl <> '') {
            if ($_contexte == "SAVE") {
                $watchdog->log('info', '║ ─────────────────────────[ Mode SAVE --> pas de lancement des actions Avant ]────────────────────────────────────────────────────────────────────────────────────');
            } else {
                // $watchdog->log('debug', "╠════> Avant de lancer le contrôle on lance les actions d'avant contrôle (s'il y en a).");
                $watchdog->LanceActions('Avant');
            }
        }

        $watchdog->log('info', '╠════════ On lance les contrôles ════════════════════════════════════════════════════════════════════════════════════════ ');

        foreach ($watchdog->getCmd('info') as $controle) {
            if ($controle->getLogicalId() == "resultatglobal")  // on ignore resultatglobal
                continue;
            if ($controle->getConfiguration('disable', '0') == '1')   // ignore les conditions desactivees
                continue;

            $controle->lanceControle($_contexte);
        }

        // Calcul du résultat global
        if ($typeControl != "") {   // Que si en OU ou en ET

            // $watchdog->CalculResultatGlobal($_contexte);
            unset($controle);
            $controle = $watchdog->getCmd(null, "resultatglobal");
            if (is_object($controle)) {
                $controle->setConfiguration('controle', 'Méthode ' . $watchdog->getConfiguration('typeControl')); // pour affichage dans l interface utilisateur
                $controle->lanceControle($_contexte);
            }
        }
        $watchdog->log('info', '╠════════ Fin des contrôles ════════════════════════════════════════════════════════════════════════════════════════ ');
        if ($LancementActionsAvantApres != 'ALL' || $typeControl <> '') {
            if ($_contexte == "SAVE") {
                $watchdog->log('info', '║ ─────────────────────────[ Mode SAVE --> pas de lancement des actions Apres ]────────────────────────────────────────────────────────────────────────────────────');
            } else {
                // $watchdog->log('debug', "╠════> Apres avoir lancé le contrôle on lance les actions d'apres contrôle (s'il y en a).");
                $watchdog->LanceActions('Apres');
            }
        }
    }

    // lance les actions correspondant au changement de résultat du watchdog si $condition vide ou autrement de la condition
    // + les actions AVANT / APRES
    public function LanceActions($_OrigineAction, $condition = '', $resultat_a_change = '0')
    {
        $watchdog = $this;
        if ($condition == '')
            $watchdog->log('info', '║ ══> On lance les actions ' . $_OrigineAction . ' de [' . $watchdog->getName() . ']');
        else
            $watchdog->log('info', '║ │ ══> On lance les actions ' . $_OrigineAction . ' de [' . $condition->getName() . ']' . ' a changé ' . (string) $resultat_a_change);


        foreach ($watchdog->getConfiguration("watchdogAction") as $action) {
            $options = [];
            if (isset($action['options'])) $options = $action['options'];

            if (($action['actionType'] != $_OrigineAction) || $options['enable'] != '1') continue;
            if ($condition != '') {
                $seulement_si_changement = '';
                if (isset($options['seulement_si_changement'])) // on doit passer par une variable car l'option n'est pas forcément définie
                    $seulement_si_changement = $options['seulement_si_changement'];
                if ($watchdog->getConfiguration('typeControl') == '' &&  in_array($watchdog->getConfiguration('typeAction'), array('ALL', 'True', 'False')) && $seulement_si_changement == '1' && $resultat_a_change == '0') continue;
                $watchdog->log('info', '║ │ ══> actions ' . $_OrigineAction . ' de [' . $condition->getName() . ']' . ' a changé ' . (string) $resultat_a_change . ' seulement_si_changement ' . $seulement_si_changement);
            }
            // On va remplacer les variables dans tous les champs du array "options"
            foreach ($options as $key => $option) {
                $option = $watchdog->remplace_parametres($option, $key, $condition);  // remplace les parametres utilisés dans les commandes action
                $options[$key] = jeedom::toHumanReadable($option);
            }

            $commande_action = $watchdog->remplace_parametres($action['cmd'], '', $condition);  // remplace les paramètres dans la commande
            $watchdog->log('info', '║ │ ═════> Exécution de la commande ' . jeedom::toHumanReadable($commande_action) . " avec comme option(s) : " . json_encode($options));
            if ($options['log'] == '1') {
                $action_env = "";
                if ($_OrigineAction == 'Vrai' or $_OrigineAction == 'Faux') {
                    if ($condition != '')
                        $action_env = '" Condition : "' .  $condition->getName() . '" Résultat Controle :' . $_OrigineAction;
                    else
                        $action_env =  '" Résultat Global : ' . $_OrigineAction;
                } else {
                    $action_env =  '" Action ' . $_OrigineAction;
                }
                log::add('watchdog_actions', 'info', 'Watchdog "' . $watchdog->getHumanName() . $action_env . ' Commande "' . jeedom::toHumanReadable($commande_action) . '" avec comme option(s) : "' . json_encode($options) . '"');
            }
            try {
                $options['source'] = 'watchdog';
                scenarioExpression::createAndExec('action', $commande_action, $options);
            } catch (Exception $e) {
                $watchdog->log('error', __('function LanceActions : Erreur lors de l\'éxecution de ', __FILE__) . $commande_action . __('. Détails : ', __FILE__) . $e->getMessage());
                if ($options['log'] == '1') {
                    log::add('watchdog_actions', 'error', 'Watchdog "' . $watchdog->getHumanName() . '"' . $action_env . ' Erreur lors de l\'éxecution de ' . $commande_action . " : " . $e->getMessage());
                }
            }
        }
    }


    // Remplace les parametres dans les expressions et options
    public function remplace_parametres($_string, $_key = '', $condition = '')
    {

        // ignore les options liés au lancement des actions
        if (strpos(';enable;background;log;',  $_key . ';') > 0)
            return $_string;

        $watchdog = $this;

        // remplace la macro
        $macro = $watchdog->getConfiguration('macro', '');
        if ($macro != '') {
            if (stripos(' ' . $_string, '_macro_(') == 1) {
                $_string = $macro;
            }
        }

        // remplace les paramètres liés à une condition 
        if ($condition <> '') {

            $_string = str_ireplace("#controlname#", $condition->getName(), $_string);
            $_string = str_ireplace("_controlname_", $condition->getName(), $_string);
            $_string = str_replace("#internalAddr#", '"' . config::byKey('internalAddr') . '"', $_string);  // généré par l'assistant sur controle IP de jeedom
            $_string = str_replace("_internalAddr_", '"' . config::byKey('internalAddr') . '"', $_string);
            // remplace les arguments de la macro
            for ($i = 1; $i <= 9; $i++) {
                $arg = $condition->getConfiguration('arg' . $i, '');
                if ($arg <> '') {
                    $arg = jeedom::toHumanReadable($arg);
                    $_string = str_ireplace("_arg" . (string)$i . "_", trim($arg), $_string);
                    $_string = str_ireplace("_arg" . (string)$i . "name_", $arg = str_ireplace('#', '', $arg), $_string);
                } else break;
            }

            // remplace _equip_ par le premier équipement référencé dans la condition
            $equip = $condition->getConfiguration("equip", "");
            if ($equip <> '') {
                $equip = jeedom::toHumanReadable($equip);
                $_string = str_ireplace("_equip_", $equip, $_string);
                $_string = str_ireplace("_equipname_", str_ireplace('#', '', $equip), $_string);
            }

            // remplace _cmd_ par la premiere commande référencée dans la condition
            $cmd = $condition->getConfiguration("cmd", "");
            if ($cmd <> '') {
                $cmd = jeedom::toHumanReadable($cmd);
                $_string = str_ireplace("_cmd_", $cmd, $_string);
                $_string = str_ireplace("_cmdname_", str_ireplace('#', '', $cmd), $_string);
            }
        }

        // remplace title par le nom du watchdog
        $_string = str_ireplace("#title#", $watchdog->getName(), $_string);
        $_string = str_ireplace("_title_", $watchdog->getName(), $_string);

        // Remplace les valeurs de var 1 2 et 3
        for ($i = 1; $i <= 3; $i++) {
            $_string = str_ireplace("_var" . (string)$i . "_", trim($watchdog->getConfiguration("var" . (string)$i, '')), $_string);
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

        // remplacement pour les champs exemple :  #_arg1_[Résultat Global]# --> ##[maison][Tous Controles]#[Résultat Global]#  --> #[maison][Tous Controles][Résultat Global]#
        $_string = jeedom::toHumanReadable($_string);
        $_string = str_ireplace("##[", "#[", $_string);
        $_string = str_ireplace("]##", "]#", $_string);
        $_string = str_ireplace("]#[", "][", $_string);
        // $_string = jeedom::fromHumanReadable($_string);

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

    public function preSave()
    {
        if ($this->getType() == 'action') return; //On ne fait pas le test si c'est une Commande Action		

        $condition = $this;
        $watchdog = $this->getEqLogic();

        if ($condition->getConfiguration('disable', '0') == '1') {
            // rend invisible les conditions desactivées
            if ($condition->getIsVisible() == 1)
                $condition->setIsVisible(0);
            $condition->report_cmd_notVisible();
        }

        // memorise les arguments de la macro
        for ($i = 1; $i <= 9; $i++) {
            if ($condition->getConfiguration('arg' . $i, '') <> '')
                $condition->setConfiguration('arg' . $i, '');
        }
        $macro = $watchdog->getConfiguration('macro', '');
        if ($macro != '') {
            $_string = $condition->getConfiguration('controle');
            if (stripos(' ' . $_string, '_macro_(') == 1) {
                $parsedCommand = parseFunctionCall(trim($_string));
                if ($parsedCommand) {
                    $i = 1;
                    foreach ($parsedCommand['arguments'] as $arg) {
                        $condition->setConfiguration('arg' . $i, $arg);
                        $i++;
                        if ($i == 9) break;
                    }
                }
            }
        }
    }

    public function lanceControle($_contexte)
    {
        $condition = $this;
        $watchdog = $this->getEqLogic();

        $typeControl = $watchdog->getConfiguration('typeControl');
        $LancementActionsAvantApres = $watchdog->getConfiguration('LancementActionsAvantApres', '');

        $watchdog->log('info', '║ ┌──────────────────────[Contrôle ' . $condition->getName() . ']  en mode ' . $_contexte . ' ────────────────────────────────────────────────────────────────────────────────────');

        // dans la version initiale du plugin, le subtype était watchdog qui n est pas un subType supporté par jeedom et ne permettait pas un affichage correct
        // changé en binary à partir de aout 2025
        if ($condition->getSubType() != 'binary') {
            $condition->setSubType('binary');
        }

        // en mode lancer les actions Avant/Apres pour chaque contrôle
        if ($LancementActionsAvantApres == 'ALL' && $typeControl == '') {
            if ($_contexte != "SAVE") {
                $watchdog->LanceActions('Avant', $condition);
            }
        }

        $resultatPrecedent = $condition->getConfiguration('resultat');
        if ($condition->getLogicalId() != "resultatglobal") {
            $expression = $condition->getConfiguration('controle');
            $resultat = $condition->TesteCondition($expression); // retourne True, False ou Error
        } else {
            $resultat = $condition->CalculResultatGlobal($_contexte); // retourne True ou False
        }

        $watchdog->log('info', '║ │ ╚══> Resultat : ' . $resultat);

        // sauve le resultat pour affichage dans le widget et historique seulement si pas d erreur
        if ($resultat == 'True' ||  $resultat == 'False') {
            $resultatBinary = ($resultat == 'True') ? 1 : 0;
            $watchdog->checkAndUpdateCmd($condition, $resultatBinary);
        }

        // sauve le resultat dans la config de la condition 
        $condition->setConfiguration('resultat', $resultat);

        // On sauvegarde le dernier résultat dans resultatAvant seulement si le resultatPrecedent est valide
        // et que l'on est pas en mode SAVE
        if ($_contexte != "SAVE") {
            if ($resultatPrecedent == 'True' || $resultatPrecedent == 'False') {
                $condition->setConfiguration('resultatAvant', $resultatPrecedent);
            }
        }

        // Recherche et sauve le 1er équipement et la 1ere commande dans l expression pour l utiliser dans les actions lancées dans le mode 'Lancer action sur chaque controle
        if ($condition->getLogicalId() != "resultatglobal") {
            $expression = $condition->getConfiguration('expression'); // récupère l'expression après remplacement des paramètres
            $condition->setConfiguration("equip", $condition->cherche_equipement_dans_expression($expression));
            $condition->setConfiguration("cmd", $condition->cherche_commande_dans_expression($expression));
        }

        // configure l'affichage de la condition
        $condition->ConfigureConditionDisplay($resultat);

        // sauve la condition
        $condition->save();


        // Reporting de la condition dans le virtuel 
        $condition->report_cmd();

        // Lance les actions seulement si on n'est pas en mode SAVE
        if ($_contexte != "SAVE") {

            // uniquement si on est en mode "Actions sur chaque controle indépendamment"
            // ou calcul du resultat global
            // et si la conditions n'est pas en erreur
            if (($watchdog->getConfiguration('typeControl') == '' or $condition->getLogicalId() == "resultatglobal") &&
                ($resultat == 'True' or $resultat == 'False')
            ) {

                // Evalue si il faut lancer les actions
                // dépend du type de lancement des actions 
                $typeAction = $watchdog->getConfiguration('typeAction', '');
                $LanceActions =  False;
                if ($resultatPrecedent  != $resultat)
                    $LanceActions = True;
                else {
                    switch ($typeAction) {
                        case 'ALL':
                            $LanceActions = True;
                            break;
                        case 'True':
                            if ($resultat == 'True')
                                $LanceActions = True;
                            break;
                        case 'False':
                            if ($resultat == 'False')
                                $LanceActions = True;
                            break;
                    }
                }
                $msg = '║ │ ═════>  typeAction: "';
                $msg .= ($typeAction == '') ? 'Défaut' : $typeAction;
                $msg .= '" Résultat précédent: "' . $resultatPrecedent . '" Résultat  "' . $resultat .  '" --> ';
                if ($LanceActions == True)
                    $msg .= ' Lancement Actions ' . $resultat;
                else
                    $msg .= ' Ne rien faire ';
                $watchdog->log('info', $msg);

                if ($LanceActions == True) {
                    $a_change = '0';
                    if ($resultatPrecedent  != $resultat) $a_change = '1';
                    $watchdog->LanceActions($resultat, $condition, $a_change);
                }
            }
            if ($LancementActionsAvantApres == 'ALL' && $typeControl == '') {
                $watchdog->LanceActions('Apres', $condition);
            }
        }

        $watchdog->log('info', '║ └──────────────────────[Fin du Contrôle ' . $condition->getName() . ']  en mode ' . $_contexte . ' ────────────────────────────────────────────────────────────────────────────────────');
    }

    public function TesteCondition($_string)
    {
        $scenario = null;
        $condition = $this;
        $watchdog = $this->getEqLogic();

        // remplace les parametres
        $_string = $watchdog->remplace_parametres($_string, '', $condition);

        $fromHumanReadable = jeedom::fromHumanReadable($_string);
        $toHumanReadable = jeedom::toHumanReadable($_string);
        // $watchdog->log('debug', '║ │ ║ ╚═╦═>   ' . $fromHumanReadable);
        $watchdog->log('info', '║ │ ╦═>   ' . $toHumanReadable);

        $condition->setConfiguration('calcul', scenarioExpression::setTags($fromHumanReadable));  // stocke les valeurs du calcul
        $condition->setConfiguration('expression', $toHumanReadable);  // stocke l expression développée

        $return = evaluate(scenarioExpression::setTags($fromHumanReadable, $scenario, true)); // apparemment, setTags permet de récupérer les valeurs des variables dans la formule

        if (is_bool($return)) {
            if ($return == true) $return = 'True';
            else $return = 'False';
        } else {
            $watchdog->log('warning', '║ │ ╦═══> Problème avec l\'expresion:    ' . $fromHumanReadable);
            $return = 'Error';
        }

        return $return;
    }
    public function CalculResultatGlobal($_contexte)
    {
        $condition = $this;
        $watchdog = $condition->getEqLogic();

        //        $watchdog->log('debug', '║ ─────────────────────────[ Calcul du résultat Global ]────────────────────────────────────────────────────────────────────────────────────');


        $typeControl = $watchdog->getConfiguration('typeControl');
        if ($typeControl == "ET") {
            $leResultatdelaBoucle = true;
        } else {
            $leResultatdelaBoucle = false;
        }

        //On évalue toutes les commandes du watchdog pour calculer le résultat global des tests
        foreach ($watchdog->getCmd('info') as $controle) {
            if ($controle->getLogicalId() == "resultatglobal")  // on ignore resultatglobal
                continue;
            if ($controle->getConfiguration('disable', '0') != '0')   // ignore les conditions desactivees
                continue;
            $leResultat = $controle->getConfiguration('resultat');
            $watchdog->log('info', '║ │ ╚═>[' . $typeControl . "] " . $leResultat . ' (contrôle "' . $controle->getName() . '")');

            if ($leResultat == 'True' || $leResultat == 'False') {
                //Résultat valide, on continue le test
                if ($typeControl == "ET") {
                    if ($leResultat == 'False')
                        $leResultatdelaBoucle = false; // On est sur une fonction ET
                } else {
                    if ($leResultat == 'True')
                        $leResultatdelaBoucle = true; // On est sur une fonction OU
                }
            }
        }
        if ($leResultatdelaBoucle == true)
            $leResultatdelaBoucle = 'True';
        else
            $leResultatdelaBoucle = 'False';
        // $watchdog->log('debug', "║ │   ╚═════>[==] " . $leResultatdelaBoucle);
        return $leResultatdelaBoucle;
    }

    public function ConfigureConditionDisplay($resultat)
    // gère l'affichage et les caractéristique de la condition
    {

        $condition = $this;
        $watchdog = $this->getEqLogic();

        // indique quelle est la valeur du Resultat pour laquelle le controle est OK
        if ($watchdog->getConfiguration("ResultatOK_Courant", '0') == '0') {
            $ResultatOK = 'False';
            $invertBinary = '1';
        } else {
            $ResultatOK = 'True';
            $invertBinary = '0';
        }
        // applique l'affichage inversé
        $invertBinaryCurrent = $condition->getDisplay("invertBinary", '0');
        if ($invertBinary <> $invertBinaryCurrent) {
            $condition->setDisplay("invertBinary", $invertBinary);
        }

        // historique 
        $ResultatHistory = $watchdog->getConfiguration("ResultatHistory_Courant", '');
        if ($ResultatHistory <> '') {  // historique demandé
            if ($condition->getIsHistorized() == 0) {
                $condition->setIsHistorized(1);
                $condition->setDisplay('graphType', 'area'); // affichage aire sur historique
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

        // configure les conditions si on n'est pas en mode ET/OU
        if ($watchdog->getConfiguration('typeControl') == '') {

            $DisplayOnlyConditionNonOK = $watchdog->getConfiguration("DisplayOnlyConditionNonOK_Courant");
            // affiche le controle quelque soit le résultat
            if ($DisplayOnlyConditionNonOK == '0') {
                $condition->setIsVisible(1);
            } else {
                // Affiche ou non la commande en fonction de l'état du watchdog
                if ($resultat == $ResultatOK) {
                    $condition->setIsVisible(0);
                } else {
                    $condition->setIsVisible(1);
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
        } else {  // on n'est pas en mode ET/OU
            if ($condition->getLogicalId() != "resultatglobal") {
                // désactivage l'affichage car on n'affiche que le résultat global
                $condition->setIsVisible(0);
            } else {
                // configure l'affichage du resultat global
                $cmdResultatGlobal = $condition;

                // place le resultat global en bas de la liste
                $cmdResultatGlobal->setOrder(9999);

                // affiche le résultat global dans le widget
                if ($cmdResultatGlobal->getIsVisible() == '0') {
                    $cmdResultatGlobal->setIsVisible(1);
                }

                // pas utile d'afficher le nom de la commande
                if ($cmdResultatGlobal->getDisplay("showNameOndashboard", '1') == '1') {
                    $cmdResultatGlobal->setDisplay("showNameOndashboard", '0');
                }

                if ($cmdResultatGlobal->getDisplay("showNameOnmobile", '1') == '1') {
                    $cmdResultatGlobal->setDisplay("showNameOnmobile", '0');
                }

                // applique les templates à résultat global
                $template_resultat_dashboard = $watchdog->getConfiguration("template_resultatglobal_dashboard_Courant");
                if ($template_resultat_dashboard <> $cmdResultatGlobal->getTemplate("dashboard", "core::default")) {
                    $cmdResultatGlobal->setTemplate("dashboard", $template_resultat_dashboard);
                }

                $template_resultat_mobile = $watchdog->getConfiguration("template_resultatglobal_mobile_Courant");
                if ($template_resultat_mobile <> $cmdResultatGlobal->getTemplate("mobile", "core::default")) {
                    $cmdResultatGlobal->setTemplate("mobile", $template_resultat_mobile);
                }
            }
        }
    }


    // recherche le premier Eqlogic dans la formule
    public function cherche_equipement_dans_expression($text)
    {

        $condition = $this;
        $watchdog = $this->getEqLogic();
        $text = jeedom::fromHumanReadable($text);

        // recherche le 1er équipement
        preg_match_all("/#eqLogic([0-9]*)#/", $text, $matches);
        foreach ($matches[1] as $eqLogic_id) {
            if (is_numeric($eqLogic_id)) {
                $eqLogic = eqLogic::byId($eqLogic_id);
                if (is_object($eqLogic)) {
                    return '#eqLogic' . $eqLogic_id . '#';
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
                    return '#eqLogic' . $cmd->getEqLogic()->getId() . '#';
                }
            }
        }

        // rien trouvé
        return "";
    }

    // recherche le premier Cmd dans la formule
    public function cherche_commande_dans_expression($text)
    {

        $condition = $this;
        $watchdog = $this->getEqLogic();
        $text = jeedom::fromHumanReadable($text);
        preg_match_all("/#[0-9]*#/", $text, $matches);
        foreach ($matches[0] as $cmd_id) {
            $cmd_id = str_replace('#', '', $cmd_id);
            if (is_numeric($cmd_id)) {
                $cmd = cmd::byId($cmd_id);
                if (is_object($cmd)) {
                    return '#' . $cmd_id . '#';
                }
            }
        }

        // rien trouvé
        return "";
    }


    public function preRemove()
    {
        $condition = $this;
        $watchdog = $this->getEqLogic();

        // supprime les résultats dans le virtuel du reporting
        if ($watchdog->getConfiguration("ReportingSuppressionAutomatique_Courant", '') != '0') {
            $condition->report_cmd_delete();
        }

        return true;
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
            $watchdog->watchdog_Update('REFRESH');
            return true;
        }
    }

    //  code nécessaire pour compatibilité avec les versions antérieures à aout 2025
    // à supprimer dens des mises à jour ultérieures
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

            $watchdog_ok = '0'; // indique quelle est la valeur de Resultat Global/Condition pour laquelle le whatchdog est OK
            if ($watchdog->getConfiguration("ResultatOK_Courant", '0') == '1')
                $watchdog_ok = '1';
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
                    $watchdog->log('debug', '++++ Création dans le virtuel de reporting ' . $eqVirtualReport->getHumanName() . ' de la commande ' . $cmdresult->getHumanName() . ' du watchdog ' . $watchdog->getHumanName());
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
                    $cmdReportWatchdog->setDisplay('graphType', 'area');
                    $cmdReportWatchdog->setConfiguration('type', 'watchdog'); // pour identifier les commandes lors du clean/rename
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
                $ReportingHistory = $watchdog->getConfiguration("ReportingHistory_Courant", '');
                if ($ReportingHistory <> '') {  // historique demandé
                    if ($cmdReportWatchdog->getIsHistorized() == 0) {
                        $cmdReportWatchdog->setIsHistorized(1);
                        $cmdReportWatchdog->setDisplay('graphType', 'area'); // affichage aire sur historique
                        $update_cmdReportWatchdog = true;
                    }
                    $historyPurge = $ReportingHistory;
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
                if ($DisplayOnlyReportingNonOK <> '1') {
                    if ($cmdReportWatchdog->getIsVisible() == '0') {
                        $cmdReportWatchdog->setIsVisible(1);
                        $update_cmdReportWatchdog = true;
                    }
                } else { // affiche le controle uniquement si résultat non OK
                    // récupère le résultat de la commande Resultat 
                    $WatchdogResultat = $cmdresult->execCmd();
                    // Affiche ou non la commande associée au watchdog en fonction de l'état du watchdog
                    if ($WatchdogResultat == $watchdog_ok) {
                        if ($cmdReportWatchdog->getIsVisible() == '1') {
                            $cmdReportWatchdog->setIsVisible('0');
                            $update_cmdReportWatchdog = true;
                        }
                    }
                    if ($WatchdogResultat != $watchdog_ok) {
                        if ($cmdReportWatchdog->getIsVisible() == '0') {
                            $cmdReportWatchdog->setIsVisible('1');
                            $update_cmdReportWatchdog = true;
                        }
                    }
                }

                if ($update_cmdReportWatchdog == true) {
                    $cmdReportWatchdog->save();
                }
            }
        }
    }
    // -----------------------------------------------------
    // Suppression du reporting lié à une commande
    // utilisé en cas de suppression d'un watchdog ou d une condition
    // -----------------------------------------------------
    public function report_cmd_delete()
    {
        $cmdresult = $this;
        $watchdog = $this->getEqLogic();
        $VirtualReportName = $watchdog->getConfiguration("VirtualReport_Courant");

        if (trim($VirtualReportName) <> '') {

            unset($eqVirtualReport);
            try {
                $eqVirtualReport = eqLogic::byString($VirtualReportName);
            } catch (Exception $e) {
                $watchdog->log('warning', '╠═══> Virtuel nécessaire pour le reporting non défini ' . $VirtualReportName);
            }
            if (is_object($eqVirtualReport)) {

                $eqVirtualReportId = $eqVirtualReport->getId();

                // récupère la commande correspondant à l'Id de la condition
                $cmdresultId = $cmdresult->getId();

                unset($cmdReportWatchdog);
                $cmdReportWatchdog = cmd::byEqLogicIdAndLogicalId($eqVirtualReportId, $cmdresultId);
                if (is_object($cmdReportWatchdog)) {

                    $watchdog->log('debug', '++++ Suppression dans le virtuel de reporting ' . $eqVirtualReport->getHumanName() . ' de la commande ' . $cmdresult->getHumanName() . ' du watchdog ' . $watchdog->getHumanName());
                    $cmdReportWatchdog->remove();
                }
            }
        }
    }
    // -----------------------------------------------------
    // Cache les résultats dans le reporting
    // utilisé en cas d'inactivation du controle ou de l'équipement
    // -----------------------------------------------------
    public function report_cmd_notVisible()
    {
        $cmdresult = $this;
        $watchdog = $this->getEqLogic();
        $VirtualReportName = $watchdog->getConfiguration("VirtualReport_Courant");
        if (trim($VirtualReportName) <> '') {

            unset($eqVirtualReport);
            try {
                $eqVirtualReport = eqLogic::byString($VirtualReportName);
            } catch (Exception $e) {
                $watchdog->log('warning', '╠═══> Virtuel nécessaire pour le reporting non défini ' . $VirtualReportName);
            }
            if (is_object($eqVirtualReport)) {

                $eqVirtualReportId = $eqVirtualReport->getId();

                // récupère la commande correspondant à l'Id de la condition
                $cmdresultId = $cmdresult->getId();

                unset($cmdReportWatchdog);
                $cmdReportWatchdog = cmd::byEqLogicIdAndLogicalId($eqVirtualReportId, $cmdresultId);
                if (is_object($cmdReportWatchdog)) {
                    if ($cmdReportWatchdog->getIsVisible() == 1) {
                        $watchdog->log('debug', '++++ Rend invisible dans le virtuel de reporting ' . $eqVirtualReport->getHumanName() . ' la commande ' . $cmdresult->getHumanName() . ' du watchdog ' . $watchdog->getHumanName());
                        $cmdReportWatchdog->setIsVisible(0);
                        $cmdReportWatchdog->save();
                    }
                }
            }
        }
    }

    // -----------------------------------------------------
    // Supprime du reporting les commandes non utilisées qui ne correspondent à aucune commande existante
    // dans le reporting du watchdog 
    // -----------------------------------------------------
    public static function report_cmd_clean($VirtualReportId)
    {

        unset($eqVirtualReport);
        try {
            $eqVirtualReport = eqLogic::byId($VirtualReportId);
        } catch (Exception $e) {
            log::add('watchdog', 'warning', 'report_cmd_clean: Virtuel non défini ID=' . $VirtualReportId);
            return False;
        }


        $VirtualReportName = $eqVirtualReport->getHumanName();

        log::add('watchdog', 'info', '----------------------------------------------------------------------------------------');
        log::add('watchdog', 'info', 'Suppresion des commandes infos orphelines dans ' . $VirtualReportName);
        if (config::byKey('ReportingSuppressionAutomatique', 'watchdog', '1') == '0') {
            log::add('watchdog', 'info', 'Suppresion des commandes infos orphelines dans ' . $VirtualReportName . ' non effectuée car la suppression des actions orphelines est désactivée dans la configuration du plugin');
            return;
        }
        foreach ($eqVirtualReport->getCmd('info') as $cmdresult) {

            if ($cmdresult->getConfiguration('type') != 'watchdog') continue;

            $remove = false;
            // récupère la commande correspondant à l'Id de la condition
            $cmd_id = $cmdresult->getLogicalId();
            unset($cmd);
            $cmd = cmd::byId($cmd_id);
            if (!is_object($cmd)) {
                $remove = true;
            } else {
                $watchdog = $cmd->getEqLogic();
                // ne gère pas le Resultat Global si pas de condition ET / OU
                $typeControl = $watchdog->getConfiguration('typeControl');
                if (($typeControl == '' && $cmd->getLogicalId() == 'resultatglobal') || ($typeControl != '' && $cmd->getLogicalId() != 'resultatglobal')) {
                    $remove = true;
                }
            }
            if ($remove == true) {
                log::add('watchdog', 'info', 'Suppresion de ' . $cmdresult->getHumanName());
                $cmdresult->remove();
            }
        }

        log::add('watchdog', 'info', '----------------------------------------------------------------------------------------');
        return true;
    }

    // -----------------------------------------------------
    // Renomme les commandes dans le reporting du watchdog 
    // -----------------------------------------------------
    public static function report_cmd_rename($VirtualReportId)
    {
        unset($eqVirtualReport);
        try {
            $eqVirtualReport = eqLogic::byId($VirtualReportId);
        } catch (Exception $e) {
            log::add('watchdog', 'warning', 'report_cmd_clean: Virtuel non défini ID=' . $VirtualReportId);
            return False;
        }

        $VirtualReportName = $eqVirtualReport->getHumanName();

        log::add('watchdog', 'info', '----------------------------------------------------------------------------------------');
        log::add('watchdog', 'info', 'Rename des commandes infos dans ' . $VirtualReportName);

        $eqVirtualReportId = $eqVirtualReport->getId();

        // renomme la commande avec l'ID de la commande cible
        foreach ($eqVirtualReport->getCmd('info') as $cmdresult) {
            if ($cmdresult->getConfiguration('type') != 'watchdog') continue;
            log::add('watchdog', 'info', 'Rename de ' . $cmdresult->getHumanName() . ' en  ' . $cmdresult->getLogicalId());
            $cmdresult->setName($cmdresult->getLogicalId());
            $cmdresult->save();
        }

        foreach ($eqVirtualReport->getCmd('info') as $cmdresult) {
            if ($cmdresult->getConfiguration('type') != 'watchdog') continue;
            // renomme la commande à partir de la commande source
            $cmd_id = $cmdresult->getLogicalId();
            unset($cmd);
            $cmd = cmd::byId($cmd_id);
            if (is_object($cmd)) {
                $name = $cmd->getEqLogic()->getName();
                if ($cmd->getLogicalId() != 'resultatglobal')
                    $name .= ': ' . $cmd->getName();
                // teste si le nom de la commande est déjà attribué    
                // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
                if (is_object(cmd::byEqLogicIdCmdName($eqVirtualReportId, $name))) {
                    $count = 1;
                    while (is_object(cmd::byEqLogicIdCmdName($eqVirtualReportId, substr($name, 0, 100) . "..." . $count))) {
                        $count++;
                    }
                    $name = substr($name, 0, 100) . "..." . $count;
                }
                $cmdresult->setName($name);
                $cmdresult->save();
                log::add('watchdog', 'info', 'Rename de ' .  $cmdresult->getLogicalId()  . ' en ' . $cmdresult->getHumanName());
            }
        }
        log::add('watchdog', 'info', '----------------------------------------------------------------------------------------');
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
