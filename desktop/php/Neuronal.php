<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'Neuronal');
$eqLogics = eqLogic::byType('Neuronal');
?>

<div class="row row-overflow">
	<div class="col-lg-2 col-md-3 col-sm-4">
		<div class="bs-sidebar">
			<ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
				<a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un neurone}}</a>
				<li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
				<?php
					foreach ($eqLogics as $eqLogic) {
						echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
					}
				?>
			</ul>
		</div>
	</div>
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
		<legend>{{Mes Neurone}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; " >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 7em;color:#497CB1;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#497CB1">
					<center>Ajouter</center>
				</span>
			</div>
			<?php
				if (count($eqLogics) == 0) {
					echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez pas encore configurer de reseau de neurone, cliquez sur Ajouter pour commencer}}</span></center>";
				} else {
					foreach ($eqLogics as $eqLogic) {
						echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
						echo "<center>";
						echo '<img src="plugins/Neuronal/doc/images/Neuronal_icon.png" height="105" width="95" />';
						echo "</center>";
						echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
						echo '</div>';
					}
				} 
			?>
		</div>
	</div>
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<div class="right">
			<div>
				<form class="form-horizontal">
					<fieldset>
						<legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}  <i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
						<div class="form-horizontal">
							<div class="form-group">
								<label class="col-md-2 control-label">{{Nom du groupe}}</label>
								<div class="col-sm-3">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du groupe}}"/>
							
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-2 control-label" >{{Objet parent}}</label>
								<div class="col-sm-3">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
											foreach (object::all() as $object) {
												echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
											}
										?>
									</select>
								</div>
							</div>	
							<div class="form-group">
								<label class="col-sm-2 control-label" ></label>
								<div class="col-sm-9">
									<label>{{Activer}}</label>
									<input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>
									<label>{{Visible}}</label>
									<input type="checkbox" class="eqLogicAttr" data-label-text="{{Visible}}" data-l1key="isVisible" checked/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-2 control-label">{{Catégorie}}</label>
								<div class="col-md-8">
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
								<label class="col-md-2 control-label">{{Gestion Temporel}}</label>
								<div class="col-md-8">
									<label>{{Activer}}</label>
									<input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="configuration" data-l2key="temporel"/>
								</div>
							</div>
						</div>
					</fieldset> 
				</form>
				<form class="form-horizontal">
					<fieldset>
						<div class="form-actions">
							<a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
							<a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
						</div>
					</fieldset>
				</form>
			</div>
			<div class="row" style="padding-left:25px;">
				<ul class="nav nav-tabs" id="tab_parametre">	
					<li class="active"><a href="#tab_entree_neurone"><i class="fa fa-map"></i> {{Commande d'entrée du neurone}}</a></li>
					<li><a href="#tab_sortie_neurone"><i class="fa fa-pencil"></i> {{Commande de sortie du neurone}}</a></li>
					<li><a href="#tab_calibration"><i class="fa fa-cogs"></i> {{Calibration}}</a></li>	
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="tab_entree_neurone">
						<table id="table_Entree" class="table table-bordered table-condensed">
							<thead>
								<tr>
									<th>Commande</th>
									<th>Parametre</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<div class="tab-pane" id="tab_sortie_neurone">
						<table id="table_Sortie" class="table table-bordered table-condensed">
							<thead>
								<tr>
									<th>Commande</th>
									<th>Parametre</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<div class="tab-pane" id="tab_calibration" style="width: 90%; overflow:auto;">
						<table id="table_Calibration" class="table table-bordered table-condensed">
							<thead>
								<tr>
									<th>Parametre</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>	
					<form class="form-horizontal">
						<fieldset>
							<div class="form-actions">
								<a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
								<a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>	
	</div>
</div>
<?php include_file('desktop', 'Neuronal', 'js' , 'Neuronal'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
