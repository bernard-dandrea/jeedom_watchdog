<!--  
  Last Modified : 2025/09/04 16:21:03
-->

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

			<!-- Bouton de reporting -->
			<div class="cursor logoSecondary" id="bt_reporting_maintenance">
				<i class="fas fa-book-open" style="font-size : 5em;color:#a15bf7;" title="{{Supprime les commandes orphelines et renomme les commandes info dans le virtuel sélectionné}}"></i>
				<br>
				<span>{{Reporting}}</span>
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

		<div id='buttons' class="input-group pull-right" style="display:inline-flex">

			<a class="btn btn-info bt_plugin_view_log" style="margin-right:5px" title="{{Logs du Watchdog}}"><i class="fa fa-file"></i> </a>
			<a class="btn btn-info bt_help" style="margin-right:5px" title="{{Afficher/Cacher l'aide}}"><i class="far fa-question-circle"></i> </a>
			<div id="insert_documentation">
				<?php include_file('desktop', 'bt_documentation', 'js', 'watchdog'); ?>
			</div>
			<a class="btn btn-default eqLogicAction " style="margin-right:5px" data-action="configure" title="{{Configuration avancée du Watchdog}}"><i class="fas fa-cogs"></i> </a>
			<a class="btn btn-warning eqLogicAction " style="margin-right:5px" data-action="copy" title="{{Dupliquer ce watchdog}}"><i class="fas fa-copy"></i> </a>
			<a class="btn btn-danger eqLogicAction " style="margin-right:5px" data-action="remove" title="{{Supprimer le Watchdog}}"><i class="fas fa-minus-circle"></i> </a>
			<a class="btn btn-success eqLogicAction" style="margin-right:5px" data-action="save" title="{{Attention, lors de la sauvegarde, seuls les contrôles sont effectués. Les actions ne sont pas lancées. Les résultats précédents ne sont pas changés non plus.}}"><i class="fas fa-check-circle"></i> {{Sauver / Contrôler}}</a>
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
								<sup><i id='logspecifique_tooltip' class="fas fa-question-circle tooltips" ></i></sup>
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
									<option value="">{{Actions sur chaque contrôle indépendamment}}</option>
									<option value="OU">{{Actions sur l'ensemble des contrôles (avec méthode OU)}}</option>
									<option value="ET">{{Actions sur l'ensemble des contrôles (avec méthode ET)}}</option>
								</select>
							</div>
						</div>
						<br>
						<div class="help_field">
							<div class="alert-info bg-success  ">
								Il existe trois modes de fonctionnement : <br>
								<br>&nbsp;&nbsp;* Actions sur chaque contrôle indépendamment : Ce mode teste indépendamment chaque contrôle et déclenche les actions suivant le mode de fonctionnement des actions (voir paramètre suivant). Dans cette configuration, le Résultat Global n'est pas géré.
								<br>&nbsp;&nbsp;* Méthode OU : Ce mode teste le résultat global des contrôles en appliquant un test "OU" entre chaque contrôle (le résultat global est vrai si au moins un des contrôles est vrai). A la fin des contrôles, les actions sont lancées suivant le mode de fonctionnement des actions (voir paramètre suivant).
								<br>&nbsp;&nbsp;* Méthode ET : Ce mode teste le résultat global des contrôles en appliquant un test "ET" entre chaque contrôle (le résultat global est vrai si tous les contrôles sont vrais). A la fin des contrôles, les actions sont lancées suivant le mode de fonctionnement des actions (voir paramètre suivant).
							</div>
							<br>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">{{Lancer les actions}}</label>
							<div class="col-sm-3">
								<select name="typeAction" style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="typeAction">
									<option value="">{{Défaut: Uniquement si le résultat a changé de valeur}}</option>
									<option value="ALL">{{ALL: Même si le résultat n'a pas changé de valeur}}</option>
									<option value="True">{{True: Tant que le résultat vaut True ou si il passe à False}}</option>
									<option value="False">{{False: Tant que le résultat vaut False ou si il passe à True}}</option>
								</select>
							</div>
						</div>
						<br>
						<div class="help_field">
							<div class="alert-info bg-success">
								Il existe quatre modes de fonctionnement de lancement des actions qui dépendent du résultat contrôle: <br>
								<br>&nbsp;&nbsp;* dans le mode 'Actions sur chaque contrôle indépendamment', le résultat correspond au résultat du contrôle de la condition qui est traitée.
								<br>&nbsp;&nbsp;* dans le mode ET/OU, le résultat correspond au résultat global.
							</div>
							<br>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">{{Actions Avant/Après}}</label>
							<div class="col-sm-3">
								<select name="typecontrole" style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="LancementActionsAvantApres">
									<option value="">{{Uniquement avant et après le lancement des contrôles}}</option>
									<option value="ALL">{{Avant et Après CHAQUE contrôle}}</option>
								</select>
							</div>
						</div>
						<br>
						<div class="help_field">
							<div class="alert-info bg-success">
								Cette option ne s'applique que dans le mode Actions sur chaque contrôle indépendamment<br>
							</div>

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
								<select style="width: 500px;" id="sel_ResultatHistory" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ResultatHistory">
									<option value="">{{Valeur par défaut}}</option>
									<option value="/">{{Aucun}}</option>
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


						<legend><i class="icon kiko-book-open" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Reporting}}</span></legend>

						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Virtuel pour le reporting}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Les résultats des contrôles seront enregistrés dans ce virtuel, ce qui permet d'avoir une vue globale de l'état des watchdogs.}}"></i></sup>
							</label>
							<div class="col-sm-3">
								<div class="input-group">
									<input title='Laisser vide si valeur par défaut. Mettre / si on ne veut pas reporting même si il y en a un de défini par défaut.' class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="VirtualReport" />
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
								<select style="width: 500px;" id="sel_ReportingHistory" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ReportingHistory">
									<option value="">{{Valeur par défaut}}</option>
									<option value="/">{{Aucun}}</option>
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
								<select style="width: 500px;" id="sel_ReportingSuppressionAutomatique" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ReportingSuppressionAutomatique">
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
				<div class="help_field">
					<div class="alert-info bg-success">
						Vous pouvez entrer dans la zone contrôle n'importe quelle expression reconnue dans les scénarios. Cette expression doit renvoyer True (=1) ou False (=0).
						<br>Vous pouvez tester l'expression dans le Testeur d'expressions (3ème bouton à droite de l'expression)).
						<br>Les expressions incorrectes sont ignorées lors de l'exécution du watchdog en mode programmé ou via la commande Refresh.
					</div>
				</div>
				<legend><i class="icon jeedomapp-settings" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Macro}}</span></legend>

				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: left; width: 100px;"><b>Macro</b></td>
							<td>
								<div class="input-group">
									<input style="width: 1000px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="macro" />
								</div>
							</td>
						</tr>
					</tbody>
				</table><br>

				<div class="help_field">
					<div class="alert-info bg-success">
						La macro peut être utilisée pour répéter les mêmes conditions en faisant varier les paramètres qui peuvent être des équipements, des commandes ou toute autre donnée passée en argument.
						<br><br>Définissez l'expression en utilisant les parametres _arg1_, _arg2_, ... qui représentent les paramètres. Vous pouvez ensuite utiliser la macro dans la condition avec la syntaxe _macro_(arg1,arg2, ... ).
						<br><br>Par exemple pour tester qu'une commande est mise à jour régulièrement, définissez la macro suivante: (#timestamp# - strtotime(lastCommunication(_arg1_))) > _arg2_ et eqEnable(_arg1_) == 1
						<br>Vous utiliser la macro dans la condition avec la syntaxe : _macro_(#[Surveillance Maison][Fuite LV]#,_tempo1_)
						<br>Le test généré sera (avec tempo1 = 21600): (#timestamp# - strtotime(lastCommunication(#[Surveillance Maison][Fuite LV]#))) > 21600 et eqEnable(#[Surveillance Maison][Fuite LV]#) == 1
						<br><br>Pour spécifier une commande info d'un équipement, utiliser la syntaxe #_equipement_[nom de la commande info]# par exemple #_arg1_[Statut]#.
						<br><br>Noter que le générateur d'expression permet de sélectionner un équipement ou une commande et de générer l'appel correspondant à la macro.
						<br>
					</div>
				</div>
				<legend><i class="icon jeedomapp-settings" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Variables}}</span></legend>
				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: left; width: 100px;"><b>tempo1</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="tempo1" />
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: left; width: 100px;"><b>tempo2</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="tempo2" />
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: left; width: 100px;"><b>tempo3</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="tempo3" />
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<br>
				<div class="help_field">
					<div class=" alert-info bg-success">
						Les variables _tempoX_ peuvent être utilisées pour faire des tests dans un contrôle. Par exemple, mettre _tempo1_ (ou #tempo1#) pour récupérer la valeur de la tempo1 dans la formule du contrôle.
						<br>Vous pouvez utiliser des formules, par exemple 3*3600 pour indiquer 3 heures. Par défaut, on considère que l'unité est la seconde mais ce n'est pas une obligation
					</div>
				</div>
				<br>
				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: left; width: 100px;"><b>var1</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="var1" />
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: left; width: 100px;"><b>var2</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="var2" />
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: left; width: 100px;"><b>var3</b></td>
							<td>
								<div class="input-group">
									<input style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="var3" />
								</div>
							</td>
						</tr>
					</tbody>
				</table><br>
				<div class="help_field">
					<div class="alert-info bg-success">
						Les variables _varX_ peuvent être utilisées pour faire des tests dans un contrôle. Par exemple, mettre _var1_ pour récupérer la variable 1 dans la formule du contrôle.
						<br>Vous pouvez également utiliser une formule pour définir la variable.
					</div>
				</div>
				<br>
				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: left; width: 100px;"><b>Equipement 1</b></td>
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
							<td style="text-align: left; width: 100px;"><b>Equipement 2</b></td>
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
							<td style="text-align: left; width: 100px;"><b>Equipement 3</b></td>
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
				<div class="help_field">
					<div class="alert-info bg-success">
						Les variables _equipX_ peuvent être utilisées pour faire des tests dans un contrôle. Par exemple, mettre _equip1_ pour récupérer l'équipement 1 dans la formule du contrôle.
						<br><br>Exemple de formule:
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* tester la dernière communication d'un équipement 1 --> (#timestamp# - strtotime(lastCommunication(_equip1_))) > #tempo1#
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* tester la valeur commande info Statut de l'équipement 2 --> #_equip2_[Statut]# == 1
						<br>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="infocmd">
				<form class="form-horizontal">
					<div id="table_actions"></div>
				</form>
				<br><a class="btn btn-success btn-sm bt_addAction pull-left"><i class="fa fa-plus-circle"></i>Ajouter une action</a><br><br>
				<div class="help_field">
					<div class="alert-info bg-success">
						Vous pouvez utiliser _title_ (ou #title#) pour récupérer le nom du watchdog. Vous pouvez également utiliser les variables _equipX_ (_equipXname_ pour le nom), _argX_ (_argXname_ pour le nom) et les autres variables (_tempoX_, _varX_) dans les commandes et leurs paramètres.
						<br>Exemple: envoi d'un mail avec la date de dernière communication
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Titre--> Communication perdue avec _title_
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Message --> Dernière communication avec _equip1name_ : #_equip1_[Dernière communication]#
						<br><br>Dans la configuration 'Actions sur chaque contrôle indépendamment', vous pouvez utiliser _controlname_ (ou #controlname#) pour récupérer le nom du contrôle et _equip_ (_equipname_ pour le nom) pour récupérer le premier équipement référencé dans le contrôle (soit directement, soit via une commande) et _cmd_ (_cmdname_ pour le nom) pour la première commande
						<br><br>Exemple: envoi d'un mail avec la date de dernière communication
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Titre--> _controlname_ n'est plus en ligne
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Message --> Dernière communication avec _arg1name_ (IP #_arg1_[addresseIP]#): valuedate(_cmd_)
						<br><br>Pour spécifier une commande info d'un équipement référencé, utiliser la syntaxe #_equipement_[nom de la commande info]# par exemple #_arg1_[addresseIP]#. Pour désigner l'équipement, vous pouvez utiliser _equip_ (première commande référencée dans le contrôle), _argX_ (argument X), _equipX_ (variable equipX). Vous pouvez également utiliser directement _cmd_ (première commande référencée dans la condition).<br>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php include_file('desktop', 'watchdog', 'js', 'watchdog'); ?>
	<?php include_file('desktop', 'generer_expression', 'js', 'watchdog'); ?>
	<?php include_file('core', 'plugin.template', 'js'); ?>
	<script>
		// restore ou non les textes d aide
		set_help_state('reload');
	</script>