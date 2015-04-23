jQuery(document).ready( function($) {

	var dates = $( "#wdf_goal_start_date, #wdf_goal_end_date" ).datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			numberOfMonths: 2,
		});

	//$('.wdf_level .wdf_check_switch').live('change', function(e) {
	//	if($(this).is(':checked')) {
	//		$(this).parents('.wdf_level').next('tr').find('div.wdf_reward_toggle').slideDown(400);
	//	} else {
	//		$(this).parents('.wdf_level').next('tr').find('div.wdf_reward_toggle').slideUp(400);
	//	}
	//});

	//Delete Level Line Item
	$('#wdf_levels_table').on('click', "#wdf_add_level", function(e){
		e.preventDefault();
		var current = returnNameIndex($('#wdf_levels_table tr.wdf_level.last').find('input:first').attr('name'));
		var newi = parseInt(current) + 1;
		$('#wdf_level_index').val(parseInt($('#wdf_level_index').val()) + 1);
		var template = $('tr[rel="wdf_level_template"]').clone().removeAttr('rel').show();
		var level = template.filter('tr:first').addClass('wdf_level');
		//Replace the name for all inputs with the appropriate index
		$.each($(template).find(':input'), function(i,e) {
			var rel = $(e).attr('rel');
			$(e).attr('name',rel.replace('wdf[levels][','wdf[levels][' + String(newi)))
			$(e).removeAttr('rel');
		});
		$('tr[rel="wdf_level_template"]:first').before(template);
		fixDelete();

		$('.wdf_level.last .delete a').click(function(e) {
			e.preventDefault();
			var reward = $(this).parents('tr.wdf_level.last').next('tr.wdf_reward_options');
			$(this).parents('tr.wdf_level.last').add(reward).remove();
			fixDelete();
		});
		return false;
	});
	$('#wdf_levels_table').on('click', ".wdf_level.last .delete a", function(e){
			e.preventDefault();
			var reward = $(this).parents('tr.wdf_level.last').next('tr.wdf_reward_options');
			$(this).parents('tr.wdf_level.last').add(reward).remove();
			fixDelete();
			return false;
	});

	$('.postbox-container').on('click', "#tooltip_submit", function(){
		$('#publish').trigger('click');
		return false;
	});

	function fixDelete() {
		if($('#wdf_levels_table tbody .wdf_level').length < 1){
			return false;
		}
		$('#wdf_levels_table tbody .wdf_level').removeClass('last');
		$('#wdf_levels_table tbody .wdf_level:last').addClass('last');
		fixInputs();
	}
	fixDelete();

	function returnNameIndex(string) {
		if($('#wdf_levels_table tr.wdf_level').length > 9)
			return string.substr(12,2);
		else
			return string.substr(12,1);
	}

	//Event Level Line Item
	$('#wdf_eventlevels_table').on('click', "#wdf_add_eventlevel", function(e){
		e.preventDefault();
		var current = returnEventNameIndex($('#wdf_eventlevels_table tr.wdf_eventlevel.last').find('input:first').attr('name'));
		var newi = parseInt(current) + 1;
		$('#wdf_eventlevel_index').val(parseInt($('#wdf_eventlevel_index').val()) + 1);
		var template = $('tr[rel="wdf_eventlevel_template"]').clone().removeAttr('rel').show();
		var level = template.filter('tr:first').addClass('wdf_eventlevel');
		//Replace the name for all inputs with the appropriate index
		$.each($(template).find(':input'), function(i,e) {
			var rel = $(e).attr('rel');
			$(e).attr('name',rel.replace('wdf[eventlevels][','wdf[eventlevels][' + String(newi)))
			$(e).removeAttr('rel');
		});
		$('tr[rel="wdf_eventlevel_template"]:first').before(template);
		fixEventDelete();

		$('.wdf_eventlevel.last .delete a').click(function(e) {
			e.preventDefault();
			var event = $(this).parents('tr.wdf_eventlevel.last');
			$(this).parents('tr.wdf_eventlevel.last').add(event).remove();
			fixDelete();
		});
		return false;
	});
	$('#wdf_eventlevels_table').on('click', ".wdf_eventlevel.last .delete a", function(e){
			e.preventDefault();
			var event = $(this).parents('tr.wdf_eventlevel.last');
			$(this).parents('tr.wdf_eventlevel.last').add(event).remove();
			fixEventDelete();
			return false;
	});

	function fixEventDelete() {
		if($('#wdf_eventlevels_table tbody .wdf_eventlevel').length < 1){
			return false;
		}
		$('#wdf_eventlevels_table tbody .wdf_eventlevel').removeClass('last');
		$('#wdf_eventlevels_table tbody .wdf_eventlevel:last').addClass('last');
		fixInputs();
	}
	fixEventDelete();

	function returnEventNameIndex(string) {
        if($('#wdf_eventlevels_table tr.wdf_eventlevel').length > 9)
            return string.substr(17,2);
        else
            return string.substr(17,1);
	}


	$('.wdf_actvity_level').hover( function() {
		$(this).find('td:last a').show();
	}, function() {
		$(this).find('td:last a').hide();
	});
	$('.wdf_goal_progress').progressbar({
		value: 0,
		create: function() {
			$(this).progressbar( "option", "value", Math.round( parseInt( $(this).attr('total') * 100) ) / parseInt( $(this).attr('goal') ) );
		}
	});

	function fixInputs() {
		var input_switches = $('.wdf_input_switch');

		$.each(input_switches, function(i,elm) {
			if(elm.localName == 'textarea') {
				var current = $(elm).html();
			} else if(elm.localName == 'input') {
				var current = $(elm).val();
			}
			$(elm).bind('focusin focusout', function(e) {

				if(e.type == 'focusout') {
					$(elm).prev('.wdf_bignum').addClass('wdf_disabled');
				} else {
					$(elm).prev('.wdf_bignum').removeClass('wdf_disabled');
				}
			});
		});
	}
	//run fix_inputs() on load
	fixInputs();

	$('select.wdf_toggle').bind('change', function(e) {
		var rel = $(this).attr('rel');
		var val = $(this).val();
		if(rel == 'wdf_has_goal' && val == '1') {
			var elm = $('*[rel="'+rel+'"]').not(this);
			elm.show();
		} else if(rel == 'wdf_has_reward') {
			var elm = $('*[rel="'+rel+'"]').not(this);
			if(val == '1') {
				elm.show();
			} else {
				elm.hide();
			}
		} else if(rel == 'wdf_thanks_type') {
			$('*[rel="'+rel+'"]').not(this).hide(1, function() {
				$('.wdf_thanks_'+val+'[rel="'+rel+'"]').show();
			});
		} else if(rel == 'wdf_has_goal' && val == '0') {
			var elm = $('*[rel="'+rel+'"]').not(this);
			elm.hide();
		} else if(rel == 'wdf_send_email')  {
			if(val == '1')
				$('*[rel="'+rel+'"]').not(this).show();
			else
				$('*[rel="'+rel+'"]').not(this).hide();
		} else if(rel == 'wdf_recurring')  {
			if(val == 'yes' || val == 'only') {
				$('*[rel="'+rel+'"]').not(this).show();
				$('*[rel="wdf_fixed_recurrance"]').not(this).show();
			}
			else {
				$('*[rel="'+rel+'"]').not(this).hide();
			    $('*[rel="wdf_fixed_recurrance"]').not(this).hide();
			}
        } else if(rel == 'wdf_has_eventlevels') {
		    var elm = $('*[rel="'+rel+'"]').not(this);
		    if(val == '1') {
			    elm.show();
		    } else {
			    elm.hide();
		    }
		}
	});

});
