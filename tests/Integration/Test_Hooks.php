<?php

declare(strict_types=1);

/**
 * Integration tests for the up()/down() migration lifecycle hooks.
 *
 * @since 2.2.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\Perique\Migration\Tests\Integration;

use WP_UnitTestCase;
use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\Plugin_Lifecycle\Plugin_Life_Cycle;
use PinkCrab\Perique\Migration\Module\Perique_Migrations;
use PinkCrab\Perique\Migration\Tests\Helpers\App_Helper_Trait;
use PinkCrab\Perique\Migration\Tests\Fixtures\Hooks_First_Table_Migration;
use PinkCrab\Perique\Migration\Tests\Fixtures\Hooks_Recording_Migration;
use PinkCrab\Perique\Migration\Tests\Fixtures\Hooks_Recording_Kept_Migration;
use PinkCrab\Perique\Migration\Tests\Fixtures\Alter_First_Table_Via_Up_Migration;

class Test_Hooks extends WP_UnitTestCase {

	use App_Helper_Trait;

	public static $app_instance;
	public static $wpdb;

	public function setUp(): void {
		parent::setUp();
		self::$app_instance    = ( new App_Factory() )->default_setup();
		$GLOBALS['wp_filter']  = array();
		$GLOBALS['wp_actions'] = array();
		self::$wpdb            = $GLOBALS['wpdb'];
		self::$wpdb->suppress_errors( true );

		// Fresh counters per test.
		Hooks_Recording_Migration::reset();
		Hooks_Recording_Kept_Migration::reset();
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->unset_app_instance();
		$GLOBALS['wp_actions'] = array();
		$GLOBALS['wp_filter']  = array();
		\delete_option( 'uninstall_plugins' );
		self::$wpdb->suppress_errors( false );
	}

	/** @testdox [INT] Migration::up() fires once per migration on activation, after the table has been upserted. */
	public function test_up_fires_on_activation(): void {
		self::$app_instance
			->module(
				Plugin_Life_Cycle::class,
				fn( Plugin_Life_Cycle $e) => $e->plugin_base_file( __FILE__ )
			)
			->module(
				Perique_Migrations::class,
				fn( Perique_Migrations $e) => $e
					->set_migration_log_key( 'test_up_fires_on_activation' )
					->add_migration( Hooks_Recording_Migration::class )
			)
			->boot();

		\do_action( 'activate_' . ltrim( __FILE__, '/' ) );

		$this->assertSame( 1, Hooks_Recording_Migration::$up_calls );
		$this->assertSame( 0, Hooks_Recording_Migration::$down_calls );
	}

	/** @testdox [INT] Migration::down() fires on deactivation for migrations flagged drop_on_deactivation, and NOT for migrations that stay. */
	public function test_down_fires_on_deactivation_only_for_dropped_migrations(): void {
		self::$app_instance
			->module(
				Plugin_Life_Cycle::class,
				fn( Plugin_Life_Cycle $e) => $e->plugin_base_file( __FILE__ )
			)
			->module(
				Perique_Migrations::class,
				fn( Perique_Migrations $e) => $e
					->set_migration_log_key( 'test_down_fires_on_deactivation' )
					->add_migration( Hooks_Recording_Migration::class )      // drops on deactivation
					->add_migration( Hooks_Recording_Kept_Migration::class ) // kept
			)
			->boot();

		\do_action( 'activate_' . ltrim( __FILE__, '/' ) );
		\do_action( 'deactivate_' . ltrim( __FILE__, '/' ) );

		$this->assertSame( 1, Hooks_Recording_Migration::$down_calls, 'down() should fire for a migration being dropped' );
		$this->assertSame( 0, Hooks_Recording_Kept_Migration::$down_calls, 'down() must NOT fire for a migration that is kept' );
	}

	/** @testdox [INT] Migration::down() fires on uninstall for migrations flagged drop_on_uninstall, and NOT for migrations that stay. */
	public function test_down_fires_on_uninstall_only_for_dropped_migrations(): void {
		self::$app_instance
			->module(
				Plugin_Life_Cycle::class,
				fn( Plugin_Life_Cycle $e) => $e->plugin_base_file( __FILE__ )
			)
			->module(
				Perique_Migrations::class,
				fn( Perique_Migrations $e) => $e
					->set_migration_log_key( 'test_down_fires_on_uninstall' )
					->add_migration( Hooks_Recording_Migration::class )      // drops on uninstall
					->add_migration( Hooks_Recording_Kept_Migration::class ) // kept
			)
			->boot();

		\do_action( 'activate_' . ltrim( __FILE__, '/' ) );

		// Reset counters so we only see uninstall contribution.
		Hooks_Recording_Migration::reset();
		Hooks_Recording_Kept_Migration::reset();

		// Invoke the registered uninstall callback directly (what WP does on uninstall).
		$uninstall_hooks = \get_option( 'uninstall_plugins' );
		$plugin_file     = ltrim( __FILE__, '/' );
		if ( isset( $uninstall_hooks[ $plugin_file ] ) && is_callable( $uninstall_hooks[ $plugin_file ] ) ) {
			\call_user_func( $uninstall_hooks[ $plugin_file ] );
		} else {
			$this->fail( 'No uninstall hook registered for ' . $plugin_file );
		}

		$this->assertSame( 1, Hooks_Recording_Migration::$down_calls, 'down() should fire for a migration being dropped on uninstall' );
		$this->assertSame( 0, Hooks_Recording_Kept_Migration::$down_calls, 'down() must NOT fire for a migration that is kept on uninstall' );
	}

	/** @testdox [INT] A second migration can use up() to ALTER a table owned by an earlier migration — the canonical "new migration in a later plugin version" scenario. */
	public function test_second_migration_up_alters_first_migration_table(): void {
		self::$app_instance
			->module(
				Plugin_Life_Cycle::class,
				fn( Plugin_Life_Cycle $e) => $e->plugin_base_file( __FILE__ )
			)
			->module(
				Perique_Migrations::class,
				fn( Perique_Migrations $e) => $e
					->set_migration_log_key( 'test_second_migration_up_alters_first_table' )
					->add_migration( Hooks_First_Table_Migration::class )
					->add_migration( Alter_First_Table_Via_Up_Migration::class )
			)
			->boot();

		\do_action( 'activate_' . ltrim( __FILE__, '/' ) );

		$first_table = Hooks_First_Table_Migration::TABLE_NAME;
		$columns     = self::$wpdb->get_results( "SHOW COLUMNS FROM {$first_table};" );
		$names       = array_map( fn( $c) => $c->Field, $columns );

		$this->assertContains( 'email', $names, 'Alter_First_Table_Via_Up_Migration::up() should have added an "email" column to the first migration\'s table.' );

		// Tidy up the DDL so other tests don't inherit an altered table.
		// ALTER is auto-committed by MySQL so WP's transaction rollback won't clean it.
		self::$wpdb->query( "DROP TABLE IF EXISTS {$first_table}" );
		self::$wpdb->query( 'DROP TABLE IF EXISTS ' . Alter_First_Table_Via_Up_Migration::TABLE_NAME );
	}
}
