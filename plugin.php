<?php
/*
Plugin Name: Lightbox By Mark Advertising
Plugin URI: http://www.markadvertising.com
Description: WordPress lightbox that utilizes WordPress included Thickbox.js
Author: Mark Advertising
Version: 1.0.3
Author URI: http://www.markadvertising.com
*/



/*
	Add settings link to plubin page
*/
//add_filter( 'plugin_row_meta', 'set_markad_lightbox_plugin_meta', 10, 2 );
function set_markad_lightbox_plugin_meta($links, $file) {
    
    $plugin = plugin_basename(__FILE__);
 
    // create link
    if ($file == $plugin) {
        return array_merge(
            $links,
            array( sprintf( '<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings') ) )
        );
    }
 
    return $links;
}
 

/*
	Enqueue scripts for front-end only
*/ 
add_action('wp_enqueue_scripts', 'markad_lightbox_plugin_init');
function markad_lightbox_plugin_init()
{
	if(!is_admin())
	{
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'thickbox' );
	}
}


//Thickbox Lightbox for wordpress
add_filter('the_content', 'markad_add_thickbox_lightbox', 2);
function markad_add_thickbox_lightbox($content)
{
	//Check the page for link images direct to image (no trailing attributes)
	$string = '/<a href="(.*?).(jpg|jpeg|png|gif|bmp|ico)"><img(.*?)class="(.*?)wp-image-(.*?)" \/><\/a>/i';
	preg_match_all( $string, $content, $matches, PREG_SET_ORDER);

	//Check which attachment is referenced
	foreach ($matches as $val)
	{
		$pos = strpos($val[5], '"');
		$post_id = intval(substr($val[5],0,$pos));
		//$alt = get_post_meta($post_id, '_wp_attachment_image_alt', true);
		$alt = get_posts(array('p' => $post_id, 'post_type' => 'attachment'));
		//$alt = $alt[0]->post_excerpt;
		$alt = $alt[0]->post_title;
		$img_url = wp_get_attachment_image_src( $post_id, 'large' );

		//Replace the instance with the lightbox and title(caption) references. Won't fail if caption is empty.
		$string = '<a href="' . $val[1] . '.' . $val[2] . '"><img' . $val[3] . 'class="' . $val[4] . 'wp-image-' . $val[5] . '" /></a>';
		$replace = '<a href="' . $img_url[0] . '" title="' . $alt . '" class="thickbox" rel="post-photo"><img' . $val[3] . 'class="' . $val[4] . 'wp-image-' . $val[5] . '" /></a>';
		$content = str_replace( $string, $replace, $content);
	}

	return $content;
}

//Replace post thumbnail link with large image link
add_filter('wp_get_attachment_link', 'markad_attachment_link_filter', 10, 4);
function markad_attachment_link_filter( $content, $post_id, $size, $permalink ) {
    // Only do this if we're getting the file URL
    if (! $permalink) {
		$post = get_post($post_id);
		$post_parent = $post->post_parent;
        $image = wp_get_attachment_image_src( $post_id, 'large' );
		
        $content = preg_replace('/href=\'(.*?)\'/', 'href=\'' . $image[0] . '\' class=\'thickbox\' rel=\'gallery-'.$post_parent.'\'', $content );
    }
	return $content;
}
?>