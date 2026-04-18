<?php

declare(strict_types=1);

/**
 * Fixture migration that records up()/down() calls like
 * Hooks_Recording_Migration, but opts OUT of drop_on_deactivation /
 * drop_on_uninstall so we can assert down() is NOT called when the
 * migration is kept rather than dropped.
 *
 * @package PinkCrab\Perique\Migration
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 */

namespace PinkCrab\Perique\Migration\Tests\Fixtures;

use PinkCrab\Perique\Migration\Migration;
use PinkCrab\Table_Builder\Schema;

class Hooks_Recording_Kept_Migration extends Migration {

	public const TABLE_NAME = 'hooks_recording_kept_migration';

	public static int $up_calls   = 0;
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
		return false;
	}

	public function drop_on_uninstall(): bool {
		return false;
	}

	public function up(): void {
		++self::$up_calls;
	}

	public function down(): void {
		++self::$down_calls;
	}
}
