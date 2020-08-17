<?php
class Address {

	public function control_button() {
		ob_start();
		?>
		<li class="list-group-item" data-type="<?php echo __CLASS__ ?>" for="Address">
			<span class="lfi lfi-name"></span> Address
			<a title="Address" rel="Address" class="add" data-template='Address' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
	</li>
	    <?php
		$control_button_html = ob_get_clean();
		return $control_button_html;
	}

	public function field_settings($fieldindex, $fieldid, $field_infos) {
		ob_start();
?>
		<li class="list-group-item" data-type="<?php echo __CLASS__ ?>" id="field_<?php echo $fieldindex; ?>">
			<input type="hidden" name="contact[fields][<?php echo $fieldindex ?>]" value="<?php echo $fieldid; ?>">
			<span id="label_<?php echo $fieldindex; ?>"><?php echo $field_infos[$fieldindex]['label'] ?>:</span>
			<a href="#" rel="field_<?php echo $fieldindex; ?>" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
			<a href="#" class="cog-trigger" rel="#cog_<?php echo $fieldindex; ?>"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
			<div class="cog" id="cog_<?php echo $fieldindex; ?>" style='display: none'>
				<fieldset>
					<h5>Settings</h5>
					<div class="form-group">
						<label>Label:</label>
						<input class="form-control form-field-label" data-target="#label_<?php echo $fieldindex; ?>" type="text" value="<?php echo $field_infos[$fieldindex]['label'] ?>" name="contact[fieldsinfo][<?php echo $fieldindex ?>][label]" />
					</div>
					<div class="form-group">
						<label>Note</label>
						<textarea class="form-control" type="text" value="" name="contact[fieldsinfo][<?php echo $fieldindex ?>][note]"><?php echo $field_infos[$fieldindex]['note'] ?></textarea>
					</div>
					<div class='form-group'>
						<label><input rel='condition-params' class='cond' type='checkbox' name='contact[fieldsinfo][<?php echo $fieldindex ?>][conditioned]' value='1' <?php if (isset($field_infos[$fieldindex]['conditioned'])) echo 'checked="checked"'; ?>/> Conditional logic</label>
						<div id="cond_<?php echo $fieldindex ?>" class='cond-params' style='display:none'>
							<div class="form-group">
								<div class="row row-bottom-buffer">
									<div class="col-md-12">
										<select class="select" name="contact[fieldsinfo][<?php echo $fieldindex ?>][condition][action]">
											<option <?php if (isset($field_infos[$fieldindex]['condition']) and $field_infos[$fieldindex]['condition']['action'] == 'show') echo 'selected="selected"' ?> value="show">Show</option>
											<option <?php if (isset($field_infos[$fieldindex]['condition']) and $field_infos[$fieldindex]['condition']['action'] == 'hide') echo 'selected="selected"' ?> value="hide">Hide</option>
										</select>
										this field if
										<select class="select" name="contact[fieldsinfo][<?php echo $fieldindex ?>][condition][boolean_op]">
											<option <?php if (isset($field_infos[$fieldindex]['condition']) and $field_infos[$fieldindex]['condition']['boolean_op'] == 'all') echo 'selected="selected"' ?> value="all">All</option>
											<option <?php if (isset($field_infos[$fieldindex]['condition']) and $field_infos[$fieldindex]['condition']['boolean_op'] == 'any') echo 'selected="selected"' ?> value="any">Any</option>
										</select>
										of these conditions are met
									</div>
								</div>
								<?php $cond_list = isset($field_infos[$fieldindex]['condition']) ? $field_infos[$fieldindex]['condition']['value'] : array(); ?>
								<?php foreach($cond_list as $key => $value) { ?>
								<div class='row row-bottom-buffer' rel="row">
									<div class='col-md-4'>
										<select class='form-control cond-field-selector' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['field'][$key]) ? $field_infos[$fieldindex]['condition']['field'][$key] : ''  ?>' name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][field][]'>
											<option value="">Select a field</option>
										</select>
									</div>
									<div class='col-md-3'>
										<select class='form-control cond-operator' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['op'][$key]) ? $field_infos[$fieldindex]['condition']['op'][$key] : ''  ?>' name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][op][]'>
											<option value='is'>Is</option>
											<option value='is-not'>Is not</option>
											<option value='less-than'>Less than</option>
											<option value='greater-than'>Greater than</option>
											<option value='contains'>Contains</option>
											<option value='starts-with'>Starts with</option>
											<option value='ends-with'>Ends with</option>
										</select>
									</div>
									<div class='col-md-4'>
										<select class='form-control is-cond-selector' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>'>
											<option value='email'>Email</option>
											<option value='phone'>Phone</option>
										</select>
										<input type='text' class='form-control is-cond-text hide' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>' placeholder='Enter a value' value=''/>
										<input type='hidden' value='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>' class="is-cond-data" name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][value][]'/>
									</div>
									<div class="col-md-1">
										<a href="#" class="add-cond-option"
										   rel="<?php echo $fieldindex ?>"><i
												class="glyphicon glyphicon-plus-sign pull-left"></i></a>
										<a href="#" class="del-cond-option"
										   rel="<?php echo $fieldindex ?>"><i
												class="glyphicon glyphicon-minus-sign pull-left"></i></a>

									</div>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label><input rel="req-params" class="req" type="checkbox"
									  name="contact[fieldsinfo][<?php echo $fieldindex ?>][required]"
									  value="1" <?php echo (isset($field_infos[$fieldindex]['required']) ? "checked=checked" : "") ?> />
							Required</label>

						<div
							class="req-params" <?php echo (!isset($field_infos[$fieldindex]['required']) ? "style='display: none'" : "") ?>>
							<input type="text"
								   name="contact[fieldsinfo][<?php echo $fieldindex ?>][reqmsg]"
								   placeholder="Field Required Message"
								   value="<?php echo $field_infos[$fieldindex]['reqmsg'] ?>"
								   class="form-control"/>
							<label>Validation:</label>
							<select name="contact[fieldsinfo][<?php echo $fieldindex ?>][validation]" class="form-control">
								<option value='text'>Text</option>
							</select>
						</div>
					</div>
				</fieldset>
				<?php do_action("form_field_".__CLASS__."_settings",$fieldindex, $fieldid, $field_infos); ?>
				<?php do_action("form_field_settings",$fieldindex, $fieldid, $field_infos); ?>
			</div>
			<div class="field-preview">
				<?php
					$finfo = $field_infos[$fieldindex];
					$finfo['id'] = $fieldindex;
					echo self::field_preview_html($finfo);
				?>
			</div>
		</li>
		<?php
		$field_settings_html = ob_get_clean();
		return $field_settings_html;
	}

	public function field_preview_html( $params = array(), $form_val = '' ) {
		ob_start();
		?>
		<div class='form-group'>
			<div class='row'>
				<div class='col-md-12'>
					<input disabled='disabled' class='form-control row-bottom-buffer' type='text' name='submitform[][address1]' id='street1' placeholder="Address 1" />
				</div>
			</div>
			<div class='row'>
				<div class='col-md-12'>
					<input disabled='disabled' class='form-control row-bottom-buffer' type='text' name='submitform[][address2]' id='street2' placeholder="Address 2" />
				</div>
			</div>
			<div class='row'>
				<div class='col-md-12'>
					<input disabled='disabled' class='form-control row-bottom-buffer' type='text' name='submitform[][city]' id='city' placeholder="City" />
				</div>
			</div>
			<div class='row'>
				<div class='col-md-6'>
					<select data-placeholder='Choose a country' disabled='disabled' style='width: 100%' class='select2element' id='_selector_country' name='submitform[][country]' >
						<option value='none'>Choose a country</option>
					</select>
				</div>
				<div class='col-md-6'>
					<select data-placeholder='Choose a state' disabled='disabled' style='width: 100%' class='select2element' id='_selector_state' name='submitform[][state]' >
					<option value='none'>Choose a state</option>
					</select>
				</div>
			</div>
		</div>
		<?php
		$field_render_html = ob_get_clean();
		return $field_render_html;
	}

	public function field_render_html($params = array(), $form_val = [] ) {
		ob_start();

		$condition_fields = '';
		$cond_action = '';
		$cond_boolean = '';
		if (isset($params['condition']) and isset($params['conditioned'])) {
			$cond_boolean = $params['condition']['boolean_op'];
			$cond_action = $params['condition']['action'];
			foreach($params['condition']['field'] as $key => $value) {
				$field_id = $value;
				$field_op = $params['condition']['op'][$key];
				$field_value = $params['condition']['value'][$key];
				$condition_fields .= ($field_id.':'.$field_op.':'.$field_value . '|');
			}
			$condition_fields = rtrim($condition_fields, '|');
		}
		if ( ! empty( $form_val ) && is_array( $form_val ) && array_key_exists( $params['id'], $form_val ) ) {
			$value_field = $form_val[$params['id']];
		}else{
			$value_field = '';
		}
		?>
		<div id="<?php echo $params['id'] ?>" class='form-group <?php if (isset($params['conditioned'])) echo " conditioned hide "?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
			<label style='display: block; clear: both'><?php echo $params['label'] ?></label>
			<div class='form-group'>
				<div class='row'>
					<div class='col-md-12'>
						<input class='form-control row-bottom-buffer' type='text' name='submitform[<?php echo $params['id'] ?>][address1]' id='street1' placeholder="Address 1" value='<?php if( $value_field != '' && $value_field['address1'] != '' ) echo $value_field['address1'];?>' <?php echo mpCustomRequired($params) ?>/>
					</div>
				</div>
				<div class='row'>
					<div class='col-md-12'>
						<input class='form-control row-bottom-buffer' type='text' name='submitform[<?php echo $params['id'] ?>][address2]' id='street2' placeholder="Address 2" value='<?php if( $value_field != '' && $value_field['address2'] != '' ) echo $value_field['address2'];?>'  <?php echo mpCustomRequired($params) ?> />
					</div>
				</div>
				<div class='row'>
					<div class='col-md-12'>
						<input class='form-control row-bottom-buffer' type='text' name='submitform[<?php echo $params['id'] ?>][city]' id='city' placeholder="City" value='<?php if( $value_field != '' && $value_field['city'] != '' ) echo $value_field['city'];?>' <?php echo mpCustomRequired($params) ?> />
					</div>
				</div><?php
				global $woocommerce;
				$countries_obj         = new WC_Countries();
				$countries             = $countries_obj->__get( 'countries' );
				$default_country       = $countries_obj->get_base_country();
				$default_county_states = $countries_obj->get_states( $default_country );
				if ( $value_field != '' && $value_field['country'] != '' ) {
					$val_con = $value_field['country'];
				}else{
					$val_con = '';
				}
				echo '<div id="custom_countries" style="display:inline-block;margin-right:60px;">';
				$sd = 'submitform['.$params["id"].'][country]';
				woocommerce_form_field($sd, array(
					'type'        => 'select',
					'class'       => array( 'chzn-drop' ),
					'value'       => ! empty( $default_country ) ? $default_country : '',
					'label'       => __('Choose country'),
					'placeholder' => __('Enter country'),
					'options'     => $countries,
					'default'       => ! empty( $val_con ) ? $val_con : $default_country,
					)
				);
				echo '</div>';

				if( $value_field != '' && $value_field['state'] != '' ) {
					$val_state = $value_field['state'];
				}else{
					$val_state = '';
				}

				if( $val_con ){
					$states = $countries_obj->get_states( $val_con );
				} else {
					$states = array();
				}


				echo '<div id="custom_states" style="display:inline-block;">';
				$sd_state = 'submitform['.$params["id"].'][state]';

				woocommerce_form_field($sd_state, array(
					'type'        => 'select',
					'class'       => array( 'chzn-drop' ),
					'label'       => __('Choose states'),
					'placeholder' => __('Enter states'),
					'options'     => ! empty( $states ) ? $states : $default_county_states,
					'default'       => $val_state,
					)
				);
				echo '</div>';

				?>
			</div>
		</div>
		<?php
		$field_render_html = ob_get_clean();
		return $field_render_html;
	}

	public function configuration_template() {
		ob_start();
		?>
	<script type="text/x-mustache" id="template-Address">
		<li class="list-group-item" data-type="<?php echo __CLASS__ ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
			<span id="label_{{ID}}">{{title}}</span>
			<a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
			<a href="#" class="cog-trigger" rel="#cog_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
			<div class="cog" id="cog_{{ID}}" style="display: none;">
				<fieldset>
					<h5>Settings</h5>

					<div class="form-group">
						<label>Label:</label>
						<input class="form-control form-field-label" data-target="#label_{{ID}}" type="text"
							   value="{{title}}"
							   name="contact[fieldsinfo][{{ID}}][label]"/>
					</div>
					<div class="form-group">
						<label>Note</label>
						<textarea class="form-control" type="text" value=""
								  name="contact[fieldsinfo][{{ID}}][note]"></textarea>
					</div>
					<div class='form-group'>
						<label><input rel='condition-params' class='cond' type='checkbox' name='contact[fieldsinfo][{{ID}}][conditioned]' value='1'/> Conditional logic</label>
						<div id="cond_{{ID}}" class='cond-params' style='display:none'>
							<div class='form-group'>
								<div class="row row-bottom-buffer">
									<div class="col-md-12">
										<select class="select" name="contact[fieldsinfo][{{ID}}][condition][action]">
											<option value="show">Show</option>
											<option value="hide">Hide</option>
										</select>
										this field if
										<select class="select" name="contact[fieldsinfo][{{ID}}][condition][boolean_op]">
											<option value="show">All</option>
											<option value="hide">Any</option>
										</select>
										of these conditions are met
									</div>
								</div>
								<div class='row row-bottom-buffer' rel='row'>
									<div class='col-md-4'>
										<select class='form-control cond-field-selector' data-selection='' name='contact[fieldsinfo][{{ID}}][condition][field][]'>
											<option value="">Select a field</option>
										</select>
									</div>
									<div class='col-md-3'>
										<select class='form-control cond-operator' name='contact[fieldsinfo][{{ID}}][condition][op][]'>
											<option value='is'>Is</option>
											<option value='is-not'>Is not</option>
											<option value='less-than'>Less than</option>
											<option value='greater-than'>Greater than</option>
											<option value='contains'>Contains</option>
											<option value='starts-with'>Starts with</option>
											<option value='ends-with'>Ends with</option>
										</select>
									</div>
									<div class='col-md-4'>
										<select class='form-control is-cond-selector'>
											<option value='email'>Email</option>
											<option value='phone'>Phone</option>
										</select>
										<input type='text' class='form-control is-cond-text hide' placeholder='Enter a value' value=''/>
										<input type='hidden' value='' class='is-cond-data' name='contact[fieldsinfo][{{ID}}][condition][value][]'/>
									</div>
									<div class="col-md-1">
										<a href="#" class="add-cond-option"
										   rel="{{ID}}"><i
												class="glyphicon glyphicon-plus-sign pull-left"></i></a>
										<a href="#" class="del-cond-option"
										   rel="{{ID}}"><i
												class="glyphicon glyphicon-minus-sign pull-left"></i></a>

									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label><input rel="req-params" class="req" type="checkbox"
									  name="contact[fieldsinfo][{{ID}}][required]" value="1"/> Required</label>

						<div class="req-params" style="display: none">
							<input type="text" name="contact[fieldsinfo][{{ID}}][reqmsg]"
								   placeholder="Field Required Message" value="" class="form-control"/>
							<label>Validation:</label>
							<select name="contact[fieldsinfo][{{ID}}][validation]" class="form-control">
							<?php foreach(getCustomValidation() as $op => $label) { ?>
								<option value="<?php echo $op ?>"><?php echo $label ?></option>
							<?php } ?>
							</select>
						</div>
					</div>
				</fieldset>
				<?php do_action("form_field_".__CLASS__."_settings_template"); ?>
				<?php do_action("form_field_settings_template"); ?>
			</div>
			<div class="field-preview">
				<?php echo self::field_preview_html() ?>
			</div>
		</li>
	</script>
		<?php
		$field_configuration_template = ob_get_clean();
		return $field_configuration_template;
	}

	function process_field() {

	}

}
