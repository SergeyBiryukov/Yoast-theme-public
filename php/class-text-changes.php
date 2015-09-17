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
	}

	/**
	 * Filters the archive title
	 *
	 * @param string $title The previous archive title.
	 *
	 * @return string
	 */
	function filter_archive_title( $title ) {
		if ( is_home() && ! is_front_page() ) {
			$title = 'Blog';
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
	function filter_breadcrumbs_separator() {
		return '<span class="sep">&raquo;</span>';
	}

	/**
	 * Breaks block quotes out of the content they're in. We want blockquotes to be full width
	 *
	 * @param string $content Current content of the page.
	 *
	 * @return string
	 */
	function blockquote_full_width( $content ) {

		$shortcode = new Shortcodes();

		$content = str_replace( '<blockquote>', $shortcode->get_break_out_body() . '<blockquote>', $content );
		$content = str_replace( '</blockquote>', '</blockquote>' . $shortcode->get_restart_body(), $content );

		return $content;
	}
}
