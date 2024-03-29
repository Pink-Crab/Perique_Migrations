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
	 * @code 101
	 * @return Migration_Exception
	 */
	public static function failed_to_construct_migration( string $migration_class_name ): Migration_Exception {
		$message = \sprintf( 'Failed to construct %s using the DI Container', $migration_class_name );
		return new Migration_Exception( $message, 101 );
	}

	/**
	 * Returns an exception for a none Migration (string class name or instance) type
	 * @code 102
	 * @param string $var
	 * @return Migration_Exception
	 */
	public static function none_migration_type( string $var ): Migration_Exception {
		$message = \sprintf(
			'Only valid Migration Class names can be passed as migrations, "%s" was passed to Perique_Migrations::add_module()',
			esc_attr( $var )
		);
		return new Migration_Exception( $message, 102 );
	}
}
