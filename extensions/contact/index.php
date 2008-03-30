<?php
/**
* Contact extension for viennaCMS
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

class extension_contact {
	public $config = array();
	function list_modules() {
		return array(
			'contact' => 'contact'
		);
	}
	
	function args_contact() {
		return array(
			'toemail' => array( // 'content' is the argument name
				'title' => __('Send the e-mail to'), // title is what it will show
				'type' => 'text', // textarea or text, currently
				'newrow' => false // true to make the control 100% width in ACP
			),
			/*
			 * SMTP is not working yet
			 *'use_smtp'    => array(
				'title'	=> __('Use SMTP? Type yes for on'),
				'type'  => 'text',
			),
			'smtp_auth'	  => array(
				'title'		=> __('Use SMTP auth? Type yes for on'),
				'type'		=> 'text',
			),
			'smtp_server' => array(
				'title' => __('SMTP Server'),
				'type'	=> 'text',
			),
			'smtp_port' => array(
				'title'	=> __('SMTP Port'),
				'type'	=> 'text',
			),
			'smtp_username' => array(
				'title'	=> __('SMTP Username'),
				'type'	=> 'text',
			),
			'smtp_password' => array(
				'title'	=> __('SMTP Password'),
				'type'	=> 'text',
			),
			'ssl' => array(
				'title' => __('Use SSL? Type yes for using SSL'),
				'type'	=> 'text',
			),*/
			
			
		);
	}
	
	function module_contact($args) {
		if (!isset($_POST['contactform'])) {
			$form = utils::load_extension('form');
			$form->contactform();
			$form->generateform();
			echo $form->content;
		} else {
			if (empty($_POST['subject']) || empty($_POST['from']) || !utils::validate_email($_POST['from']) || empty($_POST['message'])) {
				echo __('Please fill in all fields.');
				$form = utils::load_extension('form');
				$form->contactform();
				$form->generateform();
				echo $form->content;
			} else {
				// include('class.phpmailer.php');
				$subject = str_replace(array("\n", "\r"), '', $_POST['subject']);
				$from = str_replace(array("\n", "\r"), '', $_POST['from']);
				$message = $_POST['message'];
				$phpversion = phpversion();
				$headers = 
<<<HEADERS
From: $from
Reply-to: $from
X-Mailer: PHP/$phpversion
Subject: $subject
HEADERS;
				// TODO: Add a SMTP option
				/*
				// Get PHPMailer class
				$mail = new PHPMailer();
				// Use SMTP?
				if($args['use_smtp'] == 'yes') {
					$mail->IsSMTP();
					$mail->Host 		= $args['smtp_server'];
				}
				// Use SMTP Auth?
				if($args['smtp_auth'] == 'yes') {
					$mail->Username = $args['smtp_username'];
					$mail->Password = $args['smtp_password'];
					$mail->SMTPAuth = true;
				}
				// Debugging
				$mail->SMTPDebug = true;
				// Now set some things
				$mail->From = $from;
				$mail->AddAddress($args['toemail']);
				$mail->AddReplyTo($from);
				// Wordwrap, and no html!
				$mail->WordWrap = 70;
				$mail->IsHTML(false);
				// Set subject and message
				$mail->Subject	= $subject;
				$mail->Body		= $message;
				
				
				// And now try to send the mail
				$succes = $mail->Send();
				
				if ($succes) 
				{
					echo __('Your e-mail has been successfully sent.');
				} 
				else
				{
					echo __('Your e-mail could not be sent. Please try again later. Error: ') . $mail->ErrorInfo;
				}
				return $succes;
				
				*/
				
				/*
				 * Old stuff, but beacuse the SMTP is not working, we are using it.
				 */
				if (mail($args['toemail'], $subject, $message, $headers)) {
					echo __('Your e-mail has been successfully sent.');
				} else {
					echo __('Your e-mail could not be sent. Please try again later.');
				}
				
			}
		}
	}
	
	function extinfo() {
		return array(
			'version' => '0.0.1',
			'name' => __('Contact extension'),
			'description' => __('Contact extension for sending emails'),
		);
	}
}

?>