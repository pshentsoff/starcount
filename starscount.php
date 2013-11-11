<?php
/*
Plugin Name: Starscount
Plugin URI:
Description: Stars rating.
Version: 0.0.1
Author: Vadim Pshentsov
Author URI: http://pshentsoff.ru
License: Apache License, Version 2.0
Wordpress version supported: 3.6 and above
*/
/**
 * @file        starscount.php
 * @description
 *
 * PHP Version  5.3.13
 *
 * @package 
 * @category
 * @plugin URI
 * @copyright   2013, Vadim Pshentsov. All Rights Reserved.
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @author      Vadim Pshentsov <pshentsoff@gmail.com> 
 * @link        http://pshentsoff.ru Author's homepage
 * @link        http://blog.pshentsoff.ru Author's blog
 *
 * @created     11.11.13
 */

define('STARSCOUNT_MAX_DEFAULT', 7);
define('STARSCOUNT_STEP_DEFAULT', 50);

class WP_Starscount {

    function max() {
        return get_option('_starscount_max', STARSCOUNT_MAX_DEFAULT);
    }

    function step() {
        return get_option('_starscount_step', STARSCOUNT_STEP_DEFAULT);
    }

    function get_init($user_id) {
        return get_user_meta($user_id, '_starscount_init', true);
    }

    function set_init($user_id, $starscount = 1, $update_current = true) {
        update_user_meta($user_id, '_starscount_init', $starscount);
        if($update_current) {
            update_user_meta($user_id, '_starscount', $starscount);
        }
    }

    function get($user_id) {
        return get_user_meta($user_id, '_starscount', true);
    }

    function recalc($user_id) {

    }
}