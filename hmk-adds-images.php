<?php
/*
Plugin Name: Header Add Image
Plugin URI: http://www.kashif-arain.com/
Description: Header Add Image for each category, Custom Taxonomy.
Author: Muhammad Kashif Arain
Version: 1.1
Author URI: http://www.kashif-arain.com/
*/
?>
<?php

if (!defined('Z_PLUGIN_URL'))

	define('Z_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__)));



define('Z_IMAGE_PLACEHOLDER', Z_PLUGIN_URL."/images/placeholder.png");

define('Z_DEFAULT_IMAGE', Z_PLUGIN_URL."/images/default.jpg");





// l10n

load_plugin_textdomain('zci', FALSE, 'categories-images/languages');



add_action('admin_init', 'z_init');

function z_init() {

	$z_taxonomies = get_taxonomies();

	if (is_array($z_taxonomies)) {

		$zci_options = get_option('zci_options');

		if (empty($zci_options['excluded_taxonomies']))

			$zci_options['excluded_taxonomies'] = array();

		

	    foreach ($z_taxonomies as $z_taxonomy) {

			if (in_array($z_taxonomy, $zci_options['excluded_taxonomies']))

				continue;

	        add_action($z_taxonomy.'_add_form_fields', 'z_add_texonomy_field');

			add_action($z_taxonomy.'_edit_form_fields', 'z_edit_texonomy_field');

			add_filter( 'manage_edit-' . $z_taxonomy . '_columns', 'z_taxonomy_columns' );

			add_filter( 'manage_' . $z_taxonomy . '_custom_column', 'z_taxonomy_column', 10, 3 );

	    }

	}

}

add_action( 'admin_enqueue_scripts', 'hmk_add_styles_admin' );
add_action( 'wp_enqueue_scripts', 'hmk_add_styles_front' );
 
function hmk_add_styles_admin( $page ) {
        
		wp_enqueue_style( 'hmk-admin-style', plugins_url('/css/hmk_style_admin.css', __FILE__) );
        
}

function hmk_add_styles_front( $page ) {        
		wp_enqueue_style( 'hmk-front-style', plugins_url('/css/hmk_style_front.css', __FILE__) );        
}



function z_add_style() {

	echo '<style type="text/css" media="screen">

		th.column-thumb {width:60px;}

		.form-field img.taxonomy-image {border:1px solid #eee;max-width:300px;max-height:300px;}

		.inline-edit-row fieldset .thumb label span.title {width:48px;height:48px;border:1px solid #eee;display:inline-block;}

		.column-thumb span {width:48px;height:48px;border:1px solid #eee;display:inline-block;}

		.inline-edit-row fieldset .thumb img,.column-thumb img {width:48px;height:48px;}

		.hmk-meta {margin-top:20px}

		.adds_image { width:90%}

	</style>';

}



// add image field in add form

function z_add_texonomy_field() {

	if (get_bloginfo('version') >= 3.5)

		wp_enqueue_media();

	else {

		wp_enqueue_style('thickbox');

		wp_enqueue_script('thickbox');

	}

	

	echo '<div class="form-field">

		<label for="taxonomy_image">' . __('Image', 'zci') . '</label>

		<input  type="text" name="taxonomy_image" id="taxonomy_image" value="" />

		<br/>

		<button class="z_upload_image_button button">' . __('Upload/Add image', 'zci') . '</button> <br />
		
		<label for="taxonomy_image_link">' . __('Image Link', 'zci') . '</label>
		<input class="hmk-input" type="text" name="taxonomy_image_link" id="taxonomy_image_link" value="" /><br />
		
		<label for="taxonomy_image_title">' . __('Image Title', 'zci') . '</label>
		<input class="hmk-input" type="text" name="taxonomy_image_title" id="taxonomy_image_title" value="" /><br />
		
		<label for="taxonomy_image_title">' . __('Open in new tab', 'zci') . '</label>
		<input class="hmk-input" type="checkbox" name="taxonomy_image_tab" id="taxonomy_image_tab" value="yes" /><br />


	</div>'.z_script();

}



// add image field in edit form

function z_edit_texonomy_field($taxonomy) {

	if (get_bloginfo('version') >= 3.5)

		wp_enqueue_media();

	else {

		wp_enqueue_style('thickbox');

		wp_enqueue_script('thickbox');

	}

	

	if (hmk_taxonomy_image_url( $taxonomy->term_id, TRUE ) == Z_IMAGE_PLACEHOLDER) 

		$image_text = "";

	else

		$image_text = hmk_taxonomy_image_url( $taxonomy->term_id, TRUE );
		$taxonomy_image_link = get_option('taxonomy_image_link'.$taxonomy->term_id);
		$taxonomy_image_title = get_option('taxonomy_image_title'.$taxonomy->term_id);
		$taxonomy_image_tab = get_option('taxonomy_image_tab'.$taxonomy->term_id);
		
		 if( $taxonomy_image_tab == "yes"){ $checked = "checked=\"checked\""; } else{ $checked = "";}

	echo '<tr class="form-field">

		<th scope="row" valign="top">
		
		<label for="taxonomy_image">' . __('Image', 'zci') . '</label></th>

		<td><img class="taxonomy-image" src="' . hmk_taxonomy_image_url( $taxonomy->term_id, TRUE ) . '"/><br/>
		<input class="hmk-input" type="text" name="taxonomy_image" id="taxonomy_image" value="'.$image_text.'" /><br />

		<button class="z_upload_image_button button">' . __('Upload/Add image', 'zci') . '</button>

		<button class="z_remove_image_button button">' . __('Remove image', 'zci') . '</button> <br />
		
		<br />
		<label for="taxonomy_image_link">' . __('Image Link', 'zci') . '</label>
		<input class="hmk-input" type="text" name="taxonomy_image_link" id="taxonomy_image_link" value="'.$taxonomy_image_link.'" /><br />
		
		<label for="taxonomy_image_title">' . __('Image Title', 'zci') . '</label>
		<input class="hmk-input" type="text" name="taxonomy_image_title" id="taxonomy_image_title" value="'.$taxonomy_image_title.'" /><br />
		
		<label for="taxonomy_image_title">' . __('Open in new tab', 'zci') . '</label>
		<input class="hmk-input" type="checkbox" name="taxonomy_image_tab" id="taxonomy_image_tab" value="yes" '.$checked.' /><br />

		</td>

	</tr>'.z_script();

}

// upload using wordpress upload

function z_script() {

	return '<script type="text/javascript">

	    jQuery(document).ready(function($) {

			var wordpress_ver = "'.get_bloginfo("version").'", upload_button;

			$(".z_upload_image_button").click(function(event) {

				upload_button = $(this);

				var frame;

				if (wordpress_ver >= "3.5") {

					event.preventDefault();

					if (frame) {

						frame.open();

						return;

					}

					frame = wp.media();

					frame.on( "select", function() {

						// Grab the selected attachment.

						var attachment = frame.state().get("selection").first();

						frame.close();

						if (upload_button.parent().prev().children().hasClass("tax_list")) {

							upload_button.parent().prev().children().val(attachment.attributes.url);

							upload_button.parent().prev().prev().children().attr("src", attachment.attributes.url);

						}

						else

							$("#taxonomy_image").val(attachment.attributes.url);

					});

					frame.open();

				}

				else {

					tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");

					return false;

				}

			});

			

			$(".z_remove_image_button").click(function() {

				$("#taxonomy_image").val("");

				$(this).parent().siblings(".title").children("img").attr("src","' . Z_IMAGE_PLACEHOLDER . '");

				$(".inline-edit-col :input[name=\'taxonomy_image\']").val("");

				return false;

			});

			

			if (wordpress_ver < "3.5") {

				window.send_to_editor = function(html) {

					imgurl = $("img",html).attr("src");

					if (upload_button.parent().prev().children().hasClass("tax_list")) {

						upload_button.parent().prev().children().val(imgurl);

						upload_button.parent().prev().prev().children().attr("src", imgurl);

					}

					else

						$("#taxonomy_image").val(imgurl);

					tb_remove();

				}

			}

			

			$(".editinline").live("click", function(){  

			    var tax_id = $(this).parents("tr").attr("id").substr(4);

			    var thumb = $("#tag-"+tax_id+" .thumb img").attr("src");

				if (thumb != "' . Z_IMAGE_PLACEHOLDER . '") {

					$(".inline-edit-col :input[name=\'taxonomy_image\']").val(thumb);

				} else {

					$(".inline-edit-col :input[name=\'taxonomy_image\']").val("");

				}

				$(".inline-edit-col .title img").attr("src",thumb);

			    return false;  

			});  

	    });

	</script>';

}





// upload using wordpress upload

function z_script_page() {

	return '<script type="text/javascript">

	    jQuery(document).ready(function($) {

			var wordpress_ver = "'.get_bloginfo("version").'", upload_button;

			$(".z_upload_image_button").click(function(event) {

				upload_button = $(this);

				var frame;

				if (wordpress_ver >= "3.5") {

					event.preventDefault();

					if (frame) {

						frame.open();

						return;

					}

					frame = wp.media();

					frame.on( "select", function() {

						// Grab the selected attachment.

						var attachment = frame.state().get("selection").first();

						frame.close();

						if (upload_button.parent().prev().children().hasClass("tax_list")) {

							upload_button.parent().prev().children().val(attachment.attributes.url);

							upload_button.parent().prev().prev().children().attr("src", attachment.attributes.url);

						}

						else

							$("#adds_image").val(attachment.attributes.url);

					});

					frame.open();

				}

				else {

					tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");

					return false;

				}

			});

			

			$(".z_remove_image_button").click(function() {

				$("#adds_image").val("");

				$(this).parent().siblings(".title").children("img").attr("src","' . Z_IMAGE_PLACEHOLDER . '");

				$(".inline-edit-col :input[name=\'adds_image\']").val("");

				return false;

			});

			

			if (wordpress_ver < "3.5") {

				window.send_to_editor = function(html) {

					imgurl = $("img",html).attr("src");

					if (upload_button.parent().prev().children().hasClass("tax_list")) {

						upload_button.parent().prev().children().val(imgurl);

						upload_button.parent().prev().prev().children().attr("src", imgurl);

					}

					else

						$("#adds_image").val(imgurl);

					tb_remove();

				}

			}

			

			$(".editinline").live("click", function(){  

			    var tax_id = $(this).parents("tr").attr("id").substr(4);

			    var thumb = $("#tag-"+tax_id+" .thumb img").attr("src");

				if (thumb != "' . Z_IMAGE_PLACEHOLDER . '") {

					$(".inline-edit-col :input[name=\'adds_image\']").val(thumb);

				} else {

					$(".inline-edit-col :input[name=\'adds_image\']").val("");

				}

				$(".inline-edit-col .title img").attr("src",thumb);

			    return false;  

			});  

	    });

	</script>';

}



// save our taxonomy image while edit or save term

add_action('edit_term','z_save_taxonomy_image');

add_action('create_term','z_save_taxonomy_image');

function z_save_taxonomy_image($term_id) {

    if(isset($_POST['taxonomy_image'])) {
	update_option('z_taxonomy_image'.$term_id, $_POST['taxonomy_image']);
	}
	
	if(isset($_POST['taxonomy_image_link'])) {
	update_option('taxonomy_image_link'.$term_id, $_POST['taxonomy_image_link']);
	}
	
	if(isset($_POST['taxonomy_image_title'])) {
	update_option('taxonomy_image_title'.$term_id, $_POST['taxonomy_image_title']);
	}
	
	
	update_option('taxonomy_image_tab'.$term_id, $_POST['taxonomy_image_tab']);
	

}



// output taxonomy image url for the given term_id (NULL by default)

function hmk_taxonomy_image_url($term_id = NULL, $return_placeholder = FALSE) {

	if (!$term_id) {

		if (is_category())

			$term_id = get_query_var('cat');

		elseif (is_tax()) {

			$current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));

			$term_id = $current_term->term_id;

		}elseif(is_single()) {
		$category = get_the_category();
		$term_id = $category[0]->term_id;
		}

	}
	
	$taxonomy_image_url = get_option('z_taxonomy_image'.$term_id);

		

	if (!$taxonomy_image_url)  {

	global $wpdb;

	$parent = $wpdb->get_var("SELECT parent FROM ".$wpdb->prefix."term_taxonomy WHERE term_id = $term_id");

	$taxonomy_image_url = get_option('z_taxonomy_image'.$parent);	

	}

	

	if (is_page())  {	

	$taxonomy_image_url = get_post_meta(get_the_ID(), 'adds_image', true);	

	}

	

	if ($return_placeholder)

		return ($taxonomy_image_url != "") ? $taxonomy_image_url : Z_IMAGE_PLACEHOLDER;

	else

		return $taxonomy_image_url;

}



function z_quick_edit_custom_box($column_name, $screen, $name) {

	if ($column_name == 'thumb') 

		echo '<fieldset>

		<div class="thumb inline-edit-col">

			<label>

				<span class="title"><img src="" alt="Thumbnail"/></span>

				<span class="input-text-wrap"><input type="text" name="taxonomy_image" value="" class="tax_list" /></span>

				<span class="input-text-wrap">

					<button class="z_upload_image_button button">' . __('Upload/Add image', 'zci') . '</button>

					<button class="z_remove_image_button button">' . __('Remove image', 'zci') . '</button>
					
					


				</span>
				<br/>
				<input class="hmk-input" type="text" name="taxonomy_image_link" id="taxonomy_image_link" value="" /><br />
			    <input class="hmk-input" type="text" name="taxonomy_image_title" id="taxonomy_image_title" value="" /><br />
				
				
		        <input class="hmk-input" type="checkbox" name="taxonomy_image_tab" id="taxonomy_image_tab" value="yes"  /><br />


			</label>

		</div>

	</fieldset>';

}



/**

 * Thumbnail column added to category admin.

 *

 * @access public

 * @param mixed $columns

 * @return void

 */

function z_taxonomy_columns( $columns ) {

	$new_columns = array();

	$new_columns['cb'] = $columns['cb'];

	$new_columns['thumb'] = __('Image', 'zci');



	unset( $columns['cb'] );



	return array_merge( $new_columns, $columns );

}



/**

 * Thumbnail column value added to category admin.

 *

 * @access public

 * @param mixed $columns

 * @param mixed $column

 * @param mixed $id

 * @return void

 */

function z_taxonomy_column( $columns, $column, $id ) {

	if ( $column == 'thumb' )

		$columns = '<span><img src="' . hmk_taxonomy_image_url($id, TRUE) . '" alt="' . __('Thumbnail', 'zci') . '" class="wp-post-image" /></span>';

	

	return $columns;

}



// change 'insert into post' to 'use this image'

function z_change_insert_button_text($safe_text, $text) {

    return str_replace("Insert into Post", "Use this image", $text);

}



// style the image in category list

if ( strpos( $_SERVER['SCRIPT_NAME'], 'edit-tags.php' ) > 0 ) {

	add_action( 'admin_head', 'z_add_style' );

	add_action('quick_edit_custom_box', 'z_quick_edit_custom_box', 10, 3);

	add_filter("attribute_escape", "z_change_insert_button_text", 10, 2);

}



// New menu submenu for plugin options in Settings menu

add_action('admin_menu', 'z_options_menu');

function z_options_menu() {

	add_options_page(__('HMK Add Images settings', 'zci'), __('HMK Add Images', 'zci'), 'manage_options', 'zci-options', 'zci_options');

	add_action('admin_init', 'z_register_settings');

}



// Register plugin settings

function z_register_settings() {

	register_setting('zci_options', 'zci_options', 'z_options_validate');

	add_settings_section('zci_settings', __('HMK Add Images settings', 'zci'), 'z_section_text', 'zci-options');

	add_settings_field('z_excluded_taxonomies', __('Excluded Taxonomies', 'zci'), 'z_excluded_taxonomies', 'zci-options', 'zci_settings');

}



// Settings section description

function z_section_text() {

	echo '<p>'.__('Please select the taxonomies you want to exclude it from HMK Add Images plugin', 'zci').'</p>';

}



// Excluded taxonomies checkboxs

function z_excluded_taxonomies() {

	$options = get_option('zci_options');

	$disabled_taxonomies = array('nav_menu', 'link_category', 'post_format');

	foreach (get_taxonomies() as $tax) : if (in_array($tax, $disabled_taxonomies)) continue; ?>

<input type="checkbox" name="zci_options[excluded_taxonomies][<?php echo $tax ?>]" value="<?php echo $tax ?>" <?php checked(isset($options['excluded_taxonomies'][$tax])); ?> />
<?php echo $tax ;?><br />
<?php endforeach;

}



// Validating options

function z_options_validate($input) {

	return $input;

}



// Plugin option page

function zci_options() {

	if (!current_user_can('manage_options'))

		wp_die(__( 'You do not have sufficient permissions to access this page.', 'zci'));

		$options = get_option('zci_options');

	?>
<div class="wrap">
  <?php screen_icon(); ?>
  <h2>
    <?php _e('Categories Add Images', 'zci'); ?>
  </h2>
  <form method="post" action="options.php">
    <?php settings_fields('zci_options'); ?>
    <?php do_settings_sections('zci-options'); ?>
    <?php submit_button(); ?>
  </form>
</div>
<?php

}



add_filter('site_transient_update_plugins', 'dd_remove_update_nag');

function dd_remove_update_nag($value) {

 unset($value->response[ plugin_basename(__FILE__) ]);

 return $value;

}



add_action( 'add_meta_boxes', 'adds_meta_box_add' );  

function adds_meta_box_add() {  

    add_meta_box( 'adds-meta-box-id', 'Header Image', 'adds_image_meta_box_cb', 'page', 'normal', 'high' );  

}  

function adds_image_meta_box_cb() {

    wp_nonce_field( basename( __FILE__ ), 'adds_image_meta_box_nonce' );

    $image_url = get_post_meta(get_the_ID(), 'adds_image', true);
    $taxonomy_image_link = get_post_meta(get_the_ID(), 'taxonomy_image_link', true);
    $taxonomy_image_title = get_post_meta(get_the_ID(), 'taxonomy_image_title', true);
    $taxonomy_image_tab = get_post_meta(get_the_ID(), 'taxonomy_image_tab', true);
	
	if( $taxonomy_image_tab ){ $checked = "checked=\"checked\""; }else{ $checked = "";}

   echo '<div class="hmk-meta"><label for="adds_image">' . __('Upload Image', 'zci') . '</label>

		<img style="margin-bottom:15px; height:70px" class="taxonomy-image" src="' . $image_url . '"/><br/><input type="text" name="adds_image" id="adds_image" value="'.$image_text.'" style="width:700px; margin-top:5px; margin-bottom:10px"/><br />

		<button class="z_upload_image_button button">' . __('Upload/Add image', 'zci') . '</button><br />
		
		<label for="taxonomy_image_link">' . __('Image Link', 'zci') . '</label>
		<input class="hmk-input" type="text" name="taxonomy_image_link" id="taxonomy_image_link" value="'.$taxonomy_image_link.'" /><br />
		
		<label for="taxonomy_image_title">' . __('Image Title', 'zci') . '</label>
		<input class="hmk-input" type="text" name="taxonomy_image_title" id="taxonomy_image_title" value="'.$taxonomy_image_title.'" /><br />
		
		<label for="taxonomy_image_title">' . __('Open in new tab', 'zci') . '</label>
		<input class="hmk-input" type="checkbox" name="taxonomy_image_tab" id="taxonomy_image_tab" value="yes" '.$checked.' /><br />

				

	</div>'.z_script_page();

}

add_action( 'save_post', 'adds_image_meta_box_save' );  

function adds_image_meta_box_save( $post_id ){   

    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 

    if ( !isset( $_POST['adds_image_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['adds_image_meta_box_nonce'], basename( __FILE__ ) ) ) return;

    if( !current_user_can( 'edit_post' ) ) return;  

    if( isset( $_POST['adds_image'] ) ) {
	update_post_meta( $post_id, 'adds_image', esc_attr( $_POST['adds_image'], $allowed ) );
	}   
	
	if( isset( $_POST['taxonomy_image_link'] ) ) {
	update_post_meta( $post_id, 'taxonomy_image_link', esc_attr( $_POST['taxonomy_image_link'], $allowed ) );
	}   
	
	if( isset( $_POST['taxonomy_image_title'] ) ) {
	update_post_meta( $post_id, 'taxonomy_image_title', esc_attr( $_POST['taxonomy_image_title'], $allowed ) );
	}
	
	update_post_meta( $post_id, 'taxonomy_image_tab', esc_attr( $_POST['taxonomy_image_tab'], $allowed ) );
	 

}



function hmk_header_image() {  
	

  $hmk_image = hmk_taxonomy_image_url();

  if ($hmk_image)  {
  
  if (!$term_id) {

		if (is_category())

			$term_id = get_query_var('cat');

		elseif (is_tax()) {

			$current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));

			$term_id = $current_term->term_id;

		}elseif(is_single()) {
		$category = get_the_category();
		$term_id = $category[0]->term_id;
		}
		
		

	}
	
	$taxonomy_image_title = get_option('taxonomy_image_title'.$term_id);
	$taxonomy_image_link = get_option('taxonomy_image_link'.$term_id);
	$taxonomy_image_tab = get_option('taxonomy_image_tab'.$term_id);
	
	if(is_page()) {	
	 $taxonomy_image_link = get_post_meta(get_the_ID(), 'taxonomy_image_link', true);
     $taxonomy_image_title = get_post_meta(get_the_ID(), 'taxonomy_image_title', true);
     $taxonomy_image_tab = get_post_meta(get_the_ID(), 'taxonomy_image_tab', true);
	}
	
	
   if( $taxonomy_image_tab == "yes" ){ $target = "target=\"_blank\""; }else{ $target = "";}
   
    echo '<a '.$target.' title="' .$taxonomy_image_title. '" href="' .$taxonomy_image_link. '"><img  class="taxonomy-image" src="' .$hmk_image. '" alt="' .$taxonomy_image_title. '"  /></a>';

  } else {

   echo '<img class="taxonomy-image" src="' .Z_DEFAULT_IMAGE.'"/>';

  }

}



add_shortcode('header_ads_image', 'hmk_header_image');  
