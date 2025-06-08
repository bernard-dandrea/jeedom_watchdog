<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('watchdog');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

// https://jeedom-facile.fr/index.php/2018/12/18/la-surveillance-de-vos-equipements-domotiques/
// http://sarakha63-domotique.fr/surveillance-equipement-z-wave-xiaomi-blea/
// 

?>

<div class="row row-overflow">
   <div class="col-xs-12 eqLogicThumbnailDisplay">
  <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
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
	
	$typeControl= $eqLogic->getConfiguration('typeControl');
	
	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
	echo '<br>';
	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-xs-12 eqLogic" style="display: none;">


<!-- Methode récupérée de Jeedouino, Merci !! -->

		<div class="input-group pull-right" style="display:inline-flex">
			
			<a href="http://jeedom.sigalou-domotique.fr/watchdog-documentation"  style="margin-right:5px" target="_blank" class="btn btn-success eqLogicAction "  title="{{Lien vers la Documentation du plugin}}"><i class="fa fa-book"></i> </a>
			<a class="btn btn-info eqLogicAction  bt_plugin_view_log"  style="margin-right:5px" title="{{Logs du Watchdog}}"><i class="fa fa-file"></i> </a>
			<a class="btn btn-default eqLogicAction "  style="margin-right:5px" data-action="configure" title="{{Configuration avancée du Watchdog}}"><i class="fas fa-cogs"></i> </a>
			<a class="btn btn-warning eqLogicAction " style="margin-right:5px" data-action="copy" title="{{Dupliquer cet équipement}}"><i class="fas fa-copy"></i> </a>
			<a class="btn btn-danger eqLogicAction " style="margin-right:5px" data-action="remove" title="{{Supprimer le Watchdog}}"><i class="fas fa-minus-circle"></i> </a>
			<a class="btn btn-success eqLogicAction "  style="margin-right:5px" data-action="save"  title="{{Sauver et Contrôler}}"><i class="fas fa-check-circle"></i> {{Sauver / Contrôler}}</a>
			
		</div>
		

	      <!-- Liste des onglets -->
	
		
 <ul class="nav nav-tabs" role="tablist">
  <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
  <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Watchdog}}</a></li>
  <li role="presentation"><a href="#controlestab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-stethoscope"></i></i> {{Equipements ou Commandes à surveiller}}</a></li>
  <li role="presentation"><a href="#infocmd" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-cogs"></i> {{Actions}}</a></li>
</ul>

  <div class="tab-content">
<!--   <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;"> -->
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
    <form class="form-horizontal"><br>
        <fieldset>
		
<br><legend><i class="fa animal-dog56" style="font-size : 3em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Identification et options du watchdog}}</span></legend>

            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom du watchdog}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement watchdog}}"/>
                </div>
            </div>
			
			
            <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                        <option value="">{{Aucun}}</option>
                        <?php

			    
/* Ancienne version
foreach (jeeObject::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
} */

			    
/* Modifications apportées */
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
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked >{{Activé}}</label>
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked >{{Visible}}</label>
		</div>
	</div>
	<br>
	<div class="form-group">
		<label class="col-sm-3 control-label">Log spécifique pour ce watchdog</label>
		<div class="col-sm-9">
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="logspecifique" >{{Activé}}</label>
		</div>
	</div>
	<br>
			
			<div class="form-group">
			<label class="col-xs-3 control-label">{{Auto-actualisation (cron)}}</label>
				<div class="col-xs-2">
					<div class="input-group">
					<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="autorefresh" placeholder="{{Auto-actualisation (cron)}}"/>
					<span class="input-group-btn">
					<a class="btn btn-success btn-sm " id="bt_cronGenerator" ><i class="fas fa-question-circle"></i></a>
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
			
<br><legend><i class="fa fa-list-alt" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Mode de fonctionnement}}</span></legend>
			<div class="form-group">
                <label class="col-sm-3 control-label" >{{Mode de fonctionnement des contrôles}}</label>
                <div class="col-sm-3">
                    <select style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="typeControl">
<option value="">{{Actions sur chaque contrôle indépendamment (par défaut)}}</option>
<option value="OU">{{Actions sur l'ensemble des contrôles (avec méthode OU)}}</option>
<option value="ET">{{Actions sur l'ensemble des contrôles (avec méthode ET)}}</option>
                   </select>
               </div><br><br>
		   	
           </div>
		
<div class="alert-info bg-success">
	Il existe trois modes de fonctionnement  : <br>
	* Actions sur chaque contrôle indépendamment : Ce mode teste indépendamment chaque contrôle et déclenche les actions quand ce contrôle a changé d'état<br>
	* Actions sur l'ensemble des contrôles (avec méthode OU) : Ce mode teste le résultat global des contrôles en y appliquant un test "OU" entre chaque contrôle. Il déclenche les actions quand le résultat global a changé d'état.<br>
	* Actions sur l'ensemble des contrôles (avec méthode ET) : Ce mode teste le résultat global des contrôles en y appliquant un test "ET" entre chaque contrôle. Il déclenche les actions quand le résultat global a changé d'état.	</div>			

			<br><br><div class="form-group">
                <label class="col-sm-3 control-label" >{{Mode de fonctionnement des actions}}</label>
                <div class="col-sm-3">
                    <select style="width: 500px;" id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="typeAction">
<option value="">{{Lancer une action uniquement si le contrôle change de valeur (par défaut)}}</option>
<option value="ALL">{{Lancer une action à chaque controle en fonction du résultat}}</option>
                   </select>
               </div><br><br>
		   	
           </div>
		
<div class="alert-info bg-success">
	Il existe deux modes de fonctionnement  : <br>
	* Lancer une action uniquement si le contrôle change de valeur : Ce mode vous prévient quand le contrôle effectué a changé d'état<br>
	* Lancer une action à chaque controle en fonction du résultat : Ce mode execute à chaque contrôle l'action qui correspond au résultat du contrôle.<br>
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



<!--<br><br><legend><i class="fa loisir-weightlift"></i> {{Résultat global des contrôles, méthode <?php print $typeControl;?>}}</legend>
 <table id="table_controles_resultat00000000" class="table  table-condensed ui-sortable table_controles_resultat0000000000">
</table> -->


<!-- ICI la partie qui affiche le résultat global dans le cas d'un mode OU ou d'un ET-->
<div id="section_resultatGlobal" >
</div>




<legend><i class="fas fa-hourglass-half" style="font-size : 2em;color:#a15bf7;"></i> <span style="color:#a15bf7">{{Configuration des tempos}}</span></legend>

<table border="0">
<tbody>
<tr>
<td style="text-align: right; width: 100px;"><b>tempo1  : </b></td>
<td><input style="width: 100px;" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key='tempo1' placeholder="{{En secondes}} "/></td>
<td style="text-align: right; width: 100px;"><b>tempo2 : </b></td>
<td><input style="width: 100px;" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key='tempo2' placeholder="{{En secondes}} "/></td>
<td style="text-align: right; width: 100px;"><b>tempo3 : </b></td>
<td><input style="width: 100px;" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key='tempo3' placeholder="{{En secondes}} "/></td>
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
        Les tempos peuvent être utilisées pour faire des tests lors d'un contôle  : <br/>
        #tempo1# = Valeur en secondes. Mettre #tempo1# pour récupérer la valeur
        </div>
</div>



       <div role="tabpanel" class="tab-pane" id="infocmd">
		<form class="form-horizontal">
			<div id="table_actions"></div>
		</form>
	<br><a class="btn btn-success btn-sm bt_addAction pull-left" ><i class="fa fa-plus-circle"></i>Ajouter une action</a><br><br>	   
<?php if ($typeControl=="")
							{?>
							<br><br><div class="alert-info bg-success">
							<b>#controlname#</b> = Nom du contrôle qui a déclenché l'action. Mettre #controlname# dans <b>Titre</b> ou dans <b>Message</b> pour y récupérer la valeur<br/>
							<b>#title#</b> = Nom du Watchdog. Mettre #title# dans <b>Titre</b> ou dans <b>Message</b> pour y récupérer la valeur
							</div>
							<?php 
							}
else {?>
							<br><br><div class="alert-info bg-success">
							<b>#title#</b> = Nom du Watchdog. Mettre #title# dans <b>Titre</b> ou dans <b>Message</b> pour y récupérer la valeur
							</div>
							<?php 
							}

							?>
	   
</div>
</div>
</div>

<?php include_file('desktop', 'watchdog', 'js', 'watchdog');?>
<?php include_file('core', 'plugin.template', 'js');?>

