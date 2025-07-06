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
			<a class="btn btn-success eqLogicAction " style="margin-right:5px" data-action="save" title="{{Sauver et Contrôler}}"><i class="fas fa-check-circle"></i> {{Sauver / Contrôler}}</a>
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
									<option value="">{{Aucun}}</option>
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
						<br>
						<div class="form-group">
							<label class="col-sm-3 control-label">Log spécifique pour ce watchdog</label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="logspecifique">{{Activé}}</label>
							</div>
						</div>
						<br>

						<div class="form-group">
							<label class="col-xs-3 control-label">{{Auto-actualisation (cron)}}</label>
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
								<select style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="typeControl">
									<option value="">{{Actions sur chaque contrôle indépendamment (par défaut)}}</option>
									<option value="OU">{{Actions sur l'ensemble des contrôles (avec méthode OU)}}</option>
									<option value="ET">{{Actions sur l'ensemble des contrôles (avec méthode ET)}}</option>
								</select>
							</div><br><br>
						</div>

						<div class="alert-info bg-success">
							Il existe trois modes de fonctionnement : <br>
							* Actions sur chaque contrôle indépendamment : Ce mode teste indépendamment chaque contrôle et déclenche les actions suivant le mode de fonctionnement des actions (paramètre suivant). Dans cette configuration, le Résultat Global n'est pas géré.<br>
							* Méthode OU : Ce mode teste le résultat global des contrôles en appliquant un test "OU" entre chaque contrôle (le résultat global est vrai si au moins un des controles est vrai). A la fin des contrôles, les actions sont lancées suivant le mode de fonctionnement des actions (paramètre suivant).<br>
							* Méthode ET : Ce mode teste le résultat global des contrôles en appliquant un test "ET" entre chaque contrôle  (le résultat global est vrai si tous les controles sont vrais).  A la fin des contrôles, les actions sont lancées suivant le mode de fonctionnement des actions (paramètre suivant).</div>

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

						<legend><i class="icon kiko-check-line" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Résultat Global}}</span></legend>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Le contrôle est OK lors le Résultat Global est égal à}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ResultatGlobalOK">
									<option value="">{{Valeur par défaut}}</option>
									<option value="1">{{True}}</option>
									<option value="0">{{False}}</option>
								</select>
							</div>
						</div> <br>
						
						<legend><i class="icon kiko-book-open" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Reporting}}</span></legend>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Virtuel pour le reporting}}</label>
							<div class="col-sm-3">
								<div class="input-group">
									<input style="width: 500px;" title='Laisser vide si valeur par défaut. Mettre / si on ne veut pas reporting même si il y en a un de défini par défaut.' class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="VirtualReport" />
									<span class="input-group-btn">
										<a class="btn btn-default cursor" title="Rechercher un équipement" id="VirtualReport"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</div>
						</div> <br>
						<div class=" form-group"></div>
						<label class="col-sm-3 control-label">{{Afficher seulement les watchdogs non OK}}</label>
						<div class="col-sm-3">
							<select style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ReportOnlyNonOK">
								<option value="">{{Valeur par défaut}}</option>
								<option value="1">{{Oui}}</option>
								<option value="0">{{Non}}</option>
							</select>
						</div> <br>

						<?php
						$widgetDashboard = cmd::getSelectOptionsByTypeAndSubtype('info', 'binary', 'dashboard', cmd::availableWidget('dashboard'));
						$widgetMobile = cmd::getSelectOptionsByTypeAndSubtype('info', 'binary', 'dashboard', cmd::availableWidget('mobile'));
						?>
						<br><br>
						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Widget Résultat Global dashboard}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="template_resultatglobal_dashboard">
									<option value="">{{Valeur par défaut}}</option>
									<?php
									echo $widgetDashboard;
									?>
								</select>
							</div>
						</div>
						<br>

						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Widget Résultat Global mobile}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="template_resultatglobal_mobile">
									<option value="">{{Valeur par défaut}}</option>
									<?php
									echo $widgetMobile;
									?>
								</select>
							</div>
						</div>
						<br>

						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Widget Reporting dashboard}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="template_reporting_dashboard">
									<option value="">{{Valeur par défaut}}</option>
									<?php
									echo $widgetDashboard;
									?>
								</select>
							</div>
						</div>
						<br>

						<div class=" form-group">
							<label class="col-sm-3 control-label">{{Widget Reporting mobile}}</label>
							<div class="col-sm-3">
								<select style="width: 500px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="template_reporting_mobile">
									<option value="">{{Valeur par défaut}}</option>
									<?php
									echo $widgetMobile;
									?>
								</select>
							</div>
						</div>
						<br>
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

				<!-- ICI la partie qui affiche le résultat global dans le cas d'un mode OU ou d'un ET-->
				<div id="section_resultatGlobal">
				</div>

				<legend><i class="icon jeedomapp-settings" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Variables}}</span></legend>

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
					Les variables tempo peuvent être utilisées pour faire des tests dans un contrôle. Par exemple, mettre #tempo1# pour récupérer la valeur de la tempo1 dans la formule du contrôle
					<br>
				</div>
				<br>
				<table border="0">
					<tbody>
						<tr>
							<td style="text-align: right; width: 100px;"><b>Equipement 1</b></td>
							<td>
								<div class="input-group">
									<input style="width: 300px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="equip1" />
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
									<input style="width: 300px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="equip2" />
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
									<input style="width: 300px;" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="equip3" />
									<span class="input-group-btn">
										<a class="btn btn-default cursor" title="Rechercher un équipement" id="equip3"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</td>
						</tr>
					</tbody>
				</table><br>

				<div class="alert-info bg-success">
					Les variables équipements peuvent être utilisées pour faire des tests dans un contrôle. Par exemple, mettre #equip1# pour récupérer l'équipement 1 dans la formule du contrôle.<br><br>
					Exemple de formule: <br><br>
					* tester la dernière communication d'un équipement 1 --> (#timestamp# - strtotime(lastCommunication(#equip1#))) > #tempo1#<br>
					* tester le résultat d'une commande de l'équipement 2 --> value(#equip2#[Statut]) == 1

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
					<b>Vous pouvez #title# pour récupérer le nom du watchdog ou #controlname# (uniquement dans la configuration 'Actions sur chaque contrôle indépendemment')
						<br><br>Vous pouvez également utiliser les variables #equipX# et #tempoX# dans les paramètres des commandes <br>
						<br>Exemple: envoi d'un mail avec la date de dernière communication
						<br>
						* Titre--> Communication perdue avec #title#
						<br>* Message --> Dernière communication avec #equip1# : value(#equip1#[Dernière communication])
				</div>

			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'watchdog', 'js', 'watchdog'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
