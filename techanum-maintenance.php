<?php
/**
 * Plugin Name:       Techanum Maintenance
 * Plugin URI:        https://techanum.com/maintenance/
 * Description:       Replace the default WordPress maintenance page with a friendly, customizable under-construction page. Hide admin notices from non-admin users.
 * Version:           1.0.0
 * Author:            Gregory Triglidis
 * Author URI:        https://techanum.com/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       techanum-maintenance
 * Domain Path:       /languages
 */

// Ασφάλεια: Εμποδίζει την απευθείας πρόσβαση στο αρχείο από τον browser
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}