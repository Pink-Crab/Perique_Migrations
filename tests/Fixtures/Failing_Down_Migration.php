<?php

declare(strict_types=1);

/**
 * Migration fixture that throws in down() for testing failure handling.
 *
 * @package PinkCrab\Perique\Migration\Tests
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 2.0.0
 */

namespace PinkCrab\Perique\Migration\Tests\Fixtures;

use PinkCrab\Table_Builder\Schema;
use PinkCrab\Perique\Migration\Migration;
use PinkCrab\Perique\Migration\Tests\Helpers\Migration_Call_Log;

class Failing_Down_Migration extends Migration {

	public const TABLE_NAME = 'failing_down';

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
		Migration_Call_Log::log( static::class, 'up' );
	}

	public function down(): void {
		Migration_Call_Log::log( static::class, 'down' );
		throw new \RuntimeException( 'Down migration failed' );
	}
}
