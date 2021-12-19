<?php

declare(strict_types=1);

/**
 * Unit tests for the main Migrations service.
 *
 * @since 0.1.0
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\Perique\Migration\Tests\Integration;

use WP_UnitTestCase;
use PinkCrab\Perique\Migration\Migrations;
use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\Plugin_Lifecycle\Plugin_State_Controller;
use PinkCrab\FunctionConstructors\GeneralFunctions as Func;
use PinkCrab\Perique\Migration\Tests\Helpers\App_Helper_Trait;
use PinkCrab\Perique\Migration\Tests\Fixtures\Has_Seeds_Migration;
use PinkCrab\Perique\Migration\Tests\Fixtures\Simple_Table_Migration;
use PinkCrab\Perique\Migration\Tests\Fixtures\Has_Seeds_Migration_But_Disabled;

class Test_Manually_Called_Lifecylce_Events extends WP_UnitTestCase {

	use App_Helper_Trait;

	public static $app_instance;
	public static $wpdb;

	/**
	 * Sets up instance of Perique App
	 * Only loaded with basic DI Rules.
	 */
	public function setUp() {
		parent::setUp();
		self::$app_instance    = ( new App_Factory() )->with_wp_dice()->boot();
		$GLOBALS['wp_filter']  = array();
		$GLOBALS['wp_actions'] = array();
		self::$wpdb            = $GLOBALS['wpdb'];
	}

	/**
	 * Unsets the app instance, to be rebuilt next time.
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		$this->unset_app_instance();

		// Clear all hooks used.
		$GLOBALS['wp_actions'] = array();
		$GLOBALS['wp_filter']  = array();
		\delete_option( 'uninstall_plugins' );
	}

    /** @testdox [INT] It should be possible to define migrations at which will be created and have seed data populated when the activation hook is called (MIMIC'S ACTIVATION PROCESS) */
	public function test_create_and_seed_on_activation() {
		// Create migrations and state controller.
		$plugin_state_controller = new Plugin_State_Controller( self::$app_instance, __FILE__ );
		$migrations              = new Migrations( $plugin_state_controller, 'test_create_and_seed_on_activation' );

		// Populate the migrations
		$migration_a = new Simple_Table_Migration();           // No seed data
		$migration_b = new Has_Seeds_Migration();              // Has seed data, should create
		$migration_c = new Has_Seeds_Migration_But_Disabled(); // Has seed data, but should not create.
		$migrations->add_migration( $migration_a );
		$migrations->add_migration( $migration_b );
		$migrations->add_migration( $migration_c );

		$migrations->done();
		$plugin_state_controller->finalise();

		// Run mock plugin activation
		\do_action( 'activate_' . ltrim( __FILE__, '/' ) );

		// Check tables and seed data created for each table.

        // CREATE WITH NO SEEDED DATA 
		$simple_table_columns = self::$wpdb->get_results( "SHOW COLUMNS FROM {$migration_a->get_table_name()};" );
		$this->assertCount( 2, $simple_table_columns );
		$this->assertSame( array( 'id', 'user' ), array_map( Func\getProperty( 'Field' ), $simple_table_columns ) );
		$simple_table_rows = self::$wpdb->get_results( "SELECT * FROM {$migration_a->get_table_name()};" );
		$this->assertCount( 0, $simple_table_rows );

        // CREATE WITH SEEDED DATA
		$has_seeds_columns = self::$wpdb->get_results( "SHOW COLUMNS FROM {$migration_b->get_table_name()};" );
		$this->assertCount( 2, $has_seeds_columns );
		$this->assertSame( array( 'id', 'user' ), array_map( Func\getProperty( 'Field' ), $has_seeds_columns ) );
		$has_seeds_rows = self::$wpdb->get_results( "SELECT * FROM {$migration_b->get_table_name()};" );
		$this->assertCount( 2, $has_seeds_rows );
		$this->assertSame( array( 'Alpha', 'Bravo' ), array_map( Func\getProperty( 'user' ), $has_seeds_rows ) );

        // CREATE WITH SEED DATA, BUT TOLD TO IGNORE
		$has_but_ignored_seeds_columns = self::$wpdb->get_results( "SHOW COLUMNS FROM {$migration_c->get_table_name()};" );
		$this->assertCount( 2, $has_but_ignored_seeds_columns );
		$this->assertSame( array( 'bar', 'foo' ), array_map( Func\getProperty( 'Field' ), $has_but_ignored_seeds_columns ) );
		$has_but_ignored_seeds_rows = self::$wpdb->get_results( "SELECT * FROM {$migration_c->get_table_name()};" );
		$this->assertCount( 0, $has_but_ignored_seeds_rows );
	}
}