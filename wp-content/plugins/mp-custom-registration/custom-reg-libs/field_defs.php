<?php

function setCustomCommonFields() 
{
	$fields = array(
		'name'=>array('type' => 'text', 'label' => 'Name'),
		'firstname'=>array('type' => 'text', 'label' => 'First Name'),
		'lastname'=>array('type' => 'text', 'label' => 'Last Name'),		
		'email'=> array('type' => 'email', 'label' => 'Email', 'template' => 'email'),
		'subject'=> array('type' => 'text', 'label' => 'Subject'),
		'message'=> array('type' => 'textarea', 'label' => 'Message'),
		'text' => array('type' => 'text','label' => 'Text'),
		'password' => array('type' => 'password','label' => 'Password'),
		'radio' => array('type' => 'radio','label' => 'Radio','options' => true	),
		'checkbox' => array('type' => 'checkbox','label' => 'Checkbox','options' => true),
		'select' => array('type' => 'select','label' => 'Select','options' => true),
		'textarea' => array('type' => 'textarea','label' => 'Textarea')
		);

	$fields = array(
		'Name','firstname','lastname','Subject', 'Message','Text', 'Textarea', 'Number', 'Password', 'Radio', 'Select', 'Checkbox'
	);

	return $fields;
}

add_filter("common_fields", "setCustomCommonFields");

function setCustomAdavanceField() 
{
	$advanced_fields = array();
	$advanced_fields = array(
		'ProfileImage' => array(
			'type' => 'file',
			'label' => 'Profile Image Upload',
			'template' => 'file'
		),
		'fullname' => array(
			'type' => 'fullname',
			'label' => 'Full name'
		),
		'address' => array(
			'type' => 'address',
			'label' => 'Address'
		),
		'url' => array(
			'type' => 'url',
			'label' => 'Website',
			'template' => 'url'
		),
		'date' => array(
			'type' => 'date',
			'label' => 'Date',
			'template' => 'date'
		),
		'daterange' => array(
			'type' => 'daterange',
			'label' => 'Date range',
			'template' => 'daterange'
		),
		'phone' => array(
			'type' => 'phone',
			'label' => 'Phone',
			'template' => 'phone'
		),
		'paragraph_text' => array(
			'type' => 'paragraph_text',
			'label' => 'Paragraph text',
			'template' => 'paratext'
		)
	);
	return array('ProfileImage', 'FullName', 'Address', 'Url', 'Paratext', 'Phone', 'Date', 'Daterange');
	//return $advanced_fields;
}

add_filter("advanced_fields", "setCustomAdavanceField");

function setCustomMethods() {
	global $methods_set;
	return $methods_set;
}

add_filter("method_set", "setCustomMethods");

function getCustomValidation($type = '') {
	$optional_validation_ops = array(
		'url' => array('url' => 'URL'),
		'email' => array('email' => 'Email'),
		'date' => array('date' => 'Date'),
		'text' => array('text' => 'Text'),
		'numeric' => array('numeric' => 'Numeric'),
		'username' => array('username' => 'Username')
	);

	$default_validation_ops = array('text' => 'Text', 'numeric' => 'Numeric', 'email' => 'Email', 'url' => 'URL', 'date' => 'Date','username' => 'Username');
	if ($type == '') $validation_ops = $default_validation_ops;
	else {
		$validation_ops = $optional_validation_ops[$type];
	}

	return $validation_ops;
}
?>