	

$(document).ready( function() {
	$("input[type=checkbox]").click( function performAJRequest() {
		var affiliation = $('#affiliation input:checked').first().val();
		var size = $('#size input:checked').first().val();
		var items = getItems();
		var item = JSON.stringify(getItems());
		var modifiers = getModifiers();
		var states = getStates();
		
		doAJRequest(affiliation, size, items, modifiers, states);
} );

	$("input[type=radio]").click( function performAJRequest() {
		var affiliation = $('#affiliation input:checked').first().val();
		var size = $('#size input:checked').first().val();
		var items = getItems();
		var item = JSON.stringify(getItems());
		var modifiers = getModifiers();
		var states = getStates();
		
		doAJRequest(affiliation, size, items, modifiers, states);
} );
	
	$("input:radio[name=affiliation]:first").attr('checked', true);
	$("input:radio[name=unitsize]:first").attr('checked', true);
});
function getItems() {
	var reItems = [];
	$('#item input:checked').each(function() {
		reItems.push($(this).val());
	});
	return reItems;
} 


	
function getModifiers() {
	var reItems = [];
	$('#modifier input:checked').each(function() {
		reItems.push($(this).val());
	});
	return reItems;
} 

function getStates() {
	var reItems = [];
	$('#state input:checked').each(function() {
		reItems.push($(this).val());
	});
	return reItems;
} 
	
var doAJRequest = function(afil, usize, uitem, umod, ustate) {			
	var request = $.ajax({
	  url: "createmarker.php",
	  type: "POST",
	  data: {	affiliation: afil,
				unitsize: usize,
				items: JSON.stringify(uitem),
				modifiers: JSON.stringify(umod),
				state: JSON.stringify(ustate),
			},
	  dataType: "json",
	  beforeSend: function() {
		$("#vkGeneratedImage").replaceWith( "<img src='./images/system/loading.gif' id='vkGeneratedImage'></img>" );
	  },
	  success: function(result) {
		$("#vkGeneratedImage").replaceWith( "<img src='cache/" + result['image'] + "' id='vkGeneratedImage' width='256px'></img>" );
		$('#vkGeneratedString').text(result['vkstring']);
	  },
	  
	});
};

			