<?php
/**
 * Plugin Name:     Wolfie Category Nested Layout
 * Plugin URI:      https://spero.click
 * Description:     adds nested categories layout to ur woocommerce
 * Author:          Paweł Witek
 * Author URI:      https://wolfiesites.com
 * Text Domain:     wolfie-category-nested-layout
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wolfie_Category_Nested_Layout
 */

Class Wolfie_Category_Nested_Layout
{
	public function __construct(){
		add_action('pre_get_posts', [$this, 'target_main_category_query_with_conditional_tags'] );
		add_filter('posts_request', [$this, 'supress_main_query'], 10, 2);
	}
	public function wolfie_render_current_term_childs(){
		$object = get_queried_object();
		$id_current_term = get_queried_object_id();
		$parent = $object->parent;
	// print all child categories ids
		$terms = get_terms( array(
			'taxonomy' => 'product_cat',
			'parent' => $id_current_term,
			'hide_empty' => true,
		) );
		echo '<div class="wolfie-categories-wrapper">';
		foreach ($terms as $term) {
		// get each child category: image, title, url
			$id = $term->term_id;
			$default_id = apply_filters( 'wpml_object_id', $id, 'product_cat', true, 'pl' );
			$other_img = z_taxonomy_image_url($default_id, 'large');
			$placeholder = '/wp-content/uploads/2020/09/Bez_Tytulu.jpg';
			$name = $term->name;
			$link = get_term_link(intval($id));
			$thumbnail_id = get_woocommerce_term_meta( $default_id, 'thumbnail_id', true );
			$image_url = wp_get_attachment_image_src( $thumbnail_id, 'large' )[0];
			$image_url = (!empty($image_url)) ? $image_url : $other_img;
			$image_url = (!empty($image_url)) ? $image_url : $placeholder;
			echo '<div class="wolfie-products-category"><a href="'.$link.'"><img src="'.$image_url.'"><div class="overlay"><div class="go-to-category"><h2>'.$name.'</h2><button class="btn btn-outline-white">'.__('Go to category','wolfie').'</button></div></div></a></div>';
		}
		echo '</div>';
	}


	public function target_main_category_query_with_conditional_tags( $query ) {
		$object_id = get_queried_object_id();
		$ids = get_term_children($object_id, 'product_cat');
		if($query->is_main_query() && ! $query->is_admin && is_tax('product_cat') || is_shop()) {
			add_action('woocommerce_after_main_content',[$this, 'wolfie_render_current_term_childs'], 9);
		}
		if( $query->is_main_query() && ! $query->is_admin && is_tax('product_cat') && !empty($ids) || is_shop()) {
			remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
		}
	}

// remove product query if not the last category
	public function supress_main_query( $request, $query ){
		$object_id = get_queried_object_id();
		$ids = get_term_children($object_id, 'product_cat');
		if( $query->is_main_query() && ! $query->is_admin && is_tax('product_cat') && !empty($ids) || ( is_shop() && ! is_admin() ) )
			return false;
		else
			return $request;
	}


	public function remove_no_product_notification_category_layout(){
		global $wp_query;
		$query = $wp_query;
		$object_id = get_queried_object_id();
		$ids = get_term_children($object_id, 'product_cat');
		if( $query->is_main_query() && ! $query->is_admin && is_tax('product_cat') && !empty($ids)) {

		}
	}
}
New Wolfie_Category_Nested_Layout;


// FIX PERMALINK OF CATEGORIES MATCHING PRIMARY CATEGORY
add_filter( 'wc_product_post_type_link_product_cat', function( $term, $terms, $post ) {

    // Get the primary term as saved by Yoast
    $primary_cat_id = get_post_meta( $post->ID, '_yoast_wpseo_primary_product_cat', true );

    // If there is a primary and it's not currently chosen as primary
    if ( $primary_cat_id && $term->term_id != $primary_cat_id ) {

        // Find the primary term in the term list
        foreach ( $terms as $term_key => $term_object ) {

            if ( $term_object->term_id == $primary_cat_id ) {
                // Return this as the primary term
                $term = $terms[ $term_key ];
                break;
            }

        }

    }

    return $term;

}, 10, 3 );
