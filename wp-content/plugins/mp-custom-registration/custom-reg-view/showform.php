	<?php
	/**
	 * @variable    $formsetting
	 * @uses        Contains form element configurations
	 * @origin      Controller: contactforms, Method: showform()
	 */
	$url = get_permalink(isset($form_id) ? $form_id : get_the_ID());
	$sap = strpos($url, "?") ? "&" : "?";
	$formurl = $url . $sap;
	?>

	<!-- Start form -->
    <div class="w3eden">
		<div class="container-fluid">

		</div>
        <div class="container-fluid">
			<div id="method">
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="btn-group btn-breadcrumb">
						<?php if (count($form_parts_names) > 1) { ?>
							<?php foreach ($form_parts_names as $index => $crumb_text) { ?>
								<li id='<?php echo $index . "_crumb" ?>' class='btn btn-default <?php if ($index == "form_part_0") echo "active visited" ?>' data-part="<?php echo $index ?>"><a disabled='disabled' class="breadcrumbs" href="" onclick='return false'><?php echo $crumb_text ?></a></li>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
			<div id="formarea">
				<!-- <form id="form" action="" method="post" enctype="multipart/form-data" > -->
					<input type="hidden" id="formid" name="form_id" value="<?php echo $form_id ?>" />
					<?php
					$part_count = 0;

					foreach ( $form_parts_html as $part ) {

						echo $part;

						$part_count++;
					}
					?>
				<!-- </form> -->
			</div>
			</div>
		</div>
	<!-- End form -->

	<script type='text/javascript'>
		jQuery(document).ready(function(){
			jQuery('.select2element').select2();
		});
	</script>
	<script type='text/javascript'>
		jQuery(document).ready(function($){
		jQuery(function($) {
			var submit_btn_text;
			var next_part_id;
			var this_part_id;
			$(document).ready(function() { //code
				// Show hard form partitions
				var set_show = {display: 'block'};
				var set_hide = {display: 'none'};
				var validator = $('#form').validate();
				var validInput = true;

				//$('#form_part_0').css(set_show);
				$('.change-part').on('click', function() {
					next_part_id = $(this).attr('data-next');
					this_part_id = $(this).attr('data-parent');


					// Pre validate
					validInput = true;
					var $inputs = $('#' + this_part_id).find("input");
						$inputs.each(function() {
						if (!validator.element(this) && validInput) {
						validInput = false;
						$(this).parent('.form-group').removeClass('has-success').addClass('has-error');
						} else {
						if(!validator.element(this))
							$(this).parent('.form-group').removeClass('has-success').addClass('has-error');
						else
							$(this).parent('.form-group').removeClass('has-error').addClass('has-success');
						}
					});

					if (validInput == true) {
						if (next_part_id != undefined) {
							$('#' + this_part_id).css(set_hide);
							$('#' + next_part_id).css(set_show);
						}
						$('#' + next_part_id + '_crumb').addClass('active');
						$('#' + next_part_id + '_crumb').addClass('visited');
						$('#' + this_part_id + '_crumb').removeClass('active');
							$(this).parent('.form-group').removeClass('has-error').addClass('has-success');

					} else {
						msgs = new Array();
						msgs.push("Please fill this section properly before proceeding");
						showAlerts(msgs,'danger');
					}
				});

				$('.breadcrumbs').on('click', function() {
					var set_show = {display: 'block'};
					var set_hide = {display: 'none'};
					show_part_id = $(this).parent().attr('data-part');
					hide_part_id = $('.breadcrumbli.active').attr('data-part');
					if ($('#' + show_part_id + '_crumb').hasClass('visited')) {
						$('.breadcrumbli.active').removeClass('active');
						$(this).parent().addClass('active');
						$('#' + hide_part_id).css(set_hide);
						$('#' + show_part_id).css(set_show);
					} else {
						// Show the error
						msgs = new Array();
						msgs.push('Fill the current area to proceed');
						showAlerts(msgs,'danger');
					}

				});

			// ajax submit
				var options = {
					url: '<?php echo $formurl ?>action=submit_form',
					resetForm: true,
					beforeSubmit: function() {
						submit_btn_text = $('#submit').html();
						$('#submit').html("<i id='spinner' class='fa fa-spinner fa-spin'></i> Please wait");
					}, // pre-submit callback
					success: function(response) {
						console.log(response);
						msgs = new Array();
						$('#spinner').remove();
						$('#submit').html(submit_btn_text);
						$('#'+this_part_id).css(set_hide);
						$('#form_part_0').css(set_show);
						try
						{
						  response_vars = JSON.parse(response);
						}
						catch (e)
						{
							console.log(e);
						}
						if (response_vars.action == 'success' && validInput === true) {
							msgs.push(response_vars.message);
							showAlerts(msgs, 'success');
						} else {
								if(typeof(response_vars) != 'undefined' && response_vars.action == 'success'){
								msgs.push('<?php echo str_replace("'", "\'", ($formsetting['thankyou'] == '' ? 'Form submitted succesfully' : $formsetting['thankyou'])) ?>');
								showAlerts(msgs, 'success');
								$('#method').html(response_vars.paymentform);
							}
							else if(response_vars.action != 'success')
							{
								msgs.push(response_vars.message == '' ? 'Form submission failed, please check the entries again' : response_vars.action);
								showAlerts(msgs, 'danger');
							}
							else {
								msgs.push(response_vars.message == '' ? 'Form submission failed, please check the entries again' : response_vars.message);
								showAlerts(msgs, 'danger');
							}
						}
					}
				};
				$('#form').on('submit', function() {
					if (validInput == true) {
						$(this).ajaxSubmit(options);
					}
					return false;
				});
			});
		});

		jQuery(document).ready(function($){
			$('.conditioned').each(function(){
				var cur_field_id = $(this).attr('id');
				cur_conditioned_fields = $(this).attr('data-cond-fields');
				cur_cond_fields = cur_conditioned_fields.split('|');
				for (i=0 ; i<cur_cond_fields.length ; i++) {
					var cond_field = cur_cond_fields[i].split(':');
					addConditionClass(jQuery('#'+cond_field[0]), cur_field_id);

				}
				$('.cond_filler_'+cur_field_id).each(function(){
						if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio')
							$(this).on('change', function(){
								applyRule(cur_field_id);
							});
						else if ($(this).attr('type') == 'text')
							$(this).on('keyup', function(){
								applyRule(cur_field_id);
							});
						else
							$(this).on('change', function(){
								applyRule(cur_field_id);
							});
					});
			});
		});

		function showAlerts(msgs, type) {
			jQuery('.formnotice').slideUp();
			alert_box = '<div style="margin-top: 20px" class="alert formnotice alert-' + type + ' disappear"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
			for (i = 0; i < msgs.length; i++) {
				alert_box += '' + msgs[i] + '<br/>';
			}
			alert_box += '</div>';
			jQuery('#form').prepend(alert_box);

		}

		function addConditionClass(field_id, cond_class) {
			jQuery(field_id).each(function(){
				if (jQuery(this).is('input') || jQuery(this).is('select'))
					jQuery(this).addClass('cond_filler_'+cond_class);
				jQuery(this).children().each(function(){
					addConditionClass(jQuery(this), cond_class);
				})
			});
			return false;
		}

		function compareRule(objs, cmp_operator, cmp_value) {
			var comp_res = false;
			switch(cmp_operator) {
				case 'is':
					jQuery(objs).each(function(){
						if (jQuery(this).attr('type') == cmp_value) {
							comp_res = true;
							return;
						}
					});
					break;
				case 'is-not':
					jQuery(objs).each(function(){
						if (jQuery(this).attr('type') != cmp_value) {
							comp_res = true;
							return;
						}
					});
					break;
				case 'less-than':
					jQuery(objs).each(function(){
						if (jQuery(this).val() < cmp_value) {
							comp_res = true;
							return;
						}
					});
					break;
				case 'greater-than':
					jQuery(objs).each(function(){
						if (jQuery(this).val() > cmp_value) {
							comp_res = true;
							return;
						}
					});
					break;
				case 'starts-with':
					jQuery(objs).each(function(){
						if (jQuery(this).val().indexOf(cmp_value) == 0) {
							comp_res = true;
							return;
						}
					});
					break;
				case 'contains':
					jQuery(objs).each(function(){
						if (jQuery(this).val().indexOf(cmp_value) != -1) {
							comp_res = true;
							return;
						}
					});
					break;
				case 'ends-with':
					jQuery(objs).each(function(){
						indexPoint = (jQuery(this).val().length - cmp_value.length);
						if (jQuery(this).val().indexOf(cmp_value, indexPoint) == indexPoint) {
							comp_res = true;
							return;
						}
					});
					break;
				default:
					comp_res = false;
					break;

			}

			return comp_res;
		}

		function applyRule(field_id) {
			jQuery('.cond_filler_'+field_id).each(function(){
				var this_conditions = jQuery('#'+field_id).attr('data-cond-fields').split('|');
				var this_action = jQuery('#'+field_id).attr('data-cond-action').split(':');
				var cmp_res = this_action[1] == 'all' ? true : false;
				for (i=0 ; i<this_conditions.length ; i++) {
					var this_condition = this_conditions[i].split(':');
					cmp_id = this_condition[0];
					cmp_objs = null;
					if (cmp_id.indexOf('Checkbox_') == 0 || cmp_id.indexOf('Radio_') == 0) {
						cmp_objs = jQuery('#'+cmp_id).find(':checked');
					} else {
						cmp_objs = jQuery('#'+cmp_id).children();
					}
					cmp_operator = this_condition[1];
					cmp_value = this_condition[2];
					tmp_res = compareRule(cmp_objs, cmp_operator, cmp_value);
					if (this_action[1] == 'all') cmp_res = cmp_res && tmp_res;
					else cmp_res = cmp_res || tmp_res;
				}
				if (cmp_res == true) {
					jQuery('#'+field_id).removeClass('hide');
				} else {
					jQuery('#'+field_id).addClass('hide');
				}
			});

		}
		});
    </script>
