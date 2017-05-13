<?php
/**
 * Package_Manager_Interface interface.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the package manager interface.
 */
interface Package_Manager_Interface {
	/**
	 * Get a list of all plugins, themes and WordPresses.
	 *
	 * @return Package_Interface[]
	 */
	public function get_packages();

	/**
	 * Get a list of all plugins.
	 *
	 * @return Package_Interface[]
	 */
	public function get_plugins();

	/**
	 * Get a list of all themes.
	 *
	 * @return Package_Inteface[]
	 */
	public function get_themes();

	/**
	 * Get a list of all WordPresses.
	 *
	 * @return Package_Interface[]
	 */
	public function get_wordpresses();
}
