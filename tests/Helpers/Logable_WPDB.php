<?php

declare(strict_types=1);

/**
 * An instance of WPDB where all queries are logged internally.
 *
 * @package PinkCrab\Test_Helpers
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Perique\Migration\Tests\Helpers;

class Logable_WPDB extends \wpdb {

	/** @var array<string,mixed[]> */
	public $usage_log = array();

	/**
	 * Sets the value to return from the next call.
	 *
	 * @var mixed
	 */
	public $then_return = null;

	/**
	 * Ignore the constructor!
	 *
	 * @param null $a
	 * @param null $b
	 * @param null $c
	 * @param null $d
	 */
	public function __construct( $a = null, $b = null, $c = null, $d = null ) {}

	/**
	 * Logs any calls made to insert
	 *
	 * NATIVE RETURN >> The number of rows inserted, or false on error.
	 *
	 * @param string $table
	 * @param array $data
	 * @param array|string|null $format
	 * @return mixed
	 */
	public function insert( $table, $data, $format = null ) {
		$this->usage_log['insert'][ $table ][] = array(
			'data'   => $data,
			'format' => $format,
		);

		return $this->then_return;
	}
};
