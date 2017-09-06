<?php
/**
 * Client_Interface interface.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the client interface.
 */
interface Client_Interface {
	public function check( Package_Interface $package );
}
