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
define('STARSCOUNT_MANAGE_USERS', 1);

if(is_admin()) {
    // js
    function starscount_js_enqueue_admin() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'starscount_admin_ajax', plugins_url('js/starscount-admin.js', __FILE__) );
    }
    add_action('admin_head', 'starscount_js_enqueue_admin');

    if(STARSCOUNT_MANAGE_USERS) {
        add_filter( 'manage_users_columns', array('WP_Starscount','users_admin_columns') );
        add_filter( 'manage_users_custom_column', array('WP_Starscount','users_admin_columns_content'), 10, 3 );

        add_action('wp_ajax_starscount_ajax_change_init', array('WP_Starscount', 'ajax_change_init'));
    }
}

//@todo functions for inc/dec stars
//@todo recalc user posts function for inc/dec stars
//@todo replace shortcode to show stars

/**
 * В общем идея какая
 * Класс - просто контейнер функций
 * Рейтинг в звездах. Хранится в мета авторов. Вычисляется по количеству постов автора. Пересчитывается после публикации очередного поста.
 * Есть шаг - это количество постов, за которое автор получает очередную звезду. И так - до максимума звезд. По достижению
 * максимума звезды не присваиваются.
 * Выводится в виде звезд по шоткодам.
 * Все просто. Есть один небольшой нюанс.
 * Это стартовые звезды. Админ их может нарисовать автору сколько угодно до максимума. При публикации статей в этом случае
 * количество звезд не изменяется пока не перекроет этого стартового значения. И пока не ясно как считать если админ решит
 * "зарезать" звезды - т.е. сделать их меньше, чем по правилу расчета по постам. По логике понятное дело после публикации
 * очередного поста система просто дорисует необходимое количество.
 * В общем - как-то так...
 */

/**
 * Class WP_Starscount
 */

class WP_Starscount {

    function max() {
        return get_option('_starscount_max', STARSCOUNT_MAX_DEFAULT);
    }

    function step() {
        return get_option('_starscount_step', STARSCOUNT_STEP_DEFAULT);
    }

    function get_init($user_id) {
        $starscount = get_user_meta($user_id, '_starscount_init', true);
        $starscount = ($starscount ? $starscount : 0);
        return $starscount;
    }

    function set_init($user_id, $starscount = 1, $update_current = true) {
        update_user_meta($user_id, '_starscount_init', $starscount);
        if($update_current) {
            update_user_meta($user_id, '_starscount', $starscount);
        }
    }

    function get($user_id) {
        $starscount = get_user_meta($user_id, '_starscount', true);
        $starscount = ($starscount ? $starscount : 0);
        return $starscount;
    }

    function recalc($user_id) {

    }

    function users_admin_columns($columns) {
        $columns['starscount'] = __('Stars count');
        return $columns;
    }

    function users_admin_columns_content($custom_column, $column_name, $user_id) {

        switch($column_name) {
            case 'starscount':
                $starscount = WP_Starscount::get($user_id);
                $custom_column = '<span class="manage-user-starscount" id="manage-user-starscount-user-'.$user_id.'">'.$starscount.'</span>&nbsp;';
                //@todo show/hide depends on min/max values
                $custom_column .= '<input type="button" class="user-starscount-increase" id="user-starscount-user-'.$user_id.'-increase" value="+"/>';
                $custom_column .= '<input type="button" class="user-starscount-decrease" id="user-starscount-user-'.$user_id.'-decrease" value="-"/>';
                break;
        }

        return $custom_column;
    }

    function ajax_change_init() {

        $op = isset($_POST['op']) ? $_POST['op'] : false;
        $answer = array(
            'result' => 'false',
            'error_msg' => '',
            'msg' => '',
            'user_id' => ((isset($_POST['user_id'])&&is_numeric($_POST['user_id'])) ? (int)$_POST['user_id'] : false),
            'allow_increase' => 'unknown',
            'allow_decrease' => 'unknown',
        );

        if(!$op) {
            $answer['error_msg'] = __('Operation not set.');
            echo json_encode($answer);
            exit;
        }

        if(!$answer['user_id']) {
            $answer['error_msg'] = __('User ID is wrong.');
            echo json_encode($answer);
            exit;
        }

        $starscount = WP_Starscount::get_init($answer['user_id']);

        switch($op) {
            case 'increase':
                if($starscount < WP_Starscount::max()) $starscount++;
                break;
            case 'decrease':
                if($starscount > 0) $starscount--;
                break;
            default:
                $answer['error_msg'] = __('Unknown operation.');
                echo json_encode($answer);
                exit;
                break;
        }

        $answer['allow_increase'] = ($starscount == WP_Starscount::max()) ? 'false' : 'true';
        $answer['allow_decrease'] = ($starscount == 0) ? 'false' : 'true';

        WP_Starscount::set_init($answer['user_id'], $starscount);
        $answer['new_value'] = $starscount;
        $answer['result'] = 'true';

        echo json_encode($answer);
        exit;
    }
}