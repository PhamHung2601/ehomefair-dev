<?php

/**
 * File
 */

if(!defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}
?>
<div id="ask-data" style="display:block;">
    <div class="wkmp-modal-header">
        <h3><?php echo _e("Ask Question", "marketplace"); ?></h3>
        <span class="wkmp-close">x</span>
        <span style="clear:both;"></span>
    </div>
    <form id="ask-form" method="post" action="">
        <p><span class="label"><?php echo _e("SUBJECT", "marketplace"); ?> :</span>
            <input id='query_user_sub' class="wkmp-querysubject" type="text" name="subject">
            <span class="label">&nbsp;</span>
            <span  id="askesub_error" class="error-class"></span>
        </p>
        <p><span class="label"><?php echo _e("Ask", "marketplace"); ?> :</span>
        <textarea id="userquery" class="wkmp-queryquestion" name="message"></textarea>
        <span class="label">&nbsp;</span><span  id="askquest_error" class="error-class"></span>
        </p>
        <div class="wkmp-modal-footer">
        <input id="asktoaddbtn" type="button" value="<?php esc_html_e('Ask', 'marketplace'); ?>" class="button">
        <input id="resetbtn" type="reset" value="<?php esc_html_e('Reset', 'marketplace'); ?>" class="button">
        <span style="clear:both;"></span>
        </div>
    </form>
</div>
