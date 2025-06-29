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
        $this->setIsEnable(1);
        $this->setIsVisible(1);
        $this->setConfiguration('autorefresh', '*/5 * * * *');
    }

    public function preSave()
    {

        log::add('watchdog', 'info', '┌──────────────────────[Sauvegarde du Watchdog ' . $this->getName() . ']────────────────────────────────────────────────────────────────────────────────────');


        if ((substr($this->getConfiguration('dernierLancement'), 0, 7)) == "PRECRON") {
            $this->setConfiguration('dernierLancement', 'CRON ' . date("d.m.Y") . " " . date("H:i:s"));
        } else {
            $this->setConfiguration('dernierLancement', 'SAVE ' . date("d.m.Y") . " " . date("H:i:s"));
        }

        $ResultatGlobalOK = config::byKey('ResultatGlobalOK', 'watchdog', '1');
        $ResultatGlobalOK = $this->getConfiguration("ResultatGlobalOK", $ResultatGlobalOK);
        $this->setConfiguration("ResultatGlobalOKCourant", $ResultatGlobalOK);
        if ($ResultatGlobalOK == '1') {
            $invertBinary = '0';
        } else {
            $invertBinary = '1';
        }
        $this->setDisplay("invertBinary", $invertBinary);

        $VirtualReport = trim($this->getConfiguration("VirtualReport", ''));
        if ($VirtualReport == '')
            $VirtualReport = trim(config::byKey('VirtualReport', 'watchdog', ''));
        $this->setConfiguration("VirtualReportCourant", $VirtualReport);

        $ReportOnlyNonOK = config::byKey('VirtualReport', 'ReportOnlyNonOK', '1');
        $ReportOnlyNonOK = $this->getConfiguration("ReportOnlyNonOK", $ReportOnlyNonOK);
        $this->setConfiguration("ReportOnlyNonOKCourant", $ReportOnlyNonOK);
    }

    public function postSave()
    {
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
            // Applique l'affichage inversé si nécessaire
            $ResultatGlobalOK = $this->getConfiguration("ResultatGlobalOKCourant", "1");
            if ($ResultatGlobalOK == '1') {
                $invertBinary = '0';
            } else {
                $invertBinary = '1';
            }
            $invertBinaryCurrent = $cmdResultatGlobal->getDisplay("invertBinary", '0');
            if ($invertBinary <> $invertBinaryCurrent) {
                $cmdResultatGlobal->setDisplay("invertBinary", $invertBinary);
                $cmdResultatGlobal->save();
            }

            // -----------------------------------------------------
            // Reporting des watchdogs
            // -----------------------------------------------------
            $VirtualReportName = $this->getConfiguration("VirtualReportCourant");

            if (trim($VirtualReportName) <> '') {

                $ReportOnlyNonOK = $this->getConfiguration("ReportOnlyNonOKCourant");

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
                        $cmdReportWatchdog->setTemplate('dashboard', 'core::line');
                        $cmdReportWatchdog->setTemplate('mobile', 'core::line');
                        $cmdReportWatchdog->setType('info');
                        $cmdReportWatchdog->setUnite('');
                        $cmdReportWatchdog->setSubType('binary');
                        $cmdReportWatchdog->setDisplay('generic_type', 'GENERIC_INFO');
                        $cmdReportWatchdog->setDisplay('graphType', 'column');
                        $cmdReportWatchdog->save();
                    }

                    $update_cmdReportWatchdog = false; // pour savoir si il faut faire une MAJ

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

                    // n'affiche pas le watchdog si pas de condition ET / OU
                    if ($typeControl <> 'ET' && $typeControl <> 'OU') {
                        if ($cmdReportWatchdog->getIsVisible() <> '1') {
                            $cmdReportWatchdog->setIsVisible(0);
                            $update_cmdReportWatchdog = true;
                        }
                    } else {
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
                    }

                    if ($update_cmdReportWatchdog == true) {
                        $cmdReportWatchdog->save();
                    }
                }
            }
        }


        log::add('watchdog', 'info', "└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
    }

    public function lancerControle($watchdog)
    {

        log::add('watchdog', 'debug', "╠════> Avant de lancer le contrôle on lance les actions d'avant contrôle (s'il y en a).");

        foreach ($watchdog->getConfiguration("watchdogAction") as $action) {
            try {
                $options = [];
                if (isset($action['options'])) $options = $action['options'];
                if (($action['actionType'] == "Avant") && $options['enable'] == '1') {
                    // On va remplacer #controlname# par le nom du controle dans tous les champs du array "options"
                    foreach ($options as $key => $option) {
                        $options[$key] = str_replace("#controlname#", $this->getName(), $option);
                    }
                    foreach ($options as $key => $option) {
                        $options[$key] = str_replace("#title#", $watchdog->getName(), $option);
                    }
                    log::add('watchdog', 'debug', 'Exécution de la commande ' . $action['cmd'] . " avec comme option(s) : " . json_encode($options));
                    scenarioExpression::createAndExec('action', $action['cmd'], $options);
                }
            } catch (Exception $e) {
                log::add('watchdog', 'error', __('function trigger : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        log::add('watchdog', 'debug', '╠════> On lance les contrôles :');

        foreach ($watchdog->getCmd('info') as $cmd) {
            // On sauvegarde le dernier résultat dans AvantdernierResultat
            $cmd->setConfiguration('resultatAvant', $cmd->getConfiguration('resultat'));
            if ($cmd->getLogicalId() != "resultatglobal") { // on ignore resultatglobal
                $cmd->save();
            }
        }

        // On va faire le test GLOBAL
        $typeControl = $this->getConfiguration('typeControl');
        if ($typeControl != "") {    // Que si en OU ou en ET
            log::add('watchdog', 'debug', '╠═╦══> Calcul du résultat Global :');

            $typeAction = $this->getConfiguration('typeAction');

            $traceleCalcul = "Calcul : Init à ";

            if ($typeControl == "ET") {
                $leResultatdelaBoucle = true;
            } else {
                $leResultatdelaBoucle = false;
            }

            //On passe toutes les commandes de l'eqLogic pour calculer le résultat global des tests
            foreach ($this->getCmd('info') as $cmd) {
                if ($cmd->getLogicalId() != "resultatglobal") { // on ignore resultatglobal
                    $leResultat = $cmd->getConfiguration('resultat');
                    log::add('watchdog', 'debug', '║ ╚═══>[' . $typeControl . "] " . $leResultat . ' (' . $cmd->getName() . ')');

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
            //$dernierLancement=$this->getConfiguration('dernierLancement');
            //$dernierLancement=substr($dernierLancement, 0, 4);
            //---------------------------------------------------

            $resultatPrecedent = $this->getConfiguration('dernierEtat');
            $this->setConfiguration('dernierEtat', $leResultatdelaBoucle);
            //Pour que le resultat soit accessible dans une commande info, on copie dernierEtat dans resultatglobal
            $this->checkAndUpdateCmd('resultatglobal', $leResultatdelaBoucle);


            if ($typeAction == 'ALL') {
                $resultatPrecedent = "";
                log::add('watchdog', 'debug', 'Mode action à chaque contrôle : Désactivation du Résultat Précédent');
            }

            $typeControl = $this->getConfiguration('typeControl');
            if ($typeControl != "") {
                // On est ici sur le résultat général des controles, on ne fait rien si on est en mode "Actions sur chaque controle indépendamment"
                if ($resultatPrecedent != $leResultatdelaBoucle) {
                    log::add('watchdog', 'debug', '╠═════> Bilan global : [Résultat Précédent=' . $resultatPrecedent . '] [Nouveau Résultat=' . $leResultatdelaBoucle . ']-> On lance Trigger');
                    self::trigger($leResultatdelaBoucle);
                } else {
                    log::add('watchdog', 'debug', '╠═════> Bilan global : [Résultat Précédent=' . $resultatPrecedent . '] [Nouveau Résultat=' . $leResultatdelaBoucle . ']-> On ne fait rien');
                }
            }
        }
    }

    public function trigger($passe)
    {
        // La fonction trigger ne doit être appellée sur le résultat général des controles, on ne fait rien si on est en mode "Actions sur chaque cvontrole indépendamment"

        log::add('watchdog', 'debug', '╠═════> On lance les actions qui correspondent au passage de [' . $this->getName() . '] à ' . $passe);
        foreach ($this->getConfiguration("watchdogAction") as $action) {
            try {
                $options = [];
                if (isset($action['options'])) $options = $action['options'];
                if (($action['actionType'] == $passe) && $options['enable'] == '1') {

                    foreach ($options as $key => $option) {
                        $options[$key] = str_replace("#title#", $this->getName(), $option);
                    }

                    log::add('watchdog', 'debug', '**************************************************************************************************************************');
                    log::add('watchdog', 'debug', '** Exécution de la commande ' . jeedom::toHumanReadable($action['cmd']) . " avec comme option(s) : " . json_encode($options));
                    log::add('watchdog', 'debug', '**************************************************************************************************************************');
                    scenarioExpression::createAndExec('action', $action['cmd'], $options);
                }
            } catch (Exception $e) {
                log::add('watchdog', 'error', __('function trigger : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
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

    // Exécution des controles et actions d'un watchdog
    public function whatchdog_Update()
    {
        $watchdog = $this;
        try {
            $watchdog->setConfiguration('avantDernierLancement', $watchdog->getConfiguration('dernierLancement'));
            $watchdog->setConfiguration('dernierLancement', 'PRECRON ' . date("d.m.Y") . " " . date("H:i:s")); // PRECON c'est pour signaler que le CRON va etre sauvegarder

            log::add('watchdog', 'info', '╔══════════════════════[Lancement du Watchdog ' . $watchdog->getName() . ']════════════════════════════════════════════════════════════════════════════');
            $watchdog->lancerControle($watchdog);
            log::add('watchdog', 'info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");
        } catch (Exception $exc) {
            log::add('watchdog', 'error', __('Erreur pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $exc->getMessage());
        }
        $watchdog->save();
    }
}

class watchdogCmd extends cmd
{

    public function faireTestExpression($_string)
    {

        $scenario = null;
        //---------------------------------------------------
        // On va chercher les valeurs de tempo 1 2 et 3
        $eqLogic = $this->getEqLogic();
        $tempo1 = $eqLogic->getConfiguration('tempo1');
        $tempo2 = $eqLogic->getConfiguration('tempo2');
        $tempo3 = $eqLogic->getConfiguration('tempo3');
        //---------------------------------------------------
        $_string = str_replace("#tempo1#", $tempo1, $_string);
        $_string = str_replace("#tempo2#", $tempo2, $_string);
        $_string = str_replace("#tempo3#", $tempo3, $_string);
        //---------------------------------------------------
        $_string = str_replace("#internalAddr#", '"' . config::byKey('internalAddr') . '"', $_string);

        $fromHumanReadable = jeedom::fromHumanReadable($_string);
        $this->setConfiguration('calcul', scenarioExpression::setTags($fromHumanReadable));

        $return = evaluate(scenarioExpression::setTags($fromHumanReadable, $scenario, true));
        if (is_bool($return)) {
            if ($return) $return = 'True';
            else $return = 'False';
        }

        return $return;
    }

    public function triggerEquip($passe)
    {
        $eqLogic = $this->getEqLogic();
        $typeControl = $eqLogic->getConfiguration('typeControl');
        $ideqLogic = $eqLogic->getId();
        if ($typeControl == "") {
            // La fonction trigger est appellé sur le résultat général des controles, on ne fait rien si on n'est pas en mode "Actions sur chaque cvontrole indépendamment"

            log::add('watchdog', 'debug', '╠═════> On lance les actions qui correspondent au passage de [' . $this->getName() . '] à ' . $passe);

            if ($eqLogic->getConfiguration('logspecifique'))
                log::add('watchdog_' . $ideqLogic, 'info', '╔══════════════════════[' . $this->getName() . ' est passé à ' . $passe . ']════════════════════════════════════════════════════════════════════════════');

            foreach ($eqLogic->getConfiguration("watchdogAction") as $action) {
                try {
                    $options = [];
                    if (isset($action['options'])) $options = $action['options'];
                    if (($action['actionType'] == $passe) && $options['enable'] == '1') {
                        // On va remplacer #controlname# par le nom du controle dans tous les champs du array "options"
                        foreach ($options as $key => $option) {
                            $options[$key] = str_replace("#controlname#", $this->getName(), $option);
                        }
                        foreach ($options as $key => $option) {
                            $options[$key] = str_replace("#title#", $eqLogic->getName(), $option);
                        }

                        if ($options['log'] == '1') {
                            log::add('watchdog_' . $ideqLogic, 'info', '╠═══> Exécution de la commande ' . jeedom::toHumanReadable($action['cmd']) . " avec comme option(s) : " . json_encode($options));
                        }

                        log::add('watchdog', 'debug', '**************************************************************************************************************************');
                        log::add('watchdog', 'debug', '** Exécution de la commande ' . jeedom::toHumanReadable($action['cmd']) . " avec comme option(s) : " . json_encode($options));
                        log::add('watchdog', 'debug', '**************************************************************************************************************************');
                        scenarioExpression::createAndExec('action', $action['cmd'], $options);
                    }
                } catch (Exception $e) {
                    log::add('watchdog', 'error', __('function trigger : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                }
            }
            if ($eqLogic->getConfiguration('logspecifique'))
                log::add('watchdog_' . $ideqLogic, 'info', '╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════');
        }
    }

    /*     * *********************Methode d'instance************************* */

    public function preSave()
    {
        if ($this->getType() == 'action') return; //On ne fait pas le test si c'est une Commande Action		
        if ($this->getLogicalId() == 'resultatglobal') return; //On ne fait pas le test si c'est la commande 	resultatglobal	
        log::add('watchdog', 'info', '║ ┌──────────────────────[Sauvegarde du Contrôle ' . $this->getName() . ']────────────────────────────────────────────────────────────────────────────────────');

        // On va chercher si on est en SAUVEGARDE ou en CRON
        $eqLogic = $this->getEqLogic();
        $dernierLancement = $eqLogic->getConfiguration('dernierLancement');
        $dernierLancement = substr($dernierLancement, 0, 4);

        $resultatPrecedent = $this->getConfiguration('resultat');

        $resultat = self::faireTestExpression($this->getConfiguration('controle'));

        log::add('watchdog', 'debug', '║ │ ╠═╦═>     Execution de [' . $this->getName() . ']');
        log::add('watchdog', 'debug', '║ │ ║ ╚═╦═>   ' . jeedom::toHumanReadable($this->getConfiguration('controle')));
        log::add('watchdog', 'debug', '║ │ ║   ╚═══> Resultat : ' . $resultat);

        $_string = $this->getConfiguration('controle');

        if ($resultatPrecedent != $resultat) {
            $this->setConfiguration('resultat', $resultat);

            // Si le résultat a changé, il faut actualiser le calcul du résultat global, pour cela, on utilise la variable cmd.configuration.aChange qui traitera le calcul dans postSave
            $this->setConfiguration('aChange', true);
            //On ne va lancer le trigger que si on est en mode CRON et pas si on est en mode SAVE
            if (($dernierLancement == "CRON") || ($dernierLancement == "PREC"))
                $this->triggerEquip($resultat);
        }
    }

    public function postSave()
    {
        if ($this->getConfiguration('aChange')) {
            // Cette boucle est déclenchée quand le résultat du controle a changé, il faut ainsi relancer le save du resultat global
            $this->setConfiguration('aChange', false);
            $this->save();
            $this->getEqLogic()->save(); //enregistre l'équipement entier (et donc le resultat global des controles)
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
        $eqLogic = $this->getEqLogic();

        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new \Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }

        if ($this->getLogicalId() == 'refresh') {
            log::add('watchdog', 'info', '┌──────────────────────[Refresh de ' . $eqLogic->getName() . ']────────────────────────────────────────────────────────────────────────────────────');
            log::add('watchdog', 'info', "└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");
            $eqLogic->whatchdog_Update();
            return true;
        }
    }

    /*     * **********************Getteur Setteur*************************** */
}
