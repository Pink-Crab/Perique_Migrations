<?php

declare(strict_types=1);

/**
 * Custom exceptions when creating Migrations.
 *
 * @package PinkCrab\Perique\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Perique\Migration;

use Exception;
use Throwable;

class Migration_Exception extends Exception {

	/**
	 * Returns an exception if a migration can not constructed with DI.
	 *
	 * @code 101
	 * @param string      $migration_class_name
	 * @param string|null $additional_info Optional message from the underlying failure (e.g. DI error), appended to the exception message so it's visible in logs.
	 * @return Migration_Exception
	 */
	public static function failed_to_construct_migration( string $migration_class_name, ?string $additional_info = null ): Migration_Exception {
		$suffix  = $additional_info ? ': ' . $additional_info : '';
		$message = \sprintf( 'Failed to construct %s using the DI Container%s', $migration_class_name, $suffix );
		return new Migration_Exception( $message, 101 );
	}

	/**
	 * Returns an exception for a none Migration (string class name or instance) type
	 *
	 * @code 102
	 * @param string $variable
	 * @return Migration_Exception
	 */
	public static function none_migration_type( string $variable ): Migration_Exception {
		$message = \sprintf(
			'Only valid Migration Class names can be passed as migrations, "%s" was passed to Perique_Migrations::add_module()',
			esc_attr( $variable )
		);
		return new Migration_Exception( $message, 102 );
	}
}
