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



class watchdog extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */
//public static $_boucleEnCours = "99999999";
//public static $_boucleEnCours="dd";

 /* public static function boucleEnCours()
  {
	  //$_boucleEnCours = "99999999";
     return $_boucleEnCours; //c'est là que je coince (est-ce possible au moins ?)
  }
*/
    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
 	//log::add('watchdog','debug','[eqLogic] preInsert de '.$this->getName());        


		$this->setIsEnable(1);
		$this->setIsVisible(1);
		$this->setConfiguration('autorefresh', '*/5 * * * *');
    }

    public function postInsert() {
 	//log::add('watchdog','debug','[eqLogic] postInsert de '.$this->getName());        
        
    }

	public function executeAction_aaaaaaaaaaaaaaavirer($action, $options) {
								

		
		log::add('watchdog','debug','Exécution de la commande (dans executeAction) ' . $action . " avec comme option(s) : ". json_encode($options));
		scenarioExpression::createAndExec('action', $action, $options);		
	}

    public function preSave() {
	//log::add('watchdog','debug','[eqLogic] preSave de '.$this->getName());	
 	//log::add('watchdog','debug','[*****dernierLancement1] de '.$this->getName()." vaut :".$this->getConfiguration('dernierLancement'));	
	
	log::add('watchdog','info','┌──────────────────────[Sauvegarde du Watchdog '.$this->getName().']────────────────────────────────────────────────────────────────────────────────────');



	if ((substr($this->getConfiguration('dernierLancement'), 0, 7)) == "PRECRON") {
		//$this->setConfiguration('avantDernierLancement',$this->getConfiguration('dernierLancement')); 
		$this->setConfiguration('dernierLancement','CRON '.date("d.m.Y")." ".date("H:i:s"));
		//log::add('watchdog','debug','[SAUVEGARDE CRON de '.$this->getName().']');
	}
	else {
		//$this->setConfiguration('avantDernierLancement',$this->getConfiguration('dernierLancement')); 
		$this->setConfiguration('dernierLancement','SAVE '.date("d.m.Y")." ".date("H:i:s"));
		//log::add('watchdog','debug','[SAUVEGARDE de '.$this->getName().']');
	}
 	//log::add('watchdog','debug','[*****dernierLancement2] de '.$this->getName()." vaut :".$this->getConfiguration('dernierLancement'));	

	//c'était ici l'ancien test global
    }

    public function postSave() {
 	//log::add('watchdog','debug','[eqLogic] postSave de '.$this->getName());        
		$cmd = $this->getCmd(null, "resultatglobal");
		if (!is_object($cmd)) {
			log::add('watchdog', 'debug', '╠═══> Ajout de la commande info resultatglobal à '.$this->getName());
			$cmd = new watchdogCmd();
			$cmd->setType('info');
			$cmd->setLogicalId("resultatglobal");
			$cmd->setSubType('binary');
			$cmd->setEqLogic_id($this->getId());
			$cmd->setName("Résultat Global");
			$cmd->setIsVisible(1);
            //$cmd->setOrder("2");
			//$cmd->setDisplay('title_disable', 0);
			// BD code pour automatiser l'utilisation d'un Template spécifique
			// à activer ultérieurement
			/*
			$cmd->setTemplate("dashboard", "custom::watchdog");
			$cmd->setTemplate("mobile", "custom::watchdog");
			$cmd->setDisplay("showNameOndashboard", "0");
			$cmd->setDisplay("showNameOnmobile", "0");
   			*/
			// BD END
			
			$cmd->save(); 
		}    else {
			//log::add('watchdog', 'debug', '╠═══> OK resultatglobal');
		}
	log::add('watchdog', 'info', "└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");

	}

    public function preUpdate() {
 	//log::add('watchdog','debug','[eqLogic] preUpdate de '.$this->getName());        
    }

    public function postUpdate() {
 	//log::add('watchdog','debug','[eqLogic] postUpdate de '.$this->getName());        
    }

    public function preRemove() {
 	log::add('watchdog','debug','[eqLogic] preRemove de '.$this->getName());        
    }

    public function postRemove() {
 	log::add('watchdog','debug','[eqLogic] postRemove de '.$this->getName());        
    }
	
	public function lancerControle($watchdog) {
		
		log::add('watchdog', 'debug', "╠════> Avant de lancer le contrôle on lance les actions d'avant contrôle (s'il y en a).");
		
				foreach ($watchdog->getConfiguration("watchdogAction") as $action) {
					try {
							$options = [];
							if (isset($action['options'])) $options = $action['options'];
					if (($action['actionType'] == "Avant") && $options['enable'] == '1'){
								// On va remplacer #controlname# par le nom du controle dans tous les champs du array "options"
								foreach ($options as $key => $option) {
									$options[$key]=str_replace("#controlname#", $this->getName(), $option);
								}
								foreach ($options as $key => $option) {
									$options[$key]=str_replace("#title#", $watchdog->getName(), $option);
								}						
					//log::add('watchdog','debug','Exécution de la commande ' . $action['cmd'] . " avec comme option(s) : ". json_encode($options));
					//scenarioExpression::createAndExec('action', $action['cmd'], $options);
					log::add('watchdog','debug','Exécution de la commande ' . $action['cmd'] . " avec comme option(s) : ". json_encode($options));
					scenarioExpression::createAndExec('action', $action['cmd'], $options);	
					
					}					
					} catch (Exception $e) {
						log::add('watchdog', 'error', __('function trigger : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
					}
					
				}		
		
		log::add('watchdog', 'debug', '╠════> On lance les contrôles :');
		
		//set_boucleEnCours("7888885613");
		foreach ($watchdog->getCmd('info') as $cmd) {
			
			// On sauvegarde le dernier résultat dans AvantdernierResultat
			//log::add('watchdog', 'debug', $cmd->getName().'╠═══> Enregistre resultatAvant '.$cmd->getConfiguration('resultat'));
			$cmd->setConfiguration('resultatAvant', $cmd->getConfiguration('resultat'));
			
			
			
					//log::add('watchdog', 'debug', '[>>>>>>>>>>>Contrôle] Lancer le contrôle ** '.$cmd->getName()." **");
					//2 lignes inutiles car le controle se fait déja au moment de preSave
					//$resultat=$cmd->faireTestExpression($cmd->getConfiguration('controle'));
					//$cmd->setConfiguration('resultat', $resultat);
					if ($cmd->getLogicalId() != "resultatglobal") { // on ignore resultatglobal
					$cmd->save();
					}
					//log::add('watchdog', 'debug', '[>>>>FIN>>>>Contrôle] Lancer le contrôle ** '.$cmd->getName()." **");
				}
				
		// On va faire le test GLOBAL
		$typeControl= $this->getConfiguration('typeControl');
		if ($typeControl!="") {	// Que si en OU ou en ET
			log::add('watchdog','debug','╠═╦══> Calcul du résultat Global :');	

			$typeAction= $this->getConfiguration('typeAction');

			$traceleCalcul="Calcul : Init à ";
			
			if ($typeControl=="ET") {
				$leResultatdelaBoucle=true;
			}
			else {
				$leResultatdelaBoucle=false;
			}
					//g::add('watchdog','debug','leResultatdelaBoucle 1 : '.$leResultatdelaBoucle);	
			
							//On passe toutes les commandes de l'eqLogic pour calculer le résultat global des tests
							foreach ($this->getCmd('info') as $cmd) {
								if ($cmd->getLogicalId() != "resultatglobal") { // on ignore resultatglobal
									//$cmd->save();// On lance un save pour que la commande $cmd soit testée, ce sera finalement fait deux fois mais ce test est obligatoire avant le résultat global
									$leResultat=$cmd->getConfiguration('resultat');
								log::add('watchdog', 'debug', '║ ╚═══>['.$typeControl."] ".$leResultat. ' ('.$cmd->getName().')');
									//2 lignes inutiles car le controle se fait déja au moment de preSave
									//$resultat=$cmd->faireTestExpression($cmd->getConfiguration('controle'));
									//$cmd->setConfiguration('resultat', $resultat);
									if ($leResultat == "True" || $leResultat == "False"){
										//Résultat valide, on continue le test
										if ($typeControl=="ET") {
											if ($leResultat == "False")	$leResultatdelaBoucle=false; // On est sur une fonction ET
										}
										else {
											if ($leResultat == "True")	$leResultatdelaBoucle=true; // On est sur une fonction OU
										}
									}
								}
							}	
					//g::add('watchdog','debug','leResultatdelaBoucle 6 : '.$leResultatdelaBoucle);	
				if ($leResultatdelaBoucle) $leResultatdelaBoucle = 'True';
				else $leResultatdelaBoucle = 'False';
								log::add('watchdog', 'debug', "║ ╚═══>[==] ".$leResultatdelaBoucle);
				
				//---------------------------------------------------
				// On va chercher si on est en SAUVEGARDE ou en CRON
					//$dernierLancement=$this->getConfiguration('dernierLancement');
					//$dernierLancement=substr($dernierLancement, 0, 4);
				//---------------------------------------------------

				$resultatPrecedent=$this->getConfiguration('dernierEtat');
				$this->setConfiguration('dernierEtat', $leResultatdelaBoucle);
				//Pour que le resultat soit accessible dans une commande info, on copie dernierEtat dans resultatglobal
				$this->checkAndUpdateCmd('resultatglobal', $leResultatdelaBoucle);

				
				if ($typeAction == 'ALL'){
					$resultatPrecedent = "";
					log::add('watchdog','debug','Mode action à chaque contrôle : Désactivation du Résultat Précédent');	
				}
				
				$typeControl= $this->getConfiguration('typeControl');
				if ($typeControl !="") { 		
					// On est ici sur le résultat général des controles, on ne fait rien si on est en mode "Actions sur chaque controle indépendamment"
					if ($resultatPrecedent != $leResultatdelaBoucle) {
						log::add('watchdog','debug','╠═════> Bilan global : [Résultat Précédent='.$resultatPrecedent.'] [Nouveau Résultat='.$leResultatdelaBoucle.']-> On lance Trigger');	
						self::trigger($leResultatdelaBoucle);
					} else {
						log::add('watchdog','debug','╠═════> Bilan global : [Résultat Précédent='.$resultatPrecedent.'] [Nouveau Résultat='.$leResultatdelaBoucle.']-> On ne fait rien');	
					}
				}
			
		}	
		

		
	}
		
	public function trigger($passe) {
		// La fonction trigger ne doit être appellé sur le résultat général des controles, on ne fait rien si on est en mode "Actions sur chaque cvontrole indépendamment"

		log::add('watchdog','debug','╠═════> On lance les actions qui correspondent au passage de ['.$this->getName().'] à '.$passe);   
			foreach ($this->getConfiguration("watchdogAction") as $action) {
				try {
				$options = [];
				if (isset($action['options'])) $options = $action['options'];
				if (($action['actionType'] == $passe) && $options['enable'] == '1'){
					
								/*foreach ($options as $key => $option) {
									$options[$key]=str_replace("#controlname#", $this->getName(), $option);
								}*/
								foreach ($options as $key => $option) {
									$options[$key]=str_replace("#title#", $this->getName(), $option);
								}
					
					
					//log::add('watchdog','debug','Lancement de : '.$action['cmd']);   
					//scenarioExpression::createAndExec('action', $action['cmd'], $options);
				log::add('watchdog','debug','**************************************************************************************************************************');
				log::add('watchdog','debug','** Exécution de la commande ' . jeedom::toHumanReadable($action['cmd']) . " avec comme option(s) : ". json_encode($options));
				log::add('watchdog','debug','**************************************************************************************************************************');
				scenarioExpression::createAndExec('action', $action['cmd'], $options);	
				}					
				} catch (Exception $e) {
					log::add('watchdog', 'error', __('function trigger : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
				}
			}
	}

	public static function update() {
		foreach (self::byType('watchdog') as $watchdog) {
			$autorefresh = $watchdog->getConfiguration('autorefresh');
			
			if ($watchdog->getIsEnable() == 1 && $autorefresh != '') {
				try {
					$c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
					if ($c->isDue()) {
						try {
							//log::add('watchdog','debug','** ENREGISTREMENT DERNIER LANCEMENT ');
							
							$watchdog->setConfiguration('avantDernierLancement',$watchdog->getConfiguration('dernierLancement')); 
							$watchdog->setConfiguration('dernierLancement','PRECRON '.date("d.m.Y")." ".date("H:i:s")); // PRECON c'est pour signaler que le CRON va etre sauvegarder


							//$watchdog->setConfiguration('boucleEnCours', "CRON");
							//$_boucleEnCours="CRON";
							log::add('watchdog','info','╔══════════════════════[Lancement CRON du Watchdog '.$watchdog->getName().']════════════════════════════════════════════════════════════════════════════');
							$watchdog->lancerControle($watchdog);
							log::add('watchdog', 'info', "╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════");

							//log::add('watchdog','debug','fin cron-----------------------------------------------------------------');
						} catch (Exception $exc) {
							log::add('watchdog', 'error', __('Erreur pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $exc->getMessage());
						}
						$watchdog->save();
					}
				} catch (Exception $exc) {
					log::add('watchdog', 'error', __('Expression cron non valide pour ', __FILE__) . $watchdog->getHumanName() . ' : ' . $autorefresh);
				}
			}
		}
	}
	    public function execute($_options = array()) {
 	//log::add('watchdog','debug','[eqLogic] execute de '.$this->getName());        
    }

    /*
	https://www.nextdom.org/knowledge-base/ajouter-un-template-a-votre-plugin/
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
     */
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
	public function toHtml($_version = 'dashboard') {
		 	log::add('watchdog','debug','***************************************************************************Début génération Widget');        

	$replace = $this->preToHtml($_version);
		 	log::add('watchdog','debug','***********************************>>**************************************'.json_encode($replace));        
			if (!is_array($replace)) {
				return $replace;
			}
			$version = jeedom::versionAlias($_version);
			if ($this->getDisplay('hideOn' . $version) == 1) {
				return '';
			}
	/* ------------ Ajouter votre code ici ------------*/
	
	foreach ($this->getCmd('info') as $cmd) {
		 	log::add('watchdog','debug','dans boucle génération Widget');        
            $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
            $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
            if ($cmd->getLogicalId() == 'encours'){
                $replace['#thumbnail#'] = $cmd->getDisplay('icon');
            }
            if ($cmd->getIsHistorized() == 1) {
                $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
            }
        }
	
		 	log::add('watchdog','debug','***************************************************************************'.json_encode($replace));        
	
	/* ------------ N'ajouter plus de code apres ici------------ */
		 	log::add('watchdog','debug','***************************************************************************Fin génération Widget');        

	return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'watchdog', 'watchdog')));
	}


    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class watchdogCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */

/*public static $_boucleEnCours="dd789765";
  public static function get_boucleEnCours()
  {
	  //$_boucleEnCours = "99999999";
     return $_boucleEnCours; //c'est là que je coince (est-ce possible au moins ?)
  }
    public static function set_boucleEnCours($valeur)
  {
	  $_boucleEnCours = $valeur;
  }
  */
public function faireTestExpression($_string) {	

		$scenario = null;
		//---------------------------------------------------
		// On va chercher les valeurs de tempo 1 2 et 3
			$eqLogic = $this->getEqLogic();
			$tempo1=$eqLogic->getConfiguration('tempo1');
			$tempo2=$eqLogic->getConfiguration('tempo2');
			$tempo3=$eqLogic->getConfiguration('tempo3');			
		//---------------------------------------------------
			$_string = str_replace("#tempo1#", $tempo1, $_string);
			$_string = str_replace("#tempo2#", $tempo2, $_string);
			$_string = str_replace("#tempo3#", $tempo3, $_string);
		//---------------------------------------------------
			$_string = str_replace("#internalAddr#", '"'.config::byKey('internalAddr').'"', $_string);
			
			

	//$_boucleEnCours="8541";
		$fromHumanReadable = jeedom::fromHumanReadable($_string);
		$this->setConfiguration('calcul', scenarioExpression::setTags($fromHumanReadable));
		
		$return = evaluate(scenarioExpression::setTags($fromHumanReadable, $scenario, true));
				if (is_bool($return)) {
					if ($return) $return = 'True';
					 else $return = 'False';
				}
				
					//	phpQuery::$documents[$documentID]->dataNodes[] = $node;

		//log::add('watchdog','debug','>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  : ' . eqLogic::$_boucleEnCours);
		
		
		

		//log::add('watchdog','debug','Controle : ' . $_string.' => Resultat : ' . $return);
		//log::add('watchdog','debug','Controle : ' . jeedom::fromHumanReadable($_string, true).' => Resultat : ' . $return);
		//log::add('watchdog','debug','Controle : ' . jeedom::fromHumanReadable($_string, false).' => Resultat : ' . $return);
		//log::add('watchdog','debug','Controle : ' . scenarioExpression::setTags(jeedom::fromHumanReadable($_string)).' => Resultat : ' . $return);
		return $return;
}


	public function triggerEquip($passe) {
	//log::add('watchdog','debug','******************[TriggerEquip] ');   
	$eqLogic = $this->getEqLogic();
	$typeControl= $eqLogic->getConfiguration('typeControl');
	$ideqLogic=$eqLogic->getId();
		if ($typeControl == "") {
		// La fonction trigger est appellé sur le résultat général des controles, on ne fait rien si on n'est pas en mode "Actions sur chaque cvontrole indépendamment"
			
	//log::add('watchdog','debug','******************[TriggerEquip] '.$this->getName().' à '.$passe);   
	log::add('watchdog','debug','╠═════> On lance les actions qui correspondent au passage de ['.$this->getName().'] à '.$passe);   
	
	if ($eqLogic->getConfiguration('logspecifique'))
	log::add('watchdog_'.$ideqLogic,'info' ,'╔══════════════════════['.$this->getName().' est passé à '.$passe.']════════════════════════════════════════════════════════════════════════════');
						
						
				foreach ($eqLogic->getConfiguration("watchdogAction") as $action) {
					try {
							$options = [];
							if (isset($action['options'])) $options = $action['options'];
					if (($action['actionType'] == $passe) && $options['enable'] == '1'){
								// On va remplacer #controlname# par le nom du controle dans tous les champs du array "options"
								foreach ($options as $key => $option) {
									$options[$key]=str_replace("#controlname#", $this->getName(), $option);
								}
								foreach ($options as $key => $option) {
									$options[$key]=str_replace("#title#", $eqLogic->getName(), $option);
								}

						
						if ($options['log'] == '1') {
						log::add('watchdog_'.$ideqLogic,'info' ,'╠═══> Exécution de la commande ' . jeedom::toHumanReadable($action['cmd']) . " avec comme option(s) : ". json_encode($options));
						}
						
						//log::add('watchdog','debug','Exécution de la commande ' . $action['cmd'] . " avec comme option(s) : ". json_encode($options));
						//scenarioExpression::createAndExec('action', $action['cmd'], $options);
					log::add('watchdog','debug','**************************************************************************************************************************');
					log::add('watchdog','debug','** Exécution de la commande ' . jeedom::toHumanReadable($action['cmd']) . " avec comme option(s) : ". json_encode($options));
					log::add('watchdog','debug','**************************************************************************************************************************');
					//log::add('watchdog','debug','Exécution de la commande ' . $action['cmd'] . " avec comme option(s) : ". json_encode($options));
					scenarioExpression::createAndExec('action', $action['cmd'], $options);	
						
						
					}					
					} catch (Exception $e) {
						log::add('watchdog', 'error', __('function trigger : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
					}
					
				}
		if ($eqLogic->getConfiguration('logspecifique')) log::add('watchdog_'.$ideqLogic,'info' ,'╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════');
							
		}
	}


    /*     * *********************Methode d'instance************************* */
    public function preInsert() {
 	//log::add('watchdog','debug','[cmd] preInsert de '.$this->getName());        

    }

    public function postInsert() {
 	//log::add('watchdog','debug','[cmd] postInsert de '.$this->getName());        
        
    }

    public function preSave() {
 	//log::add('watchdog','debug','****************************************************[cmd] preSave de '.$this->getName());
		//$_boucleEnCours="1";
	if ($this->getType() == 'action') return; //On ne fait pas le test si c'est une Commande Action		
	if ($this->getLogicalId() == 'resultatglobal') return; //On ne fait pas le test si c'est la commande 	resultatglobal	
	log::add('watchdog','info','║ ┌──────────────────────[Sauvegarde du Contrôle '.$this->getName().']────────────────────────────────────────────────────────────────────────────────────');
			
			
		//---------------------------------------------------
		// On va chercher si on est en SAUVEGARDE ou en CRON
			$eqLogic = $this->getEqLogic();
			$dernierLancement=$eqLogic->getConfiguration('dernierLancement');
			$dernierLancement=substr($dernierLancement, 0, 4);
		//---------------------------------------------------
				//log::add('watchdog','debug','************* : ' . json_encode($eqLogic));
				
				$resultatPrecedent=$this->getConfiguration('resultat');
				//log::add('watchdog','debug','Controle2 : ' . $this->getConfiguration('controle').' => Resultat : ' . $return);
				//log::add('watchdog','debug','calcul : ' . $this->getConfiguration('calcul').' => calcul : ' . $return);
				$resultat=self::faireTestExpression($this->getConfiguration('controle'));
				//$macmd = cmd::byId(str_replace('#', '', $this->getId()));
				//log::add('watchdog','debug','*************2 : ' . json_encode($macmd));
				//log::add('watchdog','debug','[Contrôle*ID] '.$this->getName().' : ' . $macmd->getConfiguration('controle').' => Resultat : ' . $resultat);
				log::add('watchdog','debug','║ │ ╠═╦═>     Execution de ['.$this->getName().']');
				log::add('watchdog','debug','║ │ ║ ╚═╦═>   '.jeedom::toHumanReadable($this->getConfiguration('controle')));
				log::add('watchdog','debug','║ │ ║   ╚═══> Resultat : ' . $resultat);
				//log::add('watchdog','debug','zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'. jeedom::fromHumanReadable($this->getConfiguration('controle'), false));
				$_string=$this->getConfiguration('controle');
				//log::add('watchdog','debug','Controle : ' . jeedom::fromHumanReadable($_string, true));
				//log::add('watchdog','debug','Controle : ' . jeedom::toHumanReadable($_string));
				//log::add('watchdog','debug','Controle : ' . cmd::humanReadableToCmd($_string));
				//log::add('watchdog','debug','Controle : ' . scenarioExpression::setTags(jeedom::fromHumanReadable($_string)));
				
				
				if ($resultatPrecedent != $resultat)
				{
				//log::add('watchdog', 'debug', '╠═══> Enregistre resultat '.$resultat);

				$this->setConfiguration('resultat', $resultat);
					
					
				// Si le résultat a changé, il faut actualiser le calcul du résultat global, pour cela, on utilise la variable cmd.configuration.aChange qui traitera le calcul dans postSave
				$this->setConfiguration('aChange', true);
						//On ne va lancer le trigger que si on est en mode CRON et pas si on est en mode SAVE
						if (($dernierLancement =="CRON") || ($dernierLancement =="PREC"))
							$this->triggerEquip($resultat);			
				}
    }

    public function postSave() {
 	//log::add('watchdog','debug','[cmd] postSave de '.$this->getName()); 
	
	if ($this->getConfiguration('aChange')) {
		// Cette boucle est déclenchée quand le résultat du controle a changé, il faut ainsi relancer le save du resultat global
		$this->setConfiguration('aChange', false);		
		$this->save();	
		$this->getEqLogic()->save(); //enregistre l'équipement entier (et donc le resultat global des controles)
	}
		
		log::add('watchdog', 'info', "║ └──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────");

        
    }

    public function preUpdate() {
 	//log::add('watchdog','debug','[cmd] preUpdate de '.$this->getName());        
       
    }

    public function postUpdate() {
 	//log::add('watchdog','debug','[cmd] postUpdate de '.$this->getName());        
        
    }

    public function preRemove() {
 	log::add('watchdog','debug','[cmd] preRemove de '.$this->getName());        
        
    }

    public function postRemove() {
 	log::add('watchdog','debug','[cmd] postRemove de '.$this->getName());        
        
    }


	public function dontRemoveCmd()  // BD : bloque la suppression du champ 'résultat global'
	{
		if ($this->getLogicalId() == 'resultatglobal') {
			return true;
		} else {
			return false;
		}
	}

    public function execute($_options = array()) {
 	//log::add('watchdog','debug','[cmd] execute de '.$this->getName());        
    }

    /*     * **********************Getteur Setteur*************************** */
}


