<?php

declare(strict_types=1);

/**
 * Abstract class for all Migrations
 *
 * @package PinkCrab\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Migration\Migration;

use PinkCrab\Perique\Services\Container_Aware_Traits\Inject_DI_Container_Aware;
use PinkCrab\DB_Migration\Database_Migration;
use PinkCrab\Perique\Interfaces\Inject_DI_Container;
use PinkCrab\Table_Builder\Schema;


abstract class Migration extends Database_Migration implements Inject_DI_Container {

	/**
	 * Gives access to the DI Container.
	 */
	use Inject_DI_Container_Aware;

	public function __construct() {
		$this->table_name = $this->table_name();
		$this->schema     = new Schema( $this->table_name, array( $this, 'schema' ) );
		$this->seed_data  = $this->seed( array() );
	}

	abstract protected function table_name(): string;

	/**
	 * Defines the schema for the migration.
	 *
	 * @param Schema $schema_config
	 * @return void
	 */
	abstract public function schema( Schema $schema_config ): void;

	/**
	 * Is this table dropped on deactivation
	 *
	 * @return bool
	 */
	public function drop_on_deactivation(): bool {
		return false;
	}

	/**
	 * Should this migration be seeded on activation.
	 * Defaults to true.
	 *
	 * @return bool
	 */
	public function seed_on_inital_activation(): bool {
		return true;
	}
}
