<?php
/*
Plugin Name: AppPresser HTML Inserter
Plugin URI:  http://diffractive.io
Description: This plugin lets you insert arbitrary HTML in posts served in the AppPresser app
Version:     1.0.0
Author:      Diffractive.io
Author URI:  http://diffractive.io
License:     MIT License
License URI: https://github.com/diffractive/apppresser-html-inserter/blob/main/LICENSE.md
*/

function str_replace_n_after($search, $replace, $subject, $occurrence) {
	$search = preg_quote($search,'/');
	return preg_replace("/^((?:.*?$search){".$occurrence."})/s", "$1$replace", $subject);
}

/* Inject HTML into Posts. */
function insert_html_in_app_posts ( $content ) {	
	// only insert html if it is a wp json request for posts, note that we tried wp_is_json_request() but didn't work in the app (worked in preview)
	// currently amp-ad tags are either wiped or not served in the app content for unknown reasons
	if (strpos( $_SERVER['REQUEST_URI'], '/wp-json/wp/v2/posts') === 0) {
		return str_replace_n_after("</p>", get_option('html-content', ""), $content, (int)get_option('insert-after-paragraph-num', 3));
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
	<h2>AppPresser HTML inserter</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'appp-html-inserter-group' ); ?>
		<?php do_settings_sections( 'appp-html-inserter-group' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Raw HTML Content</th>
				<td>
					<textarea name="html-content" rows="8" style="width: 100%"><?php echo get_option('html-content'); ?></textarea>
					<br>
					<p><strong>Important notice:</strong> <i>Note that script tags you insert in the HTML will most likely not get executed in the app. If you want to execute any client-side javascript code, follow the <a href="https://docs.apppresser.com/article/392-custom-javascript" target="_blank">official instructions</a>.</i></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Insert HTML after paragraph number<br>(Default: 3)</th>
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