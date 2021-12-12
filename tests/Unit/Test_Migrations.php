<?php

declare(strict_types=1);

/**
 * Unit tests for the main Migrations service.
 *
 * @since 0.1.0
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\Migration\Tests\Unit\Migration;

use WP_UnitTestCase;
use PinkCrab\Migration\Migrations;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\DB_Migration\Migration_Manager;
use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\Migration\Tests\Helpers\App_Helper_Trait;
use PinkCrab\Plugin_Lifecycle\Plugin_State_Controller;
use PinkCrab\Migration\Tests\Fixtures\Has_Seeds_Migration;
use PinkCrab\Migration\Tests\Fixtures\Simple_Table_Migration;

class Test_Migrations extends WP_UnitTestCase {

	use App_Helper_Trait;

	/**
	 * Holds the mocked version of Perique
	 *
	 * @var App
	 */
	public static $app_instance;

	public static $plugin_state_controller;

	/**
	 * Sets up instance of Perique App
	 * Only loaded with basic DI Rules.
	 */
	public function setUp() {
		parent::setUp();
		self::$app_instance            = ( new App_Factory() )->with_wp_dice()->boot();
		self::$plugin_state_controller = new Plugin_State_Controller( self::$app_instance, __FILE__ );
		$GLOBALS['wp_filter']          = array();
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

	/** @testdox When the service is costructed, the DI container should be set as property from Plugin State Controllers helper method. */
	public function test_di_container_set_at_construct(): void {
		$migrations = new Migrations( self::$plugin_state_controller );
		$this->assertSame( Objects::get_property( $migrations, 'di_container' ), self::$plugin_state_controller->get_app()->get_container() );
	}

	/** @testdox It should be possible to set the migration log key, both at construct and via setter method. */
	public function test_set_migration_log_key(): void {
		$migrations = new Migrations( self::$plugin_state_controller, 'at_const' );
		$this->assertEquals( 'at_const', Objects::get_property( $migrations, 'migration_log_key' ) );

		$migrations->set_migration_log_key( 'set_with_method' );
		$this->assertEquals( 'set_with_method', Objects::get_property( $migrations, 'migration_log_key' ) );
	}

	/** @testdox It should be possible to pass a constructed migration instance to be processed. */
	public function test_add_migration_as_instance(): void {
		$migrations         = new Migrations( self::$plugin_state_controller );
		$migration_instance = new Has_Seeds_Migration();
		$migrations->add_migration( $migration_instance );

		$this->assertCount( 1, $migrations->get_migrations() );
		$this->assertSame( $migrations->get_migrations()[0], $migration_instance );
	}

	/** @testdox It should be possible to pass an unconstructed migration class by its string (class name) to be constructed via DI and then processed. */
	public function test_add_migration_as_class_string(): void {
		$migrations = new Migrations( self::$plugin_state_controller );
		$migrations->add_migration( Simple_Table_Migration::class );

		$this->assertCount( 1, $migrations->get_migrations() );
		$this->assertInstanceOf( Simple_Table_Migration::class, $migrations->get_migrations()[0] );
	}

	/** @testdox If a custom migration manager is not defined, a fallback should be used when calling done() at the end of the setup. */
	public function test_migration_manager_added_at_done_if_not_set(): void {
		$migrations = new Migrations( self::$plugin_state_controller );
		$this->assertNull( Objects::get_property( $migrations, 'migration_manager' ) );

		// Run done, should see the migration manager set.
		$migrations->done();
		$this->assertInstanceOf( Migration_Manager::class, Objects::get_property( $migrations, 'migration_manager' ) );
	}

    /** @testdox When a migration manager is defined, the fallback should not be set when calling done() */
	public function test_custom_migration_manager_instance(): void {
		$migrations = new Migrations( self::$plugin_state_controller );

		$mock_manager = $this->createMock( Migration_Manager::class );
		$migrations->set_migration_manager( $mock_manager );
		$this->assertSame( $mock_manager, Objects::get_property( $migrations, 'migration_manager' ) );

		// Should not be set when running done as already set.
		$migrations->done();
		$this->assertSame( $mock_manager, Objects::get_property( $migrations, 'migration_manager' ) );

	}





}
