<?php

declare(strict_types=1);

/**
 * Interface for all classes which are run at plugin activation.
 *
 * @package PinkCrab\Migration\Plugin_Lifecycle
 * @author Glynn Quelch glynn@pinkcrab.co.uk
 * @since 0.0.1
 */

namespace PinkCrab\Migration\Plugin_Lifecycle\State_Events;

use PinkCrab\Migration\Plugin_Lifecycle\Plugin_State_Change;

interface Activation extends Plugin_State_Change{}
