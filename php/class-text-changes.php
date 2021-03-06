<?php
/**
 * @package Yoast\YoastCom
 */

namespace Yoast\YoastCom\Theme;

/**
 * Adds filters to change default WordPress texts
 */
class Text_Changes {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'get_the_archive_title', array( $this, 'filter_archive_title' ) );

		add_filter( 'wpseo_breadcrumb_separator', array( $this, 'filter_breadcrumbs_separator' ) );

		add_filter( 'the_content', array( $this, 'blockquote_full_width' ) );

		add_action( 'wp_editor_expand', array( $this, 'reinstate_editor_for_posts_page' ) );

		add_filter( 'edd_payment_receipt_products_title', array( $this, 'edd_payment_receipt_products_title' ) );

		add_filter( 'oembed_result', array( $this, 'add_youtube_container' ), 10, 3 );
	}

	/**
	 * Make sure we can edit the posts page text
	 */
	public function reinstate_editor_for_posts_page() {
		global $post;
		if ( $post->ID == get_option( 'page_for_posts' ) && $post->post_content === '' ) {
			$post->post_content = ' ';
		}
	}

	/**
	 * Filters the archive title
	 *
	 * @param string $title The previous archive title.
	 *
	 * @return string
	 */
	public function filter_archive_title( $title ) {
		if ( is_home() ) {
			if ( is_front_page() ) {
				$title = __( 'Blog', 'yoastcom' );
			} else {
				$title = get_the_title( get_option( 'page_for_posts' ) );
			}
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$title = single_term_title( '', false );
		}

		if ( is_post_type_archive( 'yoast_dev_article' ) ) {
			$title = __( 'Dev Blog', 'yoastcom' );
		}

		if ( is_search() ) {
			$title = sprintf( __( 'Search for "%s"', 'yoastcom' ), get_search_query() );
		}

		if ( is_author() ) {
			$title = get_the_author();
		}
		return $title;
	}

	/**
	 * Filter the breadcrumbs separator to be a >>
	 *
	 * @return string
	 */
	public function filter_breadcrumbs_separator() {
		return '<span class="sep">&raquo;</span>';
	}

	/**
	 * Breaks block quotes out of the content they're in. We want blockquotes to be full width
	 *
	 * @param string $content Current content of the page.
	 *
	 * @return string
	 */
	public function blockquote_full_width( $content ) {

		$shortcode = new Shortcodes();

		$content = str_replace( '<blockquote>', $shortcode->get_break_out_body() . '<blockquote>', $content );
		$content = str_replace( '</blockquote>', '</blockquote>' . $shortcode->get_restart_body(), $content );

		return $content;
	}

	/**
	 * Add a notice to the receipt that products include the VAT
	 *
	 * @param string $title The current title.
	 *
	 * @return string
	 */
	public function edd_payment_receipt_products_title( $title ) {
		$title .= ' <small>(incl. VAT)</small>';

		return $title;
	}

	/**
	 * Adds responsive video container to youtube auto embed
	 *
	 * @param string $html The current youtube embed HTML.
	 * @param string $url The URL that has been auto embedded.
	 * @param array  $args The auto embed arguments.
	 *
	 * @return string
	 */
	public function add_youtube_container( $html, $url, $args ) {

		if ( false !== strpos( $url, 'youtube' ) ) {
			$html = '<div class="videowrapper">' . $html . '</div>';
		}

		return $html;
	}
}
