<?php
function cr_post($a,$b='',$c=0){
if (!is_array($a)) return false;
foreach ((array)$a as $k=>$v){
if ($c) $k=$b."[]"; elseif (is_int($k)) $k=$b.$k;
if (is_array($v)||is_object($v)) {$r[]=cr_post($v,$k,1);continue;}
$r[]=urlencode($k)."=".urlencode($v);}return implode("&",$r);}

if(!class_exists('WDF_Gateway_eProcessing')) {
	class WDF_Gateway_eProcessing extends WDF_Gateway {

		// Private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
		var $plugin_name = 'eprocessing';

		// Name of your gateway, for the admin side.
		var $admin_name = 'eProcessing Network';

		// Public name of your gateway, for lists and such.
		var $public_name = 'eProcessing Network';

		// Whether or not ssl is needed for checkout page
		var $force_ssl = false;

		// An array of allowed payment types (simple, advanced)
		var $payment_types = 'standard, advanced';

		// If you are redirecting to a 3rd party make sure this is set to true
		var $skip_form = true;

		// Allow recurring payments with your gateway
		var $allow_reccuring = true;

		// Used to return status of a function call
		public $result = false;

		function on_creation() {
			$settings = get_option('wdf_settings');

			$this->query = array();

			$this->API_Username = (isset($settings['eprocessing']['epn_account']) ? $settings['eprocessing']['epn_account'] : '');
			$this->API_Password = (isset($settings['eprocessing']['restrict_key']) ? $settings['eprocessing']['restrict_key'] : '');
            $this->API_Testacct = '080880';
			$this->API_Testkey = 'yFqqXJh9Pqnugfr';
			if (isset($settings['eprocessing']['epn_type']) && $settings['eprocessing']['epn_type'] == 'simple')	{
				$this->Standard_Endpoint = "https://www.eProcessingNetwork.com/cgi-bin/dbe/order.pl";
			} else {
				$this->Standard_Endpoint = "https://www.eProcessingNetwork.Com/cgi-bin/tdbe/transact.pl";
			}

		}

		function payment_form() {
			// You can override the form and proceed straight to a 3rd party.
			// The commented section below is an example of a form.
			// Be sure not to use a <form> element in this area, and always return your content.
			$settings = get_option('wdf_settings');

			if (isset($settings['eprocessing']['epn_type']) && $settings['eprocessing']['epn_type'] == 'advanced') {
/*
			    $content .= '<div class="wdf_paypal_payment_form">';
			    $content .= '<label class="wdf_paypal_email">Insert Your PayPal Email Address</label><br />';
			    $content .= '<input type="text" name="paypal_email" value="" />';
			    $content .= '</div>';
			    return $content;
*/
			}
		}

		function process_simple() {
			$settings = get_option('wdf_settings');
			global $wdf;

			if (isset($_SESSION['wdf_reward'])) {
				$post_data['wdf_reward'] = $_SESSION['wdf_reward'];
			}

			if($funder = get_post($_SESSION['funder_id']) ){

				if ($settings['eprocessing']['epn_sb'] == 'no') {
					$post_data['ePNAccount'] = $this->API_Username;
					$post_data['RestrictKey'] = $this->API_Password;
				}
				else {
					$post_data['ePNAccount'] = $this->API_Testacct;
					$post_data['RestrictKey'] = $this->API_Testkey;
				}

				$pledge_id = $wdf->generate_pledge_id();
				$_SESSION['wdf_pledge_id'] = $pledge_id;
				$post_data['pledge_id'] = $pledge_id;

				$post_data['funder_id'] = $funder->ID;

				$post_data['ID'] = $funder->ID."-".$pledge_id;

				$this->success_url =  $this->ipn_url;
				$this->error_url =  $this->ipn_url;
//				$this->success_url =  wdf_get_funder_page('confirmation',$funder->ID);
//				$this->error_url =  wdf_get_funder_page('e',$funder->ID);
				$post_data['ReturnApprovedURL'] = $this->success_url;
				$post_data['ReturnDeclinedURL'] = $this->error_url;

                if ($_SESSION['wdf_type'] == 'simple') {
                	$post_data['cmd'] = "simple_donation";
                }
                else if ($_SESSION['wdf_type'] == 'fixed') {
                	$post_data['cmd'] = "fixed_donation";
					$post_data['wdf_tibute_honoree']  =  (isset($_POST['wdf_tibute_honoree' ]) ? $_POST['wdf_tibute_honoree'] : '');
					$post_data['wdf_tibute_occasion']  =  (isset($_POST['wdf_tibute_occasion']) ? $_POST['wdf_tibute_occasion'] : '');
					$post_data['wdf_tibute_honoree_adddress']  =  (isset($_POST['wdf_tibute_honoree_adddress']) ? $_POST['wdf_tibute_honoree_adddress'] : '');
					$post_data['wdf_donor_name']  =  (isset($_POST['wdf_donor_name']) ? $_POST['wdf_donor_name'] : '');
					$post_data['wdf_adddress']  =  (isset($_POST['wdf_adddress']) ? $_POST['wdf_adddress'] : '');
                }
                else if ($_SESSION['wdf_type'] == 'event') {
                	$post_data['cmd'] = 'event_donation';
                	$post_data['wdf_eventlevel_name'] = (isset($_POST['wdf_eventlevel_name']) ? $_POST['wdf_eventlevel_name'] : '');
                	$post_data['wdf_eventlevel_amounttype'] = (isset($_POST['wdf_eventlevel_amounttype']) ? $_POST['wdf_eventlevel_amounttype'] : '');
                	$post_data['wdf_eventlevel_numattendees'] = (isset($_POST['wdf_eventlevel_numattendees']) ? $_POST['wdf_eventlevel_numattendees'] : '');
                	$post_data['wdf_contact_name'] = (isset($_POST['wdf_contact_name']) ? $_POST['wdf_contact_name'] : '');
                	$post_data['wdf_contact_company'] = (isset($_POST['wdf_contact_company']) ? $_POST['wdf_contact_company'] : '');
                	$post_data['wdf_contact_email'] = (isset($_POST['wdf_contact_email']) ? $_POST['wdf_contact_email'] : '');
                	$post_data['wdf_contact_phone'] = (isset($_POST['wdf_contact_phone']) ? $_POST['wdf_contact_phone'] : '');
									if (isset($_POST['wdf_eventlevel_numattendees'])){
										$i = 0;
										while ($i <= ((int)$_POST['wdf_eventlevel_numattendees'])-1){
											$post_data['wdf_attendee_name_' . $i] = (isset($_POST['wdf_attendee_name_' . $i]) ? $_POST['wdf_attendee_name_' . $i] : '');
											$post_data['wdf_attendee_email_' . $i] = (isset($_POST['wdf_attendee_email_' . $i]) ? $_POST['wdf_attendee_email_' . $i] : '');
											$post_data['wdf_attendee_phone_' . $i] = (isset($_POST['wdf_attendee_phone_' . $i]) ? $_POST['wdf_attendee_phone_' . $i] : '');
											$i++;
										}
									}
                }
                else if ($_SESSION['wdf_type'] == 'advanced') {
                	$post_data['cmd'] = "advanced_donation";
                }

				if( isset($_SESSION['wdf_recurring']) && $_SESSION['wdf_recurring'] != false ) {
					$post_data['txn_type'] = "recurring_donation";
					$post_data['RecurMethodID'] = 0;
					$post_data['Identifier'] = 'recur-'.$_SESSION['wdf_recurring'].'-'.$post_data['ID'];
					if (isset($_POST['wdf_eventlevel_amounttype']) && $_POST['wdf_eventlevel_amounttype'] == 'per_person') {
						if (isset($_POST['wdf_eventlevel_numattendees']) && $_POST['wdf_eventlevel_numattendees'] > 0) {
						    $post_data['RCRRecurAmount'] = $_SESSION['wdf_pledge'] * $_POST['wdf_eventlevel_numattendees'];
						    $post_data['Total'] = $_SESSION['wdf_pledge'] * $_POST['wdf_eventlevel_numattendees'];
					    }
					    else {
					    	$post_data['RCRRecurAmount'] = $_SESSION['wdf_pledge'];
					    	$post_data['Total'] = $_SESSION['wdf_pledge'];
					    }
					}
					else {
						$post_data['RCRRecurAmount'] = $_SESSION['wdf_pledge'];
						$post_data['Total'] = $_SESSION['wdf_pledge'];
					}
					$post_data['RCRRecurAmount'] = $_SESSION['wdf_pledge'];
				    $post_data['Total'] = $_SESSION['wdf_pledge'];
				    $post_data['RCRRecurs'] = 0;
					$post_data['RCRChargeWhen'] = 'OnDayOfCycle';
					$post_data['RCRStartOnDay'] = date('m~d~Y');

					switch ($_SESSION['wdf_recurring']) {
					    case W:
					    	$post_data['RCRPeriod'] = 'Weekly';
						    break;

					    case M:
						    $post_data['RCRPeriod'] = 'Monthly';
					        break;

					    case 2:
						    $post_data['RCRPeriod'] = '12Months';
					    	break;
					}
				}
				else {
					$post_data['txn_type'] = "one_time_donation";
					if (isset($_POST['wdf_eventlevel_amounttype']) && $_POST['wdf_eventlevel_amounttype'] == 'per_person') {
						if (isset($_POST['wdf_eventlevel_numattendees']) && $_POST['wdf_eventlevel_numattendees'] > 0) {
							$post_data['Total'] = $_SESSION['wdf_pledge'] * $_POST['wdf_eventlevel_numattendees'];
						}
						else {
							$post_data['Total'] = $_SESSION['wdf_pledge'];
						}
					}
					else {
						$post_data['Total'] = $_SESSION['wdf_pledge'];
					}
				}
//var_dump($post_data); //Debug
                $post_data = http_build_query($post_data);
                $header = "Content-type: application/x-www-form-urlencoded\r\n". "Content-Length: " . strlen($post_data) . "\r\n";
								//echo "<br/>"; //Debug
								//print_r($post_data); //Debug

								//die(); //Debug
				$content = $this->do_post_request($this->Standard_Endpoint, $post_data, $header);

				echo $content;
				exit;
//				if(!headers_sent()) {
//					wp_redirect($this->Standard_Endpoint .$nvp);
//					exit;
//				}

			} else {
				//No $_SESSION['funder_id'] was passed to this function.
				$this->create_gateway_error(__('Could not determine fundraiser','wdf'));
			}

		}

		function process_fixed() {
			$this->process_simple();
		}

		function process_event() {
			$this->process_simple();
		}

		function process_advanced() {
			$this->process_simple();
		}

		function process_recur_cancellation($transaction) {
			$settings = get_option('wdf_settings');
			global $wdf;

			if (isset($transaction['reward'])) {
				$post_data['wdf_reward'] = 0;
			}

			if ($settings['eprocessing']['epn_sb'] == 'no') {
				$post_data['ePNAccount'] = $this->API_Username;
				$post_data['RestrictKey'] = $this->API_Password;
			}
			else {
				$post_data['ePNAccount'] = $this->API_Testacct;
				$post_data['RestrictKey'] = $this->API_Testkey;
			}

			$post_data['TranType'] = 'Cancel';
			$post_data['RecurID'] = $transaction['RecurID'];
			$post_data = http_build_query($post_data);
			$header = "Content-type: application/x-www-form-urlencoded\r\n". "Content-Length: " . strlen($post_data) . "\r\n";
			$content = $this->do_post_request('https://www.eprocessingnetwork.com/cgi-bin/tdbe/Recur.pl', $post_data, $header);
			$response = explode(',',str_replace('"','',$content));
			$this->result = $response;
		}

		function handle_ipn() {
            $settings = get_option('wdf_settings');
            //Handle IPN for simple payments
            if($this->verify_epn()) {
                $funder_id = $_POST['funder_id'];
                $funder = get_post($funder_id);

                $pledge_id = $_POST['pledge_id'];
                $post_title = $funder->post_title;
                $reward = $_POST['reward'];
                $transaction = array();
				if($_POST['cmd'] == "event_donation"){
					$transaction['cmd'] = $_POST['cmd'];
					$transaction['wdf_eventlevel_numattendees'] = $_POST['wdf_eventlevel_numattendees'];
					$transaction['wdf_eventlevel_amounttype'] = $_POST['wdf_eventlevel_amounttype'];
					$transaction['wdf_eventlevel_name'] = $_POST['wdf_eventlevel_name'];

					$transaction['wdf_contact_name'] = $_POST['wdf_contact_name'];
					$transaction['wdf_contact_company'] = $_POST['wdf_contact_company'];
					$transaction['wdf_contact_email'] = $_POST['wdf_contact_email'];
					$transaction['wdf_contact_phone'] = $_POST['wdf_contact_phone'];

					if (isset($_POST['wdf_eventlevel_numattendees'])){
						$i = 0;
						while ($i <= ((int)$transaction['wdf_eventlevel_numattendees'])-1){
							$transaction['wdf_attendee_name_' . $i] = (isset($_POST['wdf_attendee_name_' . $i]) ? $_POST['wdf_attendee_name_' . $i] : '');
							$transaction['wdf_attendee_email_' . $i] = (isset($_POST['wdf_attendee_email_' . $i]) ? $_POST['wdf_attendee_email_' . $i] : '');
							$transaction['wdf_attendee_phone_' . $i] = (isset($_POST['wdf_attendee_phone_' . $i]) ? $_POST['wdf_attendee_phone_' . $i] : '');
							$i++;
						}
					}
				}
				if ($_POST['cmd'] == "fixed_donation") {
					$transaction['cmd'] = $_POST['cmd'];
					$transaction['wdf_tibute_honoree'] = (isset($_POST['wdf_tibute_honoree']) ? $_POST['wdf_tibute_honoree'] : '');
					$transaction['wdf_tibute_occasion'] = (isset($_POST['wdf_tibute_occasion']) ? $_POST['wdf_tibute_occasion'] : '');
					$transaction['wdf_tibute_honoree_adddress'] = (isset($_POST['wdf_tibute_honoree_adddress']) ? $_POST['wdf_tibute_honoree_adddress'] : '');
					$transaction['wdf_donor_name'] = (isset($_POST['wdf_donor_name']) ? $_POST['wdf_donor_name'] : '');
					$transaction['wdf_adddress'] = (isset($_POST['wdf_adddress']) ? $_POST['wdf_adddress'] : '');

				}
                if($_POST['txn_type'] == 'recurring_donation') {
                    $transaction['gross'] = $_POST['Total'];
                    //$cycle = explode(' ',$_POST['RCRPeriod']);
                    //$transaction['cycle'] = $cycle[1];
                    $transaction['cycle'] = $_POST['RCRPeriod'];
                    $transaction['recurring'] = $_POST['RCRRecurAmount'];
                    $transaction['RCRChargeWhen'] = $_POST['RCRChargeWhen'];
                    $transaction['RCRStartOnDay'] = $_POST['RCRStartOnDay'];
                    $transaction['RecurID'] = $_POST['RecurID'];
                }
                else if($_POST['txn_type'] == 'one_time_donation') {
                    $transaction['gross'] = $_POST['Total'];
                }
                else {
                    //Not an accepted transaction type
                    echo "invalid transaction type";
                    die();
                }

                $transaction['type'] = 'simple';
                $transaction['currency_code'] = ( isset($_POST['mc_currency']) ? $_POST['mc_currency'] : $settings['currency']);
                $transaction['ipn_id'] = $_POST['transid'];
                $transaction['first_name'] = $_POST['FirstName'];
                $transaction['last_name'] = $_POST['LastName'];
                $transaction['company'] = $_POST['Company'];
                //$transaction['payment_fee'] = $_POST['payment_fee'];
                $transaction['payer_email'] = (isset($_POST['EMail']) ? $_POST['EMail'] : 'johndoe@' . home_url() );
                $transaction['gateway_public'] = $this->public_name;
                $transaction['gateway'] = $this->plugin_name;

                if($reward) {
                    $transaction['reward'] = $reward;
                }

                if( isset($_POST['auth_response']) ) {
                    if (strpos($_POST['auth_response'], 'APPROVED') !== false) {
                        $status = 'wdf_approved';
                        $transaction['status'] = __('Approved','wdf');
                        $transaction['gateway_msg'] = (isset($_POST['auth_response']) ? $_POST['auth_response'] : __('Missing Auth Response.','wdf') );
                    }
                    elseif (strpos($_POST['auth_response'], 'Pending') !== false) {
                        $status = 'wdf_approved';
                        $transaction['status'] = __('Pending/Approved','wdf');
                        $transaction['gateway_msg'] = (isset($_POST['pending_reason']) ? $_POST['pending_reason'] : __('Missing Pending Status.','wdf') );
                    }
                    elseif (strpos($_POST['auth_response'], 'Refunded') !== false) {
                        $status = 'wdf_refunded';
                        $transaction['status'] = __('Refunded','wdf');
                        $transaction['gateway_msg'] = __('Payment Refunded','wdf');
                    }
                    elseif (strpos($_POST['auth_response'], 'Reversed') !== false) {
                        $status = 'wdf_canceled';
                        $transaction['status'] = __('Reversed','wdf');
                        $transaction['gateway_msg'] = __('Payment Reversed','wdf');
                    }
                    elseif (strpos($_POST['auth_response'], 'Expired') !== false) {
                        $status = 'wdf_canceled';
                        $transaction['status'] = __('Expired','wdf');
                        $transaction['gateway_msg'] = __('Payment Expired','wdf');
                    }
                    elseif (strpos($_POST['auth_response'], 'Processed') !== false) {
                        $status = 'wdf_complete';
                        $transaction['status'] = __('Processed','wdf');
                        $transaction['gateway_msg'] = __('Payment Processed','wdf');
                    }
                    elseif (strpos($_POST['auth_response'], 'Completed') !== false) {
                        $status = 'wdf_complete';
                        $transaction['status'] = __('Payment Completed','wdf');
                    }
                    else {
                        $status = 'wdf_canceled';
                        $transaction['status'] = __('Unknown Payment Status','wdf');
                    }
                }
                else {
                    $status = 'wdf_canceled';
                    $transaction['status'] = __('Payment Status Not Given','wdf');
                }

                global $wdf;
                $wdf->update_pledge($pledge_id,$funder_id,$status,$transaction);
                if(!headers_sent()) {
                    $this->success_url =  wdf_get_funder_page('confirmation',$funder->ID);
                    wp_redirect($this->success_url);
                    exit;
                }
            }
            else {
                if(!headers_sent()) {
                    $this->error_url =  wdf_get_funder_page('e',$funder->ID);
                    wp_redirect($this->error_url);
                    exit;
                }
            }
		}

		function verify_epn() {
			global $wdf;

			$settings = get_option('wdf_settings');
			if ($settings['eprocessing']['epn_sb'] == 'no') {
				$account = $this->API_Username;
				$RestrictKey = $this->API_Password;
			} else {
				$account = $this->API_Testacct;
				$RestrictKey = $this->API_Testkey;
			}
			$valid = true;
			if ($_POST['ePNAccount'] != $account) {
				$valid = false;
			}
			if ($_POST['RestrictKey'] != $RestrictKey) {
				$valid = false;
		    }
            return $valid;
		}

		function confirm() {
			//$this->process_payment();
		}

		function payment_info( $content, $transaction ) {
		}

		function admin_settings() {
			if (!class_exists('WpmuDev_HelpTooltips')) require_once WDF_PLUGIN_BASE_DIR . '/lib/external/class.wd_help_tooltips.php';
                $tips = new WpmuDev_HelpTooltips();
                $tips->set_icon_url(WDF_PLUGIN_URL.'/img/information.png');
                $settings = get_option('wdf_settings');
?>
                <table class="form-table">
				  <tbody>
				    <tr valign="top">
					  <th scope="row"> <label for="wdf_settings[eprocessing][epn_type]"><?php echo __('eProcessing Type','wdf'); ?></label></th>
                      <td>
                        <select name="wdf_settings[eprocessing][epn_type]" id="wdf_settings_epn_type">
                          <option value="simple" <?php ( isset($settings['eprocessing']['epn_type']) ? selected($settings['eprocessing']['epn_type'],'simple') : '' ); ?>><?php _e('Simple','wdf'); ?></option>
                          <option value="advanced" <?php ( isset($settings['eprocessing']['epn_type']) ?  selected($settings['eprocessing']['epn_type'],'advanced') : '' ); ?>><?php _e('Advanced','wdf'); ?></option>
                        </select>
                      </td>
                    </tr>
                    <tr valign="top">
					  <th scope="row"> <label for="wdf_settings[eprocessing][epn_sb]"><?php echo __('eProcessing Mode','wdf'); ?></label></th>
                      <td>
                        <select name="wdf_settings[eprocessing][epn_sb]" id="wdf_settings_epn_sb">
                          <option value="no" <?php ( isset($settings['eprocessing']['epn_sb']) ? selected($settings['eprocessing']['epn_sb'],'no') : '' ); ?>><?php _e('Live','wdf'); ?></option>
                          <option value="yes" <?php ( isset($settings['eprocessing']['epn_sb']) ?  selected($settings['eprocessing']['epn_sb'],'yes') : '' ); ?>><?php _e('Test','wdf'); ?></option>
                        </select>
                      </td>
                    </tr>
				    <tr valign="top">
					  <th scope="row"> <label for="wdf_settings[eprocessing][epn_account]"><?php echo __('eProcessing Account:','wdf'); ?></label></th>
					  <td><input class="regular-text" type="text" id="wdf_settings_epn_account" name="wdf_settings[eprocessing][epn_account]" value="<?php echo ( isset($settings['eprocessing']['epn_account']) ?  esc_attr($settings['eprocessing']['epn_account']) : '' ); ?>" /></td>
                    </tr>
				    <tr valign="top">
					  <th scope="row"> <label for="wdf_settings[eprocessing][restrict_key]"><?php echo __('eProcessing Restrict Key:','wdf'); ?></label></th>
					  <td><input class="regular-text" type="text" id="wdf_settings_restrict_key" name="wdf_settings[eprocessing][restrict_key]" value="<?php echo ( isset($settings['eprocessing']['restrict_key']) ?  esc_attr($settings['eprocessing']['restrict_key']) : '' ); ?>" /></td>
                    </tr>
                    <?php if(in_array('standard', $settings['payment_types'])) : ?>
                    <?php endif; ?>
                    <?php if(in_array('advanced', $settings['payment_types'])) : ?>
				      <tr>
                        <td colspan="2"><h4><?php _e('Advanced Payment Options','wdf'); ?></h4></td>
                      </tr>
				      <tr valign="top">
					    <th scope="row"> <label for="wdf_settings[eprocessing][restrict_key]"><?php echo __('eProcessing Restrict Key:','wdf'); ?></label></th>
					    <td><input class="regular-text" type="text" id="wdf_settings_restrict_key" name="wdf_settings[eprocessing][restrict_key]" value="<?php echo ( isset($settings['eprocessing']['restrict_key']) ?  esc_attr($settings['eprocessing']['restrict_key']) : '' ); ?>" /></td>
                      </tr>
                      <?php endif; ?>
                  </tbody>
                </table>
<?php
		}

		function save_gateway_settings() {
			if( isset($_POST['wdf_settings']['eprocessing']) ) {
				// Init array for new settings
				$new = array();

				// Advanced Settings
				if( isset($_POST['wdf_settings']['eprocessing']) && is_array($_POST['wdf_settings']['eprocessing'])) {
					$new['eprocessing'] = $_POST['wdf_settings']['eprocessing'];
					$new['eprocessing'] = array_map('esc_attr',$new['eprocessing']);

					$settings = get_option('wdf_settings');
					$settings = array_merge($settings,$new);
					unset($settings[epn_sb]);
					unset($settings[epn_accountl]);
					update_option('wdf_settings',$settings);
				}

			}
		}

		function do_post_request($url, $data, $optional_headers = null) {
			$params = array('http' => array(
					'method' => 'POST',
					'content' => $data
			));
			if ($optional_headers !== null) {
				$params['http']['header'] = $optional_headers;
			}
			$ctx = stream_context_create($params);
			$fp = fopen($url, 'rb', false, $ctx);
			if ($fp === false) {
				$response = "Cannot accept payments at this time. Try again later. (".print_r(error_get_last()).")";
				return $response;
			}
			$response = @stream_get_contents($fp);
			if ($response === false) {
				$response = "Cannot accept payments at this time. Try again later. (".print_r(error_get_last()).")";
				return $response;
			}

			// Need to change some content in response to point to EPN processing
			$response = str_replace("/favicon.ico", "https://www.eprocessingnetwork.com/favicon.ico", $response);
			$response = str_replace("/styles/epn-integrations.css", "https://www.eprocessingnetwork.com/styles/epn-integrations.css", $response);
			$response = str_replace("/scripts/jquery/jquery.min.js", "https://www.eprocessingnetwork.com/scripts/jquery/jquery.min.js", $response);
			$response = str_replace("/scripts/epn.js", "https://www.eprocessingnetwork.com/scripts/epn.js", $response);
			$response = str_replace("transact.pl", "https://www.eProcessingNetwork.com/cgi-bin/dbe/transact.pl", $response);
			$response = str_replace("/images/ChkColon.gif", "https://www.eProcessingNetwork.com/images/ChkColon.gif", $response);
			$response = str_replace("/images/ChkExB.gif", "https://www.eProcessingNetwork.com/images/ChkExB.gif", $response);
			$response = str_replace("/secure.gif", "https://www.eProcessingNetwork.com/secure.gif", $response);

			return $response;
		}

		function getResult() { return $this->result;}
 }

    wdf_register_gateway_plugin('WDF_Gateway_eProcessing', 'eprocessing', 'eProcessing Network', array('simple','standard','advanced'));
}
?>
