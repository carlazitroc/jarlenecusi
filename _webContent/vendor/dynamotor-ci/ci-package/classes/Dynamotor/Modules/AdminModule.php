<?php 

namespace Dynamotor\Modules;

use Dynamotor\Core\HC_Module; 

class AdminModule extends HC_Module
{
	public $email_layout = 'layouts/admin_email';

	public $config = NULL;

	function __construct($config = NULL){

		$this->config = $config;

		if(!isset($config['auth_config'])) $config['auth_config'] = array();

		if(!isset($config['acl_config'])) $config['acl_config'] = array();

		// Feature: Authentication
		$this->admin_auth = new \Dynamotor\Modules\Auth\SimpleAuth($config['auth_config']);

		// Feature: Access Control List
		$this->acl = new \Dynamotor\Modules\Auth\Acl($config['acl_config']);
	}


	public function change_password($user, $new_pass=NULL, $email_notify=FALSE, $email_subject = 'Password has been changed' ){

		$this->loa->model('admin_account_model');

		log_message('debug','Admin/change_password: params= '.print_r(compact('user','new_pass','email_notify'),true));

		if(is_string($user)){
			$user = $this->admin_account_model->read(array('id'=>$user));
		}
		if(empty($user['id'])){
			log_message('error','AdminModule/change_password: Member account not found.');
			return $this->error(-1, 'User account ');
		}

		if(empty($new_pass)){
			$this->load->helper('string');
			$new_pass = random_string('alnum',16);
		}

		log_message('debug','AdminModule/change_password: params= '.print_r(compact('user','new_pass','email_notify'),true));

		$new_data = array(
			'login_pass' => $this->encode($new_pass),
		);

		$result = $this->admin_account_model->save($new_data,array('id'=>$user['id']));
		if(empty($result['id'])){
			log_message('error','AdminModule/change_password: cannot save new password.');
			return FALSE;
		}else{
			log_message('debug','AdminModule/change_password: saved new password: '.$this->db->last_query());
		}


		if($email_notify){
			$this->notify_email('email/change_password', $user['email'],  $email_subject, array(
				'name'=>$user['name'],
				'new_pass'=>$new_pass,
			));
		}
		return TRUE;
	}


	public function notify_email($view_setting, $to_email, $subject = 'System Notice', $vals=false){

		if(!class_exists('Swift_Message')){
			log_message('error','AdminModule/notify_email: Swift_Message does not exist.');
			return $this->error(-1, 'Required class does not exist.');
		}
		
		$layout = $this->email_layout ;
		$view = $view_setting;
		if(is_array($view_setting)){

			if(!empty($view_setting['layout'])){
				$layout = $view_setting['layout'];
			}
			if(isset($view_setting['view'])){
				$view = $view_setting['view'];
			}
		}

		$vals['view'] = $view;

		$from_addr = $this->config->item('sender_from');
		$from_name = $this->config->item('sender_from_name');

		if(empty($from_addr) && !empty($_SERVER['HTTP_HOST'])){
			$from_addr = 'no-reply@'.$_SERVER['HTTP_HOST'];
		}
		if(empty($from_name) && !empty($_SERVER['HTTP_HOST'])){
			$from_name = $_SERVER['HTTP_HOST'];
		}

		$this->load->config('email');

		$content = $this->load->view($layout,$vals, TRUE);

		// Create the message
		$message = Swift_Message::newInstance()
			// Give the message a subject
			->setSubject($subject)
			// Set the From address with an associative array
			->setFrom(array($from_addr => $from_name))
			// Set the To addresses with an associative array
			->setTo(array($to_email));

		if(preg_match_all('#\{embed\:(.+)\}#', $content, $matches)){
			foreach($matches[0] as $idx => $search_str){
				$embed_content = $message->embed(Swift_Image::fromPath($matches[1][$idx]));
				$content = str_replace($search_str, $embed_content, $content);
			}
		}

			// Give it a body
		$message->setBody($content, 'text/html');


		$protocol = $this->config->item('protocol');
		$smtp_host = $this->config->item('smtp_host');
		$smtp_user = $this->config->item('smtp_user');
		$smtp_pass = $this->config->item('smtp_pass');

		$transport = NULL;

		if($protocol == 'smtp'){
			if(empty($smtp_host)) $smtp_host = 'localhost';
			$transport = Swift_SmtpTransport::newInstance($smtp_host, 25);

			if(!empty($smtp_user)){
				$transport->setUsername($smtp_user);
			}
			if(!empty($smtp_pass)){
				$transport->setPassword($smtp_pass);
			}


		}elseif($protocol == 'sendmail'){
			$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs -t');
		}

		if(empty($transport)){
			$transport = Swift_MailTransport::newInstance();
		}

		$mailer = Swift_Mailer::newInstance($transport);

		$failures = NULL;
		// Send the message
		try{
			$result = $mailer->send($message, $failures);
		}catch(Exception $exp){
			log_message('error','AdminModule/notify_email, send email failure: '.$exp->getMessage().', config='.print_r(compact('protocol','smtp_host','smtp_user','smtp_pass'),true));
			return FALSE;	
		}

		if($result){
			return TRUE;
		}else{

			log_message('error','AdminModule/notify_email, send email failure: '.print_r($failures,true));
			return FALSE;
		}
	}

	public function encode($val){
		$this->load->library('encrypt');
		// getting encryption key
		$encryption_key = $this->config->item($this->config['auth_config']['encryption_key']);
		if(empty($encryption_key)){
			$encryption_key = $this->config->item('encryption_key');
		}

		return $this->encrypt->encode($val, $encryption_key);
	}

	public function decode($val){
		$this->load->library('encrypt');
		// getting encryption key
		$encryption_key = $this->config->item($this->config['auth_config']['encryption_key']);
		if(empty($encryption_key)){
			$encryption_key = $this->config->item('encryption_key');
		}

		return $this->encrypt->decode($val, $encryption_key);
	}
}