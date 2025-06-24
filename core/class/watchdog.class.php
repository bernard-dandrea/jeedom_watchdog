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

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/* * ***************************Includes********************************* */

require_once __DIR__  . '/../../../../core/php/core.inc.php';



class watchdog extends eqLogic
{
    /*     * *************************Attributs****************************** */



    /*     * *********************Méthodes d'instance************************* */

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
    }

    public function postSave()
    {
        unset($cmd);
        $cmd = $this->getCmd(null, 'refresh');
        if (!is_object($cmd)) {

            log::add('watchdog', 'debug', '╠═══> Ajout de la commande action refresh à ' . $this->getName());
            $cmd = new watchdogCmd();
            $cmd->setName('Refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setLogicalId('refresh');
            $cmd->setIsVisible(1);
            $cmd->setDisplay('generic_type', 'GENERIC_INFO');
            $cmd->save();
        }

        unset($cmd);
        $cmd = $this->getCmd(null, "resultatglobal");
        if (!is_object($cmd)) {
            log::add('watchdog', 'debug', '╠═══> Ajout de la commande info resultatglobal à ' . $this->getName());
            $cmd = new watchdogCmd();
            $cmd->setType('info');
            $cmd->setLogicalId("resultatglobal");
            $cmd->setSubType('binary');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName("Résultat Global");
            $cmd->setIsVisible(1);

            $cmd->save();
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
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */

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
            if ($eqLogic->getConfiguration('logspecifique')) log::add('watchdog_' . $ideqLogic, 'info', '╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════');
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
        if ($this->getLogicalId() == 'resultatglobal') {
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
