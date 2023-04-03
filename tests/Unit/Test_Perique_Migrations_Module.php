<?php

declare(strict_types=1);

/**
 * Unit tests for the main Migrations service.
 *
 * @since 0.1.0
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\Perique\Migration\Tests\Unit;

use WP_UnitTestCase;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Perique\Application\Hooks;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Perique\Interfaces\DI_Container;
use PinkCrab\Perique\Migration\Event\Uninstall;
use PinkCrab\Perique\Migration\Event\Activation;
use PinkCrab\Plugin_Lifecycle\Plugin_Life_Cycle;
use PinkCrab\Perique\Migration\Event\Deactivation;
use PinkCrab\Perique\Migration\Module\Perique_Migrations;

class Test_Perique_Migrations_Module extends WP_UnitTestCase {

	public function assertArrayHasObjectOfType( $type, $array, $message = '' ) {
		$found = false;
		foreach ( $array as $obj ) {
			if ( get_class( $obj ) === $type ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, $message );
	}

	/** @testdox It should be possible to create an instance of the Module, without passing a log key */
	public function test_can_create_instance_without_log_key(): void {
		$module = new Perique_Migrations();

		// Without setting, use default.
		$this->assertSame( 'pinkcrab_migration_log', $module->get_migration_log_key() );

		// Set and check.
		$module->set_migration_log_key( 'test_log_key' );
		$this->assertSame( 'test_log_key', $module->get_migration_log_key() );
	}

	/** @testdox It should be possible to add a valid Migration class by its name. */
	public function test_can_add_migration_by_name(): void {
		$module = new Perique_Migrations();
		$module->add_migration( 'PinkCrab\Perique\Migration\Tests\Fixtures\Simple_Table_Migration' );
		$this->assertCount( 1, Objects::get_property( $module, 'migrations' ) );
	}

	/** @testdox Attempting to add a none migrations class, should result in a Migration_Exception being thrown [CODE 102] */
	public function test_adding_none_migration_class_throws_exception(): void {
		$this->expectException( \PinkCrab\Perique\Migration\Migration_Exception::class );
		$this->expectExceptionCode( 102 );
		$this->expectExceptionMessage( 'Only valid Migration Class names can be passed as migrations, "Not a class" was passed to Perique_Migrations::add_module()' );

		$module = new Perique_Migrations();
		$module->add_migration( 'Not a class' );
	}

	/** @testdox When the Plugin Life Cycle process is run, the Migrations instances should be created via the container and passed as the events  */
	public function test_migrations_are_created_via_container(): void {
		// Mocks
		$app_config = new App_Config();
		$loader     = $this->createMock( 'PinkCrab\Loader\Hook_Loader' );
		$container  = $this->createMock( DI_Container::class );
		$container->method( 'create' )
			->willReturnCallback( fn( $class_name ) => new $class_name() );

		// Create the module and add migration
		$module = new Perique_Migrations();
		$module->set_migration_log_key( 'test_log_key' );
		$module->add_migration( 'PinkCrab\Perique\Migration\Tests\Fixtures\Simple_Table_Migration' );

		// Run pre_boot
		$module->pre_boot( $app_config, $loader, $container );
		$events = apply_filters( Plugin_Life_Cycle::EVENT_LIST, array() );

		$this->assertCount( 3, $events );

		$this->assertArrayHasObjectOfType( Activation::class, $events );
		$this->assertArrayHasObjectOfType( Deactivation::class, $events );
		$this->assertArrayHasObjectOfType( Uninstall::class, $events );
	}

	/** @testdox If the container throws an exception creating the instance of a migration, a Migration_Exception should be thrown [CODE 101] */
	public function test_container_cant_create_migration_throws_exception(): void {
		$migration = 'PinkCrab\Perique\Migration\Tests\Fixtures\Simple_Table_Migration';
        
        $this->expectException( \PinkCrab\Perique\Migration\Migration_Exception::class );
		$this->expectExceptionCode( 101 );
		$this->expectExceptionMessage( "Failed to construct {$migration} using the DI Container" );

		// Mocks
		$app_config = new App_Config();
		$loader     = $this->createMock( 'PinkCrab\Loader\Hook_Loader' );
		$container  = $this->createMock( DI_Container::class );
		$container->method( 'create' )
			->willReturnCallback( function( $class_name ) { throw new \Exception();} );

        // Create the module and add migration
        $module = new Perique_Migrations();
        $module->set_migration_log_key( 'test_log_key' );
        $module->add_migration( $migration );

        // Run pre_boot
        $module->pre_boot( $app_config, $loader, $container );
	}

	/** @testdox If the container can not create a valid instance of a migration (NONE OBJECT), a Migration_Exception should be thrown [CODE 101] */
    public function test_container_cant_create_valid_migration_throws_exception(): void {
        $migration = 'PinkCrab\Perique\Migration\Tests\Fixtures\Simple_Table_Migration';
        
        $this->expectException( \PinkCrab\Perique\Migration\Migration_Exception::class );
        $this->expectExceptionCode( 101 );
        $this->expectExceptionMessage( "Failed to construct {$migration} using the DI Container" );

        // Mocks
        $app_config = new App_Config();
        $loader     = $this->createMock( 'PinkCrab\Loader\Hook_Loader' );
        $container  = $this->createMock( DI_Container::class );
        $container->method( 'create' )
            ->willReturnCallback( function( $class_name ) { return 'Not an object';} );

        // Create the module and add migration
        $module = new Perique_Migrations();
        $module->set_migration_log_key( 'test_log_key' );
        $module->add_migration( $migration );

        // Run pre_boot
        $module->pre_boot( $app_config, $loader, $container );
    }

    /** @testdox If the container can not create a valid instance of a migration (INVALID OBJECT TYPE), a Migration_Exception should be thrown [CODE 101] */
    public function test_container_cant_create_valid_migration_type_throws_exception(): void {
        $migration = 'PinkCrab\Perique\Migration\Tests\Fixtures\Simple_Table_Migration';
        
        $this->expectException( \PinkCrab\Perique\Migration\Migration_Exception::class );
        $this->expectExceptionCode( 101 );
        $this->expectExceptionMessage( "Failed to construct {$migration} using the DI Container" );

        // Mocks
        $app_config = new App_Config();
        $loader     = $this->createMock( 'PinkCrab\Loader\Hook_Loader' );
        $container  = $this->createMock( DI_Container::class );
        $container->method( 'create' )
            ->willReturnCallback( function( $class_name ) { return new \stdClass();} );

        // Create the module and add migration
        $module = new Perique_Migrations();
        $module->set_migration_log_key( 'test_log_key' );
        $module->add_migration( $migration );

        // Run pre_boot
        $module->pre_boot( $app_config, $loader, $container );
    }
}
