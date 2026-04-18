<?php

declare(strict_types=1);

/**
 * Fixture for the "second migration alters first" integration scenario.
 * A minimal table owned exclusively by the hooks integration test, so the
 * ALTER in Alter_First_Table_Via_Up_Migration::up() can't leak DDL into
 * other tests' expectations of the shared Simple_Table_Migration.
 *
 * @package PinkCrab\Perique\Migration
 */

namespace PinkCrab\Perique\Migration\Tests\Fixtures;

use PinkCrab\Perique\Migration\Migration;
use PinkCrab\Table_Builder\Schema;

class Hooks_First_Table_Migration extends Migration {

	public const TABLE_NAME = 'hooks_first_table_migration';

	protected function table_name(): string {
		return self::TABLE_NAME;
	}

	public function schema( Schema $schema_config ): void {
		$schema_config->column( 'id' )->unsigned_int( 11 )->auto_increment();
		$schema_config->index( 'id' )->primary();
	}
}
