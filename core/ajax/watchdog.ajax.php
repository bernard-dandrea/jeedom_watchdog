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


	if (init('action') == 'testaction') {
		$watchdog = watchdog::byId(init('id'));
		if (!is_object($watchdog)) {
			throw new Exception(__('Equipement watchdog introuvable : ', __FILE__) . init('id'));
		}

		// en mode action sur tous les contrôles, on va récupérer les paramètres sur la première condition
		$controlname = '';
		$equip = '';
		if ($watchdog->getConfiguration('typeControl', '') == '') {
			foreach ($watchdog->getCmd() as $condition) {
				$controlname = $condition->getName();
				$equip = $condition->getConfiguration("equip", "");
				break;
			}
		}


		$comptageid = 0;  // sert à se positionner sur la bonne action 
		foreach ($watchdog->getConfiguration('watchdogAction') as $cmd) {
			if (init('id_action') == $comptageid) {  // on se positionne sur la bonne commande
				$optionsCommandeaTester = $cmd['options'];
				foreach ($optionsCommandeaTester as $key => $option) {
					if ($controlname <> '')
						$option = str_replace("#controlname#", $controlname, $option);
					if ($equip <> '')
						$option  = str_replace("_equip_", $equip, $option);
					$optionsCommandeaTester[$key] = $watchdog->remplace_parametres($option, $key);  // remplace les parametres dans les options de la commande
				}
				$commandeaTester = $cmd['cmd'];
				if ($equip <> '')
					$commandeaTester  = str_replace("_equip_", $equip, $commandeaTester);
				$commandeaTester = $watchdog->remplace_parametres($commandeaTester);   // remplace les paramètres dans la commande

				log::add('watchdog', 'debug', '**************************************************************************************************************************');
				log::add('watchdog', 'debug', '** Exécution de la commande ' . jeedom::toHumanReadable($commandeaTester) . " avec comme option(s) : " . json_encode($optionsCommandeaTester));
				log::add('watchdog', 'debug', '**************************************************************************************************************************');
				scenarioExpression::createAndExec('action', $commandeaTester, $optionsCommandeaTester);
			}
			$comptageid++;
		}
		ajax::success();
	}

	if (init('action') == 'cherche_equipement_dans_expression') {

		$condition = init('condition');
		$id = init('id');

		$watchdogCmd = watchdogCmd::byId($id);
		$equip = '';
		if (is_object($watchdogCmd)) {
			$equip = $watchdogCmd->cherche_equipement_dans_expression($condition);
		}
		ajax::success($equip);
	}

	if (init('action') == 'test_expression') {

		$condition = init('condition');
		$id = init('id');

		$watchdogCmd = watchdogCmd::byId($id);

		if (is_object($watchdogCmd)) {
			$watchdog = $watchdogCmd->getEqlogic();  // utile pour récupérer les paramètres généraux du watchdog
			if (is_object($watchdog)) {
				$expression = $watchdog->remplace_parametres($condition);
				$expression = jeedom::toHumanReadable($expression);
			}
			ajax::success($expression);
		}
	}

	throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
