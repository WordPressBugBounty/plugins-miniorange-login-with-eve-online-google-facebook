<?php
/**
 * IDP Attributes
 *
 * @package    display-idp-attributes
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

/**
 * Display Attribute values coming from OAuth/OpenID provider in a tabular format.
 */
class MO_OAuth_Client_Attribute_Mapping {
	/**
	 * Variable to store attributes coming from IDP
	 *
	 * @var $attributes attributes coming from IDP.
	 */
	protected static $attributes;
	/**
	 * Initialize local $attributes variable.
	 */
	protected static function initialize_vars() {
		self::$attributes = get_option( 'mo_oauth_attr_name_list' );
	}

	/**
	 * CSS for the table to be dsiplayed for attribute mapping
	 */
	private static function emit_css() {
		?>
		<?php
	}
	/**
	 * Display list of attributes in table format.
	 */
	public static function emit_attribute_table() {
		self::initialize_vars();
		if ( false === self::$attributes || ! is_array( self::$attributes ) ) {
			return;
		}
		self::emit_css();
		?>
			<div id="mo_support_layout" class="mo_support_layout mo_oauth_outer_div">
					<h2 class="mo_oauth_attribute_map_heading mo_oauth_x_guides_1">Test Configuration</h2>
					<table class="mo-side-table">
						<tr class="mo-side-table-tr">
							<th class="mo-side-table-th">Attribute Name</th>
							<th class="mo-side-table-th">Attribute Value</th>
						</tr>
						<?php mooauth_client_testattrmappingconfig( '', self::$attributes, 'mo-side-table-' ); ?>
					</table>
			</div>
		<?php
	}
}
