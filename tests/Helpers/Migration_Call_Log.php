<?php

declare(strict_types=1);

/**
 * Static call log for tracking migration up()/down() calls in tests.
 *
 * @package PinkCrab\Perique\Migration\Tests
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 2.0.0
 */

namespace PinkCrab\Perique\Migration\Tests\Helpers;

class Migration_Call_Log {

	/** @var array<array{class:string, method:string}> */
	public static array $calls = array();

	public static function reset(): void {
		self::$calls = array();
	}

	public static function log( string $class, string $method ): void {
		self::$calls[] = array(
			'class'  => $class,
			'method' => $method,
		);
	}
}
