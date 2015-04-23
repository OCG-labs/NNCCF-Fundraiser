<?php

global $pagenow;

if($pagenow == 'nav-menus.php') { ?>

	<p><a href="#" id="wdf_add_nav_archive" class="button secondary-button"><?php _e('Add Archive Page To Menu','wdf'); ?></a></p>
	<?php
	$funder_obj['args']->name = 'funder';
	wp_nav_menu_item_post_type_meta_box('', $funder_obj);
} else {

	//Setup tooltips for all metaboxes
	if (!class_exists('WpmuDev_HelpTooltips')) require_once WDF_PLUGIN_BASE_DIR . '/lib/external/class.wd_help_tooltips.php';
		$tips = new WpmuDev_HelpTooltips();
		$tips->set_icon_url(WDF_PLUGIN_URL.'/img/information.png');

	// Setup $meta for all metaboxes
	$meta = get_post_custom($post->ID);
	$settings = get_option('wdf_settings');
	//pull out the meta_box id and pass it through a switch instead of using individual functions
	switch($data['id']) {

		///////////////////////////
		// PLEDGE STATUS METABOX //
		///////////////////////////
		case 'wdf_pledge_status' : ?>

			<?php $trans = $this->get_transaction($post->ID); ?>
			<label><?php _e('Gateway Status','wdf'); ?>: <?php echo $trans['status']; ?></label>
			<p>
				<label><?php _e('Pledge Status','wdf'); ?></label><br />
				<select class="widefat" name="post_status">
					<option value="wdf_complete" <?php selected($post->post_status,'wdf_complete'); ?>><?php _e('Complete','wdf'); ?></option>
					<option value="wdf_approved" <?php selected($post->post_status,'wdf_approved'); ?>><?php _e('Approved','wdf'); ?></option>
					<option value="wdf_refunded" <?php selected($post->post_status,'wdf_refunded'); ?>><?php _e('Refunded','wdf'); ?></option>
					<option value="wdf_canceled" <?php selected($post->post_status,'wdf_canceled'); ?>><?php _e('Canceled','wdf'); ?></option>
				</select>
			</p>
			<p><input type="submit" class="button-primary" value="Save Pledge" /></p>
			<?php break;
		///////////////////////////
		// PLEDGE INFO METABOX //
		///////////////////////////
		case 'wdf_pledge_info' :

			$trans = $this->get_transaction($post->ID);


			if($meta['wdf_native'][0] !== '1') : ?>
				<?php $funders = get_posts(array('post_type' => 'funder', 'numberposts' => -1, 'post_status' => 'publish')); ?>
				<?php if(!$funders) : ?>
					<div class="error below-h2"><p style="width: 100%;"><?php echo __('You have not made any fundraisers yet.  You must create a fundraiser to make a pledge to.','wdf') ?></p></div>
				<?php else : ?>
					<input type="hidden" name="post_title" value="Manual Payment" />
					<input type="hidden" name="wdf[transaction][status]" value="Manual Payment" />
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<label><?php echo __('Choose The Fundraiser','wdf') ?></label>
								</th>
								<td>
									<p>
										<select name="post_parent">
										<?php foreach($funders as $funder) : ?>
											<option <?php selected($post->post_parent,$funder->ID); ?> value="<?php echo $funder->ID ?>"><?php echo $funder->post_title; ?></option>
										<?php endforeach; ?>
										</select>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php _e('First & Last Name','wdf'); ?></label>
								</th>
								<td>
									<p><input type="text" name="wdf[transaction][name]" value="<?php echo $trans['first_name'] . ' ' . $trans['last_name']; ?>" /></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php _e('Email Address','wdf'); ?></label>
								</th>
								<td>
									<p><input type="text" name="wdf[transaction][payer_email]" value="<?php echo $trans['payer_email']; ?>" /></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php _e('Donation Amount','wdf'); ?></label>
								</th>
								<td>
									<p><input type="text" name="wdf[transaction][gross]" value="<?php echo $trans['gross']; ?>" /></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php _e('Payment Source','wdf'); ?>:</label>
								</th>
								<td>
									<select name="wdf[transaction][gateway]">
										<?php global $wdf_gateway_plugins; ?>
										<?php foreach($wdf_gateway_plugins as $name => $plugin) : ?>
											<option value="<?php echo $name; ?>"><?php echo $plugin[1]; ?></option>
										<?php endforeach; ?>
										<option value="manual"><?php _e('Check/Cash','wdf'); ?></option>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
				<?php endif; ?>
			<?php else : ?>
				<?php $parent = get_post($post->post_parent); ?>
				<?php if($parent) : ?>
					<h3><?php _e('Fundraiser','wdf'); ?>:</h3><p><a href="<?php echo get_edit_post_link($parent->ID); ?>"><?php echo $parent->post_title; ?></a></p>
				<?php else : ?>
					<?php $donations = get_posts(array('post_type' => 'funder', 'numberposts' => -1, 'post_status' => 'publish')); ?>
					<p>
						<?php if(!$donations) : ?>
							<label><?php echo sprintf( __('You have not made any %s yet.','wdf'), esc_attr($settings['funder_labels']['plural_name']) ); ?></label>
						<?php else : ?>
							<label><?php echo sprintf( __('Not attached to any %s please choose one','wdf'), esc_attr($settings['funder_labels']['singular_name']) ); ?></label>
							<select name="post_parent">
							<?php foreach($donations as $donation) : ?>
								<option value="<?php echo $donation->ID ?>"><?php echo $donation->post_title; ?></option>
							<?php endforeach; ?>
							</select>
						<?php endif; ?>
					</p>
				<?php endif; ?>
					<?php $trans = $this->get_transaction(); ?>
					<h3><?php _e('From','wdf'); ?>:</h3><p><label><strong><?php echo __('Name:','wdf'); ?> </strong></label><?php echo $trans['first_name'] . ' ' . $trans['last_name']; ?></p><p><label><strong><?php echo __('Email:','wdf'); ?> </strong></label><?php echo $trans['payer_email']; ?></p>
					<h3><?php _e('Amount Donated','wdf'); ?>:</h3>
					<?php $reward = (isset($trans['reward'])) ? ' ('.__('Reward: ','wdf').$trans['reward'].')' : ''; ?>
					<?php if(isset($trans['recurring']) && $trans['recurring'] == 1) :?>
						<p><?php echo $this->format_currency($trans['currency_code'],$trans['gross']); ?> every <?php echo $trans['cycle']; ?><?php echo $reward; ?></p>
					<?php else: ?>
						<p><?php echo $this->format_currency($trans['currency_code'],$trans['gross']); ?><?php echo $reward; ?></p>
					<?php endif; ?>
					<?php if( isset($trans['gateway_public']) ) : ?><h3><?php _e('Payment Source','wdf'); ?>:</h3><p><?php echo esc_attr($trans['gateway_public']); ?></p><?php endif; ?>
					<?php if( isset($trans['gateway_msg']) ) : ?><h3><?php _e('Last Gateway Activity','wdf'); ?>:</h3><p><?php echo esc_attr($trans['gateway_msg']); ?></p><?php endif; ?>
					<?php if( isset($trans['ipn_id']) ) : ?><h3><?php _e('Transaction ID','wdf'); ?>:</h3><p><?php echo esc_attr($trans['ipn_id']); ?></p><?php endif; ?>

			<?php if (isset($trans['cmd'])) { ?>
			<?php if($trans['cmd'] == "event_donation") { ?>
				<?php if ((isset($trans['wdf_contact_name']) || (isset($trans['wdf_contact_company'])) || (isset($trans['wdf_contact_email'])) || (isset($trans['wdf_contact_phone'])))) { ?>
				<h3>Primary Contact</h3>
				<?php } ?>
				<?php if (isset($trans['wdf_contact_name'])) {  echo "<p><label><strong>Contact Name: </strong></label>" . $trans['wdf_contact_name'] ."</p>";}?>
				<?php if (isset($trans['wdf_contact_company'])) {  echo "<p><label><strong>Company Name: </strong></label>" . $trans['wdf_contact_company'] ."</p>";}?>
				<?php if (isset($trans['wdf_contact_email'])) {  echo "<p><label><strong>Contact Email: </strong></label>" . $trans['wdf_contact_email'] ."</p>";}?>
				<?php if (isset($trans['wdf_contact_phone'])) {  echo "<p><label><strong>Contact Phone: </strong></label>" . $trans['wdf_contact_phone'] ."</p>";}?>
				<style type="text/css">
					.tftable {font-size:12px;color:#333333;width:100%;border-width: 1px;border-color: #a9a9a9;border-collapse: collapse;}
					.tftable th {font-size:12px;background-color:#b8b8b8;border-width: 1px;padding: 8px;border-style: solid;border-color: #a9a9a9;text-align:left;}
					.tftable tr {background-color:#ffffff;}
					.tftable td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #a9a9a9;}
					.tftable tr:hover {background-color:#ffff99;}
				</style>
				<h3>Attendees</h3>
				<table class="tftable" border="1">
					<thead>
						<tr>
						  <th>Attendee #</th>
						  <th>Name</th>
						  <th>Email</th>
						  <th>Phone</th>
						</tr>
					  </thead>
					  <tbody>
								<?php
								$i = 0;
								while ($i <= ((int) $trans['wdf_eventlevel_numattendees']-1)) {
								?>
						<tr>
						  <td><?php echo $i; ?></td>
						  <td><?php echo $trans['wdf_attendee_name_' . $i]; ?></td>
						  <td><?php echo $trans['wdf_attendee_email_' . $i]; ?></td>
						  <td><?php echo $trans['wdf_attendee_phone_' . $i]; ?></td>
						</tr>
								<?php $i++; } ?>
					  </tbody>
				</table>
				<?php } else if ($trans['cmd'] == "fixed_donation") {?>
					<h3>Donation Information</h3>
					<?php if (isset($trans['wdf_tibute_honoree'])) {  echo "<p><label><strong>Honoree: </strong></label>" . $trans['wdf_tibute_honoree'] ."</p>";}?>
					<?php if (isset($trans['wdf_tibute_occasion'])) {  echo "<p><label><strong>Occasion: </strong></label>" . $trans['wdf_tibute_occasion'] ."</p>";}?>
					<?php if (isset($trans['wdf_tibute_honoree_adddress'])) {  echo "<p><label><strong>Honoree Adddress: </strong></label>" . $trans['wdf_tibute_honoree_adddress'] ."</p>";}?>
					<?php if (isset($trans['wdf_adddress'])) {  echo "<p><label><strong>Contact Adddress: </strong></label>" . $trans['wdf_adddress'] ."</p>";}?>
					<?php if (isset($trans['wdf_donor_name'])) {  echo "<p><label><strong>Donor Name: </strong></label>" . $trans['wdf_donor_name'] ."</p>";}?>
				<?php } ?>
			<?php } ?>
			<?php endif; ?>
		<?php break;

		/////////////////////
		// FUNDER PROGRESS //
		/////////////////////
		case 'wdf_progress' : ?>

			<?php if($this->has_goal($post->ID)) : ?>
				<?php if(strtotime($meta['wdf_goal_start'][0]) > time()) : ?>
					<div class="below-h2 updated"><p><?php echo sprintf(__('Your %s %s','wdf'),esc_attr($settings['funder_labels']['singular_name']), wdf_time_left(false,$post->ID)); ?></p></div>
				<?php endif; ?>
				<?php echo $this->prepare_progress_bar($post->ID,null,null,'admin_metabox',true); ?>
			<?php else : ?>
				<?php /* <label><?php _e('Amount Raised So Far','wdf'); ?></label><br /><span class="wdf_bignum"><?php echo $this->format_currency('',$this->get_amount_raised($post->ID)); ?></span><?php */ ?>
			<?php endif; ?>

			<?php break;

		/////////////////////////
		// FUNDER TYPE METABOX //
		/////////////////////////
		case 'wdf_type' :
			$settings = get_option('wdf_settings');	?>

			<div id="wdf_type">
				<?php if( isset($settings['payment_types']) && is_array($settings['payment_types']) && count($settings['payment_types']) >= 1 ) : ?>
					<?php foreach($settings['payment_types'] as $name) : ?>
						<?php
							if($name == 'simple') {
								$label = __('Simple Donations: ','wdf');
								$description = __('Allows for a simple continuous donations with no Goals or Rewards','wdf');
							} elseif($name == 'fixed') {
								$label = __('Fixed price donation: ','wdf');
								$description = __('Set one price for donation. Set donation rewards.','wdf');
							} elseif($name == 'event') {
								$label = __('Event fundraiser: ','wdf');
								$description = __('','wdf');
							} elseif($name == 'advanced') {
								$label = __('Advanced Crowdfunding: ','wdf');
								$description = __('Set fundraising goals and rewards.  Pledges are only authorized and payments are not processed until your goal is reached.','wdf');
							} else {
								$label = '';
								$description = '';
							}
							// Some filters incase your trying to make new available types
							$label = apply_filters('wdf_funder_type_label', $label, $name);
							$description = apply_filters('wdf_funder_type_description', $description, $name);
						?>
						<?php // if(!isset($meta['wdf_type'][0]) || empty($meta['wdf_type'][0])) : ?>

							<?php //if(isset($settings['payment_types']) && count($settings['payment_types']) >= 1 ) : ?>
								<?php //if(count($settings['payment_types']) > 1) : ?>
									<h3>
										<label>
											<span class="description"><?php echo $label; ?></span>
											<input name="wdf[type]" type="radio" value="<?php echo $name; ?>" <?php (isset($meta['wdf_type'][0]) ? checked($meta['wdf_type'][0],$name) : ''); ?>/>
										</label>
										<?php echo $tips->add_tip($description); ?>
									</h3>
								<?php /*?><?php else : ?>
									<h3>
										<label><span class="description"><?php echo $label; ?></span></label>
										<div style="float:right;"><input name="wdf[type]" type="hidden" value="<?php echo $name; ?>" /><?php echo $tips->add_tip($description); ?></div>
									</h3>
								<?php endif; ?>	<?php */?>
							<?php //endif; ?>

						<?php /*?><?php else : // Type Has Been Set ?>
							<?php if($meta['wdf_type'][0] == $name) : //Current Funder Type Matches The Foreach ?>
								<h3>
									<label><span class="description"><?php echo $label; ?></span></label>
									<div style="float:right;"><input name="wdf[type]" type="hidden" value="<?php echo $meta['wdf_type'][0]; ?>" /><?php echo $tips->add_tip($description); ?></div>
								</h3>
							<?php endif; ?><?php */?>

						<?php //endif; ?>
					<?php endforeach; ?>
					<p><input type="submit" name="save" id="save-post" value="<?php _e('Save Fundraising Type','wdf'); ?>" class="button button-primary" /><br /></p>

				<?php else : // No Valid Payment Types Available?>
					<div class="message updated below-h2"><p><?php _e('No payment types have been enabled yet.','wdf'); ?></p></div>
				<?php endif; ?>
			</div><!-- #wdf_type -->
			<?php break;

		////////////////////////////
		// FUNDER OPTIONS METABOX //
		////////////////////////////
		case 'wdf_options' :
			global $pagenow;
			$settings = get_option('wdf_settings'); ?>
			<h4>
			<?php
			  _e('Type : ','wdf');
			  if (isset($meta['wdf_type'][0])) {
                if($meta['wdf_type'][0] == 'simple') {
                  echo __('Simple Donations','wdf');
                } elseif($meta['wdf_type'][0] == 'fixed') {
                  echo __('Fixed Donations','wdf');
                } elseif($meta['wdf_type'][0] == 'event') {
                  echo __('Event Fundraiser','wdf');
                } elseif($name == 'advanced') {
                  echo _('Advanced Crowdfunding','wdf');
                }
              }
            ?>
            </h4>
			<?php if($settings['single_styles'] == 'yes') : ?>
				<div id="wdf_style">
					<p>
						<label><?php echo __('Choose a display style','wdf'); ?>
						<select name="wdf[style]">
							<?php if(is_array($this->styles) && !empty($this->styles)) : ?>
								<?php foreach($this->styles as $key => $label) : ?>
									<option <?php (isset($meta['wdf_style'][0]) ? selected($meta['wdf_style'][0],$key) : ''); ?> value="<?php echo $key ?>"><?php echo $label; ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select></label>
					</p>
				</div>
			<?php endif; ?>

			<?php if($meta['wdf_type'][0] == 'fixed') : ?>
                <p>
                  <label><span class="description"><?php _e('Enter fixed donation amount $','wdf') ?></span>
                    <input class="wdf_input_switch active" name="wdf[fixed_amount]" value="<?php echo (isset($meta['wdf_fixed_amount'][0]) ? $meta['wdf_fixed_amount'][0] : ''); ?>" />
                  </label>
                </p>
			<?php elseif($meta['wdf_type'][0] == 'event') : ?>
 			<?php endif; ?>

			<?php if($meta['wdf_type'][0] != 'advanced') : ?>
                <p>
                  <label><span class="description"><?php _e('Allow Recurring Donations?','wdf') ?></span>
                    <select name="wdf[recurring]" rel="wdf_recurring" class="wdf_toggle">
                      <option value="no" <?php (isset($meta['wdf_recurring'][0]) ? selected($meta['wdf_recurring'][0],'no') : ''); ?>><?php _e('No','wdf'); ?></option>
                    <option value="yes" <?php (isset($meta['wdf_recurring'][0]) ? selected($meta['wdf_recurring'][0],'yes') : ''); ?>><?php _e('Yes','wdf'); ?></option>
                      <option value="only" <?php (isset($meta['wdf_recurring'][0]) ? selected($meta['wdf_recurring'][0],'only') : ''); ?>><?php _e('Only Recurring','wdf'); ?></option>
                    </select>
                  </label>
				</p>
				<div id="wdf_fixed_recurrance" rel="wdf_fixed_recurrance" <?php echo (isset($meta['wdf_recurring'][0]) && $meta['wdf_recurring'][0] != 'no' ? '' : 'style="display:none"') ?>>
                  <p>
                    <label><span class="description"><?php _e('Donation Recurrance','wdf') ?></span>
                      <select name="wdf[fixed_recurrance]" rel="wdf_fixed_recurrance" class="wdf_toggle">
                        <option value="A" <?php (isset($meta['wdf_fixed_recurrance'][0]) ? selected($meta['wdf_fixed_recurrance'][0],'D') : ''); ?>><?php _e('Any','wdf'); ?></option>
                        <option value="D" <?php (isset($meta['wdf_fixed_recurrance'][0]) ? selected($meta['wdf_fixed_recurrance'][0],'D') : ''); ?>><?php _e('Daily','wdf'); ?></option>
                        <option value="W" <?php (isset($meta['wdf_fixed_recurrance'][0]) ? selected($meta['wdf_fixed_recurrance'][0],'W') : ''); ?>><?php _e('Weekly','wdf'); ?></option>
                        <option value="M" <?php (isset($meta['wdf_fixed_recurrance'][0]) ? selected($meta['wdf_fixed_recurrance'][0],'M') : ''); ?>><?php _e('Monthly','wdf'); ?></option>
						<option value="Q" <?php (isset($meta['wdf_fixed_recurrance'][0]) ? selected($meta['wdf_fixed_recurrance'][0],'Q') : ''); ?>><?php _e('Quarterly','wdf'); ?></option>
                        <option value="Y" <?php (isset($meta['wdf_fixed_recurrance'][0]) ? selected($meta['wdf_fixed_recurrance'][0],'Y') : ''); ?>><?php _e('Yearly','wdf'); ?></option>
                       </select>
                    </label>
				  </p>
				</div>
			<?php endif; ?>
                <p>
                  <label><span class="description"><?php _e('Panel Position','wdf') ?></span>
                    <select name="wdf[panel_pos]">
                      <option value="top" <?php (isset($meta['wdf_panel_pos'][0]) ? selected($meta['wdf_panel_pos'][0],'top') : ''); ?>><?php _e('Above Content','wdf'); ?></option>
                      <option value="bottom" <?php (isset($meta['wdf_panel_pos'][0]) ? selected($meta['wdf_panel_pos'][0],'bottom') : ''); ?>><?php _e('Below Content','wdf'); ?></option>
                    </select>
                  </label><?php echo $tips->add_tip(__('If you are not using the Fundraiser sidebar widget, choose the position of your info panel.','wdf')); ?>
                </p>
            <?php if($settings['single_checkout_type'] == '1') : ?>
                <p>
                  <label><span class="description"><?php _e('Checkout Type','wdf') ?></span>
                    <select name="wdf[checkout_type]">
                      <option value="1" <?php (isset($meta['wdf_checkout_type'][0]) ? selected($meta['wdf_checkout_type'][0],'1') : ''); ?>><?php _e('Checkout directly from panel','wdf'); ?></option>
                      <option value="2" <?php (isset($meta['wdf_checkout_type'][0]) ? selected($meta['wdf_checkout_type'][0],'2') : ''); ?>><?php _e('Use elaborated checkout page','wdf'); ?></option>
                    </select>
                  </label>
                </p>
            <?php endif; ?>

			<?php if(isset($meta['wdf_type'][0]) && $meta['wdf_type'][0] == 'advanced') : ?>
				<script type="text/javascript">
					jQuery(document).ready( function($) {

						$('input#publish').on( 'click', null, 'some data', function(e) {
							var has_goal = $('select#wdf_has_goal option:selected').val();
							var start_date = $('input#wdf_goal_start_date').val();
							var end_date = $('input#wdf_goal_end_date').val();
							var goal_amount = $('input#wdf_goal_amount').val();

							if(has_goal == '1') {
								if(start_date == '' || typeof start_date == 'undefined') {
									alert("<?php _e('You must set a starting date','wdf'); ?>");
									e.preventDefault();
									e.stopImmediatePropagation();
									return false;
								} else if(end_date == '' || typeof start_date == 'undefined') {
									alert("<?php _e('You must set a ending date that is after the current date','wdf'); ?>");
									e.preventDefault();
									e.stopImmediatePropagation()
									return false;
								}  else if( goal_amount == '' || typeof goal_amount == 'undefined' || parseInt(goal_amount) < 1  ) {
									alert("<?php _e('You must set a goal amount greater than at least 1','wdf'); ?>");
									e.preventDefault();
									e.stopImmediatePropagation()
									return false;
								}
							}

							//var check = confirm("<?php _e('Are you sure you are ready to publish?  You will be unable to change your fundraising type, goals and rewards after publishing.','wdf'); ?>");
							//if (check == true)  {
								//return true;
							//} else {
								//e.preventDefault();
								//e.stopImmediatePropagation();
								//return false;
							//}
						});
					});
				</script>
			<?php endif; ?>
		<?php break;

		//////////////////////////
		// FUNDER GOALS METABOX //
		//////////////////////////
		case 'wdf_goals' :

			if($meta['wdf_type'][0] == 'advanced' && $post->post_status == 'publish' && $this->get_pledge_list($post->ID) != false) {
				$disabled = 'disabled="disabled"';
			}
			else {
				$disabled = '';
			}
			$settings = get_option('wdf_settings');
            if($disabled != '') : ?>
              <div class="below-h2 updated"><p><?php _e('Your fundraising dates, goals and rewards are locked in.','wdf'); ?></p></div>
              <?php endif; ?>
              <div id="wdf_funder_goals">
                <?php //if( in_array('advanced', $settings['payment_types']) || in_array('standard', $settings['payment_types']) ) : ?>
                <p><label><?php echo __('Create a funding goal?','wdf'); ?>
                  <select class="wdf_toggle" id="wdf_has_goal" rel="wdf_has_goal" name="wdf[has_goal]" <?php echo $disabled; ?>>
                    <option <?php (isset($meta['wdf_has_goal'][0]) ? selected($meta['wdf_has_goal'][0],'0') : ''); ?> value="0"><?php _e('No','wdf'); ?></option>
                    <option <?php (isset($meta['wdf_has_goal'][0]) ? selected($meta['wdf_has_goal'][0],'1') : '');  ?> value="1"><?php _e('Yes','wdf'); ?></option>
                  </select></label>
                </p>
              </div>
              <div rel="wdf_has_goal" <?php echo (isset($meta['wdf_has_goal'][0]) && $meta['wdf_has_goal'][0] == '1' ? '' : 'style="display:none"') ?>>
                <?php /*?><input type="hidden" name="wdf[show_progress]" value="0" />
                <p><label><input type="checkbox" name="wdf[show_progress]" value="1" <?php checked($meta['wdf_show_progress'][0],'1'); ?> /> <?php echo __('Show Progress Bar','wdf') ?></label></p><?php */?>

                <table class="widefat">
                  <thead>
                    <tr>
                      <th class="wdf_goal_start_date"><?php _e('Start Date','wdf') ?></th>
                      <th class="wdf_goal_end_date"><?php _e('End Date','wdf') ?></th>
                      <th class="wdf_goal_amount" align="right"><?php _e('Goal Amount','wdf') ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="wdf_goal_start_date">
                        <input <?php echo $disabled; ?> id="wdf_goal_start_date" style="background-image: url(<?php echo admin_url('images/date-button.gif'); ?>);" type="text" name="wdf[goal_start]" class="wdf_biginput" value="<?php echo (isset($meta['wdf_goal_start'][0]) ? $meta['wdf_goal_start'][0] : ''); ?>" />
                      </td>
                      <td class="wdf_goal_end_date">
                        <input <?php echo $disabled; ?> id="wdf_goal_end_date" style="background-image: url(<?php echo admin_url('images/date-button.gif'); ?>);" type="text" name="wdf[goal_end]" class="wdf_biginput" value="<?php echo (isset($meta['wdf_goal_end'][0]) ? $meta['wdf_goal_end'][0] : ''); ?>" />
                      </td>
                      <td class="wdf_goal_amount">
                        <?php echo ( (isset($settings['curr_symbol_position'])) && $settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                        <input <?php echo $disabled; ?> id="wdf_goal_amount" type="text" name="wdf[goal_amount]" class="wdf_input_switch active wdf_biginput wdf_bignum" value="<?php echo (isset($meta['wdf_goal_amount'][0]) ? $this->filter_price($meta['wdf_goal_amount'][0]) : '') ?>" />
                        <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                      </td>
                    </tr>
                  </tbody>
					</table>
				</div>
				<p>
				  <label><?php echo sprintf(__('Create %s','wdf'), esc_attr($settings['funder_labels']['plural_level'])); ?>
                    <select <?php echo $disabled; ?> class="wdf_toggle" rel="wdf_has_reward" name="wdf[has_reward]">
                      <option <?php (isset($meta['wdf_has_reward'][0]) ? selected($meta['wdf_has_reward'][0],'0') : ''); ?> value="0"><?php _e('No','wdf'); ?></option>
                      <option <?php (isset($meta['wdf_has_reward'][0]) ? selected($meta['wdf_has_reward'][0],'1') : ''); ?> value="1"><?php _e('Yes','wdf'); ?></option>
                    </select>
                  </label>
                </p>
                <div id="wdf_has_reward" rel="wdf_has_reward" <?php echo (isset($meta['wdf_has_reward'][0]) && $meta['wdf_has_reward'][0] == '1' ? '' : 'style="display:none"') ?>>
                  <h2><?php apply_filters('wdf_admin_meta_reward_title', esc_attr($settings['funder_labels']['singular_name']) . esc_attr($settings['funder_labels']['plural_level']) ); ?></h2>
                  <table id="wdf_levels_table" class="widefat">
                    <thead>
                      <tr>
                        <th class="wdf_level_amount"><?php echo __('Choose Amount','wdf'); ?></th>
                        <th class="wdf_level_description"><?php echo sprintf(__('%s Description','wdf'), esc_attr($settings['funder_labels']['singular_level'])); ?></th>
                        <th class="delete" align="right"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if(isset($meta['wdf_levels']) && is_array($meta['wdf_levels'])) :
                        $level_count = count($meta['wdf_levels']);
                        $i = 1;

                        foreach($meta['wdf_levels'] as $level) :
                          $level = maybe_unserialize($level);
                          foreach($level as $index => $data) : ?>
                            <tr class="wdf_level <?php echo ($level_count == $i ? 'last' : ''); ?>">
                              <td class="wdf_level_amount">
                                <?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                                <input <?php echo $disabled; ?> class="wdf_input_switch active wdf_biginput wdf_bignum" type="text" name="wdf[levels][<?php echo $index ?>][amount]" value="<?php echo (isset($data['amount']) ? $this->filter_price($data['amount']) : '' ); ?>" />
                                <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                              </td>
                              <td class="wdf_level_description"><textarea <?php echo $disabled; ?> class="wdf_input_switch active " name="wdf[levels][<?php echo $index ?>][description]"><?php echo (isset($data['description']) ? $data['description'] : '') ?></textarea></td>
                              <td class="delete">
                                <?php if($disabled == false) : ?>
                                  <a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Delete','wdf'); ?></a>
                                <?php endif; ?>
                              </td>
                            </tr>
                            <tr class="wdf_reward_options">
                              <td colspan="5">
                                <div class="wdf_reward_toggle" <?php echo ( isset($data['reward']) && $data['reward'] == 1 ? '' : 'style="display:none"'); ?>>
                                  <p><label><?php echo sprintf(__('Describe Your %s','wdf'), esc_attr($settings['funder_labels']['singular_level'])); ?><input <?php echo $disabled; ?> type="text" name="wdf[levels][<?php echo $index ?>][reward_description]" value="<?php echo $data['reward_description'] ?>" class="widefat" /></label></p>
                                </div>
                              </td>
                            </tr>
                          <?php $i++; endforeach; endforeach; ?>
                      <?php else : ?>
									<tr class="wdf_level last">
										<td class="wdf_level_amount">
											<?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
											<input class="wdf_input_switch wdf_biginput wdf_bignum" type="text" name="wdf[levels][0][amount]" value="" />
											<?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
										</td>
										<?php /*?><td class="wdf_level_title"><input class="wdf_input_switch wdf_biginput wdf_bignum" type="text" name="wdf[levels][0][title]" value="" /></td><?php */?>
										<td class="wdf_level_description"><textarea class="wdf_input_switch" name="wdf[levels][0][description]"><?php //echo __('Add a description for this level','wdf'); ?></textarea></td>
										<?php /*?><td class="wdf_level_reward"><input class="wdf_check_switch" type="checkbox" name="wdf[levels][0][reward]" value="1" /></td><?php */?>
										<td class="delete">
											<?php if($disabled == false) : ?>
												<a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Delete','wdf'); ?></a>
											<?php endif; ?>
										</td>
									</tr>
									<tr class="wdf_reward_options">
										<td colspan="5">
											<div class="wdf_reward_toggle" style="display:none">
												<p><label><?php echo sprintf(__('Describe Your %s','wdf'),esc_attr($settings['funder_labels']['singular_level'])); ?><input type="text" name="wdf[levels][0][reward_description]" value="<?php echo isset($data['reward_description']) ? $data['reward_description'] : ''; ?>" class="widefat" /></label></p>
											</div>
										</td>
									</tr>
								<?php endif; ?>
									<tr rel="wdf_level_template" style="display:none">
										<td class="wdf_level_amount">
											<?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
											<input class="wdf_input_switch active wdf_biginput wdf_bignum" type="text" rel="wdf[levels][][amount]" value="" />
											<?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
										</td>
										<?php /*?><td class="wdf_level_title"><input class="wdf_input_switch active wdf_biginput wdf_bignum" type="text" rel="wdf[levels][][title]" value="" /></td><?php */?>
										<td class="wdf_level_description"><textarea class="wdf_input_switch active" rel="wdf[levels][][description]"></textarea></td>
										<?php /*?><td class="wdf_level_reward"><input class="wdf_check_switch" type="checkbox" rel="wdf[levels][][reward]" value="1" /></td><?php */?>
										<td class="delete"><a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Delete','wdf'); ?></a></td>
									</tr>
									<tr rel="wdf_level_template" class="wdf_reward_options" style="display:none">
										<td colspan="5">
											<div class="wdf_reward_toggle" style="display:none">
												<p><label><?php echo sprintf(__('Describe Your %s','wdf'),esc_attr($settings['funder_labels']['singular_level'])); ?><input type="text" rel="wdf[levels][][reward_description]" value="" class="widefat" /></label></p>
											</div>
										</td>
									</tr>
									<?php if($disabled == false) : ?>
										<tr><td colspan="3" align="right"><a href="#" id="wdf_add_level"><?php echo sprintf(__('Add A %s','wdf'), esc_attr($settings['funder_labels']['singular_level'])); ?></a></td></tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div><!-- #wdf_has_reward -->

		<?php break;

		////////////////////
		// LEVELS METABOX //
		////////////////////
		case 'wdf_levels' : ?>
			<?php $settings = get_option('wdf_settings'); ?>

		<?php break;

		//////////////////////
		// ACTIVITY METABOX //
		//////////////////////
		case 'wdf_activity' : ?>
			<?php $donations = $this->get_pledge_list($post->ID); ?>
			<table class="widefat">
					<thead>
						<tr>
							<th><?php _e('Amount','wdf'); ?>:</th>
							<th><?php _e('Status','wdf'); ?>:</th>
							<th><?php echo esc_attr($settings['donation_labels']['backer_single']) ?>:</th>
							<th><?php _e('Method','wdf'); ?>:</th>
							<th><?php _e('Date','wdf'); ?>:</th>
							<th class="wdf_actvity_edit"><br /></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($donations as $donation) : ?>
						<?php $trans = $this->get_transaction($donation->ID); ?>
						<tr class="wdf_actvity_level">
							<td><?php echo $this->format_currency('',$trans['gross']); ?></td>
							<td><?php echo $trans['status']; ?></td>
							<td><label><?php echo $trans['first_name'].' '.$trans['last_name']; ?></label><br /><a href="mailto:<?php echo $trans['payer_email']; ?>"><?php echo $trans['payer_email']; ?></a></</td>
							<td><?php echo $trans['gateway']; ?></td>
							<td><?php echo get_post_modified_time('F d Y', null, $donation->ID) ?></td>
							<td><a class="hidden" href="<?php echo get_edit_post_link($donation->ID); ?>">View Details</a></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
		<?php break;

		//////////////////////
		// MESSAGES METABOX //
		//////////////////////
		case 'wdf_messages' :
			$settings = get_option('wdf_settings');
		?>
			<?php /*?><label id="wdf_thanks_type"><?php echo __('Thank You Message','wdf'); ?>
			<select class="wdf_toggle" rel="wdf_thanks_type" name="wdf[thanks_type]">
				<option <?php selected($meta['wdf_thanks_type'][0],'custom'); ?> value="custom"><?php echo __('Custom Thank You Message','wdf'); ?></option>
				<option <?php selected($meta['wdf_thanks_type'][0],'post'); ?> value="post"><?php echo __('Use A Post or Page ID','wdf'); ?></option>
				<option <?php selected($meta['wdf_thanks_type'][0],'url'); ?> value="url"><?php echo __('Use A Custom URL','wdf'); ?></option>
			</select></label><?php */?>
			<p<?php //echo ($meta['wdf_thanks_type'][0] == 'custom' || $pagenow == 'post-new.php' ? 'style="display: block;"' : ''); ?> rel="wdf_thanks_type" class="wdf_thanks_custom">
				<label><?php echo __('Text or HTML Allowed','wdf'); ?><?php echo $tips->add_tip('Provide a custom thank you message for users.  You can use the following codes to display specific information from the payment: %DONATIONTOTAL% %FIRSTNAME% %LASTNAME%'); ?></label><br />
				<textarea id="wdf_thanks_custom" name="wdf[thanks_custom]"><?php echo (isset($meta['wdf_thanks_custom'][0]) ? urldecode(wp_kses_post($meta['wdf_thanks_custom'][0])) : ''); ?></textarea>
			</p>
			<?php /*?><p <?php echo ($meta['wdf_thanks_type'][0] == 'post' ? 'style="display: block;"' : 'style="display: none;"'); ?> rel="wdf_thanks_type" class="wdf_thanks_post">
				<?php do_action('wdf_error_thanks_post');?>
				<label><?php echo __('Insert A Post or Page ID','wdf'); ?><input type="text" name="wdf[thanks_post]" value="<?php echo $meta['wdf_thanks_post'][0]; ?>" /></label>
			</p>
			<p <?php echo ($meta['wdf_thanks_type'][0] == 'url' ? 'style="display: block;"' : 'style="display: none;"'); ?> rel="wdf_thanks_type" class="wdf_thanks_url">
				<label><?php echo __('Insert A Custom URL','wdf'); ?><input type="text" name="wdf[thanks_url]" value="<?php echo $meta['wdf_thanks_url'][0]; ?>" /></label>
			</p><?php */?>

			<h3>Email Settings</h3>

			<p>
				<label><?php echo __('Send a confirmation email after a payment?','wdf'); ?>
					<select class="wdf_toggle" rel="wdf_send_email" name="wdf[send_email]" id="wdf_send_email">
						<option value="0" <?php (isset($meta['wdf_send_email'][0]) ? selected($meta['wdf_send_email'][0],'0') : ''); ?>><?php _e('No','wdf'); ?></option>
						<option value="1" <?php (isset($meta['wdf_send_email'][0]) ? selected($meta['wdf_send_email'][0],'1') : ''); ?>><?php _e('Yes','wdf'); ?></option>
					</select>
				</label>
			</p>

		<div <?php echo (isset($meta['wdf_send_email'][0]) && $meta['wdf_send_email'][0] == '1' ? '' : 'style="display: none;"');?> rel="wdf_send_email">
			<label><?php echo __('Create a custom email message or use the default one.','wdf'); ?></label><?php $tips->add_tip('The email will come from your Administrator email <strong>'.get_bloginfo('admin_email').'</strong>')?><br />
			<p><label><?php echo __('Email Subject','wdf'); ?></label><br />
			<input class="regular-text" type="text" name="wdf[email_subject]" value="<?php echo (isset($meta['wdf_email_subject'][0]) ? $meta['wdf_email_subject'][0] : __('Thank you for your Donation', 'wdf')); ?>" /></p>
			<p><textarea id="wdf_email_msg" name="wdf[email_msg]"><?php echo (isset($meta['wdf_email_msg'][0]) ? $meta['wdf_email_msg'][0] : esc_textarea($settings['default_email'])); ?></textarea></p>
		</div>
		<?php break;

		////////////////////////////
		// Specific Fields METABOX //
		///////////////////////////
		case 'wdf_specific' :

          if($post->post_status == 'publish' && $this->get_pledge_list($post->ID) != false) {
              $disabled = 'disabled="disabled"';
          }
          else {
              $disabled = '';
          }

          if ($meta['wdf_type'][0] == 'event') { ?>

            <p rel="wdf_specific" class="wdf_specific">
              <label>
                <?php
                echo __("Information to collect for primary contact: ","wdf");
                echo $tips->add_tip(__('Select what types of information to collect for the primary contact','wdf'));
                $contact_info = maybe_unserialize($meta['wdf_contact_info'][0]);
                ?>
                <br />
              </label>
              <table style="margin-left: auto; margin-right: auto; width:50%;">
                <tr>
                  <td><input name="wdf[contact_info][contact_name]" type="checkbox" value="true" <?php (isset($contact_info['contact_name']) ? checked($contact_info['contact_name'],'true') : ''); ?>/><label> Primary Contact Name</label></td>
                  <td><input name="wdf[contact_info][contact_company]" type="checkbox" value="true" <?php (isset($contact_info['contact_company']) ? checked($contact_info['contact_company'],'true') : ''); ?>/><label> Company Name</label></td>
                </tr>
                <tr>
                  <td><input name="wdf[contact_info][contact_email]" type="checkbox" value="true" <?php (isset($contact_info['contact_email']) ? checked($contact_info['contact_email'],'true') : ''); ?>/><label> Primary Contact Email</label></td>
                  <td><input name="wdf[contact_info][contact_phone]" type="checkbox" value="true" <?php (isset($contact_info['contact_phone']) ? checked($contact_info['contact_phone'],'true') : ''); ?>/><label> Primary Contact Phone</label></td>
                </tr>
              </table>
            </p>

            <p rel="wdf_specific" class="wdf_specific">
              <label>
                <?php
                echo __("Information to collect for Attendee's: ","wdf");
                echo $tips->add_tip(__('Select what types of information to collect for each attendee','wdf'));
                $attendee_info = maybe_unserialize($meta['wdf_attendee_info'][0]);
                ?>
                <br />
              </label>
              <table style="margin-left: auto; margin-right: auto; width:50%;">
                <tr>
                  <td><input name="wdf[attendee_info][attendee_name]" type="checkbox" value="true" <?php (isset($attendee_info['attendee_name']) ? checked($attendee_info['attendee_name'],'true') : ''); ?>/><label> Attendee Name</label></td>
                  <td><input name="wdf[attendee_info][attendee_email]" type="checkbox" value="true" <?php (isset($attendee_info['attendee_email']) ? checked($attendee_info['attendee_email'],'true') : ''); ?>/><label> Attendee Contact Email</label></td>
                </tr>
                <tr>
                  <td><input name="wdf[attendee_info][attendee_phone]" type="checkbox" value="true" <?php (isset($attendee_info['attendee_phone']) ? checked($attendee_info['attendee_phone'],'true') : ''); ?>/><label> Attendee Contact Phone</label></td>
                </tr>
              </table>
            </p>

            <p>
              <label><?php echo __('Create Registration Levels','wdf'); ?>
                <select <?php echo $disabled; ?> class="wdf_toggle" rel="wdf_has_eventlevels" name="wdf[has_eventlevels]">
                  <option <?php (isset($meta['wdf_has_eventlevels'][0]) ? selected($meta['wdf_has_eventlevels'][0],'0') : ''); ?> value="0"><?php _e('No','wdf'); ?></option>
                  <option <?php (isset($meta['wdf_has_eventlevels'][0]) ? selected($meta['wdf_has_eventlevels'][0],'1') : ''); ?> value="1"><?php _e('Yes','wdf'); ?></option>
                </select>
              </label>
            </p>
            <div id="wdf_has_eventlevels" rel="wdf_has_eventlevels" <?php echo (isset($meta['wdf_has_eventlevels'][0]) && $meta['wdf_has_eventlevels'][0] == '1' ? '' : 'style="display:none"') ?>>
              <h2><?php apply_filters('wdf_admin_meta_reward_title', esc_attr($settings['funder_labels']['singular_name']) . esc_attr($settings['funder_labels']['plural_level']) ); ?></h2>
              <table id="wdf_eventlevels_table" class="widefat">
                <thead>
                  <tr>
                    <th class="wdf_eventlevel_title"><?php echo __('Registration Level','wdf'); ?></th>
                    <th class="wdf_eventlevel_amount"><?php echo __('Registration Fee','wdf'); ?></th>
                    <th class="wdf_eventlevel_amounttype"><?php echo __('Fee Type','wdf'); ?></th>
                    <th class="wdf_eventlevel_numberofattendees"><?php echo __('Num Attendees','wdf'); ?></th>
                    <th class="wdf_eventlevel_description"><?php echo __('Registration Description','wdf'); ?></th>
                    <th class="delete" align="right"></th>
										<th class="wdf_eventlevel_description">Disable Level</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if(isset($meta['wdf_eventlevels']) && is_array($meta['wdf_eventlevels'])) :
                    $level_count = count($meta['wdf_eventlevels']);

                    $i = 1;
                    foreach($meta['wdf_eventlevels'] as $level) :
                        $level = maybe_unserialize($level);
                        foreach($level as $index => $data) : ?>
                            <tr class="wdf_eventlevel <?php echo ($level_count == $i ? 'last' : ''); ?>">
                              <td class="wdf_eventlevel_title">
                                <input <?php //echo $disabled; ?> class="wdf_input_switch active" type="text" name="wdf[eventlevels][<?php echo ($level_count == $i ? $index : ($i-1)); ?>][title]" value="<?php echo (isset($data['title']) ? $data['title'] : '') ?>" />
                              </td>
                              <td class="wdf_eventlevel_amount">
                                <?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                                <input <?php //echo $disabled; ?> class="wdf_input_switch active" type="text" size="8" name="wdf[eventlevels][<?php echo ($level_count == $i ? $index : ($i-1)); ?>][amount]" value="<?php echo (isset($data['amount']) ? $this->filter_price($data['amount']) : '' ); ?>" />
                                <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                              </td>
                              <td class="wdf_eventlevel_amounttype">
                                <select class="widefat" name="wdf[eventlevels][<?php echo ($level_count == $i ? $index : ($i-1)); ?>][amounttype]">
                                  <option value="per_event" <?php selected($data['amounttype'],'per_event'); ?>><?php _e('Per Event','wdf'); ?></option>
                                  <option value="per_person" <?php selected($data['amounttype'],'per_person'); ?>><?php _e('Per Person','wdf'); ?></option>
                                </select>
                              </td>
                              <td class="wdf_eventlevel_numberofattendees">
                                <input <?php //echo $disabled; ?> class="wdf_input_switch active" type="text" size="6" name="wdf[eventlevels][<?php echo ($level_count == $i ? $index : ($i-1)); ?>][numberofattendees]" value="<?php echo (isset($data['numberofattendees']) ? $data['numberofattendees'] : '') ?>" />
                              </td>
                              <td class="wdf_eventlevel_description">
                                <textarea <?php //echo $disabled; ?> class="wdf_input_switch active" name="wdf[eventlevels][<?php echo ($level_count == $i ? $index : ($i-1)); ?>][description]"><?php echo (isset($data['description']) ? $data['description'] : '') ?></textarea>
                              </td>
                              <td class="delete">
                                <?php if($disabled == false) : ?>
                                  <a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Delete','wdf'); ?></a>
                                <?php endif; ?>
                              </td>
															<td>
																<input type="checkbox" name="wdf[eventlevels][<?php echo ($level_count == $i ? $index : ($i-1)); ?>][disable]"  <?php echo (isset($data['disable']) ? 'checked="checked"' : '') ?>/>
															</td>
                            </tr>
                          <?php
                          $i++;
                        endforeach;
                    endforeach;
                  else : ?>
                    <tr class="wdf_eventlevel last">
                      <td class="wdf_eventlevel_title">
                        <input class="wdf_input_switch active" type="text" name="wdf[eventlevels][0][title]" value="" />
                      </td>
                      <td class="wdf_eventlevel_amount">
                        <?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                        <input class="wdf_input_switch active" type="text" size="8" name="wdf[eventlevels][0][amount]" value="" />
                        <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                      </td>
                      <td class="wdf_eventlevel_amounttype">
                        <select class="widefat" name="wdf[eventlevels][0][amounttype]">
                          <option value="per_event"><?php _e('Per Event','wdf'); ?></option>
                          <option value="per_person"><?php _e('Per Person','wdf'); ?></option>
                        </select>
                      </td>
                      <td class="wdf_eventlevel_numberofattendees">
                        <input class="wdf_input_switch active" type="text" size="6" name="wdf[eventlevels][0][numberofattendees]" value="" />
                      </td>
                      <td class="wdf_eventlevel_description">
                        <textarea class="wdf_input_switch active " name="wdf[eventlevels][0][description]"></textarea>
                      </td>
                      <td class="delete">
                        <?php if($disabled == false) : ?>
                          <a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Delete','wdf'); ?></a>
                        <?php endif; ?>
                      </td>
											<td class="wdf_eventlevel_disable">
												<input type="checkbox" name="wdf[eventlevels][<?php echo ($level_count == $i ? $index : ($i-1)); ?>][disable]"  <?php echo (isset($data['disable']) ? 'checked="checked"' : '') ?>/>
											</td>
                    </tr>
                  <?php endif; ?>
                  <tr rel="wdf_eventlevel_template" style="display:none">
                    <td class="wdf_eventlevel_title">
                      <input class="wdf_input_switch active" type="text" rel="wdf[eventlevels][][title]" value="" />
                    </td>
                    <td class="wdf_eventlevel_amount">
                      <?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                      <input class="wdf_input_switch active" type="text" size="8" rel="wdf[eventlevels][][amount]" value="" />
                      <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                    </td>
                    <td class="wdf_eventlevel_amounttype">
                      <select class="widefat" rel="wdf[eventlevels][][amounttype]">
                        <option value="per_event"><?php _e('Per Event','wdf'); ?></option>
                        <option value="per_person"><?php _e('Per Person','wdf'); ?></option>
                      </select>
                    </td>
                    <td class="wdf_eventlevel_numberofattendees">
                      <input class="wdf_input_switch active" size="6" type="text" rel="wdf[eventlevels][][numberofattendees]" value="" />
                    </td>
                    <td class="wdf_eventlevel_description">
                      <textarea class="wdf_input_switch active " rel="wdf[eventlevels][][description]"></textarea>
                    </td>
                    <td class="delete">
                      <?php if($disabled == false) : ?>
                        <a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Delete','wdf'); ?></a>
                      <?php endif; ?>
                    </td>
                  </tr>

                    <tr>
                      <td colspan="3" align="right"><a href="#" id="wdf_add_eventlevel"><?php echo __('Add an Event Level','wdf'); ?></a></td>
                    </tr>
                </tbody>
              </table>
            </div>
          <?php }

          elseif ($meta['wdf_type'][0] == 'simple' || $meta['wdf_type'][0] == 'fixed') { ?>
            <p rel="wdf_specific" class="wdf_specific">
              <?php $additional_info = maybe_unserialize($meta['wdf_additional_info'][0]); ?>
              <label>
                <?php
                echo __("Additional Information for Donation: ","wdf");
                echo $tips->add_tip(__('Select what types of information to collect for this donation','wdf'));
                ?>
                <br />
              </label>
              <table style="margin-left: auto; margin-right: auto; width:50%;">
                <tr>
                  <td><input name="wdf[additional_info][tribute_donation_info]" type="checkbox" value="true" <?php (isset($additional_info['tribute_donation_info']) ? checked($additional_info['tribute_donation_info'],'true') : ''); ?>/><label> Tribute donation info</label></td>
                </tr>
								<tr>
<td><input name="wdf[additional_info][donor_info]" type="checkbox" value="true" <?php (isset($additional_info['donor_info']) ? checked($additional_info['donor_info'],'true') : ''); ?>/><label> Donor shipping info</label></td>
								</tr>
                </table>
            </p>
          <?php }

          break;
   }
}
