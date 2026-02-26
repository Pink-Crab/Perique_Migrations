<?php

declare(strict_types=1);

/**
 * Migration fixture that throws in up() for testing failure handling.
 *
 * @package PinkCrab\Perique\Migration\Tests
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 2.0.0
 */

namespace PinkCrab\Perique\Migration\Tests\Fixtures;

use PinkCrab\Table_Builder\Schema;
use PinkCrab\Perique\Migration\Migration;

class Failing_Up_Migration extends Migration {

	public const TABLE_NAME = 'failing_up';

	protected function table_name(): string {
		return self::TABLE_NAME;
	}

	public function schema( Schema $schema_config ): void {
		$schema_config->column( 'id' )->unsigned_int( 11 )->auto_increment();
		$schema_config->index( 'id' )->primary();
	}

	public function seed( array $seeds ): array {
		return array();
	}

	public function up(): void {
		throw new \RuntimeException( 'Migration failed' );
	}

	public function down(): void {}
}
