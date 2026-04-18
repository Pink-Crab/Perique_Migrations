<?php

declare(strict_types=1);

/**
 * Fixture for the "second migration alters first" integration scenario.
 * Has its own tiny table plus an up() hook that ALTERs the table owned by
 * Hooks_First_Table_Migration, mimicking a new migration shipped in a later
 * plugin version that needs to evolve a pre-existing table's schema.
 *
 * @package PinkCrab\Perique\Migration
 */

namespace PinkCrab\Perique\Migration\Tests\Fixtures;

use PinkCrab\Perique\Migration\Migration;
use PinkCrab\Table_Builder\Schema;

class Alter_First_Table_Via_Up_Migration extends Migration {

	public const TABLE_NAME = 'alter_first_table_via_up_migration';

	protected function table_name(): string {
		return self::TABLE_NAME;
	}

	public function schema( Schema $schema_config ): void {
		$schema_config->column( 'id' )->unsigned_int( 11 )->auto_increment();
		$schema_config->index( 'id' )->primary();
	}

	public function up(): void {
		global $wpdb;
		$target = Hooks_First_Table_Migration::TABLE_NAME;

		// Idempotency guard: only add the column if it's not there yet so
		// re-activation is safe.
		$column_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s',
				DB_NAME,
				$target,
				'email'
			)
		);
		if ( (int) $column_exists === 0 ) {
			$wpdb->query( "ALTER TABLE {$target} ADD COLUMN email VARCHAR(255) NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}
}
