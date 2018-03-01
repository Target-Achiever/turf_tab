<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
        // $this->load->library('form_validation');
		$this->load->model('profile_model');
        $this->load->library('../controllers/common');
    }

	public function index()
	{
		echo "welcome";
	}

	/* ==========         User album        ============= */
	public function user_album()
	{ 

		if($this->input->post()) {
			$data = $this->input->post();
		}
		else {
			$data = json_decode(file_get_contents("php://input"),true);		
		}

		if(!empty($data)) {

			// Check login session
			$unique_id_data['users_id'] = $data['users_id'];
			$unique_id_data['unique_id'] = $data['unique_id'];
			$unique_id_check = $this->profile_model->unique_id_verification($unique_id_data);
			if($unique_id_check == 0) {
				$response = array("status"=>"false","status_code"=>"301","message"=>"Session Expired");
				echo json_encode($response);
				exit;
    		}

    		if($data['api_action'] == "add") {

    			$album_insert_data = array();
    			if(!empty($_FILES['user_album'])) {
    				$this->load->library('upload');
    				$file_count = count($_FILES['user_album']['name']);
    				
    				for($i=0;$i<$file_count;$i++) {
    					
    					// Single upload
    					$_FILES['userfile']['name']= $_FILES['user_album']['name'][$i];
				        $_FILES['userfile']['type']= $_FILES['user_album']['type'][$i];
				        $_FILES['userfile']['tmp_name']= $_FILES['user_album']['tmp_name'][$i];
				        $_FILES['userfile']['error']= $_FILES['user_album']['error'][$i];
				        $_FILES['userfile']['size']= $_FILES['user_album']['size'][$i];

				        $file_ext = ".".strtolower(end((explode('.',$_FILES['userfile']['name']))));
    					$file_name   = time().mt_rand(00,99).$file_ext;

				        $config['upload_path'] = "./".UPLOADS."album/";
    					$config['allowed_types'] = '*';
    					$config['file_name']   = $file_name;
    					$this->upload->initialize($config);
    					if($this->upload->do_upload('userfile')) {

    						$file_path = $config['upload_path'].$file_name;
							// $config['image_library']  = 'gd2';
							// $config['source_image']   = $file_path;
							// $config['create_thumb']   = TRUE;
							// $config['maintain_ratio'] = TRUE;
							// $config['width']          = 100;
							// $config['height']         = 100;
							// $config['thumb_marker']   = "_thumb";
							// $this->load->library('image_lib', $config);
							// $this->image_lib->resize();
							$album_insert_data[$i]['users_id'] = $data['users_id'];
							$album_insert_data[$i]['albums_path'] = str_replace("./", "", $file_path);
							$album_insert_data[$i]['albums_status'] = 1;
         				}
    				}
    			}
    			$insert_user_album = $this->profile_model->insert_user_album($album_insert_data);
    			$response = array("status"=>"true","status_code"=>"200","server_data"=>$insert_user_album['album_data'],"message"=>"Added succesfully");
			}
			else if($data['api_action'] == "remove") {

				$album_ids = explode(',',$data['user_album']); // comma separeted list
				$album_remove = $this->profile_model->remove_user_album($album_ids);
				$response = array("status"=>"true","status_code"=>"200","message"=>"Removed succesfully");
			}
			else {
				$response = array("status"=>"false","status_code"=>"400","message"=>"Keyword mismatch");
			}
		}
		else {
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
		}
		echo json_encode($response);
	}

	/* ==========         User profile        ============= */
	public function user_profile()
	{ 

		if($this->input->post()) {
			$data = $this->input->post();
		}
		else {
			$data = json_decode(file_get_contents("php://input"),true);
		}

		if(!empty($data)) {

			// Check login session
			$unique_id_data['users_id'] = $data['users_id'];
			$unique_id_data['unique_id'] = $data['unique_id'];
			$unique_id_check = $this->profile_model->unique_id_verification($unique_id_data);
			if($unique_id_check == 0) {
				$response = array("status"=>"false","status_code"=>"301","message"=>"Session Expired");
				echo json_encode($response);
				exit;
    		}

    		if($data['api_action'] == "view") {

    			$profile_data = $this->profile_model->user_profile_data($data);

    			if($profile_data['status'] == "true") {

    				$user_profile_data = array();
	    			if(!empty($profile_data['data'])) {
	    				$user_profile_data = $profile_data['data'][0];

	    				// friend request,response logic
	    				$frnd_req_sender_id = $user_profile_data['sender_id'];
	    				$frnd_status = $user_profile_data['friends_status'];
	    				if(!empty($frnd_status)) {

	    					if($frnd_status == 1) {
	    						$user_profile_data['friends_status'] = ($frnd_req_sender_id == $data['users_id']) ? "1" : "2";
	    					}
	    					else {
	    						$user_profile_data['friends_status'] = 3;
	    					}
	    				}

	    				// 1-request sent, 2. ready to accept, 3- friends
        
	    				$user_profile_data['album'] = array();
	    				foreach ($profile_data['data'] as $key => $value) {
	    					foreach ($value as $k => $v) {
	    						if(empty($v)) $user_profile_data[$k] = '';
	    						if($k == "albums_id" || $k == "albums_path") {
	    							if(!empty($v)) $user_profile_data['album'][$key][$k] = $v;
	    							unset($user_profile_data[$k]);
	    						}
	    					}
	    				}
	    				unset($user_profile_data['sender_id']);
	    				$response = array("status"=>"true","status_code"=>"200","server_data"=>$user_profile_data,"message"=>"Listed succesfully");
	    			}
    			}
    			else {
    				$response = array("status"=>"false","status_code"=>"400","message"=>"No records found");
    			}
    		}
			else if($data['api_action'] == "update") {

				if(!empty($_FILES['user_profile_image'])) {

					$file_ext = ".".strtolower(end((explode('.',$_FILES['user_profile_image']['name']))));
					$file_name   = time().mt_rand(000,999).$file_ext;
					$config['upload_path'] = "./".UPLOADS."profile/";
				    $config['allowed_types'] = '*';
					$config['file_name']   = $file_name;
					$this->load->library('upload', $config);
					if ($this->upload->do_upload('user_profile_image')) {
						$file_path = $config['upload_path'].$file_name;
						$config['image_library']  = 'gd2';
						$config['source_image']   = $file_path;
						$config['create_thumb']   = TRUE;
						$config['maintain_ratio'] = TRUE;
						$config['width']          = 100;
						$config['height']         = 100;
						$config['thumb_marker']   = "_thumb";
						
						$this->load->library('image_lib', $config);
						$this->image_lib->resize();
						$file_path = str_replace("./", "", $file_path);
         			}
         			$data['user_profile_image'] = $file_path;
         			if(file_exists("./".$data['pre_profile_image'])) unlink("./".$data['pre_profile_image']);
         			$file_ext = ".".end((explode('.',$data['pre_profile_image'])));
         			$unlink_thumb = str_replace($file_ext, "_thumb$file_ext", $data['pre_profile_image']);
         			if(file_exists($unlink_thumb)) unlink($unlink_thumb);
				}
				$user_id = $data['users_id'];
				unset($data['unique_id'],$data['users_id'],$data['api_action'],$data['pre_profile_image']);
				$update_profile = $this->profile_model->update_profile($user_id,$data);
				$response = array("status"=>"true","status_code"=>"200","message"=>"Profile updated succesfully");			
			}
			else {
				$response = array("status"=>"false","status_code"=>"400","message"=>"Keyword mismatch");
			}
		}
		else {
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
		}
		echo json_encode($response);
	}

	/* ==========         User block        ============= */
	public function user_block()
	{ 

		$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			// Check login session
			$unique_id_data['users_id'] = $data['users_id'];
			$unique_id_data['unique_id'] = $data['unique_id'];
			$unique_id_check = $this->profile_model->unique_id_verification($unique_id_data);
			if($unique_id_check == 0) {
				$response = array("status"=>"false","status_code"=>"301","message"=>"Session Expired");
				echo json_encode($response);
				exit;
    		}

    		if($data['api_action'] == "blocking") {

    			$insert_data['blocklist_from_id'] = $data['users_id'];
    			$insert_data['blocklist_to_id'] = $data['block_users_id'];
				$user_block = $this->profile_model->user_block($insert_data);
				if($user_block['status'] == "true") {
					$response = array("status"=>"true","status_code"=>"200","message"=>$user_block['message']);
				}
				else {
					$response = array("status"=>"false","status_code"=>"400","message"=>$user_block['message']);
				}
			}
			else if($data['api_action'] == "unblocking") {

				$delete_data['blocklist_from_id'] = $data['users_id'];
    			$delete_data['blocklist_to_id'] = $data['block_users_id'];
				$user_block = $this->profile_model->user_unblock($delete_data);
    			if($user_block['status'] == "true") {
					$response = array("status"=>"true","status_code"=>"200","message"=>$user_block['message']);
				}
				else {
					$response = array("status"=>"false","status_code"=>"400","message"=>$user_block['message']);
				}	
    		}
    		else if($data['api_action'] == "blocklist") {

				$user_blocklist = $this->profile_model->user_blocklist($data);
				if(!empty($user_blocklist)) {
					$response = array("status"=>"true","status_code"=>"200","server_data"=>$user_blocklist,"message"=>"Listed succesfully");		
				}
				else {
					$response = array("status"=>"false","status_code"=>"400","message"=>"No records found");
				}
    		}
			else {
				$response = array("status"=>"false","status_code"=>"400","message"=>"Keyword mismatch");
			}
		}
		else {
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
		}
		echo json_encode($response);
	}

	/* ==========         User friends        ============= */
	public function user_friends()
	{ 

		$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			// Check login session
			$unique_id_data['users_id'] = $data['users_id'];
			$unique_id_data['unique_id'] = $data['unique_id'];
			$unique_id_check = $this->profile_model->unique_id_verification($unique_id_data);
			if($unique_id_check == 0) {
				$response = array("status"=>"false","status_code"=>"301","message"=>"Session Expired");
				echo json_encode($response);
				exit;
    		}

    		if($data['api_action'] == "request") {

    			$friend_data = $this->profile_model->user_add_friend($data);
    			if($friend_data['status'] == "true") {
    				$response = array("status"=>"true","status_code"=>"200","message"=>$friend_data['message']);
    			}
    			else {
    				$response = array("status"=>"false","status_code"=>"400","message"=>$friend_data['message']);
    			}
			}
			else if($data['api_action'] == "accept") {

				$friend_data = $this->profile_model->user_accept_friend($data);
    			if($friend_data['status'] == "true") {
    				$response = array("status"=>"true","status_code"=>"200","message"=>$friend_data['message']);
    			}
    			else {
    				$response = array("status"=>"false","status_code"=>"400","message"=>$friend_data['message']);
    			}
    		}
    		else if($data['api_action'] == "cancel") {

				$friend_data = $this->profile_model->user_cancel_friend($data);
    			if($friend_data['status'] == "true") {
    				$response = array("status"=>"true","status_code"=>"200","message"=>$friend_data['message']);
    			}
    			else {
    				$response = array("status"=>"false","status_code"=>"400","message"=>$friend_data['message']);
    			}
    		}
    		else if($data['api_action'] == "unfriend") {

				$friend_data = $this->profile_model->user_remove_friend($data);
    			if($friend_data['status'] == "true") {
    				$response = array("status"=>"true","status_code"=>"200","message"=>$friend_data['message']);
    			}
    			else {
    				$response = array("status"=>"false","status_code"=>"400","message"=>$friend_data['message']);
    			}
    		}
    		else if($data['api_action'] == "request_sent") {

				$friend_data = $this->profile_model->user_request_sent($data);
    			if($friend_data['status'] == "true") {
    				$response = array("status"=>"true","status_code"=>"200","server_data"=>$friend_data['data'],"message"=>"Listed successfully");
    			}
    			else {
    				$response = array("status"=>"false","status_code"=>"400","message"=>"No request sent");
    			}
    		}
    		else if($data['api_action'] == "request_received") {

				$friend_data = $this->profile_model->user_request_received($data);
    			if($friend_data['status'] == "true") {
    				$response = array("status"=>"true","status_code"=>"200","server_data"=>$friend_data['data'],"message"=>"Listed successfully");
    			}
    			else {
    				$response = array("status"=>"false","status_code"=>"400","message"=>"No request received");
    			}
    		}
    		else if($data['api_action'] == "friendlist") {

				$friend_data = $this->profile_model->user_friendlist($data);

				if($friend_data['status'] == "true") {

					$friends_data = array();

					if(!empty($friend_data['mutual_frndlist'])) {
						$user_id_column = array_column($friend_data['mutual_frndlist'],'user_id');
						foreach ($friend_data['friendslist'] as $key => $value) {

							if(!in_array($value['user_id'],$user_id_column)) {
								$value['friends_status'] = '';
								$friends_data['friends_list'][] = $value;
							}
							else {
								$mkey = array_search($value['user_id'], $user_id_column);

								if($friend_data['mutual_frndlist'][$mkey]['friends_status'] == 1) 
								{
									$value['friends_status'] = ($friend_data['mutual_frndlist'][$mkey]['user_request'] == "sender") ? 1 : 2;
									$friends_data['friends_list'][] = $value;
								}
								else {
									$value['friends_status'] = 3;
									$friends_data['mutual_friends'][] = $value;
								}
							}
						}
					}
					else if(!empty($friend_data['friendslist'])) {
						$friends_data['friends_list'] = array_map(function($arr) { return $arr + array('friends_status' => ''); }, $friend_data['friendslist']);
					}
					$response = array("status"=>"true","status_code"=>"200","server_data"=>$friends_data,"message"=>"Listed successfully");
				}
				else {
					$response = array("status"=>"false","status_code"=>"400","message"=>"Something went wrong. Please try again later.");
				}	
    		}
			else {
				$response = array("status"=>"false","status_code"=>"400","message"=>"Keyword mismatch");
			}
		}
		else {
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
		}
		echo json_encode($response);
	}

	
} // End profile controller
