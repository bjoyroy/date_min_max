$(document).ready(function () {
	//console.log("Hello, world!");
	
	let module = ExternalModules['HonorValidation'].HonorValidationModule;

	var instrument_structure;
	var required_not_completed;

	var form_complete_field_name;

	apply_button_status(true);

	// https://stackoverflow.com/questions/16026942/how-do-i-chain-three-asynchronous-calls-using-jquery-promises

	
	module.ajax('instrument_structure_json', {}).then(function(response) {
   		instrument_structure = JSON.parse(response);

   		var field_names = Object.keys(instrument_structure);

   		for (var i = field_names.length - 1; i >= 0; i--){
   			var field_name = field_names[i];
   			var $obj = instrument_structure[field_name];
   			if(field_name.endsWith('_complete') && $obj['element_label'] == 'Complete?'){
   				form_complete_field_name = field_name;
   				//console.log(field_name);
   				//console.log(instrument_structure[field_name]);
   				break;
   			}

   		}
   		
   		//console.log(instrument_structure);
   		apply_button_status(false);
	}).catch(function(err) {
   		// Handle error
   		console.log(err);
	});

	function apply_button_status(status){

		var sel = $("select[name=" + form_complete_field_name + "]").val();
		console.log(sel);
		
		//console.log('apply button status!!!');
		$("#submit-btn-saverecord").prop('disabled', status);

		$("#submit-btn-savecontinue").prop('disabled', status);

		$("#submit-btn-dropdown").prop('disabled', status);

	}


	$("#form").change(function () {
		//console.log('hello, world!');
		apply_button_status(false);
	})

	$("button.btn-primaryrc").on("mousedown", function () {
		$(this).prop("disabled", false);
	});

	$("button.btn-primaryrc").on("mouseup", function () {
		// https://stackoverflow.com/questions/8643739/cancel-click-event-in-the-mouseup-event-handler
		// do a test for validation, if validation passes then enable
		$(this).prop("disabled", true);
		var returnVal = check_form_required_status();		
		// then do a test for validation, if validation passes then enable
		$(this).prop("disabled", returnVal);

		var that = this;


		if (returnVal == true){
			//simpleDialog(data,window.lang.survey_1231,null,570);
			var msg_div = "<div>Your data was not save. To save the data, you require to provide value for all required fields.<br><br>Please provide value for:<br>";
			msg_div += "<ul>";
			required_not_completed.forEach(function(d){
				var str = instrument_structure[d]['element_label'] == ''? d:instrument_structure[d]['element_label'];
				console.log();
				var val = "<li>" + str + "</li>";
				msg_div += val;
			});
			msg_div += "</ul>";
			msg_div += "</div>";
			simpleDialog(msg_div,'Please fill out all the required fields', null, 570);

		}
	});


	function check_form_required_status() {
		required_not_completed = [];
	    $("#questiontable tr").each(function() {
	        if ($(this).attr("req") != null && !$(this).hasClass('row-field-embedded') && $(this).attr("hasval") != "1" && (!($(this).hasClass("\@READONLY")))
	        	&& ((!($(this).hasClass("\@HIDDEN"))) || (!$(this).hasClass("\@HIDDEN-FORM")))) {
	        	if ($(this).css("display") == "none") {
	        		// should be hidden. do nothing
	        	} else {
	        		//console.log($(this).attr("sq_id"));
	        		var fld_name = $(this).attr("sq_id");

	        		var element_type = instrument_structure[fld_name]['element_type'];

	        		if(element_type == 'select'){
	        			var sel_val = $("select[name=" + fld_name + "]").find(":selected").val();
						if(sel_val == ''){
							required_not_completed.push(fld_name);
						}
	        		} else if (element_type == 'checkbox'){
	        			var checkboxes = $(this).find("input[type=checkbox]").filter(":visible");
						var enhancedchoice = $(this).find("div.enhancedchoice label.selectedchkbox, div.enhancedchoice label.unselectedchkbox").filter(":visible");
						var checkboxes_checked = $('input:checked',this);
						if (checkboxes.length > 0 && checkboxes_checked.length == 0 && enhancedchoice.length == 0) {
							required_not_completed.push(fld_name);
						} 
	        		} else {
	        			var val = $("input[name=" + fld_name +"]").val();

	        			if (val == ''){
	        				required_not_completed.push(fld_name);
	        			}
	        		}

	        	}
	        }
	    });

	    //console.log(required_not_completed);

	    if (required_not_completed.length == 0){
	    	return false;
	    }

	    return true;



	}



	$("button").on("mouseout", function () {
		//$(this).prop("disabled", false);
	});
	
	

	
});