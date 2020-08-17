<?php
 /*
 *  Plugin Name: Mp Custom Registration
 *  Plugin URI: http://webkul.com
 *  Description: create custom registration and other form using mp-custom-registration plugin.
 *  Author: Webkul
 *  Version: 1.1.0
 *  Author URI: http://webkul.com
 *  License: GNU/GPL for more info see license.txt included with plugin
 *  License URI: https://store.webkul.com/license.html
 *  WC requires at least: 3.0.0
 *  WC tested up to: 3.6.5.
 */

add_action('admin_init', 'check_marketplace_is_istalled');

function check_marketplace_is_istalled()
{
    ob_start();
    if (!is_plugin_active('wk-woocommerce-marketplace/functions.php') ) {
        echo 'please install or activate marketplace plugin to use this plugin';
        exit;
    }
}


define("PLUGIN_BASE_DIR", dirname(__FILE__) . "/");
define("PLUGIN_BASE_URL", plugins_url("/mp-custom-registration/"));
define("PLUGIN_UPLOAD_PATH", WP_CONTENT_DIR . '/uploads/');
define('PLUGIN_ACTIVATED', true);

// Include libraries
require PLUGIN_BASE_DIR . '/custom-reg-libs/advanced-fields.class.php';
require PLUGIN_BASE_DIR . '/custom-reg-libs/field_defs.php';
require PLUGIN_BASE_DIR . '/custom-reg-libs/form-fields.class.php';
require PLUGIN_BASE_DIR . '/custom-reg-libs/functions.php';


Class MP_CUSTOM_REGISTRATION
{
    public $mp_comman_fields;
    public $mp_adavanced_fields;
    public $mp_set_methods;

    /**
     * Constructor function
     */
    private function __construct()
    {
        //plugin short code
        add_action('wk_mkt_add_register_field', array($this, 'view_showform'));
        add_action('mp_add_seller_profile_field', array($this, 'view_showform_details'));
        add_action('init', array($this, 'custom_reg_post_type_init'));


        // ajax and data start here
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('init', array($this, 'ajax_action_submit_form'));
        add_action('init', array($this, 'autoload_field_classes'));

        // // Custom UI elements
        add_action('save_post', array($this, 'action_save_form'));
        add_filter("the_content", array($this, "form_preview"));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('publish_post', array($this, 'post_published_notification'), 10, 2);
        add_action('save_post', array($this, 'post_published_notification'), 10, 2);
        add_action('woocommerce_new_customer_data', array($this,'save_mp_custom_user_fields'));
        add_action('woocommerce_created_customer', array( $this, 'data_custom_save' ), 10, 2);
        $this->setup_fields();

        add_action('wp_ajax_nopriv_get_mp_select_state', array( $this, 'get_mp_select_state' ));
        add_action('wp_ajax_get_mp_select_state', array( $this, 'get_mp_select_state' ));

    }


    function get_mp_select_state()
    {

        if (check_ajax_referer('mp-form-ajaxnonce', 'nonce', false) ) {

            $country = strip_tags($_POST['s_country']);

            $countries_obj = new WC_Countries();

            $result = $countries_obj->get_states($country);

            wp_send_json($result);

            die;

        }

    }

    public function data_custom_save( $user_id, $data )
    {


        if(count($data['mp_custom_data'])>0){
           if (isset($data['mp_custom_data']) ) {

            update_user_meta($user_id, 'mp_custom_data', $data['mp_custom_data']);
           }
        }
     }

    public static function getInstance()
    {
        static $instance;
        if ($instance == null) {
            $instance = new self;
        }
        return $instance;
    }
    public function save_mp_custom_user_fields( $data )
    {
        
        if (isset($_POST[ 'submitform' ]) ) {
            

            $data['mp_custom_data'] = $_POST[ 'submitform' ];
           

            if (! empty($_FILES['submitform']) ) {


                // $target_dir = plugins_url()."/assets/images/";
                 
                // $img_tmpName = $_FILES['submitform']['tmp_name'];
                 
                // $img_name = $_FILES['submitform']['name'];
                 
                // $url = wp_upload_dir();
                 
                // $user_folder = wp_get_current_user()->user_login;
                 
                // if (!file_exists($url['basedir'].'/' .$user_folder)) {
                //         wp_mkdir_p( $url['basedir'].'/' .$user_folder );
                // }
                 
                // $target_file_img = $url['basedir'].'/' . $user_folder .'/'. basename($_FILES['submitform']['name']['wk_Profile_Image']);
                 
                // $uploaded_profile_img = move_uploaded_file( $_FILES['submitform']['tmp_name']['wk_Profile_Image'], $target_file_img );

                // require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                //  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                //  require_once(ABSPATH . "wp-admin" . '/includes/media.php');
                 
                //   $attachment_id = media_handle_upload( $uploaded_profile_img, get_current_user_id() );
                 
                //  $attachment_url = wp_get_attachment_url( $attachment_id );
                 
                // $data['mp_custom_data']['image'] = $attachment_url;


                $key_value_img = '';

                foreach($_FILES['submitform']['name'] as $key => $val){
                    $key_value_img = $key;
                }

                $wordpress_upload_dir = wp_upload_dir();

                $profile_image = $_FILES['submitform'];
                
                $profile_img_file = time().'_'.$profile_image['name']["$key_value_img"];

                $file_path = ( isset( $profile_image['name'] ) && $profile_image['name'] ) ? $wordpress_upload_dir['path'] . '/' . $profile_img_file : '';

                if(file_exists( $file_path )){

                    $file_path = $wordpress_upload_dir['path'] . '/' . $profile_img_file;

                    $file_base_url = $wordpress_upload_dir['baseurl'] . '/' . $profile_img_file;
                }
                // looks like everything is OK
                $file_mime = $profile_image['type']["$key_value_img"];
                
                $pi_uploaded_file = move_uploaded_file( $profile_image['tmp_name']["$key_value_img"], $file_path );
                if ($pi_uploaded_file) {
                    $upload_id = wp_insert_attachment( array(
                    'guid' => $file_base_url,
                    'post_mime_type' => $file_mime,
                    'post_title' => preg_replace( '/\.[^.]+$/', '', $profile_img_file ),
                    'post_content' => '',
                    'post_status' => 'inherit'
                    ), $file_path );

                    // wp_generate_attachment_metadata() won't work if you do not include this file
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );

                    // Generate and save the attachment metas into the database
                    $result = wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $file_path ) );

                    $file_url = $wordpress_upload_dir['url'] . '/' . $profile_img_file;
                    $data['mp_custom_data']['image'] = $file_url;
                }
            }
            return $data;
        }

    }
    /**
     * @function ajax_action_submit_form
     * @uses     Submit form using AJAX
     * @return   type ajax response
     */
    public function ajax_action_submit_form()
    {
        global $wpdb;
        if ( $this->is_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'submit_form' ) {
            $form_id = $_REQUEST['form_id'];
            $data = isset( $_REQUEST['submitform'] ) ? $_REQUEST['submitform'] : array();
            $form_data = get_post_meta($form_id, 'form_data', $single = true);
            foreach( $form_data['fields'] as $key => $value ) {
                if( $value == 'Username' ) {
                    $username = $_POST['submitform'][$key];
                    if( validate_username( $username ) ) {
                        $user_login=$wpdb->get_results("select user_login from {$wpdb->prefix}users where user_login='".$username."'");
                        if(!empty($user_login)) {
                            $return_data['action']='username is already taken';
                            echo json_encode($return_data);
                            die;
                        }
                    } else {
                        $return_data['action']='invalid username';
                        echo json_encode($return_data);
                        die;
                    }
                }
                
                if( $value == 'Firstname' ) {
                    $firstname=$_POST['submitform'][$key];
                }

                if( $value == 'Lastname' ) {
                    $lastname=$_POST['submitform'][$key];
                }

                if( $value == 'Email' ) {
                    $useremail=$_POST['submitform'][$key];
                    $user_email=$wpdb->get_results("select user_email from {$wpdb->prefix}users where user_email='".$useremail."'");
                    if(!empty($user_email)) {
                        $return_data['action']='this email-id is already registerd';
                        echo json_encode($return_data);
                        die;
                    }
                }

                if( $value == 'Password' ) {
                    $userpass=$_POST['submitform'][$key];
                    //$userpass=wp_hash_password($_POST['submitform'][$key]);
                }
            }

            if( empty( $userpass ) ) {
                $userpass = wp_generate_password();
            }

            $user_meta = array();
            
            foreach($_POST['submitform'] as $key=>$value)
            {
                if($form_data['fields'][$key]!='Firstname'& $form_data['fields'][$key]!='Lastname' & $form_data['fields'][$key]!='Username' & $form_data['fields'][$key]!='Email' & $form_data['fields'][$key]!='Password' & $form_data['fields'][$key]!='ProfileImage') {
                    $meta_value_arr=array();
                    $meta_key= preg_replace('/\s+/', '_', $form_data['fieldsinfo'][$key]['label']);
                    $user_meta[$meta_key]=$value;
                }
            }
            $userdata = array(
            'user_login'  =>  htmlspecialchars($username, ENT_QUOTES),
            'user_pass'   =>  $upass ,
            'user_email'  =>$useremail
            );

            $user_id = wp_insert_user($userdata);
            wp_cache_delete($user_id, 'users');
            if($user_id) {
                update_user_meta($user_id, 'cr_from_id', $form_id);
                update_user_meta($user_id, 'first_name', $firstname);
                update_user_meta($user_id, 'last_name', $lastname);
                $wpdb->get_results("insert into {$wpdb->prefix}mpsellerinfo (seller_id, user_id, seller_key, seller_value) VALUES ('',$user_id,'selleraccess','0')");
                foreach($user_meta as $key=>$value)
                {
                    update_user_meta($user_id, $key, $value);
                }
                if(!empty($_FILES)) {
                    $files=array();
                    foreach ($_FILES['upload'] as $key => $value)
                    {
                        foreach($value as $val)
                        {
                                $files[$key]=$val;
                        }
                    }
                    $_FILES = array("upload_attachment" => $files);
                    foreach ($_FILES as $file => $array)
                    {
                        $newupload= $this->insert_product_attachment($file, $user_id);
                        update_user_meta($user_id, '_thumbnail_id_avatar', $newupload);
                    }

                }

                $from_email = get_option('admin_email'); // this will get admin email
                $site_name = get_bloginfo('name');
                $from_name =$site_name;

                // Prepare entry data for email template injection
                $email_template_data = array_merge(array('fid' => $form_id, 'status' => 'new'), maybe_unserialize($data));
                $field_names_for_email = $this->get_field_names($data, $form_data);
                //to user

                $user_email_text = isset($form_data['email_text']) ? $form_data['email_text'] : "Thanks for visiting {$site_name}";
                $user_email_data['subject'] = "[{$site_name}] Thanks for contacting with us";
                $user_email_data['message'] = "{$user_email_text}.<br/>Your registration is successful<br><br> You login details are:<br>";
                $user_email_data['message'].= "Username : $username<br/>";
                $user_email_data['message'].= "Password : $upass<br/>";

                $user_email_data['to'] = $_POST['submitform']['wk_Email'];
                $user_email_data['from_email'] = $from_email ;
                $user_email_data['from_name'] =$from_name;

                $user_email_data = apply_filters('user_email_data', $user_email_data, $form_id, maybe_unserialize($email_template_data));
                if(!empty($from_email)) {
                                      $headers = "From: \"{$user_email_data['from_name']}\" <{$user_email_data['from_email']}>\r\n";
                } else {
                    $headers = "{$user_email_data['from_name']} <{$user_email_data['from_email']}>\r\n";
                }
                $headers .= "Content-type: text/html";
                if (isset($user_email_data['subject']) || isset($user_email_data['message'])) {
                    //mail to new customer
                    wp_mail($_POST['submitform']['wk_Email'], $user_email_data['subject'], $user_email_data['message'], $headers);
                }
                //to form admin
                $admin_email_data['subject'] = "[{$site_name}] Form submitted";
                $admin_email_data['message'] = "New form submission on you site {$site_name}.<br/>";
                foreach (maybe_unserialize($data) as $field_name => $entry_value) {
                    if (!is_string($entry_value)) { $entry_value = get_concatenated_string($entry_value);
                    }
                    if($field_name!='Password') {
                        $admin_email_data['message'] .= "{$field_names_for_email[$field_name]}: {$entry_value}<br/>";
                    }
                }
                $admin_email_data['to'] = $from_email;
                $admin_email_data['from_email'] = $from_email;
                $admin_email_data['from_name'] = $from_name;
                $admin_email_data = apply_filters('admin_email_data', $admin_email_data, $form_id, maybe_unserialize($email_template_data));
                $headers = "{$admin_email_data['from_name']} <{$admin_email_data['from_email']}>\r\n";
                $headers .= "Content-type: text/html";

                // SENDING MAIL TO SELLER;
                wp_mail($admin_email_data['to'], $admin_email_data['subject'], $admin_email_data['message'], $headers);

                $data = maybe_unserialize($data);
                    $return_data = array();
                            $return_data['message'] = stripslashes($form_data['thankyou']);
                            $return_data['action'] = 'success';
                            echo json_encode($return_data);
                die();
            }
        }
    }

    public function insert_product_attachment($file_handler,$post_id,$setthumb='false')
    {
        global $wpdb;
        // check to make sure its a successful upload
        if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) { __return_false();
        }
        include_once ABSPATH . "wp-admin" . '/includes/image.php';
        include_once ABSPATH . "wp-admin" . '/includes/file.php';
        include_once ABSPATH . "wp-admin" . '/includes/media.php';
        $attach_id = media_handle_upload($file_handler, $post_id);

        return $attach_id;
    }
    function get_field_names($ef_data, $ef_form_data)
    {
        $ef_data = maybe_unserialize($ef_data);
        $ef_form_data = maybe_unserialize($ef_form_data);
        $ef_prep_fields = array();

        foreach($ef_data as $ef_name => $ef_value) {
            $ef_prep_fields[$ef_name] = $ef_form_data['fieldsinfo'][$ef_name]['label'];
        }

        return $ef_prep_fields;
    }

    public function post_published_notification( $ID, $post )
    {
        global $wpdb;
        if(isset($_REQUEST['action'])) {
            if($_REQUEST['action']!='trash') {
                  $wpdb->get_results("update $wpdb->posts  set post_status='draft' where post_type='mp-custom-reg' and post_status='publish'");
                  $wpdb->get_results("update $wpdb->posts  set post_status='publish' where post_type='mp-custom-reg' and ID='".$ID."'");
            }
        }
    }

    /**
     * @functino is_ajax
     * @uses     Library fucntion to check if an ajax request
     * is being handled
     * @return   type boolean
     */
    function is_ajax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    function view_showform()
    {
        global $wpdb;
        $id=$wpdb->get_results("select ID from $wpdb->posts where post_type='mp-custom-reg' and post_status='publish'", ARRAY_A);
        if(!empty($id)) {
            $form_id =$id[0]['ID'];

            $formdata = get_post_meta($form_id, 'form_data', $single = true);
        }

        if (!empty($formdata)) {
            $paginated_form = paginate_form(
                $formdata, array(
                'comman_fields' => $this->mp_comman_fields,
                'adavanced_fields' => $this->mp_adavanced_fields
                )
            );
            $html_data = array_merge($paginated_form, array('form_id' => $form_id, 'formsetting' => $formdata));

            //creating space for creating form
            $view = $this->get_html("showform", $html_data);
        } else {
            $view = "No forms defined";
        }
        echo $view;
        // return $view;
    }

    function view_showform_details()
    {
        global $wpdb;
        $id = $wpdb->get_results("select ID from $wpdb->posts where post_type='mp-custom-reg' and post_status='publish'", ARRAY_A);
        if( ! empty( $id ) ) {
            $form_id =$id[0]['ID'];
            $u_id = get_current_user_id();
            $formdata = get_post_meta($form_id, 'form_data', $single = true);
        }
        
        if (!empty($formdata)) {
            $meta_user = get_user_meta($u_id, 'mp_custom_data');
            $meta_user = isset($meta_user[0]) ? $meta_user[0] : 0;
            $paginated_form = paginate_form(
                $formdata, array(
                'comman_fields' => $this->mp_comman_fields,
                'adavanced_fields' => $this->mp_adavanced_fields
                ), $meta_user
            );
            
            $html_data = array_merge($paginated_form, array('form_id' => $form_id, 'formsetting' => $formdata));
            //creating space for creating form
            $view = $this->get_html("showform", $html_data);
        } else {
            $view = "No forms defined";
        }
        echo $view;
    }

    /**
     * @function action_save_form
     * @uses     Save the form after creation through the 'Form' creation panel
     */
    function action_save_form($post_id)
    {

        if (isset($_REQUEST['contact'])) {
            $formadata['fields']=$_REQUEST['contact']['fields'];
            $new_fields=array();
            foreach ($formadata['fields'] as $val)
            {
                $new_fields[]=$val;
            }
            foreach($_REQUEST['contact']['fieldsinfo'] as $field)
            {
                $label_field = preg_replace('/\s+/', '_', $field['label']);
                $formadata['fieldsinfo']['wk_'.$label_field]=$field;
            }
            $i=0;
            $formadata['fields']=array();
            foreach($formadata['fieldsinfo'] as $key=>$value)
            {
                $formadata['fields'][$key]=$new_fields[$i];
                $i++;
            }
            $formadata['buttontext']=$_REQUEST['contact']['buttontext'];
            $formadata['buttoncolor']=$_REQUEST['contact']['buttoncolor'];
            $formadata['thankyou']=$_REQUEST['contact']['thankyou'];
            $formadata['email']=$_REQUEST['contact']['email'];
            $formadata['from']=$_REQUEST['contact']['from'];
            $formadata['email_text']=$_REQUEST['contact']['email_text'];
            if (count($formadata) > 0 && get_post_type() == 'mp-custom-reg') {
                $prev_data = get_post_meta($post_id, 'form_data', $single = true);
                update_post_meta($post_id, 'form_data', $formadata);
            }
        }
    }
    /**
     * @function form_preview
     * @uses     Generate a preview of form
     * @return   type  HTML render string
     */
    function form_preview($content)
    {
        if (get_post_type() != "mp-custom-reg") {
            return $content;
        }
    }



    /**
     * @function autoload_field_classes
     * @uses     Autoloader to load field classes when they are used
     * @return   type  null
     */
    public static function autoload_field_classes()
    {
        
        $user_id = get_current_user_id();

        $key_value_img = '';

        foreach($_FILES['submitform']['name'] as $key => $val){
            $key_value_img = $key;
        }
        
        if (isset($_POST['update_profile_submit']) ) {  
            if (isset($_POST['submitform']) ) {
                $data['mp_custom_data'] = $_POST[ 'submitform' ];
                $getting_pro_image = get_user_meta( $user_id, 'mp_custom_data', $data['mp_custom_data'], true )['image'];
                $data['mp_custom_data']['image'] = $getting_pro_image;
                update_user_meta($user_id, 'mp_custom_data', $data['mp_custom_data']);
            }

            if(isset($_FILES['submitform']) && !empty($_FILES['submitform']['name']["$key_value_img"])){
               $wordpress_upload_dir = wp_upload_dir();

                $profile_image = $_FILES['submitform'];
                
                $profile_img_file = time().'_'.$profile_image['name']["$key_value_img"];

                $file_path = ( isset( $profile_image['name'] ) && $profile_image['name'] ) ? $wordpress_upload_dir['path'] . '/' . $profile_img_file : '';

                if(file_exists( $file_path )){

                    $file_path = $wordpress_upload_dir['path'] . '/' . $profile_img_file;

                    $file_base_url = $wordpress_upload_dir['baseurl'] . '/' . $profile_img_file;
                }
                // looks like everything is OK
                $file_url = $wordpress_upload_dir['url'] . '/' . $profile_img_file;
                $file_mime = $profile_image['type']["$key_value_img"];
                
                $pi_uploaded_file = move_uploaded_file( $profile_image['tmp_name']["$key_value_img"], $file_path );
                if ($pi_uploaded_file) {
                    $upload_id = wp_insert_attachment( array(
                    'guid' => $file_base_url,
                    'post_mime_type' => $file_mime,
                    'post_title' => preg_replace( '/\.[^.]+$/', '', $profile_img_file ),
                    'post_content' => '',
                    'post_status' => 'inherit'
                    ), $file_path );

                    // wp_generate_attachment_metadata() won't work if you do not include this file
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );

                    // Generate and save the attachment metas into the database
                    $result = wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $file_path ) );

                    $data['mp_custom_data']['image'] = $file_url;
                   
                    update_user_meta($user_id, 'mp_custom_data', $data['mp_custom_data']);

                } 
            }
        }

        $field_class_directories = array(
        PLUGIN_BASE_DIR . 'formfields/common/',
        PLUGIN_BASE_DIR . 'formfields/advance/'
        );
        foreach($field_class_directories as $dir) {
            $class_files = scandir($dir);
            for($it=2 ; $it<count($class_files); $it++) {
                include $dir.$class_files[$it];
            }
        }
    }


    function setup_fields()
    {

        $this->mp_comman_fields = apply_filters("common_fields", $this->mp_comman_fields);
        $this->mp_adavanced_fields = apply_filters("advanced_fields", $this->mp_adavanced_fields);
        $this->method_set = apply_filters("method_set", $this->mp_set_methods);
    }

    /**
     * @function add_meta_box
     * @uses     Adds the metaboxes in the 'Form' creation
     *        section of the Administration dashboard
     *        -- Form creation panel
     *        -- Agent selection panel
     */
    public function add_meta_box($post_type)
    {
        $post_types = array('mp-custom-reg');
        add_meta_box(
            'createnew', __("Custom Registration Form", 'MP_CUSTOM_REGISTRATION'), array($this, 'view_createnew'), 'mp-custom-reg', 'advanced', 'high'
        );
    }


    function get_html($view, $html_data)
    {
        if (empty($view)) {
            return null;
        }
        extract($html_data);
        ob_start();
        include PLUGIN_BASE_DIR . "custom-reg-view/{$view}.php";
        $data = ob_get_clean();
        return $data;
    }

    /**
     * View callers * 
     */

    /**
     * @function view_createnew
     * @uses     Render the Form builder window for building form
     * @return   type string HTML
     */
    function view_createnew($post)
    {
        $formdata = get_post_meta($post->ID, 'form_data', $single = true);
        $html_data = array(
        'commonfields' => $this->mp_comman_fields,
        'advanced_fields' => $this->mp_adavanced_fields,
        'methods_set' => $this->mp_set_methods,
        'form_post_id' => $post->ID
        );
        if (!empty($formdata)) {
            $html_data['form_data'] = $formdata;
        }
        $view = $this->get_html("createnew", $html_data);
        echo  $view;
    }


    function custom_reg_post_type_init()
    {
        $form_post_type_labels = array(
        'name' => _x('Custom Registration', 'post type general name', 'MP_CUSTOM_REGISTRATION'),
        'singular_name' => _x('Custom Registration', 'post type singular name', 'MP_CUSTOM_REGISTRATION'),
        'menu_name' => _x('MP Custom Registration', 'admin menu', 'MP_CUSTOM_REGISTRATION'),
        'name_admin_bar' => _x('MP Custom Registration', 'add new on admin bar', 'MP_CUSTOM_REGISTRATION'),
        'add_new' => _x('Add New', 'book', 'MP_CUSTOM_REGISTRATION'),
        'add_new_item' => __('Add New Form', 'MP_CUSTOM_REGISTRATION'),
        'new_item' => __('New Form', 'MP_CUSTOM_REGISTRATION'),
        'edit_item' => __('Edit Form', 'MP_CUSTOM_REGISTRATION'),
        'view_item' => __('View Form', 'MP_CUSTOM_REGISTRATION'),
        'all_items' => __('All Forms', 'MP_CUSTOM_REGISTRATION'),
        'search_items' => __('Search Forms', 'MP_CUSTOM_REGISTRATION'),
        'parent_item_colon' => __('Parent Forms:', 'MP_CUSTOM_REGISTRATION'),
        'not_found' => __('No forms found.', 'MP_CUSTOM_REGISTRATION'),
        'not_found_in_trash' => __('No forms found in Trash.', 'MP_CUSTOM_REGISTRATION'),
        );

        $form_post_type_args = array(
        'labels' => $form_post_type_labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'registration-form'),
        'capability_type' => 'page',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title'),
                        'menu_icon' => 'dashicons-feedback'
        );

        register_post_type('mp-custom-reg', $form_post_type_args);
    }

    /**
     * @function enqueue_scripts
     * @uses     Add the JS and CSS dependencies for loading on the public accessible pages
     */
    function enqueue_scripts()
    {
        global $wpdb;
        $current_user = wp_get_current_user();
        $main_page = get_query_var('main_page');
        $info = get_query_var('info');
        $seller_info = $wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix."mpsellerinfo WHERE user_id = '".$current_user->ID."' and seller_value='seller'");
        if( ( is_account_page() ) || ( $main_page == get_option('mp_profile', 'profile') && $info == 'edit' && ( $current_user->ID && $seller_info > 0) ) ){
            wp_enqueue_style("mp_bootstrap_css", PLUGIN_BASE_URL . "custom-reg-view/css/bootstrap.min.css");
            wp_enqueue_style("mp_fontawesome_css", PLUGIN_BASE_URL . "custom-reg-view/css/font-awesome.min.css");
            wp_enqueue_style("lf_style_css", PLUGIN_BASE_URL . "custom-reg-view/css/front.css");
            //wp_enqueue_style("lf_breadcrumbs_css", PLUGIN_BASE_URL . "custom-reg-view/css/bread-crumbs.css");
            wp_enqueue_style('mp_select2_css', PLUGIN_BASE_URL. "custom-reg-view/css/custom-reg.css");
            wp_enqueue_style('mp_bootstrap_breadcrumbs_css', PLUGIN_BASE_URL. "custom-reg-view/css/bootstrap-breadcrumbs.css");

            //jQuery UI date time picker
            wp_enqueue_style('mp_jquery_ui', PLUGIN_BASE_URL . "custom-reg-view/css/jquery-ui.css");
            wp_enqueue_style('mp_jquery_ui_timepicker_addon_css', PLUGIN_BASE_URL.'custom-reg-view/css/jquery-ui-timepicker-addon.css');


            // RateIt!
            wp_enqueue_style('mp_rateit_css', PLUGIN_BASE_URL. "custom-reg-view/css/rateit.css");

            wp_enqueue_script("jquery");
            wp_enqueue_script('jquery-form');
            wp_register_script('jquery-validation-plugin', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', array('jquery'));
            wp_enqueue_script('jquery-validation-plugin');
            wp_enqueue_script("mp_bootstrap_js", PLUGIN_BASE_URL . "custom-reg-view/js/bootstrap.min.js");
            wp_enqueue_script("mp_mustache_js", PLUGIN_BASE_URL . "custom-reg-view/js/mustache.js");
            wp_enqueue_script("mp_sha256_js", PLUGIN_BASE_URL . "custom-reg-view/js/sha256.js");
            wp_enqueue_script("jquery-ui-core");
            wp_enqueue_script("jquery-ui-sortable");
            //jQuery UI datetime picker
            wp_enqueue_script("jquery-ui-datepicker");
            wp_enqueue_script("jquery-ui-slider");
            wp_enqueue_script('lf_jquery_ui_timepicker_addon_js', PLUGIN_BASE_URL . 'custom-reg-view/js/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-core','jquery-ui-datepicker', 'jquery-ui-slider'));
            wp_enqueue_script("mp_form_js", PLUGIN_BASE_URL . "assets/js/plugin.js");
            wp_localize_script('mp_form_js', 'mp_form_script', array( 'mp_form_ajax' => admin_url('admin-ajax.php'), 'mp_form_nonce' => wp_create_nonce('mp-form-ajaxnonce') ));

            wp_enqueue_script("jquery-ui-draggable");
            wp_enqueue_script("jquery-ui-droppable");
            wp_enqueue_script("jquery-select2-jquery-js", PLUGIN_BASE_URL . "custom-reg-view/js/select2.js");
            // RateIt!
            wp_enqueue_script("mp_jquery_rateit_js", PLUGIN_BASE_URL . "custom-reg-view/js/jquery.rateit.min.js");
        }
    }



    function admin_enqueue_scripts()
    {
        if( 'mp-custom-reg' === get_post_type() ){
            wp_enqueue_style("mp_bootstrap_css", PLUGIN_BASE_URL . "custom-reg-view/css/bootstrap.min.css");
            wp_enqueue_style("mp_bootstrap_theme_css", PLUGIN_BASE_URL . "custom-reg-view/css/bootstrap-theme.min.css");
            wp_enqueue_style("mp_fontawesome_css", PLUGIN_BASE_URL . "custom-reg-view/css/font-awesome.min.css");
            wp_enqueue_style("lf_style_css", PLUGIN_BASE_URL . "custom-reg-view/css/style.css");
            wp_enqueue_style( 'mp_select2_css', plugins_url() . '/woocommerce/assets/css/select2.css' );

            //jQuery UI datetime picker
            wp_enqueue_style('mp_jquery_ui', PLUGIN_BASE_URL . "custom-reg-view/css/jquery-ui.css");
            wp_enqueue_style('mp_jquery_ui_timepicker_addon_css', PLUGIN_BASE_URL.'custom-reg-view/css/jquery-ui-timepicker-addon.css');

            // RateIt!
            wp_enqueue_style('mp_rateit_css', PLUGIN_BASE_URL. "custom-reg-view/css/rateit.css");

            wp_enqueue_script("jquery");
            wp_enqueue_script('jquery-form');
            wp_register_script('jquery-validation-plugin', '//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', array('jquery'));
            wp_enqueue_script('jquery-validation-plugin');
            wp_enqueue_script("mp_bootstrap_js", PLUGIN_BASE_URL . "custom-reg-view/js/bootstrap.min.js");
            wp_enqueue_script("mp_mustache_js", PLUGIN_BASE_URL . "custom-reg-view/js/mustache.js");
            wp_enqueue_script("jquery-ui-core");
            wp_enqueue_script("jquery-ui-sortable");

            //jQuery UI datetime picker
            wp_enqueue_script("jquery-ui-datepicker");
            wp_enqueue_script("jquery-ui-slider");
            wp_enqueue_script('lf_jquery_ui_timepicker_addon_js', PLUGIN_BASE_URL.'custom-reg-view/js/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-core','jquery-ui-datepicker', 'jquery-ui-slider'));

            wp_enqueue_script("jquery-ui-draggable");
            wp_enqueue_script("jquery-ui-droppable");
            wp_enqueue_script("jquery-select2-jquery-js", PLUGIN_BASE_URL . "custom-reg-view/js/select2.js");

            // RateIt!
            wp_enqueue_script("mp_jquery_rateit_js", PLUGIN_BASE_URL . "custom-reg-view/js/jquery.rateit.min.js");
        }
    }



}

    /**
 * Initialize of * 
*/
//new MP_CUSTOM_REGISTRATION();
MP_CUSTOM_REGISTRATION::getInstance();
