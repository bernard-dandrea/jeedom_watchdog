<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

$plugin = plugin::byId('watchdog');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">

			<!-- Bouton de scan des objets -->
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle" style="font-size : 5em;color:#a15bf7;"></i>
				<br />
				<span style="color:#a15bf7">{{Ajouter}}</span>
			</div>
			<!-- Bouton d accès à la configuration -->
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench" style="font-size : 5em;color:#a15bf7;"></i>
				<br />
				<span style="color:#a15bf7">{{Configuration}}</span>
			</div>

		</div>
		<legend><i class="fas fa-table"></i> {{Mes watchdogs}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {

				$typeControl = $eqLogic->getConfiguration('typeControl');

				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">

		<div class="input-group pull-right" style="display:inline-flex">
			<a href="https://bernard-dandrea.github.io/jeedom_watchdog/fr_FR/" style="margin-right:5px" target="_blank" class="btn btn-success eqLogicAction " title="{{Lien vers la Documentation du plugin}}"><i class="fa fa-book"></i> </a>
			<a class="btn btn-info eqLogicAction  bt_plugin_view_log" style="margin-right:5px" title="{{Logs du Watchdog}}"><i class="fa fa-file"></i> </a>
			<a class="btn btn-default eqLogicAction " style="margin-right:5px" data-action="configure" title="{{Configuration avancée du Watchdog}}"><i class="fas fa-cogs"></i> </a>
			<a class="btn btn-warning eqLogicAction " style="margin-right:5px" data-action="copy" title="{{Dupliquer cet équipement}}"><i class="fas fa-copy"></i> </a>
			<a class="btn btn-danger eqLogicAction " style="margin-right:5px" data-action="remove" title="{{Supprimer le Watchdog}}"><i class="fas fa-minus-circle"></i> </a>
			<a class="btn btn-default  " style="margin-right:5px" onclick="location.reload();" title="{{Recharger la page sans sauvegarder les modifications}}" ><i class="fas fa-sync-alt"></i> </a>
			<a class="btn btn-success eqLogicAction" style="margin-right:5px" data-action="save" title="{{Attention, lors de la sauvegarde, seuls les contrôles sont effectués, le résultat global n'est pas calculé et reste inchangé. Il sera mis à jour lors du lancement du prochain contrôle par le CRON ou la commande refresh. Les actions ne sont pas lancées non plus.}}"><i class="fas fa-check-circle"></i> {{Sauver / Contrôler}}</a>
		</div>

		<!-- Liste des onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Watchdog}}</a></li>
			<li role="presentation"><a href="#controlestab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-stethoscope"></i></i> {{Contrôles}}</a></li>
			<li role="presentation"><a href="#infocmd" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-cogs"></i> {{Actions}}</a></li>
		</ul>

		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<form class="form-horizontal"><br>
					<fieldset>
						<br>
						<legend><i class="fa animal-dog56" style="font-size : 3em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Identification et options du watchdog}}</span></legend>

						<div class="form-group">
							<label class="col-sm-3 control-label">{{Nom du watchdog}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement watchdog}}" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>eqLogic
									<?php
									foreach ((jeeObject::buildTree(null, false)) as $object) {
										echo '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Catégorie}}</label>
							<div class="col-sm-9">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Watchdog</label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activé}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">{{Log spécifique pour ce watchdog}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Si cette option est activée, les traces de ce watchdog seront enregistrées dans watchdog_ suivi de l'Id de l'eqLogic}}"></i></sup>
							</label></i>
							<div class="col-sm-3">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="logspecifique">{{Activé}}</label>
							</div>
						</div>

						<div class=" form-group">
							<label class="col-xs-3 control-label">{{Auto-actualisation (cron)}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Fréquence de mise à jour du watchdog}}"></i></sup>
							</label>
							<div class="col-xs-2">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="autorefresh" placeholder="{{Auto-actualisation (cron)}}" />
									<span class="input-group-btn">
										<a class="btn btn-success btn-sm " id="bt_cronGenerator"><i class="fas fa-question-circle"></i></a>
									</span>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="col-xs-3 control-label">{{Avant Dernier lancement}}</label>
							<div class="col-xs-3">
								<input type="text" disabled class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="avantDernierLancement">

							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3 control-label">{{Dernier lancement}}</label>
							<div class="col-xs-3">
								<input type="text" disabled class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="dernierLancement">

							</div>
						</div>

						<br>
						<legend><i class="fa fa-list-alt" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Mode de fonctionnement}}</span></legend>


						<div class="form-group">
							<label class="col-sm-3 control-label">{{Mode de fonctionnement des contrôles}}</label>
							<div class="col-sm-3">
								<select name="typecontrole" style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="typeControl">
									<option value="">{{Actions sur chaque contrôle indépendamment (par défaut)}}</option>
									<option value="OU">{{Actions sur l'ensemble des contrôles (avec méthode OU)}}</option>
									<option value="ET">{{Actions sur l'ensemble des contrôles (avec méthode ET)}}</option>
								</select>
							</div><br><br>
						</div>

						<div class="alert-info bg-success">
							Il existe trois modes de fonctionnement : <br>
							<br>* Actions sur chaque contrôle indépendamment : Ce mode teste indépendamment chaque contrôle et déclenche les actions suivant le mode de fonctionnement des actions (voir paramètre suivant). Dans cette configuration, le Résultat Global n'est pas géré.
							<br>* Méthode OU : Ce mode teste le résultat global des contrôles en appliquant un test "OU" entre chaque contrôle (le résultat global est vrai si au moins un des controles est vrai). A la fin des contrôles, les actions sont lancées suivant le mode de fonctionnement des actions (voir paramètre suivant).
							<br>* Méthode ET : Ce mode teste le résultat global des contrôles en appliquant un test "ET" entre chaque contrôle (le résultat global est vrai si tous les controles sont vrais). A la fin des contrôles, les actions sont lancées suivant le mode de fonctionnement des actions (voir paramètre suivant).
						</div>
						<br><br>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Mode de fonctionnement des actions}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="typeAction">
									<option value="">{{Lancer les actions uniquement si le contrôle changé de valeur (par défaut)}}</option>
									<option value="ALL">{{Lancer les actions même si le contrôle n'a pas changé de valeur}}</option>
								</select>
							</div><br><br>
						</div>
						<div class="alert-info bg-success">
							Il existe deux modes de fonctionnement : <br>
							<br>* Lancer les actions uniquement si le contrôle changé de valeur :
							<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- dans le mode 'Actions sur chaque contrôle indépendamment', les actions correspondant au résultat sont lancées sur chaque condition si celui-ci a changé.
							<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- dans le mode ET/OU, les actions correspondant au résultat global sont lancées si celui-ci a changé.
							<br>* Lancer les actions même si le contrôle n'a pas changé de valeur
							<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- dans le mode 'Actions sur chaque contrôle indépendamment' , les actions sont lancées sur chaque condition.
							<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- dans le mode ET/OU, les ctions sont lancées une fois quand le résultat global a été calculé .
						</div>
						<legend><i class="icon kiko-check-line" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Résultat}}</span></legend>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Le contrôle est OK lors le résultat est égal à}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ResultatOK">
									<option value="">{{Valeur par défaut}}</option>
									<option value="1">{{True}}</option>
									<option value="0">{{False}}</option>
								</select>
							</div>
						</div>

						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Historique}}</label>
							<div class="col-sm-3">
								<select style="width: 150px;" id="sel_ResultatHistory" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ResultatHistory">
									<option value="">{{Aucun}}</option>
									<option value="/">{{Défaut}}</option>
									<option value="-1 day">{{1 jour}}</option>
									<option value="-7 days">{{7 jours}}</option>
									<option value="-1 month">{{1 mois}}</option>
									<option value="-3 month">{{3 mois}}</option>
									<option value="-6 month">{{6 mois}}</option>
									<option value="-1 year">{{1 an}}</option>
									<option value="-2 years">{{2 ans}}</option>
									<option value="-3 years">{{3 ans}}</option>
									<option value="never">{{Pas de purge}}</option>
								</select>
							</div>
						</div>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Afficher seulement les résultats non OK}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" id="sel_DisplayOnlyConditionNonOK" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DisplayOnlyConditionNonOK">
									<option value="">{{Valeur par défaut}}</option>
									<option value="1">{{Oui}}</option>
									<option value="0">{{Non}}</option>
								</select>
							</div>
						</div>
						<?php
						$widgetDashboard = cmd::getSelectOptionsByTypeAndSubtype('info', 'binary', 'dashboard', cmd::availableWidget('dashboard'));
						$widgetMobile = cmd::getSelectOptionsByTypeAndSubtype('info', 'binary', 'dashboard', cmd::availableWidget('mobile'));
						?>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Widget dashboard}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le(s) résultat(s) dans la tuile du watchdog en mode dashboard}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<select style="width: 500px;" id="template_resultat_dashboard" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="template_resultat_dashboard">
									<option value="">{{Valeur par défaut}}</option>
									<?php
									echo $widgetDashboard;
									?>
								</select>
							</div>
						</div>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Widget mobile}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le(s) résultat(s) dans la tuile du watchdog en mode mobile}}"></i></sup></label>
							<div class="col-sm-3">
								<select style="width: 500px;" id="template_resultat_mobile" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="template_resultat_mobile">
									<option value="">{{Valeur par défaut}}</option>
									<?php
									echo $widgetMobile;
									?>
								</select>
							</div>
						</div>
						<br>
						<legend><i class="icon kiko-book-open" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Reporting}}</span></legend>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Virtuel pour le reporting}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Les résultats des contrôles seront enregistrés dans ce virtuel, ce qui permet d'avoir une vue globale de l'état des watchdogs.}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<div class="input-group">
									<input style="width: 500px;" title='Laisser vide si valeur par défaut. Mettre / si on ne veut pas reporting même si il y en a un de défini par défaut.' class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="VirtualReport" />
									<span class="input-group-btn">
										<a class="btn btn-default cursor" title="Rechercher un virtuel" id="VirtualReport"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</div>
						</div>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Afficher seulement les résultats non OK}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" id="sel_DisplayOnlyReportingNonOK" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DisplayOnlyReportingNonOK">
									<option value="">{{Valeur par défaut}}</option>
									<option value="1">{{Oui}}</option>
									<option value="0">{{Non}}</option>
								</select>
							</div>
						</div>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Historique}}</label>
							<div class="col-sm-3">
								<select style="width: 150px;" id="sel_ReportingHistory" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ReportingHistory">
									<option value="">{{Aucun}}</option>
									<option value="/">{{Défaut}}</option>
									<option value="-1 day">{{1 jour}}</option>
									<option value="-7 days">{{7 jours}}</option>
									<option value="-1 month">{{1 mois}}</option>
									<option value="-3 month">{{3 mois}}</option>
									<option value="-6 month">{{6 mois}}</option>
									<option value="-1 year">{{1 an}}</option>
									<option value="-2 years">{{2 ans}}</option>
									<option value="-3 years">{{3 ans}}</option>
									<option value="never">{{Pas de purge}}</option>
								</select>
							</div>
						</div>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Widget dashboard}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le(s) résultat(s) dans la tuile du virtuel en mode dashboard}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<select style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="template_reporting_dashboard">
									<option value="">{{Valeur par défaut}}</option>
									<?php
									echo $widgetDashboard;
									?>
								</select>
							</div>
						</div>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Widget mobile}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Widget appliqué sur le(s) résultat(s) dans la tuile du virtuel en mode mobile}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<select style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="template_reporting_mobile">
									<option value="">{{Valeur par défaut}}</option>
									<?php
									echo $widgetMobile;
									?>
								</select>
							</div>
						</div>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Suppression automatique}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Suppression dans le reporting si la condition ou le watchdog est supprimé}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<select style="width: 150px;" id="sel_ReportingSuppressionAutomatique" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ReportingSuppressionAutomatique">
									<option value="">{{Valeur par défaut}}</option>
									<option value="1">{{Oui}}</option>
									<option value="0">{{Non}}</option>
								</select>
							</div>
						</div>
					</fieldset>
				</form>
			</div>

			<div role="tabpanel" class="tab-pane" id="controlestab">
				<legend><i class="fas fa-stethoscope" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Contrôles à effectuer}}</span></legend>
				<table id="table_controlesTitre" class="table-condensed" width=100%></table>
				<table id="table_controles" class="table-condensed ui-sortable table_controles" width=100%>
					<tbody></tbody>
				</table>
				<br>
				<a class="btn btn-success btn-sm bt_addControle pull-left" data-type="action" style="margin-top:-15px;"><i class="fa fa-plus-circle"></i> {{Ajouter un contrôle}}</a>
				<a id="afficheCalculs" class="btn btn-info btn-sm bt_afficheCalculs pull-right" data-type="action" style="margin-top:-15px;"><i class="fas fa-square-root-alt"></i> {{Afficher les calculs}}</a><a id="masqueCalculs" class="btn btn-warning btn-sm bt_masqueCalculs pull-right" data-type="action" style="margin-top:5px;"><i class="fas fa-square-root-alt"></i> {{Masquer les calculs}}</a>
				<br>
				<!-- champ resultatAjax utilisé pour récupérer les appels des fonctions ajax (pas trouvé de meilleur moyen) -->
				<textarea id="resultatAjax" name="message" hidden></textarea>
				<div class="alert-info bg-success">
					Vous pouvez entrer dans la zone contrôle n'importe quelle expression reconnue dans les scénarios. Cette expression doit renvoyer True (=1) ou False (=0).
					<br>Vous pouvez tester l'expression dans le Testeur d'expressions (menu Outils).
					<br>Les expressions incorrectes sont ignorées lors de l'exécution du watchdog en mode programmé ou via la commande Refresh.
					<br>
				</div>
				<!-- ICI la partie qui affiche le résultat global dans le cas d'un mode ET/OU-->
				<div id="section_resultatGlobal">
				</div>

				<legend><i class="icon jeedomapp-settings" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Variables}}</span></legend>
				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: right; width: 100px;"><b>Macro</b></td>
							<td>
								<div class="input-group">
									<input style="width: 1000px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="macro" />
								</div>
							</td>
						</tr>
					</tbody>
				</table><br>
				<div class="alert-info bg-success">
					La macro peut être utilisée pour répéter les mêmes conditions en faisant varier les paramètres qui peuvent être des équipements, des commandes ou toute autre donnée passée en argument.
					<br>Définissez l'expression en utilisant les parametres _arg0_, _arg1_, ... qui représentent les paramètres.
					<br>Vous pouvez ensuite utiliser la macro dans la condition avec la syntaxe _macro_(arg0,arg1, ... ) par exemple _macro_(#[Maison][Température Pieces][Cuisine timestamp]#,_tempo1_) .
					<br>Par exemple pour tester qu'une commande est mise à jour régulièrement, la macro suivante: age(_arg0_) > _arg1_ et age(_arg0_)>0
					<br>Vous utiliser la macro dans la condition avec la syntaxe : _macro_(#[Maison][Température Pieces][Cuisine timestamp]#,_tempo1_)
					<br>Le test généré sera (avec tempo1 = 600): age(#[Maison][Température Pieces][Cuisine timestamp]#) > 600 et age(#[Maison][Température Pieces][Cuisine timestamp]#)>0
					<br>Noter que l'assistant permet de sélectionner un équipement ou une commande et de générer l'appel à la macro.
					<br>
				</div>
				<br>
				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: right; width: 100px;"><b>var1</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="var1" />
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; width: 100px;"><b>var2</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="var2" />
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; width: 100px;"><b>var3</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="var3" />
								</div>
							</td>
						</tr>
					</tbody>
				</table><br>
				<div class="alert-info bg-success">
					Les variables peuvent être utilisées pour faire des tests dans un contrôle. Par exemple, mettre _var1_ pour récupérer la variable 1 dans la formule du contrôle.
				</div>
				<br>
				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: right; width: 100px;"><b>tempo1 : </b></td>
							<td><input style="width: 100px;" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key='tempo1' placeholder="{{En secondes}} " /></td>
							<td style="text-align: right; width: 100px;"><b>tempo2 : </b></td>
							<td><input style="width: 100px;" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key='tempo2' placeholder="{{En secondes}} " /></td>
							<td style="text-align: right; width: 100px;"><b>tempo3 : </b></td>
							<td><input style="width: 100px;" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key='tempo3' placeholder="{{En secondes}} " /></td>
						</tr>
						<tr>
							<td></td>
							<td><em>(en secondes)</em></td>
							<td></td>
							<td><em>(en secondes)</em></td>
							<td></td>
							<td><em>(en secondes)</em></td>
						</tr>
					</tbody>
				</table><br>

				<div class="alert-info bg-success">
					Les variables tempo peuvent être utilisées pour faire des tests dans un contrôle. Par exemple, mettre _tempo1_ (ou #tempo1#) pour récupérer la valeur de la tempo1 dans la formule du contrôle
					<br>
				</div>
				<br>
				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: right; width: 100px;"><b>Equipement 1</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="equip1" />
									<span class="input-group-btn">
										<a class="btn btn-default cursor" title="Rechercher un équipement" id="equip1"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; width: 100px;"><b>Equipement 2</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="equip2" />
									<span class="input-group-btn">
										<a class="btn btn-default cursor" title="Rechercher un équipement" id="equip2"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; width: 100px;"><b>Equipement 3</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="equip3" />
									<span class="input-group-btn">
										<a class="btn btn-default cursor" title="Rechercher un équipement" id="equip3"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</td>
						</tr>
					</tbody>
				</table><br>

				<div class="alert-info bg-success">
					Les variables équipements peuvent être utilisées pour faire des tests dans un contrôle. Par exemple, mettre _equip1_ pour récupérer l'équipement 1 dans la formule du contrôle.<br><br>
					Exemple de formule:
					<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* tester la dernière communication d'un équipement 1 --> (#timestamp# - strtotime(lastCommunication(_equip1_))) > #tempo1#
					<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* tester le résultat d'une commande de l'équipement 2 --> value(_equip2_[Statut]) == 1

					<br>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="infocmd">
				<form class="form-horizontal">
					<div id="table_actions"></div>
				</form>
				<br><a class="btn btn-success btn-sm bt_addAction pull-left"><i class="fa fa-plus-circle"></i>Ajouter une action</a><br><br>

				<br><br>
				<div class="alert-info bg-success">
					Vous pouvez utiliser #title# pour récupérer le nom du watchdog.
					<br><br>Vous pouvez également utiliser les variables _equipX_ et _tempoX_ dans les commandes et leurs paramètres<br>
					<br><br>Exemple: envoi d'un mail avec la date de dernière communication
					<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Titre--> Communication perdue avec #title#
					<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Message --> Dernière communication avec _equip1_ : value(_equip1_[Dernière communication])
					<br><br> Dans la configuration 'Actions sur chaque contrôle indépendamment', vous pouvez utiliser #controlname# pour récupérer le nom du contrôle et _equip_ (_equipname_ pour le nom) pour récupérer le premier équipement référencé dans le contrôle (soit directement, soit via une commande) et _cmd_ (_cmdname_ pour le nom)pour la première commande
					<br><br>Exemple: envoi d'un mail avec la date de dernière communication
					<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Titre--> #controlname# n'est plus en ligne
					<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Message --> Dernière communication avec _equipname_ : lastCommunication(_equip_)
				</div>

			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'watchdog', 'js', 'watchdog'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
