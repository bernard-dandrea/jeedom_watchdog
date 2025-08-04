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

try {
    require_once __DIR__ . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');
/*
    if (init('action') == 'launchAction') {
        watchdog::launchCmd(init('id'));
		ajax::success();
    }
    if (init('action') == 'getStatus') {
		log::add('watchdog','debug', ' id ' . init('id'));
        $watchdog = watchdog::byId(init('id'));
		$return = $watchdog->getConfiguration('activAction');
		ajax::success($return);
    }
	
    if (init('action') == 'actionAll') {
		log::add('watchdog','debug', ' id ' . init('id'));
        watchdog::actionAll(init('id'));
		ajax::success();
    }
	*/
    if (init('action') == 'testaction') {
        $watchdog = watchdog::byId(init('id'));
		    if (!is_object($watchdog)) {
			  throw new Exception(__('Equipement watchdog introuvable : ', __FILE__) . init('id'));
			}
		$comptageid=0;
		foreach ($watchdog->getConfiguration('watchdogAction') as $cmd) {
					if (init('id_action')== $comptageid) {
						$commandeaTester=$cmd['cmd'];
						$optionsCommandeaTester=$cmd['options'];
						//log::add('watchdog','debug', ' optionsCommandeaTester: '.json_encode($optionsCommandeaTester));
						
						if (count($watchdog->getCmd()) == 1) {// S'il n'y en a qu'une commande, on va remplacer #controlname# par la valeur, sinon on laisse #controlname#
								foreach ($watchdog->getCmd() as $eqCmd) {
									foreach ($optionsCommandeaTester as $key => $option) {
										$optionsCommandeaTester[$key]=str_replace("#controlname#", $eqCmd->getName(), $option);
									}				
								}
						}
						foreach ($optionsCommandeaTester as $key => $option) {
							$optionsCommandeaTester[$key]=str_replace("#title#", $watchdog->getName(), $option);
						}
						scenarioExpression::createAndExec('action', $commandeaTester, $optionsCommandeaTester);		
					}
					$comptageid++;
				}
		ajax::success();
    }	
	
			
    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>
