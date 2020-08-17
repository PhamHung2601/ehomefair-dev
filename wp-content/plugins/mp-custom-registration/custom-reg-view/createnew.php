<?php
if (isset($form_data)) {
    /**
    * @variable    $checked_fields
    * @uses        Contains list of fields that were checked
    */
    $checked_fields = isset($form_data['fields']) ? $form_data['fields'] : array();
    /**
    * @variable    $field_infos
    * @uses        Contains info on each field of the form
    */
    $field_infos = isset($form_data['fieldsinfo']) ? $form_data['fieldsinfo'] : '';
} else {
    $form_data['fields']['wk_username']= 'Username';
    $form_data['fields']['wk_email']= 'Email';
    $form_data['fieldsinfo']['wk_username']['label']='Username';
    $form_data['fieldsinfo']['wk_username']['note']='';
    //	$form_data['fieldsinfo']['wk_username']['condition']=array('action'=>'show','field'=>array([0]=>''),'op'=>array([0]=>''),'value'=>array([0]=>''));
    $form_data['fieldsinfo']['wk_username']['reqmsg']='';
    $form_data['fieldsinfo']['wk_username']['validation']='text';
    $form_data['fieldsinfo']['wk_email']['label']='Email';
    $form_data['fieldsinfo']['wk_email']['note']='';
	$form_data['fieldsinfo']['wk_email']['condition']['action']='show';
	$form_data['fieldsinfo']['wk_email']['condition']['boolean_op']='show';
	$form_data['fieldsinfo']['wk_email']['condition']['field'][0]='';
	$form_data['fieldsinfo']['wk_email']['condition']['op'][0]='is';
	$form_data['fieldsinfo']['wk_email']['condition']['value'][0]='';
	$form_data['fieldsinfo']['wk_email']['reqmsg']='';
	$form_data['fieldsinfo']['wk_email']['validation_type']='email';
	$form_data['fieldsinfo']['wk_email']['validation']='email';
	$field_infos=$form_data['fieldsinfo'];
	$checked_fields = $form_data['fields'];
}
?>
<?php
$fields = $commonfields;
?>
<!-- Preprocessing starts -->
<div class="w3eden">
	<div class="container-fluid"><br/>
		<div class="row">
			<div class="col-md-4" id="allformfields">
				<div class="panel-group" id="accordion">
				    <input type="hidden" class="form-control text-center shortcode-code input-lg" readonly="readonly" value="<?php echo "[MPCR form_id={$form_post_id}]" ?>"/>
					<br/>
					<div class="panel panel-default">
						<div class="panel-heading"><a class="panel-title" data-toggle="collapse" data-parent="#accordion" href="#commonfields">General Fields</a></div>
						<div id="commonfields" class="panel-collapse collapse in">
							<div class="panel-body">
								<ul id="availablecfields" class="availablefields list-group">
									<!-- Populating Common Fields list -->
									<?php foreach ($fields as $fieldclass): ?>
										<?php $tmp_obj = new $fieldclass(); echo $tmp_obj->control_button();?>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					</div>
					<!-- Advanced fields start -->
					<div class="panel panel-default">
						<div class="panel-heading"><a class="panel-title" class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#advancedfields">Advanced Fields</a></div>
						<div id="advancedfields" class="panel-collapse collapse">
							<div class="panel-body">
								<ul id="availableafields" class="availablefields list-group">
									<!-- Populating Advanced Fields list -->
									<?php foreach ($advanced_fields as $fieldclass): ?>
										<?php
										$tmp_obj = new $fieldclass(); echo $tmp_obj->control_button();?>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					</div>
					<!-- Advanced fields end -->
				</div>
			</div>
			<div class="col-md-8">

				<div class="panel panel-default">
					<div class="panel-heading">Form Fields</div>
					<div class="panel-body">

						<ul id="selectedfields" class="list-group noborder">
							<?php
							$form_fields_list = '';
							if (isset($checked_fields)) {
									foreach ($checked_fields as $fieldindex => $fieldid) {
									$tmp_obj = new $fieldid();
									echo $tmp_obj->field_settings($fieldindex, $fieldid, $field_infos);
									$form_fields_list .=
										"<div class='hide fl-field' id='{$fieldindex}'>
											<div class='hide' id='{$fieldindex}_CLASS' rel='{$fieldid}'></div>
											<div class='hide' id='{$fieldindex}_LABEL' rel='{$field_infos[$fieldindex]['label']}'></div>
										</div>";
								}
							} ?>
						</ul>

						<div class="hide" id="selectedfields-list">
							<div class="hide fl-template">
								<div class="hide fl-field" id="{{INDEX}}">
									<div class="hide" id="{{INDEX}}_CLASS" rel="{{CLASS}}"></div>
									<div class="hide" id="{{INDEX}}_LABEL" rel="{{LABEL}}"></div>
								</div>
							</div>
							<?php echo $form_fields_list ?>
						</div>

						<!-- <div class="form-group">
							<button id="submit_button_sample" type="submit" disabled="disabled"
									class="btn btn-<?php// echo isset($form_data['buttoncolor']) ? $form_data['buttoncolor'] : 'default' ?>"><?php
										//if (!isset($form_data['buttontext']) || $form_data['buttontext'] == '')
										// 	echo "Submit";
										// else
										// 	echo $form_data['buttontext'];
										?>
							</button>
						</div> -->
					</div>
				</div>

				<div class="form-group">
					<div class="col-12">
						<ul id="settings-tabs" class="nav nav-tabs">
							<li class="">
								<a data-toggle="tab" href="#form-settings">Form settings</a>
							</li>
							<li class="">
								<a data-toggle="tab" href="#email-settings">Email settings</a>
							</li>
						</ul>
						<div id="content-tabs" class="tab-content">
							<div id="form-settings" class="tab-pane fade">
                                <div class="col-md-12"><br/>
								<div class="form-group">

										<label>Button label:</label>
										<input type="text" class="form-control" name="contact[buttontext]" placeholder="Submit button text" id="buttontext" data-target="#submit_button_sample" value="<?php echo (isset($form_data['buttontext']) ? $form_data['buttontext'] : "") ?>"/>
								</div><div class="form-group">
                                    <label>Button color:</label>
										<?php $color_selection = array (
											'primary' => 'Blue',
											'default' => 'White',
											'danger' => 'Red',
											'warning'=> 'Orange',
											'info' => 'Cyan',
											'success' => 'Green',
										) ?>
										<select id="button-color-selector" class="form-control" name="contact[buttoncolor]">
											<?php foreach($color_selection as $ccolor => $clabel) { ?>
											<option <?php if ( isset($form_data['buttoncolor']) and $form_data['buttoncolor'] == $ccolor)  echo 'selected="selected"' ?> value="<?php echo $ccolor ?>"><?php echo $clabel ?></option>
											<?php } ?>
										</select>
                                </div><div class="form-group">
										<label form="email">Thank you message: </label>
										<textarea rows="2" class="form-control" name="contact[thankyou]" placeholder="Thank you message" id="thankyou"><?php echo (isset($form_data['thankyou']) ? $form_data['thankyou'] : "") ?></textarea>
									</div>
								</div>
							</div>
							<div id="email-settings" class="tab-pane fade">

                                <div class="col-md-12"><br/>
                                <div class="form-group">

										<label form="email">From Email: </label>
										<input type="text" class="form-control" name="contact[email]" placeholder="Email address" id="email" value="<?php echo (isset($form_data['email']) ? $form_data['email'] : "") ?>"/>
                                </div>
                                <div class="form-group">

                                    <label form="email">From Name: </label>
										<input type="text" class="form-control" name="contact[from]" placeholder="Name to show in From field" id="from" value="<?php echo (isset($form_data['from']) ? $form_data['from'] : "") ?>"/>

                                </div>
								<div class='form-group'>
									<label >Email text: </label>
										<textarea class='form-control' name="contact[email_text]" id="email_text"><?php if (isset($form_data['email_text'])) echo $form_data['email_text'] ?></textarea>
								</div>
									<div class='form-group'>
												<?php if (defined('LFE_EMAIL_ADDON')) { ?>
												<a href='<?php echo admin_url('edit.php?post_type=email') ?>' ><span class='fa fa-cogs'></span> Configure emails</a>
												<?php } else { ?>
												Install the 'Live Forms Email' Addon to create and configure email seamlessly.
												<?php } ?>
									</div>
								</div>


							</div>
                            <div class="clear"></div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
<!-- Preprocessing ends -->
<!-- Teamplates start -->
<!--
    @script #template
    @uses   To populate 'Settings' panel with
            'Required message' and 'Validation'
            type list when [Required] is checked
    @access Mustache (Theme engine) via attr('id')
-->
<?php
foreach($fields as $fieldclass) {
	if (method_exists($fieldclass, 'configuration_template'))
	{
		$tmp_obj = new $fieldclass();
		echo $tmp_obj->configuration_template();
	}
}
foreach($advanced_fields as $fieldclass) {
	if (method_exists($fieldclass, 'configuration_template'))
	{
		$tmp_obj = new $fieldclass();
		echo $tmp_obj->configuration_template();
	}
}
?>

</div>
<!-- Advanced field part end -->
<!-- Teamplates end -->
<!-- Engine functions start -->
<script type="text/javascript">
var ds = 0;

function throttle( fn, time ) {
	var t = 0;
	return function() {
		var args = arguments,
			ctx = this;

			clearTimeout(t);

		t = setTimeout( function() {
			fn.apply( ctx, args );
		}, time );
	};
}

function add_field(obj, position) {

    //Add field with form
    if (jQuery(obj).attr('data-options') != undefined)
        var tmp = jQuery("#template-options").html();
    else if (jQuery(obj).attr('data-template') != undefined)
        var tmp = jQuery("#template-" + jQuery(obj).attr('data-template')).html();
    /*else if (jQuery(this).attr('data-payment') != undefined)
     var tmp = jQuery("#template-payment").html();*/
    else
        var tmp = jQuery("#template").html();
    var ID = obj.attr('rel') + "_" + new Date().getTime();
    //position.html(Mustache.render(tmp, {title: obj.attr('title'), value: obj.attr('rel'), ID: ID}));
    //position.children('li').unwrap();
    if(ds==1)
    jQuery('#selectedfields li.ui-sortable-placeholder').after(Mustache.render(tmp, {title: obj.attr('title'), value: obj.attr('rel'), ID: ID}));
    position.remove();

	// Update the form fields list
	field_label = jQuery('#label_'+ID).html();
	new_field_meta = Mustache.render(jQuery('.fl-template').html(), {INDEX: ID, CLASS: jQuery(obj).attr('data-template'), LABEL: field_label});
	jQuery('#selectedfields-list').html(jQuery('#selectedfields-list').html() + new_field_meta);

    jQuery('.form-field-label').on('keyup', function () {
        jQuery(jQuery(this).attr('data-target')).html(jQuery(this).val());
		console.log('#'+jQuery(this).attr('data-target').substring(7)+'_LABEL');
		jQuery('#'+jQuery(this).attr('data-target').substring(7)+'_LABEL').attr('rel', jQuery(this).val());
    });

    /**/
    jQuery('.cog-trigger').unbind();
    jQuery('.cog-trigger').on('click', function () {
        jQuery(this.rel).slideToggle();
        return false;
    });

    /**/
    jQuery('.req').unbind();
    jQuery('.req').on('click', function () {
        jQuery(this).parent().next('.req-params').slideToggle();
    });

	jQuery('.cond').unbind();
    jQuery('.cond').on('click', function () {
        jQuery(this).parent().next('.cond-params').slideToggle();
    });

	jQuery('.cond-operator').unbind();
	jQuery('.cond-operator').on('change', function(){
		$op_selection = jQuery(this).val();
		if ($op_selection == 'is' || $op_selection == 'is-not') {
			jQuery(this).parent().next().children('.is-cond-selector').removeClass('hide');
			jQuery(this).parent().next().children('.is-cond-text').addClass('hide');
		} else {
			jQuery(this).parent().next().children('.is-cond-selector').addClass('hide');
			jQuery(this).parent().next().children('.is-cond-text').removeClass('hide');
		}
	});

	jQuery('.add-cond-option').unbind();
	jQuery('.add-cond-option').click(function(e){
		e.preventDefault();
		jQuery('#cond_'+this.rel+' .form-group').append(jQuery('#cond_'+this.rel+' div[rel=row]:last').clone(true));
		return false;
	});

	jQuery('.del-cond-option').unbind();
	jQuery('.del-cond-option').click(function(e){
		e.preventDefault();
		jQuery(this).parent().parent().remove();
		return false;
	});

	jQuery('.payment-method-select').unbind();
	jQuery('.payment-method-select').on('change', function(){
		div = jQuery(this).attr('data-config-panel');
		if (jQuery('#configs-'+div).hasClass('hidden')) {
			jQuery('#configs-'+div).removeClass('hidden');
		} else {
			jQuery('#configs-'+div).addClass('hidden');
		}
	});

	jQuery('.form-field-label').unbind();
	jQuery('.form-field-label').on('keyup', function () {
		if (jQuery(this).attr('data-field-type') == 'separator') {
			jQuery(jQuery(this).attr('data-target')).html('[Separator] '+jQuery(this).val());
		} else {
			jQuery(jQuery(this).attr('data-target')).html(jQuery(this).val());
		}
		console.log('#'+jQuery(this).attr('data-target').substring(7)+'_LABEL');
		jQuery('#'+jQuery(this).attr('data-target').substring(7)+'_LABEL').attr('rel', jQuery(this).val());
	});

    jQuery('.remove').unbind();
    jQuery('.remove').on('click', function () {
        if (confirm('Are you sure?')) {
            jQuery('#' + this.rel).slideUp(function () {
                jQuery(this).remove();
				jQuery('#selectedfields-list #'+jQuery(this).attr('id').substring(6)).remove();
            });
        }
        return false;
    });

    jQuery('.add-option').unbind();
    jQuery('.add-option').click(function () {
        jQuery("#option_" + this.rel + " tbody").append(jQuery("#option_" + this.rel + " tr:last-child").clone(function () {

            /* Re-bind click event with delete option button */
            jQuery('.del-option').unbind();
            jQuery('.del-option').click(function () {
                console.log('#options_' + this.rel);
                //console.log(jQuery('#options_'+this.rel+" tr").html());
                if (jQuery('#options_' + this.rel + " tr").length > 2)
                    jQuery(this).parent().parent().remove();
                else
                    alert("Can't be delete. Atleast One Option is required.");
                return false;
            });


        }));

        return false;
    });

    jQuery('.del-option').unbind();
    jQuery('.del-option').click(function () {
        if (jQuery('#option_' + this.rel + " tr").length > 1)
            jQuery(this).parent().parent().remove();
        else
            alert("Can't be delete. Atleast One Option is required.");
        return false;
    });

    return ID;
}


jQuery(function ($) {

    $('.availablefields li').draggable({
        start: function(){ ds = 1; },
        connectToSortable: "#selectedfields",
        helper: "clone",
        revert: "invalid",
		refreshPositions: true
    });


    $("#selectedfields").droppable({
        activeClass: "ui-state-highlight",
		refreshPositions: true,
        drop: function (event, ui) {
        	console.log(ui);
            var position = ui.draggable;
            var obj = position.find("a.add");
            if(ds==1)
            var ID = add_field(obj, position);
           // $( this ).find( ".placeholder" ).remove();
           // $( "<li></li>" ).text( 'ok' ).appendTo( this );
           // alert(1);
          //  $('#selectedfields .list-group-item.ui-draggable').append()
            ds = 0;
            return false;
        }
    });
    $('#settings-tabs li:eq(0) a').tab('show');
	$("#selectedfields").sortable();
    $('.availablefields .add').click(function () {
        //Add field with form
        if ($(this).attr('data-options') != undefined)
            var tmp = $("#template-options").html();
        else if ($(this).attr('data-template') != undefined)
            var tmp = $("#template-" + $(this).attr('data-template')).html();
		else if ($(this).attr('data-separator') != undefined)
            var tmp = $("#template-" + $(this).attr('data-separator')).html();

        /*else if ($(this).attr('data-payment') != undefined)
         var tmp = $("#template-payment").html();*/
        else
            var tmp = $("#template").html();


        var ID = this.rel + "_" + new Date().getTime();

        $('#selectedfields').append(Mustache.render(tmp, {title: this.title, value: this.rel, ID: ID}));
		// Add this field to the form-fields-list

		$('.payment-method-select').unbind();
		$('.payment-method-select').on('change', function(){
			div = $(this).attr('data-config-panel');
			if ($('#configs-'+div).hasClass('hidden')) {
				$('#configs-'+div).removeClass('hidden');
			} else {
				$('#configs-'+div).addClass('hidden');
			}
		});

        $('.form-field-label').on('keyup', function () {
			if ($(this).attr('data-field-type') == 'separator') {
				$($(this).attr('data-target')).html('[Separator] '+$(this).val());
			} else {
				$($(this).attr('data-target')).html($(this).val());
			}
			console.log('#'+jQuery(this).attr('data-target').substring(7)+'_LABEL');
			jQuery('#'+jQuery(this).attr('data-target').substring(7)+'_LABEL').attr('rel', jQuery(this).val());
        });


        /**/
        $('.cog-trigger').unbind();
        $('.cog-trigger').on('click', function () {
            $(this.rel).slideToggle();
            return false;
        });

        /**/
        $('.req').unbind();
        $('.req').on('click', function () {
            $(this).parent().next('.req-params').slideToggle();

        });

		$('.cond').unbind();
		$('.cond').each(function () {
			if ($(this).attr('checked') == 'checked')
				$(this).parent().next('.cond-params').slideDown();
			//return false;
		});

		$('.cond-operator').unbind();
		$('.cond-operator').on('change', function(){
			console.log('--');
			$op_selection = $(this).val();
			if ($op_selection == 'is' || $op_selection == 'is-not') {
				$(this).parent().next().children('.is-cond-selector').removeClass('hide');
				$(this).parent().next().children('.is-cond-text').addClass('hide');
			} else {
				$(this).parent().next().children('.is-cond-selector').addClass('hide');
				$(this).parent().next().children('.is-cond-text').removeClass('hide');
			}
		});

		$('.add-cond-option').unbind();
		$('.add-cond-option').click(function(e){
			e.preventDefault();
			$('#cond_'+this.rel+' .form-group').append($('#cond_'+this.rel+' div[rel=row]:last').clone(true));
			return false;
		});

		$('.del-cond-option').unbind();
		$('.del-cond-option').click(function(e){
			e.preventDefault();
			$(this).parent().parent().remove();
			return false;
		});

		$('.payment-method-select').unbind();
		$('.payment-method-select').on('change', function(){
			div = $(this).attr('data-config-panel');
			if ($('#configs-'+div).hasClass('hidden')) {
				$('#configs-'+div).removeClass('hidden');
			} else {
				$('#configs-'+div).addClass('hidden');
			}
		});

        $('.remove').unbind();
        $('.remove').on('click', function () {
            if (confirm('Are you sure?')) {
                $('#' + this.rel).slideUp(function () {
                    $(this).remove();
					jQuery('#selectedfields-list #'+jQuery(this).attr('id').substring(6)).remove();
                });
            }
            return false;
        });

        $('.add-option').unbind();
        $('.add-option').click(function () {
            $("#option_" + this.rel + " tbody").append($("#option_" + this.rel + " tr:last-child").clone(function () {
                /* Re-bind click event with delete option button */
                $('.del-option').unbind();
                $('.del-option').click(function () {
                    //console.log($('#options_'+this.rel+" tr").html());
                    if ($('#options_' + this.rel + " tr").length > 2)
                        $(this).parent().parent().remove();
                    else
                        alert("Can't be delete. Atleast One Option is required.");
                    return false;
                });
            }));
            return false;
        });

        $('.del-option').unbind();
        $('.del-option').click(function () {
            if ($('#option_' + this.rel + " tr").length > 1)
                $(this).parent().parent().remove();
            else
                alert("Can't be delete. Atleast One Option is required.");
            return false;
        });



        return false;
    });

    $('.cog-trigger').on('click', function () {
        $(this.rel).slideToggle();
        return false;
    });

    $('.req').on('click', function () {
        $(this).parent().next('.req-params').slideToggle();
        //return false;
    });

    $('.cond').on('click', function () {
        $(this).parent().next('.cond-params').slideToggle();
    });

    $('.req').each(function () {
        if ($(this).attr('checked') == 'checked')
            $(this).parent().next('.req-params').slideDown();
        //return false;
    });
	$('.cond').each(function () {
        if ($(this).attr('checked') == 'checked')
            $(this).parent().next('.cond-params').slideDown();
        //return false;
    });

	$('.cond-operator').on('change', function(){
		$op_selection = $(this).val();
		console.log($op_selection);
		if ($op_selection == 'is' || $op_selection == 'is-not') {
			$(this).parent().next().children('.is-cond-selector').removeClass('hide');
			$(this).parent().next().children('.is-cond-text').addClass('hide');
		} else {
			$(this).parent().next().children('.is-cond-selector').addClass('hide');
			$(this).parent().next().children('.is-cond-text').removeClass('hide');
		}
	});

	$('.add-cond-option').click(function(e){
		e.preventDefault();
		$('#cond_'+this.rel+' .form-group').append($('#cond_'+this.rel+' div[rel=row]:last').clone(true));
		return false;
	});
	$('.del-cond-option').click(function(e){
		e.preventDefault();
		$(this).parent().parent().remove();
		return false;
	});
	$('.is-cond-selector').on('change',function(){
		$(this).next().next('.is-cond-data').val($(this).val());
	});
	$('.is-cond-text').on('keyup',function(){
		$(this).next('.is-cond-data').val($(this).val());
	});

    $('.remove').on('click', function () {
        if (confirm('Are you sure?')) {
            $('#' + this.rel).slideUp(function () {
                $(this).remove();
				jQuery('#selectedfields-list #'+jQuery(this).attr('id').substring(6)).remove();
            });
        }
        return false;
    });

    $('.form-field-label').on('keyup', function () {
		if ($(this).attr('data-field-type') == 'separator') {
			$($(this).attr('data-target')).html('[Separator] '+$(this).val()+':');
		} else {
			$($(this).attr('data-target')).html($(this).val()+':');
		}
		jQuery('#'+jQuery(this).attr('data-target').substring(7)+'_LABEL').attr('rel', jQuery(this).val());

    });

    $('#buttontext').on('keyup', function () {
        $($(this).attr('data-target')).html($(this).val());
    });

    $('.add-option').click(function () {
        $("#option_" + this.rel + " tbody").append($("#option_" + this.rel + " tr:last-child").clone(function () {
            /* Re-bind click event with delete option button */
            $('.del-option').unbind();
            $('.del-option').click(function () {
                //console.log($('#options_'+this.rel+" tr").html());
                if ($('#options_' + this.rel + " tr").length > 2)
                    $(this).parent().parent().remove();
                else
                    alert("Can't be delete. Atleast One Option is required.");
                return false;
            });
        }));
        return false;
    });

    $('.del-option').click(function () {
        if ($('#option_' + this.rel + " tr").length > 1)
            $(this).parent().parent().remove();
        else
            alert("Can't be delete. Atleast One Option is required.");
        return false;
    });

    $('#selectedfields').hover(function () {
        $(this).removeClass('noborder');
    }, function () {
        $(this).addClass('noborder');
    });

	$('.payment-method-select').on('change', function(){
		div = $(this).attr('data-config-panel');
		if ($('#configs-'+div).hasClass('hidden')) {
			$('#configs-'+div).removeClass('hidden');
		} else {
			$('#configs-'+div).addClass('hidden');
		}
	});

    $('.field-preview input,.field-preview select,.field-preview textarea').attr('disabled', 'disabled');

	$('#button-color-selector').on('change', function() {
		$('#submit_button_sample').removeClass();
		new_class = $(this).val();
		$('#submit_button_sample').addClass('btn');
		$('#submit_button_sample').addClass('btn-'+new_class);
	});

});

jQuery(document).ready(function($){
	var form_selections = "<option value=''>Selected a field</option>";
	$('#selectedfields-list .fl-field').each(function(){
		this_id = $(this).attr('id');
		var nonCondFields = ["Address", "Captcha", "Daterange", "File", "Fullname", "Location", "Mathresult", "Pageseparator", "Paratext", "Phone", "Password"];
		if (this_id != '{{INDEX}}' && $.inArray($('#'+this_id+'_CLASS').attr('rel'), nonCondFields) == -1) {
			form_selections += 	"<option value='"+this_id+"'>"+$('#'+this_id+'_LABEL').attr('rel')+"</option>";
		}
	});

	$('.cond-operator').on('change', function(){
		$op_selection = $(this).val();
		if ($op_selection == 'is' || $op_selection == 'is-not') {
			$(this).parent().next().children('.is-cond-selector').removeClass('hide');
			$(this).parent().next().children('.is-cond-text').addClass('hide');
		} else {
			$(this).parent().next().children('.is-cond-selector').addClass('hide');
			$(this).parent().next().children('.is-cond-text').removeClass('hide');
		}
	});

	$('.cond-field-selector , .cond-operator , .is-cond-selector , .is-cond-text').each(function () {
		if ($(this).hasClass('cond-field-selector')) $(this).html(form_selections);
		var preselected_var = $(this).attr('data-selection');
		if ($(this).attr('type') == 'text') $(this).val(preselected_var);
		else {
			$(this).children().each(function(){
				if ($(this).attr('value') == preselected_var)
					$(this).attr('selected', 'selected');
			});
		}
	});

	$('.cond-operator').each(function(){
		$op_selection = $(this).val();
		if ($op_selection == 'is' || $op_selection == 'is-not') {
			$(this).parent().next().children('.is-cond-selector').removeClass('hide');
			$(this).parent().next().children('.is-cond-text').addClass('hide');
		} else {
			$(this).parent().next().children('.is-cond-selector').addClass('hide');
			$(this).parent().next().children('.is-cond-text').removeClass('hide');
		}
	});

	$('#selectedfields-list').bind('DOMNodeInserted DOMNodeRemoved DOMSubtreeModified', throttle( function(){
		var form_selections = "<option value=''>Selected a field</option>";
		$('#selectedfields-list .fl-field').each(function(){
			this_id = $(this).attr('id');
			var nonCondFields = ["Address", "Captcha", "Daterange", "File", "Fullname", "Location", "Mathresult", "Pageseparator", "Paratext", "Phone", "Password"];
		if (this_id != '{{INDEX}}' && $.inArray($('#'+this_id+'_CLASS').attr('rel'), nonCondFields) == -1) {
				form_selections += 	"<option value='"+this_id+"'>"+$('#'+this_id+'_LABEL').attr('rel')+"</option>";
			}
		});
		$('.cond-field-selector , .cond-operator , .is-cond-selector , .is-cond-text').each(function () {
			if ($(this).hasClass('cond-field-selector')) $(this).html(form_selections);
			var preselected_var = $(this).attr('data-selection');
			if ($(this).attr('type') == 'text') $(this).val(preselected_var);
			else {
				$(this).children().each(function(){
					if ($(this).attr('value') == preselected_var)
						$(this).attr('selected', 'selected');
				});
			}
		});
	}, 50));

});

</script>
<!-- Engine functions end -->
