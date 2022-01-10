<?php

/**
 * File that contains all the constants used throughout the plugin
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.2.0
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/includes
 */


/**
 * Status of entries in trash.
 * 
 * @since 1.2.0
 * @var int WPE_TRASHED
 */
define( 'WPE_TRASHED', 0 );

/**
 * Status of regular entry without approval.
 * 
 * @since 1.2.0
 * @var int WPE_ACTIVE
 */
define( 'WPE_ACTIVE', 1 );

/**
 * Status of entry pending approval.
 * 
 * @since 1.2.0
 * @var int WPE_PENDING
 */
define( 'WPE_PENDING', 2 );

/**
 * Status of approved entry.
 * 
 * @since 1.2.0
 * @var int WPE_APPROVED
 */
define( 'WPE_APPROVED', 3 );

/**
 * Status of entry not approved.
 * 
 * @since 1.2.0
 * @var int WPE_CANCELLED
 */
define( 'WPE_CANCELLED', 4 );

/**
 * Status of entry permanently deleted.
 * 
 * @since 1.2.0
 * @var int WPE_DELETED
 */
define( 'WPE_DELETED', -1 );

