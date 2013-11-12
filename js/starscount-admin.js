/**
 * Javascript source file
 * @file        starcount-admin.js
 * @description
 *
 * @package     starcount-admin
 * @category
 * @copyright   2013, Vadim Pshentsov. All Rights Reserved.
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @author      Vadim Pshentsov <pshentsoff@gmail.com>
 * @link        http://pshentsoff.ru Author's homepage
 * @link        http://blog.pshentsoff.ru Author's blog
 *
 * @created     11.11.13
 */

jQuery(function() {

    change_user_starscount = (function(id_attr, op){

        var user_id = parseInt(id_attr.replace(/\D+/g,''));
        var data = 'action=starscount_ajax_change_init&op='+op+'&user_id='+user_id;

        jQuery.ajax({
            type: 'POST',
            data: data,
            dataType: 'json',
            url: '/wp-admin/admin-ajax.php',
            success: function(data){
                if(data['result'] == 'true'){
                    jQuery('#manage-user-starscount-user-'+data['user_id']).html(data['new_value']);
                    if(data['allow_increase'] == 'true') {
                        jQuery('#user-starscount-user-'+data['user_id']+'-increase').show();
                    } else if(data['allow_increase'] == 'false') {
                        jQuery('#user-starscount-user-'+data['user_id']+'-increase').hide();
                    }
                    if(data['allow_decrease'] == 'true') {
                        jQuery('#user-starscount-user-'+data['user_id']+'-decrease').show();
                    } else if(data['allow_decrease'] == 'false') {
                        jQuery('#user-starscount-user-'+data['user_id']+'-decrease').hide();
                    }
                } else {
                    alert(data['error_msg']);
                }
            }
        });
    });

    jQuery('.user-starscount-increase').live('click', function(){
        change_user_starscount(jQuery(this).attr('id'), 'increase');
        return false;
    });

    jQuery('.user-starscount-decrease').live('click', function(){
        change_user_starscount(jQuery(this).attr('id'), 'decrease');
        return false;
    });

});