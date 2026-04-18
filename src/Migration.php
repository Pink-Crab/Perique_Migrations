<?php

declare(strict_types=1);

/**
 * Abstract class for all Migrations
 *
 * @package PinkCrab\Perique\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Perique\Migration;

use PinkCrab\DB_Migration\Database_Migration;
use PinkCrab\Table_Builder\Schema;

/**
 * Abstract class for all Migrations
 *
 * @method void schema(\PinkCrab\Table_Builder\Schema $schema)
 */
abstract class Migration extends Database_Migration {

	/**
	 * Creates an instance of the migration.
	 */
	public function __construct() {
		$this->table_name = $this->table_name();
		$this->schema     = new Schema( $this->table_name, array( $this, 'schema' ) );
		$this->seed_data  = $this->seed( array() );
	}

	/**
	 * Must return the table name for the migration.
	 *
	 * @return string
	 */
	abstract protected function table_name(): string;

	/**
	 * Is this table dropped on deactivation
	 *
	 * Defaults to false.
	 *
	 * @return bool
	 */
	public function drop_on_deactivation(): bool {
		return false;
	}

	/**
	 * Drop table on uninstall.
	 *
	 * Defaults to false.
	 *
	 * @return bool
	 */
	public function drop_on_uninstall(): bool {
		return false;
	}

	/**
	 * Should this migration be seeded on activation.
	 *
	 * Defaults to true.
	 *
	 * @return bool
	 */
	public function seed_on_inital_activation(): bool {
		return true;
	}

	/**
	 * Hook fired on every activation, after the table has been upserted and
	 * before it is seeded. Default implementation is a no-op; override it to
	 * run custom SQL (e.g. ALTER statements on an existing table installed by
	 * an earlier migration) or any other post-upsert work.
	 *
	 * Implementations MUST be idempotent — this method fires on every
	 * activation, not just on first install.
	 *
	 * @return void
	 */
	public function up(): void {
	}

	/**
	 * Hook fired just before the migration's table is dropped by the
	 * Deactivation or Uninstall event handlers. Only called for migrations
	 * that are actually being dropped (i.e. drop_on_deactivation() or
	 * drop_on_uninstall() returned true). Default implementation is a no-op;
	 * override it to perform custom teardown while the table still exists.
	 *
	 * @return void
	 */
	public function down(): void {
	}
}
