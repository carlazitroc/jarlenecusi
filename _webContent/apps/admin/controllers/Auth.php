<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Gregwar\Captcha\CaptchaBuilder;
class Auth extends MY_Controller
{
	public function popup(){
		if($this->_restrict()){
			return;
		}

		$this->load->view('auth/popup');
	}
	
	public function signin(){
		$this->load->model('admin_account_model');
		$this->load->language('admin_auth');
		
		$vals = array();
		
		$vals['errors'] = array();
		$vals['data']['username'] = '';
		if($this->admin_auth->is_login())
		{
			redirect( forward_url() );
			return;
		}
		if($this->input->post('do')=='login'){
			$username = $this->input->post('login_name');
			$password = $this->input->post('login_pass');
			
			$vals['data']['username']= $username;
			
			$rst = $this->admin_auth->check_access($username,$password);
			$user = $this->admin_auth->get_user($username);
			if($rst == 2) $vals['errors'][ 'loginid_notfound' ] = true;
			elseif($rst == 3) $vals['errors'][ 'password_incorrect'] = true;
			elseif($rst == 1){
				if(!empty($user['expiry_date']) && strtotime($user['expiry_date']) < time() ){
					$vals['errors']['expiried'] = true;	
				}else{
					
					$this->admin_auth->login( $username );
					$url = rawurldecode(forward_url());
					//$url.=strpos($url,'?')>0 ? '&' : '?';
					//$url.= 'token='.$token;
					
					redirect( $url  );
				
				}
				
			}else{
				$vals['errors']['unknown'] = true;
			}
		}
		
		$this->_render('auth/signin', $vals, 'blank');
	}
	
	public function signout(){
		
		$this->load->language('admin_auth');
		
		$vals = array();
		$this->admin_auth->logout();
		$this->_render('auth/signout', $vals, 'blank');
	}
	
	public function forgetPassword($action = 'form', $request_id = NULL) {
		$this->load->language('admin_auth');

		// If session available, redirect to home
		if (!$this->_restrict(NULL, FALSE)) {
			redirect(site_url());
			return;
		}


		if(empty($request_id)){
			$this->load->helper('string');
			$request_id = random_string('alnum','32');
		}

		$session_key = 'auth_fgpwd_answer_'.$request_id;

		if($action == 'form'){
			
			//
			$this->session->set_userdata($session_key , NULL);

			return $this->_render('auth/forget_password',array('request_id'=>$request_id),'blank');
		}

		if($action == 'check'){
			print $this->session->userdata($session_key);
			return;
		}

		if($action == 'captcha'){

			$builder = new CaptchaBuilder;
			$builder->build();
			$this->session->set_userdata($session_key , $builder->getPhrase() );

			$builder->output();
			return;
		}

		if($action == 'submit'){

			$answer = $this->input->post('captcha_answer');
			$login_name = $this->input->post('login_name');
			$email = $this->input->post('email');


			$correct_answer = $this->session->userdata($session_key);
			$this->session->set_userdata($session_key , NULL);

			if(empty($login_name) || empty($answer)){
				return $this->_error(1201, 'empty_value');
			}

			if($correct_answer != $answer && !empty($correct_answer)){
				return $this->_error(1202, 'captcha_answer_incorrect',200, compact('correct_answer','answer'));
			}

			$user = $this->admin_auth->get_user($login_name);
			if(empty($user['id'])){
				return $this->_error(1203, 'user_not_found');
			}

			if(empty($user['email']) || $user['email'] != $email){
				return $this->_error(1204, 'user_not_found');
			}

			$this->load->helper('string');
			$new_password = random_string('alnum',16);

			$this->load->model('admin_account_model');
			$this->admin_account_model->save( array(
				'login_pass'=> $this->admin_auth->encrypt($new_password),
			), array(
				'id'=> $user['id']
			));



			$email_vals = array(
				'login_name'=>$user['login_name'],
				'new_password'=>$new_password,
				'login_url'=>site_url(),
			);

			$subject = lang('admin_reset_password_subject');
			$message = $this->load->view('auth/reset_password_email', $email_vals, TRUE);
			$message_alt = strip_tags($message);

			$this->load->config('email');
			$this->load->library('email');

			$this->email->from($this->config->item('sender_from'), $this->config->item('sender_from_name'));
			$this->email->to($user['email']);
			$this->email->subject($subject);
			$this->email->message($message);
			$this->email->set_alt_message($message_alt);

			if(!$this->email->send()){
				return $this->_error(1204, 'cannot_send_reset_password_email');
			}


			return $this->_api(array('done'=>TRUE));
		}

		return $this->_show_404();
	}

	public function changePassword() {
		if ($this->_restrict()) {
			return;
		}
		$this->load->language('admin_auth');

		$vals   = array();
		$result = $this->input->get_post('result');
		$errors = array();

		if ($this->input->post('do') == 'change') {
			$result  = 'checking';
			$allowed = true;
			$this->load->model('admin_account_model');

			$user = $this->admin_account_model->read(array('id'=> $this->admin_auth->get_id() ));

			$old_password = $this->admin_auth->decrypt( $user['login_pass'] );

			if(!empty($user['login_pass'])){
				if ($old_password != $this->input->post('old_password') ) {
				$allowed = false;
					$errors['old_password_incorrect'] = true;
				}
			}

			if (!empty($old_password) && $old_password == $this->input->post('new_password')) {
				$allowed = false;
				$errors['same_password'] = true;
			}

			if ($this->input->post('new_password') != $this->input->post('retype_new_password')) {
				$allowed = false;
				$errors['retype_new_password'] = true;
			}

			if (!preg_match("/^[a-zA-Z][a-zA-Z0-9]{5,23}$/", $this->input->post('new_password'))) {
				$allowed = false;
				$errors['new_password_invalid'] = true;
			}

			if ($allowed) {
				$result = 'saved';

				$this->admin_account_model->save( array(
					'login_pass'=> $this->admin_auth->encrypt($this->input->post('new_password')),
				), array(
					'id'=> $user['id']
				));


				redirect('auth/changePassword?result=saved');
			} else {
				$result = 'error';
			}
		}



		$vals['result'] = $result;
		$vals['errors'] = $errors;

		$this->_render('auth/change_password', $vals);
	}

	public function access(){

		if($this->input->get_post('refresh') == 'yes')
			$this->acl->rebuild();

		if($this->input->get('scopes') != NULL){

			return $this->_api(array(
				'valid'=>$this->acl->has_permission($this->input->get('scopes')),
			));
		}

		return $this->_api(array(
			'roles'=>$this->acl->user_roles,
			'permissions'=>$this->acl->user_perms
		));
	}
	
	public function _is_debug(){
		if($this->input->get('debug') == 'no'){
			return FALSE;
		}
		if( $this->input->get('debug') == 'hdv'){
			return TRUE;
		}
		return FALSE;
	}
}
