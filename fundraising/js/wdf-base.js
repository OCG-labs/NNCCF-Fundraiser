/*!
* jQuery Bullseye v1.0
* http://pixeltango.com
*
* Copyright 2010, Mickel Andersson
* Dual licensed under the MIT or GPL Version 2 licenses.
*
* Date: Fri Aug 31 19:09:11 2010 +0100
*/
(function($){
jQuery.fn.bullseye = function (b, h) { b = jQuery.extend({ offsetTop: 0, offsetHeight: 0, extendDown: false }, b); return this.each(function () { var a = $(this), c = $(h == null ? window : h), g = function () { var d = a.outerWidth(), e = a.outerHeight() + b.offsetHeight; c.width(); var f = c.height(), i = c.scrollTop(), j = c.scrollLeft() + d; f = i + f; var k = a.offset().left; d = k + d; var l = a.offset().top + b.offsetTop; e = l + e; if (f < l || (b.extendDown ? false : i > e) || j < k || j > d) { if (a.data("is-focused")) { a.data("is-focused", false); a.trigger("leaveviewport") } } else if (!a.data("is-focused")) { a.data("is-focused", true); a.trigger("enterviewport") } }; c.scroll(g).resize(g); g() }) };
})(jQuery);

jQuery(document).ready( function($) {

	var infoReminder = false;

	var prog_default = {
		value: 0,
		create: function() {
			var value = Math.round( parseInt( $(this).attr('total') * 100) ) / parseInt( $(this).attr('goal') );
			if(value > 100) { value = 100 }
			$(this).find('.ui-progressbar-value').animate({ width: value + '%'},4000,'swing');
			$(this).progressbar( "option", "value", value);
		}
	};

	$('.wdf_rewards .wdf_reward_item').bind('click', function(e) {
		var _this = $(this);
		var rel = _this.find('.wdf_level_amount').attr('rel');
		var pledge = _this.parent().find('input.wdf_pledge_amount');
		_this.parent().find('input.wdf_pledge_amount').val(rel);

		_this.find('input:radio').prop('checked', true);
	});

	var donate_inputs = $('.wdf_donate_amount');
	$('.wdf_goal_progress').progressbar(prog_default).bind('enterviewport', function() {
		if($(this).hasClass('not-seen')) {
			var value = Math.round( parseInt( $(this).attr('total') * 100) ) / parseInt( $(this).attr('goal') );
			if(value > 100) { value = 100 }
			$(this).find('.ui-progressbar-value').width(0).animate({ width: value + '%'},4000,'swing');
			$(this).removeClass('not-seen').addClass('seen');
			$(this).progressbar( "option", "value", value);
		} else {
			//Do Nothing
		}
	}).bullseye();

	$('.wdf_donate_btn.oneclick').click( function() {
		$(this).parents('form').trigger('submit');
		return false;
	});
	donate_inputs.bind('focusin focusout focus', function(e) {
		var _this = $(this);
		var initVal = _this.val();
		if(e.type == 'focusin') {

		} else if(e.type == 'focusout') {

		} else {
		}
	});

	$('.wdf_eventlevels .wdf_eventlevel_pledge').bind('click', function(e) {
		var _this = $(this);
		var rel = _this.attr('rel');
		var partsArray = rel.split('_');
		var level = partsArray[partsArray.length-1];
		var numattendees = $("input[name='wdf_eventlevel_numattendees_" + level + "']").val();
		var level_name = $("input[name='wdf_eventlevel_name_" + level + "']").val();
		var level_type = $("input[name='wdf_eventlevel_amounttype_" + level + "']").val();
		$("input[name='wdf_eventlevel_name").val(level_name);
		$("input[name='wdf_eventlevel_amounttype").val(level_type);
		$("input[name='wdf_eventlevel_numattendees").val(numattendees);

		//build event level attendee form
		var template_dom = $('.wdf_eventattendee_table_template').clone();
		template_dom.removeClass("wdf_eventattendee_table_template");
		template_dom.addClass("wdf_eventattendee_table" );
		template_dom.css('display', 'block');

		var htmlStr = '<h4>Attendee Information</h4>';
		htmlStr += '<p>Please provide the following information for each attendee</p>';
		for (var i=0;i<numattendees;i++) {
			var new_template_dom = template_dom.clone();
			var name = new_template_dom.find("input[name='wdf_attendee_name']").attr("name")
			var email = new_template_dom.find("input[name='wdf_attendee_email']").attr("name")
			var phone = new_template_dom.find("input[name='wdf_attendee_phone']").attr("name")
			new_template_dom.find("input[name='wdf_attendee_name']").attr("name", (name + "_" + i))
			new_template_dom.find("input[name='wdf_attendee_email']").attr("name", (email + "_" + i))
			new_template_dom.find("input[name='wdf_attendee_phone']").attr("name", (phone + "_" + i))
			htmlStr += new_template_dom[0].outerHTML;
		}
		$('.wdf_eventattendee_info').html(htmlStr);

	});

	$('.wdf_fundraiser_panel > .wdf_checkout_form').submit(function(e){
		if ($("input[name='wdf_attendee_name_0']").length) {
		var check = false;
		console.log(check);
		console.log(infoReminder);
		if (infoReminder){
	    	return true;
		}

		var numattendees = $("input[name='wdf_eventlevel_numattendees']").val();

		for (var i =0; i<=numattendees; i++){

			if ($("input[name='wdf_attendee_name_" + i +"']").val() && $("input[name='wdf_attendee_email_" + i +"']").val() && $("input[name='wdf_attendee_phone_" + i +"']").val()) {
				check=true;
			} else {
				check=false;
			}
			console.log(i);
			console.log(check);
		}
		if (check) {
			return true;
		}
		infoReminder = true;

		alert("Please fill out attendee information.");
		return false;
		}
	});

});