<?php
/**
 * Plugin Name: WP Use Forwarded URLs
 * Plugin URI: http://www.skyverge.com
 * Description: Automatically use forwarded URLs if ya got 'em
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com
 * Version: 0.1
 *
 * Copyright: (c) 2011-2014 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-2.1.3-1.html
 *
 * @package   WP-Use-Forwarded-URLs
 * @author    SkyVerge
 * @category  Tools
 * @copyright Copyright (c) 2011-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// exit if accessed directly
defined( 'ABSPATH' ) or exit;

/**
 * Forwarded URLs Class
 *
 * Note: You really should not use this plugin in production as it could
 * have unexpected results when filtering content URLs
 *
 * @since 0.1
 */
class WP_Use_Forwarded_URLs {


	/** @var string non-forwarded host as defined in the siteurl option */
	public $non_forwarded_host;


	/**
	 * Setup actions
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// bail when not forwarding
		if ( ! $this->has_forwarded_host() ) {
			return;
		}

		// save for URL replacement
		$this->non_forwarded_host = parse_url( get_option( 'siteurl' ), PHP_URL_HOST );

		// from https://github.com/50east/wp-forwarded-host-urls/
		$filters = array(
			'post_link',
			'post_type_link',
			'page_link',
			'attachment_link',
			'get_shortlink',
			'post_type_archive_link',
			'get_pagenum_link',
			'get_comments_pagenum_link',
			'term_link',
			'search_link',
			'day_link',
			'month_link',
			'year_link',
			'option_siteurl',
			'blog_option_siteurl',
			'option_home',
			'admin_url',
			'home_url',
			'includes_url',
			'site_url',
			'site_option_siteurl',
			'network_home_url',
			'network_site_url',
			'get_the_author_url',
			'get_comment_link',
			'wp_get_attachment_image_src',
			'wp_get_attachment_thumb_url',
			'wp_get_attachment_url',
			'wp_login_url',
			'wp_logout_url',
			'wp_lostpassword_url',
			'get_stylesheet_uri',
			'get_locale_stylesheet_uri',
			'script_loader_src',
			'style_loader_src',
			'get_theme_root_uri',
			'stylesheet_uri',
			'template_directory_uri',
			'stylesheet_directory_uri',
			'the_content',
			'the_content_pre',
		);

		foreach ( $filters as $filter ) {
			add_filter( $filter, array( $this, 'replace_with_forwarded_url' ) );
		}

		// prevent redirection
		add_filter( 'redirect_canonical', '__return_false' );
	}


	/**
	 * Returns true if forwarding URLs
	 *
	 * @return bool
	 */
	private function has_forwarded_host() {

		return array_key_exists( 'HTTP_X_FORWARDED_HOST', $_SERVER );
	}


	/**
	 * Returns the forwarded host
	 *
	 * @return string
	 */
	public function get_forwarded_host() {

		return $_SERVER['HTTP_X_FORWARDED_HOST'];
	}


	/**
	 * Replace incoming content with non-forwarded URLs converted to
	 * the forwarded URL
	 *
	 * Note this doesn't attempt to convert protocols, instead it relies on
	 * WordPress handling protocol changes properly
	 *
	 * @param $content
	 * @return mixed
	 */
	public function replace_with_forwarded_url( $content ) {

		$non_forwarded_host = $this->non_forwarded_host;
		$forwarded_host     = $this->get_forwarded_host();

		// http, https and protocol-less URLs
		$find_replace = array(
			"http://{$non_forwarded_host}"  => "http://{$forwarded_host}",
			"https://{$non_forwarded_host}" => "https://{$forwarded_host}",
			"//{$non_forwarded_host}"       => "//{$forwarded_host}",
		);

		return str_replace( array_keys( $find_replace ), array_values( $find_replace ), $content );
	}


} // end \WP_Use_Forwarded_URLs class


/**
 * The WP_Use_Forwarded_URLs global object
 * @name $wp_use_forwarded_urls
 * @global WP_Use_Forwarded_URLs $GLOBALS['wp_use_forwarded_urls']
 */
$GLOBALS['wp_use_forwarded_urls'] = new WP_Use_Forwarded_URLs();
