<?php
/*
Plugin Name: AppPresser AMP Ad Injector
Description: This plugin lets you insert Google Ad manager ads in posts served in the AppPresser app
Version:     1.0.0
Author:      Diffractive.io
Author URI:  http://diffractive.io
License:     MIT License
License URI: https://github.com/diffractive/apppresser-ampad-injector/blob/main/LICENSE.md
*/

function apaai_str_replace_n_after($search, $replace, $subject, $occurrence) {
	$search = preg_quote($search,'/');
	return preg_replace("/^((?:.*?$search){".$occurrence."})/s", "$1$replace", $subject);
}

function apaai_amp_ad_content($data_option) {
	$data_option_safe = esc_html($data_option);
	return "<amp-ad width=336 height=280 type=\"doubleclick\" data-slot=\"$data_option_safe\" data-multi-size=\"300x250\"></amp-ad>";
}

/* Inject AMP Ad into Posts. */
function apaai_insert_amp_ad_in_app_posts ( $content ) {
	// only insert html if it is a wp json request for posts, note that we tried wp_is_json_request() but didn't work in the app (worked in preview)
	// currently amp-ad tags are either wiped or not served in the app content for unknown reasons
	if (strpos( $_SERVER['REQUEST_URI'], '/wp-json/wp/v2/posts') === 0) {
		// insert first block
		$content = apaai_str_replace_n_after("</p>", apaai_amp_ad_content(get_option('data-slot-1', "")), $content, (int)get_option('insert-after-paragraph-num-1', 3));
		// insert second block
		$content = apaai_str_replace_n_after("</p>", apaai_amp_ad_content(get_option('data-slot-2', "")), $content, (int)get_option('insert-after-paragraph-num-2', 8));
		return $content;
	} else {
		return $content;
	}
}

// add this filter to the 'the_content' hook, see https://developer.wordpress.org/reference/hooks/the_content/
// pick a larger number to ensure a later priority, to reduce possibility of tags being sanitized away by other filters
add_filter( 'the_content', 'apaai_insert_amp_ad_in_app_posts', 90000);

/**
 * Registers a new options page: 'AppPresser Ad inserter settings page under Settings
 */
function apaai_register_apppresser_amp_ad_inserter_option_page() {
    add_options_page(
        'AppPresser AMP Ad Injector',
        'AppPresser AMP Ad Injector',
        'publish_pages',
        'apppresser-amp-ad-inserter',
        'apaai_apppresser_amp_ad_inserter_options_page',
        999
    );
	add_action( 'admin_init', 'apaai_register_apppresser_amp_ad_inserter_settings' );
}
function apaai_register_apppresser_amp_ad_inserter_settings() {
	register_setting('appp-amp-ad-inserter-group', 'data-slot-1');
	register_setting('appp-amp-ad-inserter-group', 'insert-after-paragraph-num-1');

	register_setting('appp-amp-ad-inserter-group', 'data-slot-2');
	register_setting('appp-amp-ad-inserter-group', 'insert-after-paragraph-num-2');
}
function apaai_apppresser_amp_ad_inserter_options_page() {
?>
<div class="wrap">
	<h2>AppPresser AMP Ad Injector</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'appp-amp-ad-inserter-group' ); ?>
		<?php do_settings_sections( 'appp-amp-ad-inserter-group' ); ?>
		<h3>First Block</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Data Slot</th>
				<td>
					<input type="text" name="data-slot-1" style="width: 100%" value="<?php echo esc_attr(get_option('data-slot-1')); ?>" />
					<br>
					<p><i>Data-slot can be found in the <strong>Tags</strong> tab of an ad unit created in Ad-manager</i></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Insert Ad after paragraph number (Default: 3)</th>
				<td><input type="number" name="insert-after-paragraph-num-1" value="<?php echo esc_attr(get_option('insert-after-paragraph-num-1', 3)); ?>" /></td>
			</tr>
		</table>
		<hr>
		<h3>Second Block</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Data Slot</th>
				<td>
					<input type="text" name="data-slot-2" style="width: 100%" value="<?php echo esc_attr(get_option('data-slot-2')); ?>" />
					<br>
					<p><i>Data-slot can be found in the <strong>Tags</strong> tab of an ad unit created in Ad-manager</i></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Insert Ad after paragraph number (Default: 8)</th>
				<td><input type="number" name="insert-after-paragraph-num-2" value="<?php echo esc_attr(get_option('insert-after-paragraph-num-2', 8)); ?>" /></td>
			</tr>
		</table>
		<hr>
		<?php submit_button(); ?>
	</form>
</div>
<?php
}
add_action( 'admin_menu', 'apaai_register_apppresser_amp_ad_inserter_option_page' );

?>
