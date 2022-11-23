<?php
/*
Plugin Name: AppPresser HTML Inserter
Plugin URI:  http://diffractive.io
Description: This plugin lets you insert arbitrary HTML in posts served in the AppPresser app
Version:     1.0.0
Author:      Diffractive.io
Author URI:  http://diffractive.io
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Copyright 2022 Diffractive.io (email: info@diffractive.io)
(AppPresser HTML Inserter) is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
(AppPresser HTML Inserter) is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with (AppPresser HTML Inserter). If not, see (https://diffractive.io to your plugin license).
*/

function str_replace_n_after($search, $replace, $subject, $occurrence) {
	$search = preg_quote($search,'/');
	return preg_replace("/^((?:.*?$search){".$occurrence."})/s", "$1$replace", $subject);
}

/* Inject HTML into Posts. */
function insert_html_in_app_posts ( $content ) {	
	// only insert html if it is a wp json request, and it's not serving pages
	// currently amp-ad tags are either wiped or not served in the app content for unknown reasons
	if (wp_is_json_request() && strpos( $_SERVER['REQUEST_URI'], 'wp/v2/pages') === FALSE) {
		return str_replace_n_after("</p>", get_option('html-content', ""), $content, 3);
	} else {
		return $content;
	}
}

// add this filter to the 'the_content' hook, see https://developer.wordpress.org/reference/hooks/the_content/
// pick a larger number to ensure a later priority, to reduce possibility of tags being sanitized away by other filters
add_filter( 'the_content', 'insert_html_in_app_posts', 90000);

/**
 * Registers a new options page: 'AppPresser Ad inserter settings page under Settings
 */
function register_apppresser_html_inserter_option_page() {
    add_options_page( 
        'AppPresser HTML inserter',
        'AppPresser HTML inserter',
        'publish_pages',
        'apppresser-html-inserter',
        'apppresser_html_inserter_options_page',
        999
    );
	add_action( 'admin_init', 'register_apppresser_html_inserter_settings' );
}
function register_apppresser_html_inserter_settings() {
	register_setting('appp-html-inserter-group', 'html-content');
	register_setting('appp-html-inserter-group', 'insert-after-paragraph-num');
}
function apppresser_html_inserter_options_page() {
?>
<div class="wrap">
	<h2>AppPresser Ad inserter</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'appp-html-inserter-group' ); ?>
		<?php do_settings_sections( 'appp-html-inserter-group' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Raw HTML Content</th>
				<td><textarea name="html-content" rows="8" style="width: 100%"><?php echo get_option('html-content'); ?></textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row">Insert after paragraph number<br>(Default: 3)</th>
				<td><input type="number" name="insert-after-paragraph-num" value="<?php echo get_option('insert-after-paragraph-num', 3); ?>" /></td>
			</tr>
		</table>
		
		<?php submit_button(); ?>
	</form>
</div>
<?php
}
add_action( 'admin_menu', 'register_apppresser_html_inserter_option_page' );

?>