<?php

declare(strict_types=1);

/**
 * Provider for generating stubs and mock for the Migration Manager
 *
 * @package PinkCrab\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Migration\Tests\Fixtures\Data_Providers;

use PinkCrab\Table_Builder\Schema;
use PinkCrab\Table_Builder\Builder;
use PinkCrab\Table_Builder\Engines\Engine;
use PinkCrab\DB_Migration\Migration_Manager;

class Migration_Manager_Provider {

	/**
	 * Create a migation manager with a mocked builder that logs all calls made
	 *
	 * @param string $log_key
	 * @param \wpdb|null $wpdb instance, will use global if not passed.
	 * @return array{migration_manger:Migration_Manager,engine:Engine}
	 */
	public function with_logging_table_builder( string $log_key, ?\wpdb $wpdb = null ): array {
		$engine  = new class() implements Engine{
				public $events = array();
			public function create_table( Schema $schema ): bool {
				$this->events['create'][] = $schema;
				return true;
			}
			public function drop_table( Schema $schema ): bool {
				$this->events['drop'][] = $schema;
				return true;
			}
		};
		$builder = new Builder( $engine );

		return array(
			'migration_manger' => new Migration_Manager( $builder, $wpdb ?? $GLOBALS['wpdb'], $log_key ),
			'engine'           => $engine,
		);
	}
}
