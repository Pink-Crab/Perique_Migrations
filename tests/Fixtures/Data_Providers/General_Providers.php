<?php

declare(strict_types=1);

/**
 * Mixture of general providers
 *
 * @package PinkCrab\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Migration\Tests\Fixtures\Data_Providers;

class General_Providers {

	/**
	 * Returns a mock version of WPDB that logs all common methods
	 *
	 * The log can be accessed with $wpdb->usage_log
	 * Each event is added as a key.
	 *
	 * $usage_log = [
	 *   'insert' => [ 'table_name' => [ 'data' => ['key' => 'value'], 'format' => ['%d'] ] ]
	 * ]
	 *
	 * @return \wpdb
	 */
	public function wpdb_with_log(): \wpdb {
		return new class() extends \wpdb{
			/** @var array<string,mixed[]> */
            public $usage_log = array();
			public function __construct( $a = null, $b = null, $c = null, $d = null ) {}
			public function insert( $table, $data, $format = null ) {
				$this->usage_log['insert'][ $table ][] = array(
					'data'   => $data,
					'format' => $format,
				);
			}
		};
	}
}
