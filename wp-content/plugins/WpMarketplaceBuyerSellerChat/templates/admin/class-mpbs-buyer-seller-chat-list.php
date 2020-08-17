<?php

/**
 * @author Webkul
 * @version 2.1.0
 * This file handles server settings template at admin end.
 */

namespace WpMarketplaceBuyerSellerChat\Templates\Admin;

use WpMarketplaceBuyerSellerChat\Helper;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Buyer_Seller_Chat_List')) {
    /**
     *
     */
    class Mpbs_Buyer_Seller_Chat_List
    {
       
      public function mpbs_buyer_seller_chat_list(){
       
        $helper = new Helper\Mpbs_Data();

       $buyer_list =  $helper->mpbs_get_buyer_user_list();
        
       $seller_list =  $helper->mpbs_get_seller_user_list();


       ?>

        <div class="wrap" id="mpbs-admin-config">

            <h1 class="wp-heading-inline"><?php echo __('Chat List', 'mp_buyer_seller_chat'); ?></h1>


            <form method="post" action="" id="mpbs_chat_form" enctype="multipart/form-data">

               <table class="form-table">
                <tbody>


                <tr valign="top">
                    <th scope="row" class="titledesc">
                      <label for="mpbs_seller_id"><?php echo __('Seller List', 'mp_buyer_seller_chat'); ?></label>
                    </th>

                    <td class="forminp">
                      <select id="mpbs_seller_id" name="mpbs_seller_id" style="min-width: 350px;" >
                        <option value="">--Select--</option>
                        <?php if(count($seller_list) >0 ){ 
                          foreach($seller_list as $seller_id=>$seller_name){
                          ?>
                        <option value="<?php echo esc_attr($seller_id); ?>" <?php if (isset($_REQUEST['mpbs_seller_id'])) { if($seller_id == $_REQUEST['mpbs_seller_id']){ ?> selected <?php } }?>><?php echo  esc_html($seller_name); ?></option>
                        
                        <?php }
                        }
                        ?>
                      </select>
                    </td>
                  </tr>


               <tr valign="top">
                    <th scope="row" class="titledesc">
                      <label for="mpbs_buyer_id"><?php echo __('Buyer List', 'mp_buyer_seller_chat'); ?></label>
                    </th>

                    <td class="forminp">
                      <select id="mpbs_buyer_id" name="mpbs_buyer_id" style="min-width: 350px;" >
                        <option value="">--Select--</option>
                        <?php
                        $buyer_list = array();
                        if(isset($_REQUEST['mpbs_seller_id'])&& !empty($_REQUEST['mpbs_seller_id'])){
                          $buyer_list = $helper->mpbs_get_buyer_list_from_seller(intval($_REQUEST['mpbs_seller_id']));
                        }
                        if(!empty($buyer_list)){
                          foreach($buyer_list as $buyer_id=>$buyer_name){
                          ?>
                            <option value="<?php echo esc_attr($buyer_id); ?>"  <?php if (isset($_REQUEST['mpbs_buyer_id'])) { if($buyer_id == $_REQUEST['mpbs_buyer_id']){ ?> selected <?php } }?>><?php echo  esc_html($buyer_name); ?></option>
                            <?php } 
                        }
                        ?>
                      </select>
                    </td>
                  </tr>

                </tbody>
              </table>

              <?php wp_nonce_field('mpbs_buyer_seller_chat_nonce_action', 'mpbs_buyer_seller_chat_nonce'); ?>
              <?php submit_button(__('See Chat', 'mp_buyer_seller_chat'), 'primary', 'mpbs_buyer_seller_request'); ?>
              

          </form>
           <?php  if (isset($_POST['mpbs_buyer_seller_request']) ) {

        if (! isset($_POST['mpbs_buyer_seller_chat_nonce']) || ! wp_verify_nonce($_POST['mpbs_buyer_seller_chat_nonce'], 'mpbs_buyer_seller_chat_nonce_action')) {
          ?>
          <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html__('Security check failed!', 'mp_buyer_seller_chat'); ?></p>
          </div>
          <?php
           }  else {

            if($_POST['mpbs_seller_id'] == '' || $_POST['mpbs_buyer_id'] == ''){

              ?>
              <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html__('Please select both seller and buyer!', 'mp_buyer_seller_chat'); ?></p>
              </div>
              <?php

            }
             else{
        if (isset($_REQUEST['pageno'])) {
            $pageno = $_REQUEST['pageno'];
        } else {
            $pageno = 1;
        }
        
         $no_of_records_per_page = 10;
         $offset = ($pageno-1) * $no_of_records_per_page;

          $helper = new Helper\Mpbs_Data();
          
          $total_rows = $helper->mpbs_buyer_seller_chat_total_count($_REQUEST);
          
          $total_pages = ceil($total_rows / $no_of_records_per_page);
          
          $message_data = $helper->mpbs_buyer_seller_chat_details($_REQUEST,$offset,$no_of_records_per_page);

        

          ?>

        
                <div class="wk_conversation_wrapper">
                    <div class="quote-body">
                    <h2><?php echo esc_html__('Chat Histroy','mp_buyer_seller_chat') ?> </h2>
                         <?php if(count($message_data) >0 ) {
                           
                            $match_role = $message_data[0]['role'];
                            if($match_role == 'wk_marketplace_seller'){
                             $match_seller_id = $message_data[0]['sender_id'];
                              }else{
                                $match_seller_id = $message_data[0]['receiver_id'];
                              }
                        
                           ?>
                           <?php foreach($message_data as $message_val){
                             
                            
                             if($message_val['role'] == 'customer'){
                             ?>
                            <div class="quote-comment floated-left cs-customer">
                                <div class="comment-body">
                                    <p><?php echo $message_val['message'];?>
                                    </p>
                                </div>
                                <div class="comment-footer">
                                    <div class="comment-date">
                                              <time datetime=""><?php echo esc_html($message_val['datetime']);?></time>
                                    </div>
                                    <span class="cs-customer"><?php echo esc_html(ucfirst($message_val['sender_name']));?></span>
                                </div>
                            </div>
                             <?php }    if($message_val['role'] == 'wk_marketplace_seller'){
                               
                               if($match_seller_id == $message_val['sender_id']){
                               ?>
                                                                           <!-- // my-comment -->
                            <div class="quote-comment floated-right cs-me">
                                <div class="comment-body">
                                    <p><?php echo $message_val['message'];?></p>
                                </div>
                                <div class="comment-footer">
                                    <div class="comment-date">
                                     <time><?php echo esc_html($message_val['datetime']);?></time>
                                    </div>
                                    <span class="cs-me"><?php echo esc_html(ucfirst($message_val['sender_name']));?></span>
                                </div>
                            </div>
                            <?php } else { ?>
                              <div class="quote-comment floated-left cs-customer">
                                <div class="comment-body">
                                    <p><?php echo $message_val['message'];?></p>
                                </div>
                                <div class="comment-footer">
                                    <div class="comment-date">
                                     <time><?php echo esc_html($message_val['datetime']);?></time>
                                    </div>
                                    <span class="cs-customer"><?php echo esc_html(ucfirst($message_val['sender_name']));?></span>
                                </div>
                            </div>

                            <?php } ?>

                               <?php } ?>

                           <?php }  }else {?>
                            <div class="quote-comment floated-left cs-customer">
                                <div class="comment-body">
                                    <p><?php echo esc_html('There are no conversation between them.','mp_buyer_seller_chat'); ?>
                                    </p>
                                </div>
                               
                            </div>

                           <?php } ?>
                          
                      </div>
                  </div>
                        <ul class="pagination">
                         
                              <?php if($pageno < $total_pages){ ?>
                              <li style="float:right;">
                              <?php $nextPage = $pageno+1; ?>
                              
                                    <form method="post" action="" id="mpbs_chat_form" enctype="multipart/form-data">
                                    <input type="hidden" name="pageno" value="<?php echo $nextPage; ?>"/> 
                                    <input type="hidden" name="mpbs_seller_id" value="<?php echo  $_REQUEST['mpbs_seller_id']; ?>"/>
                                    <input type="hidden" name="mpbs_buyer_id" value="<?php echo $_REQUEST['mpbs_buyer_id']; ?>" />
                                    <?php wp_nonce_field('mpbs_buyer_seller_chat_nonce_action', 'mpbs_buyer_seller_chat_nonce'); ?>
                                    <?php submit_button(__('Next', 'mp_buyer_seller_chat'), 'primary', 'mpbs_buyer_seller_request'); ?>            
                                    </form>
                              </li>
                             <?php } ?>
                             <?php if($pageno > 1){ ?>
                              <li style="float:right;margin-right:20px;">
                                <?php $prePage = $pageno-1;  ?>
                                <form method="post" action="" id="mpbs_chat_form" enctype="multipart/form-data">
                                  <input type="hidden" name="pageno" value="<?php echo $prePage; ?>"/> 
                                  <input type="hidden" name="mpbs_seller_id" value="<?php echo  $_REQUEST['mpbs_seller_id']; ?>"/>
                                  <input type="hidden" name="mpbs_buyer_id" value="<?php echo $_REQUEST['mpbs_buyer_id']; ?>" />
                                  <?php wp_nonce_field('mpbs_buyer_seller_chat_nonce_action', 'mpbs_buyer_seller_chat_nonce'); ?>
                                  <?php submit_button(__('Prev', 'mp_buyer_seller_chat'), 'primary', 'mpbs_buyer_seller_request'); ?>           
                              </form>
                              </li>
                             <?php } ?>
                        </ul>
                     <?php
                           }
                         }
                  }?>

            </div>
        <?php
      }
    }
}
