<?php
if ( !class_exists( 'OVIC_API_Resource_Not_Found_Exception' ) ) {
	class OVIC_API_Resource_Not_Found_Exception extends OVIC_API_Exception
	{
		// Thrown when a requested resource does not exist in Mailchimp
	}
}
