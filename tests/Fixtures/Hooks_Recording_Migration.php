<?php

declare(strict_types=1);

/**
 * Fixture migration that records when its up() and down() hooks fire, so
 * tests can assert the framework calls them at the right lifecycle points.
 *
 * @package PinkCrab\Perique\Migration
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 */

namespace PinkCrab\Perique\Migration\Tests\Fixtures;

use PinkCrab\Perique\Migration\Migration;
use PinkCrab\Table_Builder\Schema;

class Hooks_Recording_Migration extends Migration {

	public const TABLE_NAME = 'hooks_recording_migration';

	/**
	 * Counts up() invocations across all instances of this class (including
	 * ones instantiated separately by the DI container in different tests).
	 *
	 * @var int
	 */
	public static int $up_calls = 0;

	/**
	 * Counts down() invocations across all instances.
	 *
	 * @var int
	 */
	public static int $down_calls = 0;

	public static function reset(): void {
		self::$up_calls   = 0;
		self::$down_calls = 0;
	}

	protected function table_name(): string {
		return self::TABLE_NAME;
	}

	public function schema( Schema $schema_config ): void {
		$schema_config->column( 'id' )->unsigned_int( 11 )->auto_increment();
		$schema_config->index( 'id' )->primary();
	}

	public function drop_on_deactivation(): bool {
		return true;
	}

	public function drop_on_uninstall(): bool {
		return true;
	}

	public function up(): void {
		++self::$up_calls;
	}

	public function down(): void {
		++self::$down_calls;
	}
}
