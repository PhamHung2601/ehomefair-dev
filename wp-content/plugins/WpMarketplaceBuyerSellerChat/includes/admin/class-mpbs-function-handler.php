<?php

/**
 * @author Webkul
 * @version 2.1.0
 * This file handles all admin end action callbacks.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Admin;

use WpMarketplaceBuyerSellerChat\Templates\Admin;
use WpMarketplaceBuyerSellerChat\Includes\Admin\Util;
use WpMarketplaceBuyerSellerChat\Helper;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Function_Handler')) {
    /**
     *
     */
    class Mpbs_Function_Handler implements Util\Admin_Settings_Interface
    {
        /**
         * Add Menu under MP menu
         */
        public function mpbs_add_dashboard_menu()
        {
            $server_template = new Admin\Mpbs_Server_Settings_Template();
            $chat_template = new Admin\Mpbs_Buyer_Seller_Chat_List();
            add_menu_page(__('Buyer Seller Chat', 'mp_buyer_seller_chat'), __('Buyer Seller Chat', 'mp_buyer_seller_chat'), 'manage_options', 'chat-page', array($chat_template, 'mpbs_buyer_seller_chat_list'), 'dashicons-format-chat', 55);
            add_submenu_page('chat-page', 'Chat Histroy', 'Chat Histroy', 'manage_options', 'chat-page', array($chat_template, 'mpbs_buyer_seller_chat_list'));
            add_submenu_page('chat-page', 'Configuration', 'Configuration', 'manage_options', 'buyer-seller-chat', array($server_template, 'mpbs_server_settings_template'));
        }

        /**
         * Register Option Settings
         */
        public function mpbs_register_settings()
        {
            register_setting('mpbs-settings-group', 'mpbs_host_name');
            register_setting('mpbs-settings-group', 'mpbs_chat_name');
            register_setting('mpbs-settings-group', 'mpbs_port_number');
            register_setting('mpbs-settings-group', 'mpbs_https_enabled');
            register_setting('mpbs-settings-group', 'mpbs_server_private_key');
            register_setting('mpbs-settings-group', 'mpbs_server_ca_bundle_file');
            register_setting('mpbs-settings-group', 'mpbs_server_certificate_file');
            register_setting('mpbs-settings-group', 'mpbs_sender_text_color', array( 'default' => '#fff'));
            register_setting('mpbs-settings-group', 'mpbs_sender_chat_timetxtcolor', array( 'default' => '#fff'));
            register_setting('mpbs-settings-group', 'mpbs_sender_chat_bgcolor', array( 'default' => '#96588a'));
            register_setting('mpbs-settings-group', 'mpbs_receiver_text_color', array( 'default' => '#96588a') );
            register_setting('mpbs-settings-group', 'mpbs_receiver_chat_timetxtcolor', array( 'default' => '#999'));
            register_setting('mpbs-settings-group', 'mpbs_receiver_chat_bgcolor', array( 'default' => 'rgba(150,88,138,.05)'));
            register_setting('mpbs-settings-group', 'mpbs_customer_chat_stript_color', array( 'default' => '#96588a'));
            register_setting('mpbs-settings-group', 'mpbs_admin_chat_stript_color', array( 'default' => '#96588a'));
        }

        public function mpbs_init()
        {
            add_action('admin_enqueue_scripts', array($this, 'mpbs_enqueue_scripts'));
        }
        /**
        * Front scripts and style enqueue
        */
        public function mpbs_enqueue_scripts()
        {
            wp_enqueue_style('mpbs_admin_style', MPBS_URL . 'assets/css/style.css');
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_style('ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
            wp_enqueue_script('ui-js', '//code.jquery.com/ui/1.12.1/jquery-ui.js');
            wp_enqueue_script('mpbs_admin_script', MPBS_URL . 'assets/js/admin.js', array( 'jquery' ));
            wp_localize_script('mpbs_admin_script', 'mpbs_admin_script_object', array(
              'ajaxurl' => admin_url('admin-ajax.php'),
              'admin_ajax_nonce' => wp_create_nonce('mpbs_admin_ajax_nonce')
              ));

            $this->mpbs_add_default_stylesheet();
        }

        function mpbs_add_default_stylesheet(){

            $colors = $this->get_default_colors();

            echo '<style>';
            ?>
            .mpbs-active-users .mpbs-chat-menu .mpbs-panel-control{
                border-color: <?php echo $colors['admin']['strip'];?> !important;
            }
            .mpbs-active-users .mpbs-chat-menu .mpbs-heading{
                background-color: <?php echo $colors['admin']['strip'];?> !important;
                border-bottom-color: <?php echo $colors['admin']['strip'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-chatbox-top-bar, #mpbs-chat-window-container .mpbs-chatbox-container .mpbs-chatbox-top-bar{
                background-color: <?php echo $colors['customer']['strip'];?> !important;
            }
            .mpbs-active-users .mpbs-chatbox-container > .mpbs-chatbox-top-bar{
                background-color: #fff !important;
            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.self .mpbs-message{
                background-color: <?php echo $colors['sender']['bgcolor'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.self .mpbs-message p{
                color: <?php echo $colors['sender']['text'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.self .mpbs-message time{
                color: <?php echo $colors['sender']['time'];?> !important;
            }

            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.other .mpbs-message{
                background-color: <?php echo $colors['receiver']['bgcolor'];?> !important;

            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.other .mpbs-message p{
                color: <?php echo $colors['receiver']['text'];?> !important;

            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.other .mpbs-message time{
                color: <?php echo $colors['receiver']['time'];?> !important;

            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.other .mpbs-message:before{
                border-color: <?php echo $colors['receiver']['bgcolor'];?> !important;
                border-left-color: transparent !important;
                border-bottom-color: transparent !important;
            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.self .mpbs-message:before{
                border-color: <?php echo $colors['sender']['bgcolor'];?> !important;
                border-right-color: transparent !important;
                border-bottom-color: transparent !important;
            }
            .mpbs-active-users .mpbs-chat-menu {
                border-color: <?php echo $colors['admin']['strip'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-chat-controls {
                border-color: <?php echo $colors['customer']['strip'];?> !important;
            }
            .mpbs-active-users .mpbs-chat-menu .mpbs-chatbox-container .mpbs-chat-controls {
                border-bottom-color: <?php echo $colors['admin']['strip'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-start-chat-container, .mpbs-chatbox-container .mpbs-thread-container {
                border-color: <?php echo $colors['customer']['strip'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-input-area {
                border-color: <?php echo $colors['customer']['strip'];?> !important;
            }
            <?php
            echo '</style>';
        }

        function get_default_colors() {

            $admin_chatbox = get_option( 'mpbs_admin_chat_stript_color');
            $customer_chatbox = get_option( 'mpbs_customer_chat_stript_color' );
            $sender_text_color = get_option( 'mpbs_sender_text_color');
            $receiver_text_color = get_option( 'mpbs_receiver_text_color' );
            $sender_chatbg_color = get_option( 'mpbs_sender_chat_bgcolor');
            $receiver_chatbg_color = get_option( 'mpbs_receiver_chat_bgcolor' );
            $sender_chattime_color = get_option( 'mpbs_sender_chat_timetxtcolor');
            $receiver_chattime_color = get_option( 'mpbs_receiver_chat_timetxtcolor' );
            $colors = array(
                'sender' => array(
                    'text' => $sender_text_color,
                    'bgcolor' => $sender_chatbg_color,
                    'time' => $sender_chattime_color,
                ),
                'receiver' => array(
                    'text' => $receiver_text_color,
                    'bgcolor' => $receiver_chatbg_color,
                    'time' => $receiver_chattime_color,
                ),
                'admin' => array(
                    'strip' => $admin_chatbox
                ),
                'customer' => array(
                    'strip' => $customer_chatbox
                )
            );

            return $colors;
        }

        /**
         * Start server ajax callback
         */
        function mpbs_start_server()
        {
            if (check_ajax_referer('mpbs_admin_ajax_nonce', 'nonce', false)) {
                $port = $_POST['port'];
                $node = exec('whereis node');
                $node_path = explode(' ', $node);
                if (!isset($node_path[1]) || $node_path[1] == '') {
                    $node = exec('whereis nodejs');
                    $node_path = explode(' ', $node);
                }

                if (count($node_path)) {
                    if (substr(php_uname(), 0, 7) == "Windows") {
                        pclose(popen("start /B PORT=" . $port . ' ' . $node_path[1] . ' ' . MPBS_FILE . 'assets/serverJs/server.js', "r"));
                    } else {
                        exec('PORT=' . $port . ' ' . $node_path[1] . ' ' . MPBS_FILE . 'assets/serverJs/server.js' . " > /dev/null &");
                    }
                    $response = array(
                      'error'   => false,
                      'status'  => esc_html__('Success', 'mp_buyer_seller_chat'),
                      'message' => esc_html__('Server has been started!', 'mp_buyer_seller_chat')
                    );
                } else {
                    $response = array(
                      'error'   => true,
                      'status'  => esc_html__('Error', 'mp_buyer_seller_chat'),
                      'message' => esc_html__('Nodejs Path not found.', 'mp_buyer_seller_chat')
                    );
                }
            } else {
                $response = array(
                  'error'   => true,
                  'status'  => esc_html__('Error', 'mp_buyer_seller_chat'),
                  'message' => esc_html__('Security check failed!', 'mp_buyer_seller_chat')
                );
            }

            echo json_encode($response);
            wp_die();
        }

        /**
         * Stop server ajax callback
         */
        function mpbs_stop_server()
        {
            if (check_ajax_referer('mpbs_admin_ajax_nonce', 'nonce', false)) {
                $get_user_path = exec('whereis fuser');
                $port = $_POST['port'];
                if ($get_user_path) {
                    $get_user_path = explode(' ', $get_user_path);
                    if (isset($get_user_path[1])) {
                        $stopServer = exec($get_user_path[1].' -k '.$port.'/tcp');
                        $response = array(
                          'error'   => false,
                          'status'  => esc_html__('Success', 'mp_buyer_seller_chat'),
                          'message' => esc_html__('Server has been stopped.', 'mp_buyer_seller_chat')
                        );
                    }
                } else {
                    $response = array(
                      'error'   => true,
                      'status'  => esc_html__('Error', 'mp_buyer_seller_chat'),
                      'message' => esc_html__('Something went wrong.', 'mp_buyer_seller_chat')
                    );
                }
            } else {
                $response = array(
                  'error'   => true,
                  'status'  => esc_html__('Error', 'mp_buyer_seller_chat'),
                  'message' => esc_html__('Security check failed!', 'mp_buyer_seller_chat')
                );
            }

            echo json_encode($response);
            wp_die();
        }

        public function mpbs_add_screen_id($ids)
        {
            $ids[] = 'buyer-seller-chat_page_buyer-seller-chat';
            return $ids;
        }

        /**
         * get seller list
         */

         function mpbs_get_buyer_list(){

              if (check_ajax_referer('mpbs_admin_ajax_nonce', 'nonce', false)){
                $seller_id = $_POST['seller_id']; 
                $helper = new Helper\Mpbs_Data();
                $buyer_list =  $helper->mpbs_get_buyer_list_from_seller($seller_id);

                  $html = "<option value=''>--Select--</option>"; 
                  foreach ($buyer_list as $key=>$value){ 
                      $html .= "<option value='".$key."'>".$value."</option>";
                  }

                  $response = array(
                    'error'   => false,
                    'status'  => esc_html__('Success', 'mp_buyer_seller_chat'),
                    'message' => $html
                  );
                


              } else {
                    $response = array(
                      'error'   => true,
                      'status'  => esc_html__('Error', 'mp_buyer_seller_chat'),
                      'message' => esc_html__('Security check failed!', 'mp_buyer_seller_chat')
                    );
              }

              echo json_encode($response);
              wp_die();

         }
        /**
         *  upload dir filter
         */
         function mpbs_upload_dir_filter($dir)
         {
             return array(
                 'path'   => MPBS_FILE . 'assets/serverJs',
                 'url'    => MPBS_URL . 'assets/serverJs',
                 'subdir' => '/serverJs',
                 'basedir'=> MPBS_FILE . 'assets',
                 'baseurl'=> MPBS_URL . 'assets'
             ) + $dir;
         }

        /**
         *  Configuration form submit function
         *  @param $_POST
         */
        function mpbs_save_configuration($data)
        {
            if (! isset($data['mpbs_configuration_nonce']) || ! wp_verify_nonce($data['mpbs_configuration_nonce'], 'mpbs_configuration_nonce_action')) {
                ?>
                <div class="notice notice-error is-dismissible">
                  <p><?php echo esc_html__('Security check failed!', 'mp_buyer_seller_chat'); ?></p>
                </div>
                <?php
            } else {
                $error_arr = array();
                $host = $_POST['mpbs_host_name'];
                $port = $_POST['mpbs_port_number'];
                $chat_name = $_POST['mpbs_chat_name'];
                $https_enabled = $_POST['mpbs_https_enabled'];

                update_option('mpbs_host_name', $host);
                update_option('mpbs_port_number', $port);
                update_option('mpbs_chat_name', $chat_name);
                update_option('mpbs_https_enabled', $https_enabled);

                update_option( 'mpbs_sender_text_color', $_POST['mpbs_sender_text_color']);
                update_option( 'mpbs_sender_chat_timetxtcolor', $_POST['mpbs_sender_chat_timetxtcolor']);
                update_option( 'mpbs_sender_chat_bgcolor', $_POST['mpbs_sender_chat_bgcolor']);
                update_option( 'mpbs_receiver_text_color', $_POST['mpbs_receiver_text_color']);
                update_option( 'mpbs_receiver_chat_timetxtcolor', $_POST['mpbs_receiver_chat_timetxtcolor']);
                update_option( 'mpbs_receiver_chat_bgcolor', $_POST['mpbs_receiver_chat_bgcolor']);
                update_option( 'mpbs_customer_chat_stript_color', $_POST['mpbs_customer_chat_stript_color']);
                update_option( 'mpbs_admin_chat_stript_color', $_POST['mpbs_admin_chat_stript_color']);

                // remove files section
                if (isset($_POST['mpbs_remove_server_file'])) {
                    $options = array('mpbs_server_private_key', 'mpbs_server_certificate_file', 'mpbs_server_ca_bundle_file');
                    foreach ($_POST['mpbs_remove_server_file'] as $key => $value) {
                        if (in_array($value, $options)) {
                            $path = get_option($value);
                            $file_name = explode('/', $path);
                            $file_name = end($file_name);
                            unlink($path);
                            $option_delete = delete_option($value);
                            if ($option_delete) {
                              array_push($error_arr,
                                array(
                                  'code'    => 'success',
                                  'message' => sprintf( esc_html__( '%s file deleted successfully!', 'mp_buyer_seller_chat'), $file_name ),
                                )
                              );
                            }
                        }
                    }
                }

                // remove files section ends

                if ('yes' == $https_enabled) :
                    if (! function_exists('wp_handle_upload')) {
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                    }

                    $upload_overrides = array(
                        'test_form' => false,
                        'test_type' => false
                    );

                    add_filter('upload_dir', array($this, 'mpbs_upload_dir_filter'));

                    $upload_dir = wp_upload_dir();

                    if (isset($_FILES)) :
                        foreach ($_FILES as $key => $value) {
                            if (isset($_FILES[$key]['tmp_name']) && $_FILES[$key]['tmp_name']) {
                                if (! file_exists($upload_dir['basedir'] . $upload_dir['subdir'] . '/' . $_FILES[$key]['name'])) {
                                    if (mime_content_type($_FILES[$key]['tmp_name']) == 'text/plain') {
                                        if ('mpbs_server_private_key' == $key && pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION) != 'key') {
                                            array_push($error_arr, array('code' => 'error', 'message' => __("Private key file extension must be .key!", "mp_buyer_seller_chat")));
                                        } else if ('mpbs_server_certificate_file' == $key && pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION) != 'crt') {
                                            array_push($error_arr, array('code' => 'error', 'message' => __("Server certificate file extension must be .crt!", "mp_buyer_seller_chat")));
                                        } else if ('mpbs_server_ca_bundle_file' == $key && pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION) != 'ca-bundle') {
                                            array_push($error_arr, array('code' => 'error', 'message' => __("Server CA Bundle file extension must be .ca-bundle!", "mp_buyer_seller_chat")));
                                        } else {
                                            $uploadedfile = $_FILES[$key];
                                            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
                                            if ($movefile && ! isset($movefile['error'])) {
                                                update_option($key, $movefile['file']);
												array_push($error_arr,
													array(
														'code'    => 'success',
														'message' => sprintf( esc_html__( '%s file uploaded successfully!', 'mp_buyer_seller_chat'), $_FILES[$key]['name'] ),
													)
												);
                                            } else {
                                                array_push($error_arr, array('code' => 'error', 'message' => $movefile['error']));
                                            }
                                        }
                                    } else {
										array_push($error_arr,
											array(
												'code'    => 'error',
												'message' => sprintf( esc_html__( '%s file not supported!', 'mp_buyer_seller_chat' ), $_FILES[$key]['name'] ),
											)
										);
                                    }
                                } else {
									array_push($error_arr,
										array(
											'code'    => 'error',
											'message' => sprintf( esc_html__( '%s file already exists!', 'mp_buyer_seller_chat' ), $_FILES[$key]['name'] ),
										)
									);
                                }
                            }
                        }
                    endif;

                    remove_filter('upload_dir', array($this, 'mpbs_upload_dir_filter'));
                endif;

                // write server file based on https enabled/disabled
                if (isset($_POST['mpbs_port_number']) && $_POST['mpbs_port_number'] && isset($_POST['mpbs_host_name']) && $host) {
                    $this->mpbs_write_server_file($host, $https_enabled);
                }

                if ($error_arr) {
                    foreach ($error_arr as $key => $value) {
                        echo "<div class='notice notice-{$value['code']} is-dismissible'><p>{$value['message']}</p></div>";
                    }
                }
                ?>
                <?php
            }
        }

        /**
         *  write server js file
         *  @param $host host name
         */
        function mpbs_write_server_file($host, $https_enabled)
        {
            $key = get_option('mpbs_server_private_key');

            $cert = get_option('mpbs_server_certificate_file');

            $ca = get_option('mpbs_server_ca_bundle_file');

            $serverFile = fopen(MPBS_FILE . 'assets/serverJs/server.js', "w");

            if ('yes' == $https_enabled) :
                $serverFileData = 'var https = require(\'https\');
                var fs = require(\'fs\');

                var options_https = {
                    key: fs.readFileSync(\'' . $key . '\', \'utf8\'),
                    cert: fs.readFileSync(\'' . $cert . '\', \'utf8\'),
                    requestCert: true,
                    rejectUnauthorized: false
                };

                var app = https.createServer(options_https, function (req, res) {
                    res.setHeader(\'Access-Control-Allow-Origin\', \'' . $host . '\');
                    res.writeHead(200, { \'Content-Type\': \'text/plain\' });
                    res.end(\'okay\')
                });

                var io = require(\'socket.io\')(app)

                var roomUsers = {}

                const PORT = process.env.PORT || 3000

                app.listen(PORT, function () {
                  console.log(PORT)
                })

                io.on(\'connection\', function (socket) {
                  socket.on(\'newSellerConneted\', function (details) {
                    var index = details.sellerId
                    roomUsers[index] = socket.id
                  })

                  socket.on(\'newCustomerConneted\', function (details) {
                    var index = details.customerData.customerId
                    roomUsers[index] = socket.id
                    Object.keys(roomUsers).forEach(function (key, value) {
                      if (key == details.sellerId) {
                        receiverSocketId = roomUsers[key]
                        socket.broadcast.to(receiverSocketId).emit(\'refresh seller chat list\', details)
                      }
                    })
                  })

                  socket.on(\'customer status change\', function (data) {
                    if (typeof (data) !== \'undefined\') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.sellerId) {
                          receiverSocketId = roomUsers[key]
                          socket.broadcast.to(receiverSocketId).emit(\'send customer status change\', data)
                        }
                      })
                    }
                  })

                  socket.on(\'customer send new message\', function (data) {
                    if (typeof (data) !== \'undefined\') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.receiverId) {
                          receiverSocketId = roomUsers[key]
                          socket.broadcast.to(receiverSocketId).emit(\'seller new message received\', data)
                        }
                      })
                    }
                  })

                  socket.on(\'seller send new message\', function (data) {
                    if (typeof (data) !== \'undefined\') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.receiverId) {
                            receiverSocketId = roomUsers[key]
                            socket.broadcast.to(receiverSocketId).emit(\'customer new message received\', data)
                        }
                      })
                    }
                  })

                  socket.on(\'seller status change\', function (data) {
                    if (typeof (data) !== \'undefined\') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        Object(data.customers).forEach(function (k) {
                          if (key == k) {
                            receiverSocketId = roomUsers[key]
                            data.customerId = k
                            socket.broadcast.to(receiverSocketId).emit(\'send seller status change\', data)
                          }
                        })
                      })
                    }
                  })
                })';
            else :
                $serverFileData = 'var app = require(\'http\').createServer(function (req, res) {
                  res.setHeader(\'Access-Control-Allow-Origin\', \'' . $host . '\');
                  res.writeHead(200, { \'Content-Type\': \'text/plain\' });
                  res.end(\'okay\')
                })

                var io = require(\'socket.io\')(app)

                var roomUsers = {}

                const PORT = process.env.PORT || 3000

                app.listen(PORT, function () {
                  console.log(PORT)
                })

                io.on(\'connection\', function (socket) {
                  socket.on(\'newSellerConneted\', function (details) {
                    var index = details.sellerId
                    roomUsers[index] = socket.id
                  })

                  socket.on(\'newCustomerConneted\', function (details) {
                    var index = details.customerData.customerId
                    roomUsers[index] = socket.id
                    Object.keys(roomUsers).forEach(function (key, value) {
                      if (key == details.sellerId) {
                        receiverSocketId = roomUsers[key]
                        socket.broadcast.to(receiverSocketId).emit(\'refresh seller chat list\', details)
                      }
                    })
                  })

                  socket.on(\'customer status change\', function (data) {
                    if (typeof (data) !== \'undefined\') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.sellerId) {
                          receiverSocketId = roomUsers[key]
                          socket.broadcast.to(receiverSocketId).emit(\'send customer status change\', data)
                        }
                      })
                    }
                  })

                  socket.on(\'customer send new message\', function (data) {
                    if (typeof (data) !== \'undefined\') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.receiverId) {
                          receiverSocketId = roomUsers[key]
                          socket.broadcast.to(receiverSocketId).emit(\'seller new message received\', data)
                        }
                      })
                    }
                  })

                  socket.on(\'seller send new message\', function (data) {
                    if (typeof (data) !== \'undefined\') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.receiverId) {
                            receiverSocketId = roomUsers[key]
                            socket.broadcast.to(receiverSocketId).emit(\'customer new message received\', data)
                        }
                      })
                    }
                  })

                  socket.on(\'seller status change\', function (data) {
                    if (typeof (data) !== \'undefined\') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        Object(data.customers).forEach(function (k) {
                          if (key == k) {
                            receiverSocketId = roomUsers[key]
                            data.customerId = k
                            socket.broadcast.to(receiverSocketId).emit(\'send seller status change\', data)
                          }
                        })
                      })
                    }
                  })
                })';
            endif;

            fwrite($serverFile, $serverFileData);
            fclose($serverFile);
        }
    }
}
