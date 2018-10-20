<?php
/**
 * This file runs some clean up after the plugin has been uninstalled. 
 * 
 * It is called automatically when a user clicks to uninstall a plugin.
 */

 /**
  * Verify the uninstall process is actually calling this code and not some
  * direct access stuff.
  */
if ( !defined('WP_UNINSTALL_PLUGIN' )) {
    die;
}

error_log( "Uninstall code has been activated for RV Travel Log" );
?>