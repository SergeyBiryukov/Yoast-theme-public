<?php
/**
 * @package Yoast\YoastCom
 *
 * Template helpers
 */

namespace Yoast\YoastCom\Theme;

/**
 * Like get_template_part() put lets you pass args to the template file
 * Args are available in the template as $template_args array
 *
 * @link https://github.com/humanmade/hm-core/blob/1204806c83497d04379d287753cbe3b6c7c66a9b/hm-core.functions.php#L1255
 *
 * @throws \Exception When an undefined template is included.
 *
 * @param string $file          The file to include.
 * @param array  $template_args style argument list.
 * @param array  $cache_args    The arguments to cache.
 *
 * @return false|string
 */
function get_template_part( $file, $template_args = array(), $cache_args = array() ) {

	$template_args = wp_parse_args( $template_args );
	$cache_args    = wp_parse_args( $cache_args );

	if ( $cache_args ) {

		foreach ( $template_args as $key => $value ) {
			if ( is_scalar( $value ) || is_array( $value ) ) {
				$cache_args[ $key ] = $value;
			} else if ( is_object( $value ) && method_exists( $value, 'get_id' ) ) {
				$cache_args[ $key ] = call_user_method( 'get_id', $value );
			}
		}

		if ( ( $cache = wp_cache_get( $file, serialize( $cache_args ) ) ) !== false ) {

			if ( ! empty( $template_args['return'] ) ) {
				return $cache;
			}

			echo $cache;

			return false;
		}
	}

	$file_handle = $file;

	do_action( 'start_operation', 'hm_template_part::' . $file_handle );

	if ( file_exists( get_stylesheet_directory() . '/' . $file . '.php' ) ) {
		$file = get_stylesheet_directory() . '/' . $file . '.php';
	} elseif ( file_exists( get_template_directory() . '/' . $file . '.php' ) ) {
		$file = get_template_directory() . '/' . $file . '.php';
	} else {
		$backtrace = debug_backtrace();
		$backtrace = $backtrace[0];

		throw new \Exception( sprintf( 'Undefined template "%s" called from %s:%s', $file, $backtrace['file'], $backtrace['line'] ) );
	}

	ob_start();
	if ( WP_DEBUG ) {
		printf( '<!-- Including template "%s" -->', $file );
	}
	$return = require( $file );
	$data   = ob_get_clean();

	do_action( 'end_operation', 'hm_template_part::' . $file_handle );

	if ( $cache_args ) {
		wp_cache_set( $file, $data, serialize( $cache_args ), 3600 );
	}

	if ( ! empty( $template_args['return'] ) ) {
		if ( $return === false ) {
			return false;
		} else {
			return $data;
		}
	}

	echo $data;

	return false;
}

/**
 * Returns a legacy option as it was set with genesis
 *
 * @param string      $key     The key to retrieve from the setting.
 * @param null|string $setting The settings to retrieve.
 *
 * @return mixed The retrieved option
 */
function get_legacy_option( $key, $setting = null ) {
	$options = get_option( $setting );

	if ( null === $setting || ! is_array( $options ) || ! array_key_exists( $key, $options ) ) {
		return '';
	}

	if ( is_array( $options[ $key ] ) ) {
		return stripslashes_deep( $options[ $key ] );
	} else {
		return stripslashes( wp_kses_decode_entities( $options[ $key ] ) );
	}
}

/**
 * Retrieve a yoast theme option
 *
 * @param string      $key            The key to retrieve.
 * @param null|string $legacy_setting The legacy setting to retrieve the key from.
 *
 * @return mixed The retrieved option
 */
function get_theme_option( $key, $legacy_setting = null ) {
	$options = get_option( 'yst_theme_options' );

	if ( is_array( $options ) && array_key_exists( $key, $options ) ) {
		return $options[ $key ];
	}

	return get_legacy_option( $key, $legacy_setting );
}

/**
 * Simple wrapper around `get_post_meta` to reduce the verbosity
 *
 * @param string $key The key to retrieve the value for.
 *
 * @return mixed The retrieved value.
 */
function post_meta( $key ) {
	return get_post_meta( get_the_ID(), $key, true );
}

/**
 * Outputs a counter for a certain social network
 *
 * @param string $network
 *
 * @return void
 */
function output_social_counter( $network ) {
	$count = trim( get_theme_option( $network, 'child-settings' ) );

	switch ( strlen( $count ) ) {
		case 0:
		case 1:
			return;

		case 2:
		case 3:
			echo '<span class="counter twothird">';
			break;

		default:
			echo '<span class="counter">';
			break;
	}

	echo $count . '</span>';
}

/**
 * Queries for the plugins post type
 *
 * @param array $args Optional. An array of WP_Query arguments.
 *
 * @return \WP_Query
 */
function query_plugins( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'post_type'   => 'yoast_plugins',
		'post_parent' => 0,
	) );

	return new \WP_Query( $args );
}

/**
 * Queries for the edd bundles
 *
 * @param array $args Optional. An array of WP_Query arguments.
 *
 * @return \WP_Query
 */
function query_bundles( $args = array() ) {

	$tax_query = array(
		array(
			'taxonomy' => 'download_category',
			'field'    => 'slug',
			'terms'    => 'bundles',
		),
	);

	if ( isset( $args['query_ebook_bundles'] ) && $args['query_ebook_bundles'] ) {
		$tax_query['relation'] = 'and';
		$tax_query[]           = array(
			'taxonomy' => 'download_category',
			'field'    => 'slug',
			'terms'    => 'books',
		);
	}

	$meta_query = array(
		array(
			'key'     => 'private_bundle',
			'compare' => 'NOT EXISTS',
		),
	);

	$args = wp_parse_args( $args, array(
		'post_type'  => 'download',
		'tax_query'  => $tax_query,
		'meta_query' => $meta_query,
	) );

	return new \WP_Query( $args );
}

/**
 * Queries for categories
 *
 * @param array $args
 *
 * @return array
 */
function query_categories( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'exclude' => '494'
	) );

	return get_categories( $args );
}

/**
 * Queries the must read articles
 *
 * @param array $args
 *
 * @return \WP_Query
 */
function query_must_read_articles( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'orderby'    => 'rand',
		'meta_query' => array(
			array(
				'key'   => 'must_read',
				'value' => 'on',
			),
		),
	) );

	return new \WP_Query( $args );
}

/**
 * Determines whether or not a shortcode is present in the current post
 *
 * @param string $shortcode The shortcode to find.
 *
 * @return bool
 */
function post_has_shortcode( $shortcode ) {
	return false !== strpos( get_the_content(), '[' . $shortcode );
}

/**
 * Returns the latest version for a plugin
 *
 * @return string
 */
function plugin_latest_version() {
	$version = '';

	if ( post_meta( 'plugin' ) ) {
		$version = get_plugin_info( post_meta( 'plugin' ), 'version' );
	}

	if ( post_meta( 'download_id' ) ) {
		$version = get_post_meta( post_meta( 'download_id' ), '_edd_sl_version', true );
	}

	return $version;
}

/**
 * Kills evil scripts in blockquotes, we allow <cite> and <a> tags.
 *
 * @param string $content The content to filter.
 *
 * @return string
 */
function kses_blockquote( $content ) {
	return wp_kses( $content,
		array(
			'a'    => array(
				'class'  => true,
				'href'   => true,
				'target' => true,
			),
			'cite' => array(
				'class' => true,
			),
		)
	);
}

/**
 * Returns the url of an icon for a certain product (This can be free/premium/download), will try to find the icon
 * through references download_ids
 *
 * @param int  $product_id  The product to find the icon for.
 * @param bool $diapositive Whether to retrieve the diapositive version.
 *
 * @return string
 */
function get_product_icon( $product_id = null, $diapositive = false ) {
	$icon = '';

	if ( null === $product_id ) {
		$product_id = get_the_ID();
	}

	$icon_key = 'icon';
	if ( $diapositive ) {
		$icon_key = 'icon_diapositive';
	}

	// If we find the icon just return it.
	if ( get_post_meta( $product_id, $icon_key, true ) ) {
		$icon = get_post_meta( $product_id, $icon_key, true );
	} // If there is a connected download_id, try to get it's icon.
	elseif ( $download_id = get_post_meta( $product_id, 'download_id', true ) ) {
		$icon = get_product_icon( $download_id, $diapositive );
	} // If there is a connected premium product, try to get it's icon.
	elseif ( $premium_id = get_post_meta( $product_id, 'connected_premium_plugin', true ) ) {
		$icon = get_product_icon( $premium_id, $diapositive );
	}

	return $icon;
}

/**
 * Whether or not to skip social buttons on this page.
 *
 * @return bool
 */
function yst_skip_social() {
	static $skip_social;
	if ( ! isset( $skip_social ) ) {
		if ( is_singular() ) {
			global $post;
			if ( get_post_meta( $post->ID, 'no_social', true ) || 'on' === post_meta( 'hide_social_share' ) ) {
				$skip_social = true;
			} else {
				$skip_social = false;
			}
		} else {
			$skip_social = false;
		}
	}

	return $skip_social;
}

/**
 * Returns the twitter handle of the post author
 *
 * @return string
 */
function get_author_twitter() {
	$author = '';

	if ( is_single() ) {
		global $post;

		if ( 1 !== $post->post_author ) {
			$twitter = get_user_meta( $post->post_author, 'twitter', true );
			if ( is_string( $twitter ) && ! empty( $twitter ) ) {
				$author = $twitter;
			}
		}
	}

	return $author;
}

/**
 * Returns via what twitter user the post should be shared
 *
 * @return string The twitter handle of entity that made this post.
 */
function get_twitter_share_via() {
	$via = 'yoast';

	$handle = get_author_twitter();
	if ( $handle ) {
		$via = $handle;
	}

	return $via;
}

/**
 * Returns the twitter related share information
 *
 * @return string
 */
function get_twitter_share_related() {
	$related = 'Yoast_Updates:Update news for all our plugins &amp; themes';

	if ( get_author_twitter() ) {
		$related = 'yoast:Tweets by Joost de Valk, the founder of Yoast,Yoast_Updates:Update news for all our plugins &amp; themes';
	}

	return $related;
}

/**
 * Returns whether or not the current post is the last post in the loop
 *
 * @return bool
 */
function is_last_post() {
	global $wp_query;

	return $wp_query->current_post === ( $wp_query->post_count - 1 );
}
