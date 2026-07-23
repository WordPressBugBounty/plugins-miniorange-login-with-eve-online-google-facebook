<?php
/**
 * Attribute Mapping
 *
 * @package    attribute-mapping
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Display Attribute Mapping
 */
function mooauth_client_attribite_role_mapping_ui() {
	$appslist       = get_option( 'mo_oauth_apps_list' );
	$attr_name_list = get_option( 'mo_oauth_attr_name_list' );

	if ( false !== $attr_name_list ) {
		$temp           = array();
		$attr_name_list = mooauth_client_dropdownattrmapping( '', $attr_name_list, $temp );
	}
	$currentapp     = null;
	$currentappname = null;
	if ( is_array( $appslist ) ) {
		foreach ( $appslist as $key => $value ) {
			$currentapp     = $value;
			$currentappname = $key;
			break;
		}
	}
	?>
<div class="mo_oauth_card">
	<div class="mo_oauth_card_header"><h3><?php esc_html_e( 'Attribute/Role Mapping', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3></div>
	<div class="mo_oauth_card_body">
	<div class="mo_table_layout mo_oauth_attribute_page_font mo_oauth_outer_div" id="attribute-mapping">
		<form id="form-common" name="form-common" method="post" action="admin.php?page=mo_oauth_settings&tab=attributemapping">
			<?php wp_nonce_field( 'mo_oauth_attr_role_mapping_form', 'mo_oauth_attr_role_mapping_form_field' ); ?>
		<div class="mo_oauth_attribute_map_header"><div class="mo_oauth_attribute_map_heading"><?php esc_html_e( 'Attribute Mapping ', 'miniorange-login-with-eve-online-google-facebook' ); ?></div><div class="mo_oauth_x_attrrolemapping_1"><small>[<?php esc_html_e( 'Required for SSO & Account Linking', 'miniorange-login-with-eve-online-google-facebook' ); ?>]</small></div></div>
</br><div class="mo_oauth_x_attrrolemapping_2"><div>
		<p class="mo_oauth_x_attrrolemapping_3"><?php wp_nonce_field( 'mo_oauth_attr_role_mapping_form', 'mo_oauth_attr_role_mapping_form_field' ); ?><?php esc_html_e( 'Do ', 'miniorange-login-with-eve-online-google-facebook' ); ?><b class="mo_oauth_x_attrrolemapping_4"><?php esc_html_e( 'Test Configuration', 'miniorange-login-with-eve-online-google-facebook' ); ?></b><?php esc_html_e( ' to get configuration for attribute mapping.', 'miniorange-login-with-eve-online-google-facebook' ); ?><br></p>
		</div>
		<div><span class="mo_oauth_x_attrrolemapping_5"><div class="mo_oauth_tooltip mo_oauth_tooltip_float_right"><span class="mo_tooltiptext mo_oauth_x_attrrolemapping_6" >How to map Attributes?</span><a
			href="https://developers.miniorange.com/docs/oauth/wordpress/client/attribute-mapping" target="_blank"
			rel="noopener"><img class="mo_oauth_guide_img mo_oauth_x_attrrolemapping_7" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) ); ?>/images/mo_oauth_info-icon.png" alt="miniOrange Premium Plans Logo" aria-hidden="true"></a></div>
		</span></div></div>
		<input type="hidden" name="option" value="mo_oauth_attribute_mapping" />
		<input class="mo_table_textbox" required="" type="hidden" id="mo_oauth_app_name" name="mo_oauth_app_name" value="<?php echo esc_attr( $currentappname ); ?>">
		<input class="mo_table_textbox" required="" type="hidden" name="mo_oauth_custom_app_name" value="<?php echo esc_attr( $currentappname ); ?>">
		<table class="mo_settings_table mo_oauth_attribute_map_table mo_oauth_x_attrrolemapping_8">
			<tr id="mo_oauth_email_attr_div">
				<td>
					<div><strong class="mo_strong"><?php esc_html_e( 'Username', 'miniorange-login-with-eve-online-google-facebook' ); ?> <span class="mo_oauth_x_demo_3">*</span>: </strong></div>
					<br/>
					<div class="mo_oauth_username_notice"><span class="mo_oauth_username_notice_span"><?php esc_html_e( '[ Map unique attribute with username ]', 'miniorange-login-with-eve-online-google-facebook' ); ?></span></div>
				</td>
				<td>
					<?php
					if ( is_array( $attr_name_list ) ) {
						?>
						<select class="mo_table_textbox<?php echo ( get_option( 'mo_attr_option' ) === 'manual' ) ? ' mo_oauth_x_attrrolemapping_9' : ''; ?>"
						id="mo_oauth_username_attr_select"
						<?php
						if ( get_option( 'mo_attr_option' ) === false || get_option( 'mo_attr_option' ) === 'automatic' ) {
							echo 'name="mo_oauth_username_attr"';}
						?>
						>
						<option value="">----------- Select an Attribute -----------</option>
							<?php
							foreach ( $attr_name_list as $key => $value ) {
								echo "<option value='" . esc_attr( $value ) . "'";
								if ( ( isset( $currentapp['username_attr'] ) && $currentapp['username_attr'] === $value ) ) {
									echo ' selected';
								} else {
									echo '';
								}
								echo ' >' . esc_attr( $value ) . '</option>';
							}
							?>
						</select>
						<input type="hidden" id="mo_attr_option" name="mo_attr_option" value="
						<?php
						if ( get_option( 'mo_attr_option' ) ) {
							echo esc_attr( get_option( 'mo_attr_option' ) );
						} else {
							echo 'automatic'; }
						?>
						">
						<input
						<?php
						if ( get_option( 'mo_attr_option' ) === 'manual' ) {
							echo 'name="mo_oauth_username_attr"';}
						?>
						class="mo_table_textbox<?php echo ( get_option( 'mo_attr_option' ) === 'automatic' || get_option( 'mo_attr_option' ) === false ) ? ' mo_oauth_x_attrrolemapping_9' : ''; ?>"
						placeholder="Enter attribute name for Username" type="text" id="mo_oauth_username_attr_input" value=" <?php echo isset( $currentapp['username_attr'] ) ? esc_attr( $currentapp['username_attr'] ) : ( isset( $currentapp['email_attr'] ) ? esc_attr( $currentapp['email_attr'] ) : '' ); ?> ">
						</td>
						<?php $textattr = get_option( 'mo_attr_option' ) ? get_option( 'mo_attr_option' ) === 'manual' ? 'Change to automatic mode' : 'Change to manual mode' : 'Change to manual mode'; ?>
						<td>
							<a href="#" id="mo_username_attr_change_p" onclick="mooauth_change_form_field('username')"><?php echo esc_html( $textattr ); ?></a>
						</td>
						<?php
					} else {
						?>
						<input class="mo_table_textbox" required="" placeholder="Enter attribute name for Username" type="text" id="mo_oauth_username_attr_input" 
						<?php
						if ( ! is_array( $attr_name_list ) ) {
							echo 'disabled';}
						?>
						name="mo_oauth_username_attr" value="
						<?php
						if ( isset( $currentapp['username_attr'] ) ) {
							echo esc_attr( $currentapp['username_attr'] );
						} elseif ( isset( $currentapp['email_attr'] ) ) {
							echo esc_attr( $currentapp['email_attr'] );}
						?>
						">
						</td>
						<td>
						</td>
						<?php
					}
					?>
			</tr>
			</td>
			<tr id="mo_oauth_email_attr_div">
				<td><div><strong class="mo_strong"><?php esc_html_e( 'Email', 'miniorange-login-with-eve-online-google-facebook' ); ?> <span class="mo_oauth_x_demo_3">*</span>: </strong></div></td>
				<td>
					<?php
					if ( is_array( $attr_name_list ) ) {
						?>
						<select class="mo_table_textbox<?php echo ( get_option( 'mo_attr_option' ) === 'manual' ) ? ' mo_oauth_x_attrrolemapping_9' : ''; ?>"
						id="mo_oauth_email_attr_select"
						<?php
						if ( get_option( 'mo_attr_option' ) === false || get_option( 'mo_attr_option' ) === 'automatic' ) {
							echo 'name="mo_oauth_email_attr"'; }
						?>
						>
						<option value="">----------- Select an Attribute -----------</option>
							<?php
							foreach ( $attr_name_list as $key => $value ) {
								echo "<option value='" . esc_attr( $value ) . "'";
								if ( ( isset( $currentapp['email_attr'] ) && $currentapp['email_attr'] === $value ) ) {
									echo ' selected';
								} else {
									echo '';
								}
								echo ' >' . esc_attr( $value ) . '</option>';
							}
							?>
						</select>
						<input type="hidden" id="mo_attr_option" name="mo_attr_option" value="
						<?php
						if ( get_option( 'mo_attr_option' ) ) {
							echo esc_attr( get_option( 'mo_attr_option' ) );
						} else {
							echo 'automatic'; }
						?>
						">
						<input 
						<?php
						if ( get_option( 'mo_attr_option' ) === 'manual' ) {
							echo 'name="mo_oauth_email_attr"'; }
						?>
						class="mo_table_textbox<?php echo ( get_option( 'mo_attr_option' ) === 'automatic' || get_option( 'mo_attr_option' ) === false ) ? ' mo_oauth_x_attrrolemapping_9' : ''; ?>"
						placeholder="Enter attribute name for Email" type="text" id="mo_oauth_email_attr_input" value=" <?php echo isset( $currentapp['email_attr'] ) ? esc_attr( $currentapp['email_attr'] ) : ''; ?> ">
						</td>
						<?php $textattr = get_option( 'mo_attr_option' ) ? get_option( 'mo_attr_option' ) === 'manual' ? 'Change to automatic mode' : 'Change to manual mode' : 'Change to manual mode'; ?>
						<td>
							<a href="#" id="mo_email_attr_change_p" onclick="mooauth_change_form_field('email')"><?php echo esc_html( $textattr ); ?></a>
						</td>
						<?php
					} else {
						?>
						<input class="mo_table_textbox" required="" placeholder="Enter attribute name for Email" type="text" id="mo_oauth_email_attr_input" 
						<?php
						if ( ! is_array( $attr_name_list ) ) {
							echo 'disabled'; }
						?>
						name="mo_oauth_email_attr" value="
						<?php
						if ( isset( $currentapp['email_attr'] ) ) {
							echo esc_attr( $currentapp['email_attr'] ); }
						?>
						">
						</td><td></td>
						<?php
					}
					?>
			</tr>

		<?php
		echo '<tr>
			<td></td><td>
            <b><p class="mop_table mo_oauth_x_attrrolemapping_10">' . esc_html__( 'Advanced attribute mapping is available in', 'miniorange-login-with-eve-online-google-facebook' ) . '
			<a href="' . esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ) . '" target="_blank">premium</a> version.</b>
            </p>
			</td>
		</tr>
        <tr id="mo_oauth_name_attr_div">
				<td><strong class="mo_strong">' . esc_html__( 'First Name:', 'miniorange-login-with-eve-online-google-facebook' ) . '</strong></td>
				<td><input class="mo_table_textbox mo_oauth_input_disabled" required="" placeholder="' . esc_html__( 'Enter attribute name for First Name', 'miniorange-login-with-eve-online-google-facebook' ) . '" disabled  type="text" value=""></td>
			</tr>
		<tr>
			<td><strong class="mo_strong">' . esc_html__( 'Last Name:', 'miniorange-login-with-eve-online-google-facebook' ) . '</strong></td>
			<td>
				<input type="text" class="mo_table_textbox mo_oauth_input_disabled" placeholder="' . esc_html__( 'Enter attribute name for Last Name', 'miniorange-login-with-eve-online-google-facebook' ) . '"  disabled /></td>
		</tr>
		<tr>
			<td><strong class="mo_strong">' . esc_html__( 'Group/Role:', 'miniorange-login-with-eve-online-google-facebook' ) . '</strong></td>
			<td><input type="text" class="mo_table_textbox mo_oauth_input_disabled" placeholder="' . esc_html__( 'Enter attribute name for Group/Role', 'miniorange-login-with-eve-online-google-facebook' ) . '" value="" disabled /></td>
		</tr>
		<tr>
			<td><strong class="mo_strong">' . esc_html__( 'Display Name:', 'miniorange-login-with-eve-online-google-facebook' ) . '</strong></td>
			<td><input type="text" class="mo_table_textbox mo_oauth_input_disabled" placeholder="' . esc_html__( 'Username', 'miniorange-login-with-eve-online-google-facebook' ) . '" value="" disabled /></td>
		</tr>
		<tr>
			<td><strong class="mo_strong">' . esc_html__( ' Enable Role Mapping:', 'miniorange-login-with-eve-online-google-facebook' ) . '</strong></td>
			<td><input type="checkbox" class="mo_input_checkbox mo_oauth_input_disabled" checked disabled></td>
		</tr>
		<tr>
			<td><strong class="mo_strong">' . esc_html__( ' Allow Duplicate Emails:', 'miniorange-login-with-eve-online-google-facebook' ) . '</strong></td>
			<td><input type="checkbox" class="mo_input_checkbox mo_oauth_input_disabled" disabled></td>
		</tr>
			<tr><td colspan="3"><hr class="mo-divider"></td></tr>
			<tr></tr>
			<tr><td  colspan="2">
			<h3 class="mo_oauth_attribute_page_font">' . esc_html__( 'Map Custom Attributes ', 'miniorange-login-with-eve-online-google-facebook' ) . '<div class="mo_oauth_tooltip"  ><span class="mo_oauth_tooltiptext mo_oauth_extra_tooltip"  >PREMIUM</span><a class="mo_oauth_x_attrrolemapping_11" href="' . esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ) . '" target="_blank" rel="noopener noreferrer">
			<span><img class="mo_oauth_premium-label" src="' . esc_url( dirname( plugin_dir_url( __FILE__ ) ) ) . '/images/mo_oauth_premium-label.png" alt="miniOrange Premium Plans Logo"></span></a></div></span></h3></td>
			<td><span class="mo_oauth_x_attrrolemapping_5"><div class="mo_oauth_tooltip mo_oauth_tooltip_float_right"><span class="mo_tooltiptext"  >How to map Custom Attributes?</span><a
                href="https://developers.miniorange.com/docs/oauth/wordpress/client/attribute-mapping#custom-attr-map" target="_blank"
                rel="noopener"><img class="mo_oauth_guide_img" src="' . esc_url( dirname( plugin_dir_url( __FILE__ ) ) ) . '/images/mo_oauth_info-icon.png" alt="miniOrange Premium Plans Logo" aria-hidden="true"></a></div>
            </span></td>
			</tr>
			<tr><td  colspan="2">
			<p>' . esc_html__( 'Map extra OAuth Provider attributes which you wish to be included in the user profile below', 'miniorange-login-with-eve-online-google-facebook' ) . '</p></td>
			<td><span class="mo_oauth_x_attrrolemapping_5"><input disabled type="button" value="+" class="button button-primary mo_disabled_btn"  /><input disabled type="button" value="-" class="button button-primary mo_disabled_btn"   />
            </span></td>
			</tr>
			<tr><td class="mo_oauth_x_attrrolemapping_12"><input disabled class="mo_oauth_input_disabled" type="text" placeholder="' . esc_html__( 'Enter field meta name', 'miniorange-login-with-eve-online-google-facebook' ) . '" /></td>
			<td><input disabled type="text" placeholder="' . esc_html__( 'Enter attribute name from OAuth Provider', 'miniorange-login-with-eve-online-google-facebook' ) . '" class="mo_table_textbox mo_oauth_input_disabled" /></td>
			</tr>';
		?>
			<tr><td>
			<br>
			<input type="submit" name="submit" value="<?php esc_html_e( 'Save settings', 'miniorange-login-with-eve-online-google-facebook' ); ?>"
			class="button button-large mo_oauth_configure_btn" />
			</td></tr>
			</table>
		</form>
		</div>
		<div class="mo_table_layout mo_oauth_attribute_page_font mo_oauth_outer_div" id="role-mapping">
		<div class="mo_oauth_customization_header">
		<h3 class="mo_oauth_signing_heading mo_oauth_x_attrrolemapping_13"><?php esc_html_e( 'Role Mapping ', 'miniorange-login-with-eve-online-google-facebook' ); ?><div class="mo_oauth_tooltip"  ><span class="mo_oauth_tooltiptext"  >PREMIUM</span><a class="mo_oauth_x_attrrolemapping_11" href="<?php echo esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ); ?>" target="_blank" rel="noopener noreferrer">
		<span><img class="mo_oauth_premium-label" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) ); ?>/images/mo_oauth_premium-label.png" alt="miniOrange Premium Plans Logo"></span></a></div></h3>
		<span class="mo_oauth_x_attrrolemapping_5"><div class="mo_oauth_tooltip mo_oauth_tooltip_float_right"><span class="mo_tooltiptext"  >How to map Roles?</span><a
				href="https://developers.miniorange.com/docs/oauth/wordpress/client/role-mapping" target="_blank"
				rel="noopener"><img class="mo_oauth_guide_img" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) ); ?>/images/mo_oauth_info-icon.png" alt="miniOrange Premium Plans Logo" aria-hidden="true"></a></div>
			</span>
		</div><br>
		<p class="mo_oauth_upgrade_warning mo_oauth_x_attrrolemapping_14"><b>NOTE: </b><?php esc_html_e( 'Role will be assigned only to non-admin users (user that do NOT have Administrator privileges). You will have to manually change the role of Administrator users.', 'miniorange-login-with-eve-online-google-facebook' ); ?></p>
		<form id="role_mapping_form" name="f" method="post" action="">
		<?php wp_nonce_field( 'mo_oauth_role_mapping_form_nonce', 'mo_oauth_role_mapping_form_field' ); ?>
		<input disabled class="mo_table_textbox mo_oauth_input_disabled" required="" type="hidden"  name="mo_oauth_app_name" value="<?php echo esc_attr( $currentappname ); ?>">
		<input disabled class="mo_oauth_input_disabled"  type="hidden" name="option" value="mo_oauth_client_save_role_mapping" />
		<p><input disabled type="checkbox" class="mo_input_checkbox mo_oauth_input_disabled"/><strong class="mo_strong"><?php esc_html_e( ' Keep existing user roles', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong>&nbsp;&nbsp;<small><?php esc_html_e( '( Role mapping won\'t apply to existing WordPress users )', 'miniorange-login-with-eve-online-google-facebook' ); ?></small></p>
		<p><input disabled type="checkbox" class="mo_input_checkbox mo_oauth_input_disabled" > <strong class="mo_strong"><?php esc_html_e( ' Do Not allow login if roles are not mapped here ', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong>&nbsp;&nbsp;<small><?php esc_html_e( '( We won\'t allow users to login if we don\'t find users role/group mapped below. )', 'miniorange-login-with-eve-online-google-facebook' ); ?></small></p>
		<p><input disabled type="checkbox" class="mo_input_checkbox mo_oauth_input_disabled" > <strong class="mo_strong"><?php esc_html_e( ' Role Mapping based on Email Domain ', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><?php esc_html_e( '( This feature allows to map the roles based on email domain of the user when the email attribute is configured in Group Attributes Name. )', 'miniorange-login-with-eve-online-google-facebook' ); ?></small></p>
		<div id="panel1">
			<table class="mo_oauth_client_mapping_table" id="mo_oauth_client_role_mapping_table" class="mo_oauth_x_attrrolemapping_15">
					<tr><td>&nbsp;</td></tr>
					<tr>
					<td><font class="mo_oauth_x_attrrolemapping_16"><?php esc_html_e( 'Default Role ', 'miniorange-login-with-eve-online-google-facebook' ); ?></font>
					<small><div class="mo_oauth_tooltip"  ><span class="mo_oauth_tooltiptext mo_oauth_extra_tooltip mo_oauth_x_attrrolemapping_17"><?php esc_html_e( ' Default role will be assigned to all users for which mapping is not specified.', 'miniorange-login-with-eve-online-google-facebook' ); ?></span><a
							href="https://developers.miniorange.com/docs/oauth/wordpress/client/role-mapping"
							target="_blank" rel="noopener"><i class="fa fa-info-circle mo_oauth_info"></i></a></div></small>
					</td>
					<td>
						<select disabled class="mo_oauth_input_disabled mo_oauth_x_attrrolemapping_18">
							<option><?php esc_html_e( 'Subscriber', 'miniorange-login-with-eve-online-google-facebook' ); ?></option>
						</select>
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td class="mo_oauth_x_attrrolemapping_19"><b><?php esc_html_e( 'Group Attribute Value', 'miniorange-login-with-eve-online-google-facebook' ); ?></b></td>
					<td class="mo_oauth_x_attrrolemapping_19"><b><?php esc_html_e( 'WordPress Role', 'miniorange-login-with-eve-online-google-facebook' ); ?></b></td>
				</tr>
				<tr>
					<td><input disabled class="mo_oauth_client_table_textbox mo_oauth_input_disabled" type="text" placeholder="<?php esc_html_e( 'group name', 'miniorange-login-with-eve-online-google-facebook' ); ?>" />
					</td>
					<td>
						<select disabled class="mo_oauth_input_disabled mo_oauth_x_attrrolemapping_18">
							<option><?php esc_html_e( 'Subscriber', 'miniorange-login-with-eve-online-google-facebook' ); ?></option>
						</select>
					</td>
				</tr>
				</table>
				</br>
				<table class="mo_oauth_client_mapping_table mo_oauth_x_attrrolemapping_15">
					<tr><td><a class="mo_oauth_x_attrrolemapping_20"><u><?php esc_html_e( 'Add More Mapping', 'miniorange-login-with-eve-online-google-facebook' ); ?></u></a><br><br></td><td>&nbsp;</td></tr>
					<tr>
						<td><input disabled type="submit" class="button button-primary button-large mo_disabled_btn" value="<?php esc_html_e( 'Save Mapping', 'miniorange-login-with-eve-online-google-facebook' ); ?>" /></td>
						<td>&nbsp;</td>
					</tr>
				</table>
				</div>
			</form>
		</div>
	</div>
	</div>
	<?php
}

/**
 * Get desired attribute value from resource owner details.
 *
 * @param mixed $nestedprefix get nextson json variable.
 * @param mixed $resource_owner_details userinfo of the user performing the SSO.
 * @param mixed $temp variable to store data of nested loop.
 */
function mooauth_client_dropdownattrmapping( $nestedprefix, $resource_owner_details, $temp ) {
	foreach ( $resource_owner_details as $key => $resource ) {
		if ( is_array( $resource ) ) {
			if ( ! empty( $nestedprefix ) ) {
				$nestedprefix .= '.';
			}
			$temp         = mooauth_client_dropdownattrmapping( $nestedprefix . $key, $resource, $temp );
			$nestedprefix = rtrim( $nestedprefix, '.' );
		} else {
			if ( ! empty( $nestedprefix ) ) {
				array_push( $temp, $nestedprefix . '.' . $key );
			} else {
				array_push( $temp, $key );
			}
		}
	}
	return $temp;
}
