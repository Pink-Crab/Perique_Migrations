<?php

declare(strict_types=1);

/**
 * Mock class used for activation which writes to options table
 *
 * @package PinkCrab\Migration\Migration
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Migration\Tests\Fixtures\Migration;

use PinkCrab\Migration\Migration\Migration;
use PinkCrab\Table_Builder\Schema;



class Simple_Table_Migration extends Migration {

	public const TABLE_NAME = 'simple_table_migration';

	protected function table_name(): string {
		return self::TABLE_NAME;
	}
	/**
	 * Defines the schema for the migration.
	 *
	 * @param Schema $schema_config
	 * @return void
	 */
	public function schema( Schema $schema_config ): void {
		$schema_config->column( 'id' )->unsigned_int( 11 )->auto_increment();
		$schema_config->column( 'user' )->int( 11 );
		$schema_config->index('id')->primary();
	}
}
