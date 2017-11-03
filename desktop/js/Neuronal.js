$('#tab_parametre a').click(function(e) {
    e.preventDefault();
    $(this).tab('show');
});
$('body').on( 'click','.bt_selectCmdExpression', function() {
	var TypeCmd=$(this).closest("tr").attr('data-TypeCmd');
	var Commande=$(this).closest("tr").find(".expressionAttr[data-l1key=cmd]");
	jeedom.cmd.getSelectModal({cmd: {type: TypeCmd},eqLogic: {eqType_name : ''}}, function (result) {
		Commande.val(result.human);
	});
});  
$('body').on('click','.ActionAttr[data-action=add]', function () {
  if($(this).attr('data-type') == "element")
    addElement({},$(this).closest("table"));
  else
    addCalibration({},$(this).closest("table"));
});
$('body').on('click','.ActionAttr[data-action=remove]', function () {
	$(this).closest("tr").remove();
});
$("#table_cmd_Entree").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_cmd_Sortie").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
function saveEqLogic(_eqLogic) {
	if (typeof(_eqLogic.configuration) === 'undefined') 
		_eqLogic.configuration = new Object();
	if (typeof(_eqLogic.configuration.entrees) === 'undefined') 
		_eqLogic.configuration.entrees=new Object();
	var CommandesEntree= new Array();
	$('#table_Entree tbody tr').each(function( index ) {
		CommandesEntree.push($(this).getValues('.expressionAttr')[0])
	});	_eqLogic.configuration.entrees=CommandesEntree;
	if (typeof(_eqLogic.configuration.sotries) === 'undefined') 
		_eqLogic.configuration.sotries=new Object();	var CommandesSortie= new Array();
	$('#table_Sortie tbody tr').each(function( index ) {
		CommandesSortie.push($(this).getValues('.expressionAttr')[0])
	});
	_eqLogic.configuration.sotries=CommandesSortie;
	
	if (typeof(_eqLogic.configuration.calibration) === 'undefined') 
		_eqLogic.configuration.calibration=new Object();
	var CalibraionLigne= new Array();
	$('#table_Calibration tbody tr').each(function( index ) {
		CalibraionLigne.push($(this).getValues('.CalibraionAttr')[0])
	});
	_eqLogic.configuration.calibration=CalibraionLigne;
   	return _eqLogic;
}
function printEqLogic(_eqLogic) {
	$('#table_Entree tbody tr').remove();
	$('#table_Sortie tbody tr').remove();
	$('#table_Calibration thead tr').html($('<th>').text('Parametre'));
	$('#table_Calibration tbody tr').remove();
	if (typeof(_eqLogic.configuration.entrees) !== 'undefined'&& _eqLogic.configuration.entrees.length >0) {
		for(var index in _eqLogic.configuration.entrees) { 
			if(typeof(_eqLogic.configuration.entrees[index]) === "object" && _eqLogic.configuration.entrees[index] != null)
				addElement(_eqLogic.configuration.entrees[index],$('#table_Entree tbody'));
		}
	}	
	else
		addElement({},$('#table_Entree tbody'));
	if (typeof(_eqLogic.configuration.sotries) !== 'undefined'&& _eqLogic.configuration.sotries.length >0) {
		for(var index in _eqLogic.configuration.sotries) { 
			if(typeof(_eqLogic.configuration.sotries[index]) === "object" && _eqLogic.configuration.sotries[index] != null)
				addElement(_eqLogic.configuration.sotries[index],$('#table_Sortie tbody'));
		}
	}
	else
		addElement({},$('#table_Sortie tbody'));
	if (typeof(_eqLogic.configuration.calibration) !== 'undefined' && _eqLogic.configuration.calibration.length >0) {
      for(var index in _eqLogic.configuration.calibration) {
        if(typeof(_eqLogic.configuration.calibration[index]) === "object" && _eqLogic.configuration.calibration[index] != null)
          addCalibration(_eqLogic.configuration.calibration[index],$('#table_Calibration'));
      }
	}
	else
		addCalibration({},$('#table_Calibration'));
}
function addElement(_Commande, _el) {
	$('#table_Calibration thead tr th:last').before($('<th>').attr('data-param',_Commande.cmd).text(_Commande.cmd));
    	var tr = $('<tr>')
   		.append($('<td>')
    			.append($('<a class="btn btn-warning btn-sm bt_selectCmdExpression" >')
				.append($('<i class="fa fa-list-alt">')))
			.append($('<input class="expressionAttr form-control input-sm" data-l1key="cmd" />')))
 		.append($('<td>')
			.append($('<i class="fa fa-plus-circle pull-left cursor ActionAttr" data-action="add" data-type="element">'))
			.append($('<i class="fa fa-minus-circle pull-left cursor ActionAttr" data-action="remove" data-type="element">')));
        _el.append(tr);
        _el.find('tr:last').setValues(_Commande, '.expressionAttr');
}
function addCalibration(_Table, _el){
	var tr=_el.find('thead tr').clone();
	tr.find('th').each(function(index){
		 $(this).replaceWith($('<td>').append($('<input class="CalibraionAttr" data-l1key="'+$(this).attr('data-param')+'">')));
	});
	tr.find('td:last').replaceWith($('<td>')
                                   .append($('<i class="fa fa-plus-circle pull-left cursor ActionAttr" data-action="add" data-type="calibration">'))
                                   .append($('<i class="fa fa-minus-circle pull-left cursor ActionAttr" data-action="remove" data-type="calibration">')));
        _el.append(tr);
        _el.find('tr:last').setValues(_Table, '.CalibraionAttr');

}
