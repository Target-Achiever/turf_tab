<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Referral extends CI_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
        // $this->load->library('form_validation');
		$this->load->model('referral_model');
		$this->load->library('referral_code');
		$this->load->library('../controllers/common');
    }

	public function index()
	{
		echo "welcome";
	}

	/* ============        User referral submit        =========== */
	public function user_referral_submit() {

		$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			// Check login session
			$unique_id_data['users_id'] = $data['users_id'];
			$unique_id_data['unique_id'] = $data['unique_id'];
			$unique_id_check = $this->referral_model->unique_id_verification($unique_id_data);
			if($unique_id_check == 0) {
				$response = array("status"=>"false","status_code"=>"301","message"=>"Session Expired");
				echo json_encode($response);
				exit;
    		}

    		$user_name = (!empty($data['loggedin_user_name']) ? $data['loggedin_user_name'] : 'user');

			$check_user_referral = $this->referral_model->check_device_user($data['users_id'],$data['login_device_id'],$data['referral_code']);

			if($check_user_referral) {

				$user_referral = $this->referral_model->user_referral($data);

				if($user_referral['status'] == "true") {

  					//	push notification
  					$user_id = $user_referral['referred_user'];
  					$user_device_details = $this->referral_model->get_users_device_details($user_id);

  					// Save notifications
  					$notification_data = array('notifications_from_id'=> $data['users_id'],'notifications_to_id'=> $user_id,'notifications_msg'=> "has joined in razzle with your referral code.",'notifications_type'=> "user_referral" ,'notifications_status'=> 1);
  					$save_notifications = $this->referral_model->save_notifications($notification_data);

  					if(!empty($user_device_details)) {

  						$user_device_type = ($user_device_details['logs_device_type'] == 1) ? "android" : "ios";
  						$user_device_token = array($user_device_details['logs_device_token']);

		  				if(!empty($user_device_token)) {
			  				$msg = array (
										'title' => "You have a new notification.",
										'message' => ucfirst($user_name)." has joined in razzle with your referral code.",
										'notifications_type' => "user_referral",
										'notifications_id' => $save_notifications['insert_id'],
										'notifications_from_id' => $data['users_id']
										);
	  						$send_notification = $this->common->single_push_notification_service($user_device_type,$user_device_token,$msg,$user_id);
	  					}
					}

					$response = array("status"=>"true","status_code"=>"200","message"=>"Added successfully");
				}
				else {
					$response = array("status"=>"false","status_code"=>"400","message"=>"Invalid Referral Code");
				}
			}
			else {
				$response = array("status"=>"false","status_code"=>"400","message"=>"Not applicable");
			}
		}
		else {
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
		}

		echo json_encode($response);
	}

	/* ============        User referral credits        =========== */
	// public function user_referral_credits() {

	// 	$data = json_decode(file_get_contents("php://input"),true);

	// 	if(!empty($data)) {

	// 		// Check login session
	// 		$unique_id_data['users_id'] = $data['users_id'];
	// 		$unique_id_data['unique_id'] = $data['unique_id'];
	// 		$unique_id_check = $this->referral_model->unique_id_verification($unique_id_data);
	// 		if($unique_id_check == 0) {
	// 			$response = array("status"=>"false","status_code"=>"301","message"=>"Session Expired");
	// 			echo json_encode($response);
	// 			exit;
 //    		}

	// 		$user_referral_credits = $this->referral_model->user_referral_credits($data);

	// 		if(!empty($user_referral_credits)) {

	// 			$response = array("status"=>"true","status_code"=>"200","server_data"=>$user_referral_credits,"message"=>"Listed successfully");
	// 		}
	// 		else {
	// 			$response = array("status"=>"false","status_code"=>"400","message"=>"No record(s) found");
	// 		}
	// 	}
	// 	else {
	// 		$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
	// 	}

	// 	echo json_encode($response);
	// }

	/* ============        User reedeem credits        =========== */
	public function user_redeem_credits() {

		$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) {

			// Check login session
			$unique_id_data['users_id'] = $data['users_id'];
			$unique_id_data['unique_id'] = $data['unique_id'];
			$unique_id_check = $this->referral_model->unique_id_verification($unique_id_data);
			if($unique_id_check == 0) {
				$response = array("status"=>"false","status_code"=>"301","message"=>"Session Expired");
				echo json_encode($response);
				exit;
    		}

    		if($data['redeem_type'] == 1) {
    			$user_redeem_credits = $this->referral_model->user_redeem_credits_like($data);
    			$redeem_type = "likes";
    		}
    		else {
    			$user_redeem_credits = $this->referral_model->user_redeem_credits_post($data);
    			$redeem_type = "multimedia post";
    		}
			
			if($user_redeem_credits['status'] == "true") {

				$response = array("status"=>"true","status_code"=>"200","message"=>"Extra ".$redeem_type." added successfully");
			}
			else {
				$response = array("status"=>"false","status_code"=>"400","message"=>"Something went wrong. Please try again later");
			}
		}
		else {
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
		}

		echo json_encode($response);
	}
	
} // End referral controller


