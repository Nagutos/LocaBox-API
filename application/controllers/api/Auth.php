<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

class Auth extends BD_Controller {

    function __construct(){
        // Construct the parent class
        parent::__construct();
        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->load->model('M_main');
    }

    public function login_post(){
        $u = $this->post('email');
        $p = $this->post('password');
        $q = array('email' => $u); //For where query condition
        $kunci = $this->config->item('thekey');
        $invalidLogin = ['status' => 'Invalid Login']; //Respon if login invalid
        $val = $this->M_main->get_user($q)->row(); //Model to get single data row from database base on username
        if($this->M_main->get_user($q)->num_rows() == 0){$this->response($invalidLogin, REST_Controller::HTTP_NOT_FOUND);}
		$match = $val->password;   //Get password for user from database
        if(password_verify($p, $match)) {
            $token['id_user_box'] = $val->id_user_box;  //From here
            $token['email'] = $u;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5; //To here is to generate token
            $output['token'] = JWT::encode($token,$kunci ); //This is the output token
            $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
        }
        else {
            $this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND); //This is the respon if failed
        }
    }

    public function updateFCM_post(){
        $fcm = $this->post('fcm');
        $id_user = $this->post('id_user');
        $invalidLogin = ['status' => 'Unable to update fcm'];
        if (!empty($fcm) && !empty($id_user)) {
            // Appel à la méthode pour mettre à jour la base de données
            $this->M_main->update_user_fcm($fcm, $id_user);
            $output = ['status' => 'FCM updated successfully'];
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
        else {
            $this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND); //This is the respon if failed
        }
    }

    public function removeFCM_post(){
        $id_user = $this->post('id_user');
        $invalidLogin = ['status' => 'Unable to remove fcm'];
        if (!empty($id_user)) {
            // Appel à la méthode pour mettre à jour la base de données
            $this->M_main->delete_user_fcm($id_user);
            $output = ['status' => 'FCM removed successfully'];
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
        else {
            $this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND); //This is the respon if failed
        }
    }
}