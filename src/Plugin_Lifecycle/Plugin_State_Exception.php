<?php

declare(strict_types=1);

/**
 * Custom exceptions for handling PLugin State Changes.
 *
 * @package PinkCrab\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Migration\Plugin_Lifecycle;

use Exception;
use Throwable;

class Plugin_State_Exception extends Exception {

	/**
	 * Returns an exception if an event can not constructed with DI.
	 * @code 101
	 * @return App_Initialization_Exception
	 */
	public static function failed_to_create_state_change_event( $event, ?Throwable $th = null ): Plugin_State_Exception {
		$message = \sprintf( 'Failed to construct %s using the DI Container. %s', get_class( $event ), $th->getMessage() ?? '' );
		return new Plugin_State_Exception( $message, 101 );
	}

	/**
	 * Returns an exception for adding a none event change class
	 * @code 102
	 * @return App_Initialization_Exception
	 */
	public static function invalid_state_change_event_type( $class ): Plugin_State_Exception {
		$message = \sprintf( '$s is not a valid Plugin State Change Event class', is_string( $class ) ? $class : get_class( $class ) );
		return new Plugin_State_Exception( $message, 102 );
	}
}
