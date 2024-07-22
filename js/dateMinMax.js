$(document).ready(function () {
	//console.log("Hello, world!");

	let module = ExternalModules['UAB'].DateMaxMinModule;

	let attachedListeners = false;

	const query_params = window.location.search;

	let date_field_values = {};

	//console.log(query_params);

	// ?pid=148&id=1716-1&page=arm_m&event_id=597&instance=1

	var project_id = query_params.match(/pid=([^&]+)/)[1];

	var record_id = query_params.match(/&id=([^&]+)/)[1];

	var page = query_params.match(/page=([^&]+)/)[1];

	var event_id = query_params.match(/event_id=([^&]+)/)[1];

	var instance = 1;

	if(query_params.includes('instance=')){
		instance = query_params.match(/instance=([^&]+)/)[1];
	}


	$('#form').on('mousemove', function() {
		if (!attachedListeners) {
			attachedListeners = true;
			console.log('mouse moved!');
			$(".hasDatepicker").each(function () {
				var val = $(this).val();
				var name = $(this).attr("name");
				//console.log(name);
				//console.log(val);
				date_field_values[name] = val;
			})
		}
	});


	$(".hasDatepicker").focusout(function () {
		if(!attachedListeners){
			return;
		}
		var dt_val = $(this).val();
		var field_name = $(this).attr('name');

		if(date_field_values[field_name] == dt_val){
			return;
		} else {
			date_field_values[field_name] = dt_val;
		}

		if(dt_val == ''){
			return;
		}

		if(dt_val != ''){
			// handle future date
			var dt = new Date(dt_val);
			var today = new Date();
			//console.log(dt);
			//console.log(today);
			//console.log(dt. - today);
			var not_future = dt.setHours(0,0,0,0) <= new Date().setHours(0,0,0,0);
			//console.log(not_future);

			if(not_future === false){
				var msg_div = "You are entering a future date!";
				simpleDialog(msg_div,'Please check the entered date.', null, 570);
				$(this).val('');
				return;
			}
		}

		
		//console.log(field_name);

		var payload = {
			'project_id': project_id,
			'record': record_id,
			'instrument': page,
			'event_id': event_id,
			'instance': instance,
			'field_name': field_name,
			'entered_date': dt_val,
			'date_format': 'mdy'
		};

		module.ajax('min_max_date_validation', payload).then(function(response) {
   				//instrument_structure = JSON.parse(response);
   			console.log(JSON.parse(response));
   			//console.log(typeof response);
			//update_table(JSON.parse(response), week_dt_arr);
			//update_table_view();
			var json_response = JSON.parse(response);

			var message = json_response['message'];

			if(message != ""){
				//var msg_div = message;
				simpleDialog(message,'Please check the entered date.', null, 570);
			}

   		
		}).catch(function(err) {
   				// Handle error
   				console.log(err);
		});
	});


	// url.match(/\?.+/)[0]
});