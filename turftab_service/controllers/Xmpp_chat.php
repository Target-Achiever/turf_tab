<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xmpp_chat extends CI_Controller {

	/* Constructor */
	public function __construct()
	{	
		parent::__construct();
		$this->load->library('../controllers/common');
		$this->load->model('xmpp_chat_model');
	}

	// Insert single chat message and send push notification to the opponent user
	public function user_xmpp_single_chat()
	{
		$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) 
		{
			
			$insert_data['sender_id'] = $data['sender'];
			$insert_data['receiver_id'] = $data['receiver'];
			$nickname = $data['nickname'];
			unset($data['sender'],$data['receiver'],$data['nickname'],$data['profile_pic'],$data['sender_name']);
			$insert_data['chat_content'] = json_encode($data);
	   		$insert_chat = $this->xmpp_chat_model->insert_xmpp_single_chat_data($insert_data);
						
			if($insert_chat)
			{
				// push notification
				$user_id = $insert_data['receiver_id'];
		  		$user_device_details = $this->xmpp_chat_model->get_users_device_details($user_id);

		  		if(!empty($user_device_details)) {

	  				$user_device_type = ($user_device_details['logs_device_type'] == 1) ? "android" : "ios";
	  				$user_device_token = array($user_device_details['logs_device_token']);

	  				if(!empty($user_device_token)) {

	  					$user_name = (!empty($nickname)) ? $nickname : 'user';

		  				$msg = array (
									'title' => "You have a new notification",
									'message' => $user_name." sent a new message",
									'notifications_type' => "xmpp_notify",
									'notifications_from_id' => $insert_data['sender_id'],
									'notifications_to_id' => $insert_data['receiver_id'],
									'sender_name' => $user_name
								);

		  				// add event id, event user id, review type
  						$send_notification = $this->common->single_push_notification_service($user_device_type,$user_device_token,$msg,$insert_data['receiver_id']);

  						$response = array("status"=>"true","status_code"=>"200","message"=>"Inserted successfully");
  					}
  					else {
  						$response = array("status"=>"false","status_code"=>"404","message"=>"Device token empty");
  					}
				}
				else {
					$response = array("status"=>"false","status_code"=>"404","message"=>"Device details empty");
				}
			}
			else {
				$response = array("status"=>"false","status_code"=>"400","message"=>"Error in insertion process");
			}
		}
		else {
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
		}

		echo json_encode($response);
	}

	public function user_xmpp_single_getchat()
	{
		$data = json_decode(file_get_contents("php://input"),true);

		if(!empty($data)) 
		{
	
	   		$chat_data = $this->xmpp_chat_model->get_xmpp_single_chat($data);

			if(!empty($chat_data)) {

				$message_content_array = array_map(function($arr) {
								$arr['chat_content'] = json_decode($arr['chat_content'],true);
								return $arr;
							},$chat_data);
				$message_content_splited_data = array_column($message_content_array, 'chat_content');
				unset($message_content_array[0]['chat_content']);
				$message_data[0] = $message_content_array[0];
				$message_data[0]['message_list'] = $message_content_splited_data;

				// Delete the single chat data
				$delete_chat = $this->xmpp_chat_model->delete_xmpp_single_chat($data);
	
		   		$response = array("status"=>"true","status_code"=>"200","server_data"=>$message_data,"message"=>"Listed successfully");
			}
			else {
				$response = array("status"=>"false","status_code"=>"400","message"=>"No record(s)");
			}
		}
	   	else 
		{
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
		}

		echo json_encode($response);
	}

	
} // End xmpp chat controller
