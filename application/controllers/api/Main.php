<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends BD_Controller {
    function __construct(){
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->load->model('M_main');
    }
	
	public function test_post(){
        $theCredential = $this->user_data;
        $this->response($theCredential, 200); // OK (200) being the HTTP response code
	}

    public function code_get(){
        $id_user = $_GET["id_user"];
        $codes = [];
        $reservation = $this->M_main->isRent($id_user);
        $date = date('Y-m-d h:i:s a', time());
        foreach ($reservation->result_array() as $row){
            $code = $this->M_main->get_code_box($id_user,$date,$row["id_box"]);
            $code = $code->result_array(); 
            $codes = array_merge($codes, $code);
        }
        if ($codes){
            // Set the response and exit
            $this->response($codes, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else{
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No code were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }


    public function users_get(){
        $users = $this->M_main->get_user_box()->row();
        $id = $this->get('id_user_box');

        print($id);

        // If the id parameter doesn't exist return all the users

        if ($id === NULL)
        {
            // Check if the users data store contains users (in case the database result returns NULL)
            if ($users){
                // Set the response and exit
                $this->response($users, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else{
                // Set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'No users were found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }

        // Find and return a single record for a particular user.

        $id = (int) $id;

        // Validate the id.
        if ($id <= 0){
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Get the user from the array, using the id as key for retrieval.
        // Usually a model is to be used for this.

        $user = NULL;

        if (!empty($users))
        {
            foreach ($users as $key => $value)
            {
                if (isset($value['id']) && $value['id'] === $id)
                {
                    $user = $value;
                }
            }
        }

        if (!empty($user))
        {
            $this->set_response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'message' => 'User could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function users_delete(){
        $id = (int) $this->get('id');
        // Validate the id.
        if ($id <= 0){
            // Set the response and exit
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        // $this->some_model->delete_something($id);
        $message = [
            'id' => $id,
            'message' => 'Deleted the resource'
        ];
        $this->set_response($message, REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
    }
}
