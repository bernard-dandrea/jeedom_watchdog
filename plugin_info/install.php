<?php

// Last Modified : 2025/09/02 18:57:35

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

function watchdog_install()
{
	$cron = cron::byClassAndFunction('watchdog', 'update');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('watchdog');
		$cron->setFunction('update');
		$cron->setEnable(1);
		$cron->setDeamon(0);
		$cron->setSchedule('* * * * *');
		$cron->setTimeout(30);
		$cron->save();
	}
	$_key = 'log::level::watchdog_actions';
	$loglevel =  array("100" => "1", "200" => "0", "300" => "0", "400" => "0", "1000" => "0", "default" => "0"); // niveau debug pour afficher tous les niveaux de messages
	config::save($_key, $loglevel);
	
}

function watchdog_update()
{
	$cron = cron::byClassAndFunction('watchdog', 'update');
	if (!is_object($cron)) {
		$cron = new cron();
	}
	$cron->setClass('watchdog');
	$cron->setFunction('update');
	$cron->setEnable(1);
	$cron->setDeamon(0);
	$cron->setSchedule('* * * * *');
	$cron->setTimeout(30);
	$cron->save();
	$cron->stop();
}


function watchdog_remove()
{
	$cron = cron::byClassAndFunction('watchdog', 'update');
	if (is_object($cron)) {
		$cron->remove();
	}

	$_key = 'log::level::watchdog_actions';
	if (config::byKey($_key) != '')
		config::remove($_key);
}
