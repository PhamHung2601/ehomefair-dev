<?php
/**
 * Template customizer
 *
 * @link       
 * @since 4.0.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wf_Woocommerce_Packing_List_Customizer
{
	public $module_base='customizer';
	public static $module_id_static='';
	private $to_customize='';
	private $to_customize_id='';
	private $default_template=1;
	public $package_documents=array('packinglist', 'shippinglabel', 'deliverynote', 'picklist'); //modules that have package option
	public $template_for_pdf=false;
	public $custom_css='';
	public $print_css='';
	public $enable_code_view=true;
	public $open_first_panel=false;
	public $rtl_css_added=false;
	public function __construct()
	{
		$this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;

		/* ajax main hook to handle all ajax actions */
		add_action('wp_ajax_wfpklist_customizer_ajax',array($this,'ajax_main'),1);

		add_filter('wt_pklist_alter_tooltip_data',array($this,'register_tooltips'),1);
	}

	/**
	* 	Ajax main hook for all actions
	*	@since 	4.0.5 
	*/
	public function ajax_main()
	{
		$out=array(
			'status'=>0,
			'msg'=>__("Error",'wf-woocommerce-packing-list')
		);
    	if(Wf_Woocommerce_Packing_List_Admin::check_write_access($this->module_id)) //no error then proceed
    	{
			$allowed_actions=array('get_template_data', 'update_from_codeview', 'save_theme', 'my_templates', 'prepare_sample_pdf');
			$customizer_action=sanitize_text_field($_REQUEST['customizer_action']);
			if(method_exists($this, $customizer_action))
			{
				$out=$this->{$customizer_action}();
			}
		}
		echo json_encode($out);
		exit();
	}

	/**
	* 	Saving template data for generating sample PDF (Ajax sub hook)
	*	@since 	4.0.5 
	*/
	public function prepare_sample_pdf()
	{
		$html=isset($_POST['codeview_html']) ? Wf_Woocommerce_Packing_List_Admin::strip_unwanted_tags($_POST['codeview_html']) : '';
		$order_id=(isset($_POST['order_id']) ? intval($_POST['order_id']) : 0);
		$template_type=isset($_POST['template_type']) ? sanitize_text_field($_POST['template_type']) : '';
		$out=array(
			'status'=>0,
			'msg'=>__("Unable to generate PDF.",'wf-woocommerce-packing-list'),
			'pdf_url'=>''
		);
		if($html!="" && $template_type!="" && $order_id>0)
		{
			/* save HTML for preview */
			$this->set_preview_pdf_html($html, $template_type);

			$out['pdf_url']=Wf_Woocommerce_Packing_List_Admin::get_print_url($order_id, 'preview_'.$template_type);
			$out['status']=1;
			$out['msg']='';

		}
		echo json_encode($out);
		exit();		
	}

	/**
	* 	@since 4.0.5
	* 	Get option name for preview PDF HTML
	*/
	private function get_preview_pdf_option_name($template_type)
	{
		return Wf_Woocommerce_Packing_List::get_module_id($template_type).'_preview_pdf_html';
	}

	/**
	* 	@since 4.0.5
	* 	Save temp HTML for preview PDF
	*/
	public function set_preview_pdf_html($html, $template_type)
	{
		$option_name=$this->get_preview_pdf_option_name($template_type);
		update_option($option_name, $html);
	}

	/**
	* 	@since 4.0.5
	* 	Get temp HTML for preview PDF
	*/
	public function get_preview_pdf_html($template_type)
	{
		$option_name=$this->get_preview_pdf_option_name($template_type);
		return get_option($option_name);
	}

	/**
	* 	@since 4.0.4
	* 	Hook the tooltip data to main tooltip array
	*/
	public function register_tooltips($tooltip_arr)
	{
		include(plugin_dir_path( __FILE__ ).'data/data.tooltip.php');
		$tooltip_arr[$this->module_id]=$arr;
		return $tooltip_arr;
	}

	/**
	 *  
	 * 	Initializing customizer under module settings page hook
	 **/
	public function init($base)
	{
		$this->to_customize=$base;
		$this->to_customize_id=Wf_Woocommerce_Packing_List::get_module_id($base);
		add_filter('wf_pklist_module_settings_tabhead',array( __CLASS__,'settings_tabhead'));
		add_action('wf_pklist_module_out_settings_form',array($this,'out_settings_form'));
	}

	/**
	 *  =====Module settings page Hook=====
	 * 	Tab head for module settings page
	 **/
	public static function settings_tabhead($arr)
	{
		$added=0;
		$out_arr=array();
		$menu_pos_key=isset($arr['invoice-number']) ? 'invoice-number' : 'general';
		foreach($arr as $k=>$v)
		{
			$out_arr[$k]=$v;
			if($k==$menu_pos_key && $added==0)
			{				
				$out_arr[WF_PKLIST_POST_TYPE.'-customize']=__('Customize','wf-woocommerce-packing-list');
				$added=1;
			}
		}
		if($added==0){
			$out_arr[WF_PKLIST_POST_TYPE.'-customize']=__('Customize','wf-woocommerce-packing-list');
		}
		return $out_arr;
	}

	/**
	 *  =====Module settings page Hook=====
	 * Modulesettings form
	 * You can include a form, its outside module settings form
	 * @since 4.0.0
	 * @since 4.0.3 Dummy placeholder image added to image url placeholder's in template
	 **/
	public function out_settings_form($args)
	{
		//code editor
		if($this->enable_code_view)
		{
			wp_enqueue_script($this->module_id.'-code_editor-js',plugin_dir_url( __FILE__ ).'libraries/code_editor/lib/codemirror.js',array('jquery'),WF_PKLIST_VERSION);
			//wp_enqueue_script($this->module_id.'-code_editor-mode-xml',plugin_dir_url( __FILE__ ).'libraries/code_editor/mode/xml/xml.js',array('jquery'),WF_PKLIST_VERSION);
			wp_enqueue_script($this->module_id.'-code_editor-mode-htmlmixed',plugin_dir_url( __FILE__ ).'libraries/code_editor/mode/htmlmixed/htmlmixed.js',array('jquery'),WF_PKLIST_VERSION);
			wp_enqueue_script($this->module_id.'-code_editor-mode-css',plugin_dir_url( __FILE__ ).'libraries/code_editor/mode/css/css.js',array('jquery'),WF_PKLIST_VERSION);
			
			wp_enqueue_style($this->module_id.'-code_editor-css', plugin_dir_url( __FILE__ ).'libraries/code_editor/lib/codemirror.css', array(),WF_PKLIST_VERSION,'all');
			//wp_enqueue_style($this->module_id.'-code_editor-theme-css', plugin_dir_url( __FILE__ ).'libraries/code_editor/theme/night.css', array(),WF_PKLIST_VERSION,'all');
			//wp_enqueue_style($this->module_id.'-code_editor-doc-css', plugin_dir_url( __FILE__ ).'libraries/code_editor/doc/docs.css', array(),WF_PKLIST_VERSION,'all');
			//wp_enqueue_style($this->module_id.'-code_editor-display-css', plugin_dir_url( __FILE__ ).'libraries/code_editor/addon/display/fullscreen.css', array(),WF_PKLIST_VERSION,'all');
		}

		$active_theme_arr=$this->get_current_active_theme($this->to_customize);
		$active_template_id=0;
		$template_is_active=0;
		$active_template_name=$this->gen_page_title('--','',0);
		if(!is_null($active_theme_arr) && isset($active_theme_arr->id_wfpklist_template_data))
		{
			$active_template_id=$active_theme_arr->id_wfpklist_template_data;
			$active_template_name=$this->gen_page_title($active_theme_arr->template_name,': ',1);
			$template_is_active=1;
		}

		/* We have to replace image url placeholders to dummy image when saving customizer otherwise it will show a 404. 
		Each dummy image must be unque to each placholder. */
		$images_path=plugin_dir_url( __FILE__ ).'assets/images/';
		$img_url_placeholders=array(
			'[wfte_company_logo_url]'=>$images_path.'logo_dummy.png',
			'[wfte_barcode_url]'=>$images_path.'barcode_dummy.png',
			'[wfte_signature_url]'=>$images_path.'signature_dummy.png',
		);
		$img_url_placeholders=apply_filters('wf_pklist_alter_img_url_placeholder_list',$img_url_placeholders,$this->to_customize);

		$to_customize_module_id=Wf_Woocommerce_Packing_List::get_module_id($this->to_customize);
		wp_enqueue_script($this->module_id,plugin_dir_url( __FILE__ ).'assets/js/customize.js',array('jquery'),WF_PKLIST_VERSION);
		$params=array(
			'nonces' => array(
	            'main'=>wp_create_nonce($this->module_id),
	        ),
	        'ajax_url' => admin_url('admin-ajax.php'),
	        'template_type'=>$this->to_customize,
	        'template_id'=>$active_template_id,
	        'template_is_active'=>$template_is_active,
	        'enable_code_view'=>$this->enable_code_view,
	        'open_first_panel'=>$this->open_first_panel,
	        'img_url_placeholders'=>$img_url_placeholders,
	        'labels'=>array(
	        	'error'=>__('Error','wf-woocommerce-packing-list'),
	        	'success'=>__('Success','wf-woocommerce-packing-list'),
	        	'sure'=>__("You can't undo this action. Are you sure?",'wf-woocommerce-packing-list'),
	        	'logo_missing'=>__("Click here to add Company logo",'wf-woocommerce-packing-list'),
	        	'company_missing'=>__("Click here to add Company name",'wf-woocommerce-packing-list'),
	        	'from_address_missing'=>__("Click here to add From address",'wf-woocommerce-packing-list'),
	        	'signature_missing'=>__("Click here to add Signature",'wf-woocommerce-packing-list'),
	        	'leaving_page_wrn'=>__("Please save all data before leaving this page. All unsaved data will be lost. Are you sure?",'wf-woocommerce-packing-list'),
	        	'create_new'=>__("Create new template",'wf-woocommerce-packing-list'),
	        	'change_theme'=>__("Change layout",'wf-woocommerce-packing-list'),
	        	'saving'=>__("Saving",'wf-woocommerce-packing-list'),
	        	'enter_order_id'=>__('Please enter order number','wf-woocommerce-packing-list'),
	        	'generating'=>__('Generating','wf-woocommerce-packing-list'),
	        ),
	        'urls'=>array(
	        	'images_path'=>$images_path,
	        	'general_settings'=>admin_url('admin.php?page='.WF_PKLIST_POST_TYPE.'#wf-general'),
	        	'module_general_settings'=>admin_url('admin.php?page='.$to_customize_module_id.'#general'),
	        ),
		);
		wp_localize_script($this->module_id,$this->module_id,$params);

		$view_file=plugin_dir_path( __FILE__ ).'views/customize.php';

		//default template list
		$def_template_url=$this->get_default_template_path($this->to_customize,'url');
		$def_template_path=$this->get_default_template_path($this->to_customize);
		$template_arr=array();
		if($def_template_path) //module exists/ template exists
		{
			include_once $def_template_path;
			$template_path=plugin_dir_path($def_template_path);
		}

		$params=array(
			'customizable_items'=>$this->get_customizable_items($this->to_customize_id),
			'non_customizable_items'=>$this->get_non_customizable_items($this->to_customize_id),
			'non_disable_fields'=>$this->get_non_disable_fields($this->to_customize_id),
			'non_options_fields'=>$this->get_non_options_fields($this->to_customize_id),			
			'def_template_arr'=>$template_arr,
			'def_template_url'=>$def_template_url,
			'active_template_id'=>$active_template_id,
			'active_template_name'=>$active_template_name,
			'template_type'=>$this->to_customize,
			'to_customize_id'=>$this->to_customize_id,
			'module_id'=>$this->module_id,
			'enable_code_view'=>$this->enable_code_view,
		);
		Wf_Woocommerce_Packing_List_Admin::envelope_settings_tabcontent(WF_PKLIST_POST_TYPE.'-customize', $view_file, '', $params, 0);
	}

	protected function gen_page_title($name,$sep,$active)
	{
		return $name.($active==1 ? ' ('.__('Active','wf-woocommerce-packing-list').')' : '');
	}
	public function get_non_disable_fields($base)
	{
		$settings=array();
		return apply_filters('wf_module_non_disable_fields',$settings,$base);
	}
	public function get_non_options_fields($base)
	{
		$settings=array();
		return apply_filters('wf_module_non_options_fields',$settings,$base);
	}
	public function get_customizable_items($base)
	{
		$settings=array();
		return apply_filters('wf_module_customizable_items',$settings,$base);
	}
	public function get_non_customizable_items($base)
	{
		$settings=array();
		return apply_filters('wf_module_non_customizable_items',$settings,$base);
	}
	public function get_current_active_theme($base)
	{
		global $wpdb; 
		$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;
		return $wpdb->get_row("SELECT * FROM $table_name WHERE is_active=1 AND template_type='$base'");
	}
	public function get_theme($id,$base)
	{
		global $wpdb; 
		$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;
        $qry=$wpdb->prepare("SELECT * FROM $table_name WHERE id_wfpklist_template_data=%d AND template_type=%s",array($id,$base));
		return $wpdb->get_row($qry);
	}
	protected function get_default_template_path($base,$type='path')
	{		
		$path=$type=='path' ? plugin_dir_path(WF_PKLIST_PLUGIN_FILENAME) : plugin_dir_url(WF_PKLIST_PLUGIN_FILENAME);
		if(Wf_Woocommerce_Packing_List_Public::module_exists($base))
		{
			$path.='public/';
		}elseif(Wf_Woocommerce_Packing_List_Public::module_exists($base))
		{
			$path.='admin/';
		}
		$path.="modules/$base/data/";
		$path=apply_filters('wf_pklist_alter_template_path',$path,$base,$type);
		if($type=='path')
		{
			$path.="data.templates.php";
			if(file_exists($path))
			{
				return $path;
			}
		}else
		{
			return $path;	
		}
		return false;
	}
	protected function get_default_template_header()
	{
		return plugin_dir_path(__FILE__).'data/data.template_header.php';
	}
	protected function get_default_template_footer()
	{
		return plugin_dir_path(__FILE__).'data/data.template_footer.php';
	}
	protected function load_template_header_footer($path, $template_type, $template, $page_title="")
	{
		include $path;
		$template_path=plugin_dir_path($path);
		$file='';
		$html='';
		if($template=='header')
		{
			if(isset($template_header) && $template_header!="")
			{
				$file=$template_path.$template_header;
			}else
			{
				$file=$this->get_default_template_header();
			}
			$custom_css='';
			$custom_css=apply_filters('wf_pklist_add_custom_css',$custom_css,$template_type,$this->template_for_pdf);
			$this->custom_css.=$custom_css;

			$print_margin=apply_filters('wf_pklist_alter_print_margin_css', 'margin:0;', $template_type, $this->template_for_pdf);

			/* add print css to alter print page properties */
			$print_css='@media print {
			  body{ -webkit-print-color-adjust:exact; color-adjust:exact;}
			  @page { size:auto; '.$print_margin.' }
			  body,html{ margin:0; background-color:#FFFFFF; }
			  table.wfte_product_table tr, table.wfte_product_table tr td, table.wfte_payment_summary_table tr, table.wfte_payment_summary_table tr td{ page-break-inside: avoid; }
			}';			
			$this->print_css=apply_filters('wf_pklist_alter_print_css', $print_css, $template_type, $this->template_for_pdf);

		}elseif($template=='footer')
		{
			if(isset($template_footer) && $template_footer!="")
			{
				$file=$template_path.$template_footer;
			}else
			{
				$file=$this->get_default_template_footer();
			}
		}
		if($file!="" && file_exists($file))
		{
			ob_start();
			$template_for_pdf=$this->template_for_pdf;//need to add font family `DeJaVu` on PDF generation
			$custom_css=$this->custom_css;
			$print_css=$this->print_css;
			include $file;
			$html=ob_get_clean();
		}
		return $html;
	}
	protected function load_default_templates($path,$template_type)
	{
		include $path; //to get $template_arr
		$template_path=plugin_dir_path($path);
		foreach($template_arr as $k=>$template)
		{
			$id=$template['id'];
			$file=$template_path.'data.'.$id.'.php';
			$template_arr[$k]['html']='';
			$template_arr[$k]['codeview_html']='';
			if(file_exists($file))
			{
				ob_start();
				include $file;
				$html=ob_get_clean();
				$template_arr[$k]['codeview_html']=$html;
				$template_arr[$k]['html']=$this->convert_to_design_view_html($html,$template_type);
			}			
		}
		return $template_arr;
	}
	public function convert_to_design_view_html($html,$template_type)
	{
		//convert translation html
		$html=preg_replace_callback('/__\[(.*?)\]__/s',array($this,'convert_translation_string_for_design_view'),$html);
		
		//customizer functions
		include_once plugin_dir_path(__FILE__)."classes/class-customizer.php";
		$find_replace=array();
		$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_logo($find_replace,$template_type);
		$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_from_address($find_replace,$template_type);
		
		$find_replace=apply_filters('wf_module_convert_to_design_view_html',$find_replace,$html,$template_type);			

		//below line must be below of every find and replace
		$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::dummy_data_for_customize($find_replace,$template_type,$html);

		return $this->replace_placeholders($find_replace,$html,$template_type);
	}
	private function convert_translation_string_for_design_view($match)
	{
		return is_array($match) && isset($match[1]) ? __($match[1],'wf-woocommerce-packing-list') : '';
	}
	private function convert_translation_strings($match)
	{
		return is_array($match) && isset($match[1]) ? __($match[1],'wf-woocommerce-packing-list') : '';
	}
	private function get_themes($template_type)
	{
		global $wpdb;
		$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;
		$qry=$wpdb->prepare("SELECT * FROM $table_name WHERE template_type=%s",array($template_type));
		return $wpdb->get_results($qry);
	}

	private function activate_theme($template_id,$template_type)
	{
		global $wpdb;
		$theme_data=$this->get_theme($template_id,$template_type);
		if(!is_null($theme_data) && isset($theme_data->id_wfpklist_template_data)) //theme exists under current document type
		{
			$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;

			//removing all themes from active state
			$update_data=array(
				'is_active'=>0,
			);
			$update_data_type=array(
				'%d'
			);
			$update_where=array(
				'template_type'=>$template_type
			);
			$update_where_type=array(
				'%s'
			);
			$wpdb->update($table_name,$update_data,$update_where,$update_data_type,$update_where_type);

			//setting current theme as active
			$update_data=array(
				'is_active'=>1,
				'updated_at'=>time(),
			);
			$update_data_type=array(
				'%d','%d'
			);
			$update_where=array(
				'id_wfpklist_template_data'=>$template_id
			);
			$update_where_type=array(
				'%d'
			);
			$wpdb->update($table_name,$update_data,$update_where,$update_data_type,$update_where_type);
			return true;
		}
		return false;
	}

	private function delete_theme($template_id,$template_type)
	{
		global $wpdb;
		$list=$this->get_themes($template_type);
		if($list && is_array($list) && count($list)>1) //delete action only works if more than one template exists
		{
			$theme_data=$this->get_theme($template_id,$template_type);
			if(!is_null($theme_data) && isset($theme_data->id_wfpklist_template_data)) //theme exists under current document type
			{
				if($theme_data->is_active==0) //active themes are not allowed to delete
				{
					$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;
					$wpdb->delete($table_name,array('id_wfpklist_template_data'=>$template_id),array('%d'));
					return true;
				}
			}
		}
		return false;
	}

	/**
	*	Ajax sub hook
	* 	generate HTML list view of all templates under the current document type
	* 	Also handles actions like: Activate, Delete
	*/
	public function my_templates()
	{
		global $wpdb;
		$out=array(
			'status'=>0,
			'msg'=>__("Error.",'wf-woocommerce-packing-list'),
			'html'=>'',
		);
		$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;
		$template_type=isset($_POST['template_type']) ? sanitize_text_field($_POST['template_type']) : '';
		if($template_type!="")
		{
			$template_action=isset($_POST['template_action']) ? sanitize_text_field($_POST['template_action']) : '';
			if($template_action!="")
			{
				$template_id=isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
				if($template_id>0) //template id is necessary for actions
				{
					if($template_action=='activate')
					{
						$this->activate_theme($template_id,$template_type);
					}elseif($template_action=='delete')
					{
						$this->delete_theme($template_id,$template_type);
					}
				}
			}

			$list=$this->get_themes($template_type);
			$html='';
			if($list && is_array($list) && count($list)>0)
			{
				$active_state='<span style="line-height:30px; color:green;">
								<span class="dashicons dashicons-yes" title="'.__("Active",'wf-woocommerce-packing-list').'" style="line-height:28px;"></span>'.__("Active",'wf-woocommerce-packing-list').'</span>&nbsp;';
				foreach($list as $listv)
				{
					$activate_btn='<button class="button-secondary wf_activate_theme" data-id="'.$listv->id_wfpklist_template_data.'" title="'.__("Activate",'wf-woocommerce-packing-list').'">
								<span class="dashicons dashicons-yes" style="line-height:28px;"></span>
							</button>';
					$delete_btn=($listv->is_active==0 ? '<button class="button-secondary wf_delete_theme" data-id="'.$listv->id_wfpklist_template_data.'">
								<span class="dashicons dashicons-trash" title="Delete" style="line-height:28px;"></span>
							</button>' : ''); //no delete button for active templates
					
					$active_btn=($listv->is_active==1 ? $active_state : $activate_btn);

					$html.='<div class="wf_my_template_item">				
						<div class="wf_my_template_item_name">
						'.$listv->template_name.'
						</div>
						<div class="wf_my_template_item_btn">
							'.$active_btn.'					
							<button class="button-secondary wf_customize_theme" data-id="'.$listv->id_wfpklist_template_data.'">
								<span class="dashicons dashicons-edit" title="Customize" style="line-height:28px;"></span>
							</button>
							'.$delete_btn.'
						</div>	
					</div>';
				}
				$out['status']=1;
				$out['html']=$html;
			}else
			{
				$out['status']=1;
				$out['html']=__("No template found.",'wf-woocommerce-packing-list');
			}
		}
		return $out;
	}

	
	/**
	*	Ajax sub hook
	* 	Save theme,
	* 	Handles create/update theme actions
	*/
	public function save_theme()
	{
		global $wpdb;

		$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;
        $out=array(
			'status'=>0,
			'msg'=>__("Unable to save theme.",'wf-woocommerce-packing-list'),
			'template_id'=>0,
			'name'=>'',
		);
       
		$template_id=isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
		$template_html=isset($_POST['codeview_html']) ? Wf_Woocommerce_Packing_List_Admin::strip_unwanted_tags($_POST['codeview_html']) : '';
		$template_type=isset($_POST['template_type']) ? sanitize_text_field($_POST['template_type']) : '';
		$tme=time();
		if($template_id==0) //template id=0 then new theme
		{
			$def_template=isset($_POST['def_template']) ? intval($_POST['def_template']) : 0;
			$name=isset($_POST['name']) ? sanitize_text_field($_POST['name']) : date('d-m-Y h:i:s A');
			if($template_type!='' && $def_template>=0) // template type,default template is necessary while creating new theme
			{
				$insert_data=array(
					'template_name'=>$name,
					'template_html'=>$template_html,
					'template_from'=>$def_template,
					'template_type'=>$template_type,
					'created_at'=>$tme,
					'updated_at'=>$tme
				);
				$insert_data_type=array(
					'%s','%s','%d','%s','%d','%d'
				);
				//check any active theme exists, if not then set the current theme as active
				$tt_qry=$wpdb->prepare("SELECT COUNT(id_wfpklist_template_data) AS ttnum FROM $table_name WHERE is_active=%d AND template_type=%s",array(1,$template_type));
				$total_arr=$wpdb->get_row($tt_qry);
				$is_active=0;
				if(isset($total_arr->ttnum) && $total_arr->ttnum==0) //no active theme, then set this theme active
				{
					$insert_data['is_active']=1;
					$insert_data_type[]='%d';
					$is_active=1;
				}

				if($wpdb->insert($table_name,$insert_data,$insert_data_type)) //success
				{
					$out=array(
						'status'=>1,
						'msg'=>__("Template created.",'wf-woocommerce-packing-list'),
						'template_id'=>$wpdb->insert_id,
						'name'=>$this->gen_page_title($name,': ',$is_active),
						'is_active'=>$is_active,
					);
				}
			}
		}else //update theme
		{
			$update_data=array(
				'template_html'=>Wf_Woocommerce_Packing_List_Admin::strip_unwanted_tags($template_html),
				'updated_at'=>$tme
			);
			$update_data_type=array(
				'%s','%d'
			);
			$update_where=array(
				'id_wfpklist_template_data'=>$template_id,
				'template_type'=>$template_type
			);
			$update_where_type=array(
				'%d','%s'
			);
			if($wpdb->update($table_name,$update_data,$update_where,$update_data_type,$update_where_type))
			{
				$name_arr=$wpdb->get_row("SELECT template_name,is_active FROM $table_name WHERE id_wfpklist_template_data=$template_id");
				$name='';
				$is_active=0;
				if(isset($name_arr->template_name))
				{
					$name=$name_arr->template_name;
					$is_active=$name_arr->is_active;
				}
				$out=array(
					'status'=>1,
					'msg'=>__("Template updated.",'wf-woocommerce-packing-list'),
					'template_id'=>$template_id,
					'name'=>$this->gen_page_title($name,': ',$is_active),
					'is_active'=>$is_active,
				);
			}
		}
		return $out;
	}


	/**
	* Ajax sub hook
	* Process code view HTML to render design view when switching from code view to design view
	*/
	public function update_from_codeview()
	{
		$html=isset($_POST['codeview_html']) ? Wf_Woocommerce_Packing_List_Admin::strip_unwanted_tags($_POST['codeview_html']) : '';
		$template_type=isset($_POST['template_type']) ? sanitize_text_field($_POST['template_type']) : '';
		$out=array(
			'status'=>0,
			'msg'=>__("Unable to switch view.",'wf-woocommerce-packing-list'),
			'html'=>'',
			'codeview_html'=>$html,
		);
		if($template_type!='')
		{
			$out['msg']='';
			$out['html']=$this->convert_to_design_view_html($html,$template_type);
			$out['status']=1;
		}
		return $out;
	}

	/**
	*
	* Taking HTML of active template
	*/
	public function get_template_html($template_type)
	{
		$html='';
		if($template_type!="")
		{
			$active_theme_arr=$this->get_current_active_theme($template_type);
			if(!is_null($active_theme_arr) && isset($active_theme_arr->id_wfpklist_template_data))
			{
				$html=stripslashes($active_theme_arr->template_html);				
			}else
			{
				$def_template=0;
				$def_template_path=$this->get_default_template_path($template_type);
				if($def_template_path) //module exists/ template exists
				{
					$template_arr=$this->load_default_templates($def_template_path,$template_type);
					if($template_arr && is_array($template_arr))
					{
						//$html=$template_arr[0]['codeview_html'];
						
						foreach($template_arr as $template)
						{
							$html=stripslashes($template['codeview_html']);
							break; //use first default template
						}
					}
				}
			}
		}
		$html=apply_filters('wf_pklist_alter_template_html', $html, $template_type);
		return $html;
	}

	/**
	*
	* Get style block from HTML
	* @param string $html template html
	* @return array $style_arr style blocks
	*/
	public function get_style_blocks($html)
	{		
		$re = '/<style type="text\/css">(.*?)<\/style>/sm';
		if(preg_match_all($re,$html,$style_arr)) //style exists
		{
			$style_arr=$style_arr[0];
		}else
		{
			$style_arr=array();
		}
		return $style_arr;
	}

	/**
	*
	* Remove style block from HTML
	* @param string $html template html
	* @param array $style_arr style blocks
	* @return string $html style removed html
	*/
	public function remove_style_blocks($html,$style_arr)
	{ 
		return str_replace($style_arr,'',$html);
	}

	/**
	*
	* Append style block to HTML
	* @param string $html template html
	* @param array $style_arr style blocks
	* @return string $html style added html
	*/
	public function append_style_blocks($html,$style_arr)
	{
		return implode("\n",$style_arr).$html;
	}

	/**
	*
	* Enveloping template HTML with header and footer
	*/
	public function append_header_and_footer_html($html,$template_type,$page_title)
	{
		//append header and footer.
		$template_path=$this->get_default_template_path($template_type);
		$header_html=$this->load_template_header_footer($template_path,$template_type,'header',$page_title);
		$footer_html=$this->load_template_header_footer($template_path,$template_type,'footer');
		return $header_html.$html.$footer_html;
	}
	public function generate_pdf_name($template_type,$order_ids)
	{
		if(count($order_ids)>1)
		{
			$name=$template_type.'_bulk_'.implode('-',$order_ids);
		}else
		{
			$name=$template_type.'_'.$order_ids[0];	
		}
		$name=apply_filters('wf_pklist_alter_pdf_file_name',$name,$template_type,$order_ids);
		return sanitize_file_name($name);
	}
	public function generate_template_pdf($html,$template_type,$name,$action)
	{
		include_once plugin_dir_path(WF_PKLIST_PLUGIN_FILENAME).'includes/class-wf-woocommerce-packing-list-pdf_generator.php';
		return Wf_Woocommerce_Packing_List_Pdf_generator::generate_pdf($html, $template_type, $name, $action);
	}
	public function generate_template_html($html,$template_type,$order,$box_packing=null,$order_package=null)
	{
		//convert translation html 
		$html=preg_replace_callback('/__\[(.*?)\]__/s',array($this,'convert_translation_strings'),$html);
		//customizer functions
		include_once plugin_dir_path(__FILE__)."classes/class-customizer.php";
		if($this->rtl_css_added===false)
		{
			$html=$this->toggle_rtl($html); //this method uses funtion in above included file
			$this->rtl_css_added=true;
		}

		$find_replace=array();
		$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_logo($find_replace,$template_type,$order);
		$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_from_address($find_replace,$template_type,$order);

		/* this filter will add other datas */
		$find_replace=apply_filters('wf_module_generate_template_html',$find_replace,$html,$template_type,$order,$box_packing,$order_package);
		
		$html=apply_filters('wt_pklist_alter_order_template_html',$html,$template_type,$order,$box_packing,$order_package);

		//*******the main hook to alter everything in the template *******//
		$find_replace=apply_filters('wf_pklist_alter_find_replace',$find_replace,$template_type,$order,$box_packing,$order_package, $html);

		$html=Wf_Woocommerce_Packing_List_CustomizerLib::hide_empty_elements($find_replace,$html,$template_type);
		
		return $this->replace_placeholders($find_replace,$html,$template_type);
	}

	/**
	*
	* Enable/Disable RTL
	* @param $html template HTML
	* @return $html formatted template HTML
	*/
	public function toggle_rtl($html)
	{
		$rtl_support=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_add_rtl_support');
		if($rtl_support=='Yes')
		{
		 	$html=str_replace('wfte_rtl_main','wfte_rtl_main wfte_rtl_template_main',$html);
		 	if($this->template_for_pdf==true)
		 	{
		 		add_filter('wf_pklist_reverse_product_table_columns',function($columns_list_arr,$template_type){
		 			return array_reverse($columns_list_arr,true);
		 		},10,2);

		 		//this for checking where to add last table column CSS class, In case of `RTL PDF table` the last column CSS class must add to first column
		 		add_filter('wf_pklist_is_rtl_for_pdf','__return_true');

		 		//reverse product summary columns
		 		$html=$this->reverse_product_summary_columns($html);
		 		$this->custom_css='
		 		.wfte_invoice_data{ padding-top:1px !important;}
		 		.wfte_invoice_data div{ text-align:left !important; padding-left:10px !important;}';
		 	}
		 	$this->custom_css.='
		 	body, html{direction:rtl; unicode-bidi:bidi-override; }
		 	.wfte_rtl_main .float_left{ float:right; }
		 	.wfte_rtl_main .float_right{ float:left; }
		 	.wfte_rtl_main .float_left{ float:right; }
		 	.wfte_rtl_main .wfte_text_right{ text-align:left !important; } 	
		 	.wfte_rtl_main .wfte_text_left{ text-align:right !important; } 	
		 	.wfte_invoice_data div span:first-child{ float:right !important;} 
		 	.wfte_order_data div span:first-child{ float:right !important;} 
		 	.wfte_list_view div span:first-child{ float:right !important;} 
		 	';
		}
		return $html;
	}

	/**
	*
	* DomPDF will not revrese the table columns in RTL so we need to do it manually
	* @param $html template HTML
	* @return $html formatted template HTML
	*/
	protected function reverse_product_summary_columns($html)
	{
		$table_html_arr=array();
		$table_html=Wf_Woocommerce_Packing_List_CustomizerLib::getElmByClass('wfte_payment_summary_table',$html);
		if($table_html)
		{
			$table_arr=array();
			if(preg_match('/'.$table_html[0].'(.*?)<\/table>/s',$html,$table_arr))
			{ 
				$tbody_arr=array();
				if(preg_match('/<tbody(.*?)>/s',$table_arr[0],$tbody_arr)) //tbody exists
				{
					$table_html_arr[]=$tbody_arr[0];
				}
				$tr_arr=array();
				if(preg_match_all('/<tr(.*?)>(.*?)<\/tr>/s',$table_arr[0],$tr_arr)) //tr exists
				{ 
					foreach ($tr_arr[0] as $tr_k=>$tr_html) 
					{
						$td_arr=array();
						preg_match_all('/<td(.*?)>(.*?)<\/td>/s',$tr_html,$td_arr);
						$td_html_arr=array_reverse($td_arr[0]);
						$table_html_arr[]='<tr'.$tr_arr[1][$tr_k].'>'.implode("\n",$td_html_arr).'</tr>';
					}
				}
				if(count($tbody_arr)>0) //tbody exists
				{
					$table_html_arr[]='</tbody>';
				}
				$formatted_table_html=implode("",$table_html_arr);
				$html=str_replace($table_arr[1],$formatted_table_html,$html);
			}
		}
		return $html;
	}



	/**
	* 
	* Replacing all the placeholders with corresponding data
	* @param array $find_replace find and replace values
	* @param string $html template html
	* @param string $template_type document type
	* @return string placeholders replaced HTML
	*/
	public function replace_placeholders($find_replace,$html,$template_type)
	{
		$find=array_keys($find_replace);
		$replace=array_values($find_replace);
		$html=str_replace($find,$replace,$html);
		return $html;
	}

	/**
    *	This checking is useful when attaching same document in different email in same time.
    *	@since 4.0.8
    */
    public function is_pdf_generated($generated_list, $current_pdf_name)
    {
    	$pdf_path=false;
    	foreach ($generated_list as $generated_pdf_path)
    	{
    		if(basename($generated_pdf_path)==$current_pdf_name.'.pdf')
    		{
    			$pdf_path=$generated_pdf_path;
    			break;
    		}
    	}
    	return $pdf_path;
    }

	/*
	* Ajax sub hook
	* Get template data for customizer page
	*/
	public function get_template_data()
	{
		$template_type=isset($_GET['template_type']) ? $_GET['template_type'] : '';
		$out=array(
			'status'=>0,
			'msg'=>__("Unable to load template.",'wf-woocommerce-packing-list'),
			'html'=>'',
			'codeview_html'=>'',
			'name'=>'',
			'is_active'=>0,
		);
		if($template_type!='')
		{
			$template_id=isset($_GET['template_id']) ? $_GET['template_id']*1 : 0;
			if($template_id==0) //no template specified then use defult template id, is available
			{
				$def_template=isset($_GET['def_template']) ? $_GET['def_template']*1 : $this->default_template;
				$def_template_path=$this->get_default_template_path($template_type);
				if($def_template_path) //module exists/ template exists
				{
					$template_arr=$this->load_default_templates($def_template_path,$template_type);
					if(isset($template_arr[$def_template])) //default template exists
					{
						$out['msg']='';
						$out['html']=$template_arr[$def_template]['html'];
						$out['codeview_html']=$template_arr[$def_template]['codeview_html'];
						$out['status']=1;
						//$out['name']=$this->gen_page_title($template_arr[$def_template]['title'],': ',0);
						$out['name']='&lt;'.__('Untitled template','wf-woocommerce-packing-list').'&gt;';
					}
				}
			}else //load specified template
			{
				$theme_data=$this->get_theme($template_id,$template_type);
				if(!is_null($theme_data) && isset($theme_data->id_wfpklist_template_data)) //theme exists
				{  
					$html=isset($theme_data->template_html) ? stripslashes($theme_data->template_html) : '';
					$out['msg']='';
					$out['html']=$this->convert_to_design_view_html($html,$template_type);
					$out['codeview_html']=$html;
					$out['status']=1;
					$out['name']=$this->gen_page_title($theme_data->template_name,': ',$theme_data->is_active);
					$out['is_active']=$theme_data->is_active;
				}
			}
		}
		return $out;
	}


	public static function envelope_customize_ftblock($expndble=true)
	{
		if($expndble)
		{
		?>
			</div>
		<?php
		}
		?>
		</div>
		<?php
	}
	public static function envelope_customize_hdblock($key,$hd,$expndble=true,$toggle=true,$non_customize=false)
	{
		?>
		<div class="wf_side_panel" data-type="<?php echo $key; ?>" data-non-customize="<?php echo ($non_customize ? 1 : 0); ?>">
		<div class="wf_side_panel_hd">
			<div class="wf_side_panel_toggle" style="float:left; text-align:left; width:30px; height:20px;">	
			<?php
			if($expndble)
			{
			?>			
				<span class="dashicons dashicons-arrow-right" style="line-height:30px;"></span>
			<?php
			}
			?>
			</div>
			<?php echo $hd;?>
			<?php
			if($toggle)
			{
			?>
			<div class="wf_side_panel_toggle">
				<input type="checkbox" name="" data-type="<?php echo $key; ?>" class="wf_slide_switch">
			</div>
			<?php
			}
			?>
		</div>
		<?php
		if($expndble)
		{
		?>
		<div class="wf_side_panel_content">
		<?php	
		}
	}
}