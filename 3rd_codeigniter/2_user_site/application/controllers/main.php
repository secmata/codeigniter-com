<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

	public function index(){
		$this->login();
	}
    
    public function login(){
        $this->load->view('login');
    }
    
    public function signup(){
        $this->load->view('signup');
    }
    
    public function members(){
        if($this->session->userdata('is_logged_in')){
            $this->load->view('members');
        }else{
            redirect('main/restricted');
        }
    }
    
    public function restricted(){
        $this->load->view('restricted');
    }
    
    public function login_validation(){
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('email', 'Email', 'required|trim|xss_clean|callback_validate_credentials');
        $this->form_validation->set_rules('password', 'Password', 'required|md5|trim');
        
        if ($this->form_validation->run()){
            $data = array(
                'email' => $this->input->post('email'),
                'is_logged_in' => 1
            );
            $this->session->set_userdata($data);
            redirect('main/members');
        }else{
            $this->load->view('login');
        }
    }
    
    public function signup_validation(){
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('email', 'Email',
         'required|trim|valid_email|is_unique[user.email]');
         
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        $this->form_validation->set_rules('cpassword', 'Confirm Password',
        'required|trim|matches[password]');
        
        $this->form_validation->set_message('is_unique',"That email address already exists");
        
        if($this->form_validation->run()){
            // $this->load->models('model_users');
             $this->load->model('model_users');
            //generate  a random key
            $key = md5(uniqid());                                                     
            
            $this->load->library("email");                                                                                                  
            $this->load->library('email', array('mailtype'=>'html'));
            $this->email->from('SAR-N@redcross.com', "Red Cross");
            $this->email->to($this->input->post('email'));
            $this->email->subject("Confirm you account.");
            $message = "<p>Thank you for signing up!</p>";
            $message .= "<p><a href='".base_url()."main/register_user/$key'>Click here</a> to confirm your acount </p>";
            $this->email->message($message);
            //send and email to the user
            if($this->model_users->add_temp_user($key)){
                if($this->email->send()){
                echo $this->email->print_debugger();
                echo "The email has been sent!";
                }else echo "could not send the email."; 
            }else echo "problem adding to database.";
            
            //and the to the temp_users db
        
        }else{   
            $this->load->view('signup');
        }
    }
    
    public function validate_credentials(){
        $this->load->model('model_users');
        
        if($this->model_users->can_log_in()){
            return true;
        }else{
            $this->form_validation->set_message('validate_credentials', 'Incorrect username/password.');
            return false;
        }
    }
    
    public function logout(){
        $this->session->sess_destroy();
        redirect('main/login');
    }
    
    public function register_user($key){
        $this->load->model('model_users');
        if( $this->model_users->is_key_valid($key)){
            if($newemail = $this->model_users->add_user($key)){
                $data = array(
                'email' => $newemail,
                'is_logged_in' => 1
                );
            $this->session->set_userdata($data);
            redirect('main/members');
            }else echo "failed to add user, please try again.";
        }else echo "invalid key";
    }
}

