<?php
	if( ! defined('ABSPATH') ) {
		exit();		//Exit if accessed directly
	}

	include_once('Net/PH_SFTP.php');

	$ftp_or_sftp 		= ! empty($_POST['ftp_or_sftp']) ? $_POST['ftp_or_sftp'] : null;

	$ftp_server 		= ! empty($_POST['ftp_server']) ? $_POST['ftp_server'] : null;
	$ftp_user 			= ! empty($_POST['ftp_user']) ? $_POST['ftp_user'] : null;
	$ftp_password 		= ! empty($_POST['ftp_password']) ? $_POST['ftp_password'] : null;
	$ftp_port 			= ! empty($_POST['ftp_port']) ? $_POST['ftp_port'] : 21;
	$ftp_timeout 		= ! empty($_POST['ftp_timeout']) ? $_POST['ftp_timeout'] : 20;
	$use_ftps 			= ! empty($_POST['use_ftps']) ? $_POST['use_ftps'] : false;
	$ftp_server_path	= ! empty($_POST['ftp_server_path']) ? $_POST['ftp_server_path'] : null;
	$use_passive_mode	= ! empty($_POST['use_passive_mode']) ? $_POST['use_passive_mode'] : false;

	if( empty($ftp_server) ) {
		echo 'FTP Server Can not be empty.';
		return;
	}
	$new_line_char = PHP_EOL;

	if( $ftp_or_sftp == 'sftp' )
	{
		$sftp = new PH_Net_SFTP($ftp_server);

		if ( ! $sftp->login($ftp_user, $ftp_password )) {
			echo "Failed to Connect/Login to the SFTP Server. $new_line_char $new_line_char";
			echo "Possible Reasons : $new_line_char";
			echo "	1. Please select appropriate Protocol: FTP/SFTP. $new_line_char";
			echo "	2. SFTP server name or path may be incorrect. $new_line_char";
			echo "	3. Check your SFTP User Name. $new_line_char";
			echo "	4. Check your SFTP Password. $new_line_char";
		}
		else
		{	
			echo "Logged in to the SFTP Server Successfully. $new_line_char $new_line_char"."Note : Now you can try to import the csv.";
		}
	}
	else
	{
		$ftp_conn = $use_ftps ? @ftp_ssl_connect( $ftp_server, $ftp_port, $ftp_timeout ) : @ftp_connect( $ftp_server, $ftp_port, $ftp_timeout );
		echo "Step 1: ";
		if( $ftp_conn == false ) {
			echo "Failed to Connect to the FTP Server - $ftp_server $new_line_char $new_line_char";
			echo "Possible Reasons : $new_line_char";
			echo "	1. Please select appropriate Protocol: FTP/SFTP. $new_line_char 2. FTP server name or path may be incorrect. $new_line_char	3. FTP Port may be incorrect. $new_line_char	4. Try With / Without FTPS.";
			return;
		}
		else{
			echo "Connected to the Server Successfully. $new_line_char";
		}

		echo "Step 2 : ";
		if( ftp_login($ftp_conn, $ftp_user, $ftp_password) == false ) {
			echo "Failed to Login to the FTP Server. $new_line_char $new_line_char";
			echo "Possible Reasons : $new_line_char";
			echo "	1. Check your FTP User Name. $new_line_char";
			echo "	2. Check your FTP Password. $new_line_char";
			echo "	3. Try with/without FTPS.";
		}else{
			echo "Logged in to the FTP Server Successfully. $new_line_char $new_line_char"."Note : Now you can try to import the csv. If you are still not able to import then try with / without Passive mode.";
		}
		ftp_close($ftp_conn);
	}
	return;