<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xmpp_chat_model extends CI_Model {

	/* Constructor */
    public function __construct()
    {
        parent::__construct();
    }


	/* =============       Insert sigle chat message details        ============== */
	public function insert_xmpp_single_chat_data($data)
	{
		$insert_chat = $this->db->insert('ct_xmpp_single_chat',$data);
		return $insert_chat;
	}

	/* =============       User device token for single user        ============== */
    public function get_users_device_details($user_id) {

        $where_cond = '(logs_login_status=1 AND users_id ="'.$user_id.'")';
        $user_device_details = $this->db->select('logs_device_type,logs_device_token')->from('ct_user_logs')->where($where_cond)->get()->row_array();

        return $user_device_details;
    }

    public function get_xmpp_single_chat($data)
	{
		$model_data = array();

    	$where_cond = array("receiver_id"=>$data['users_id'],"sender_id"=>$data['friend_id']);

	    $this->db->select('xc.sender_id as sender,xc.receiver_id as receiver,xc.chat_content,u.user_fullname as nickname,u.user_fullname as sender_name,u.user_profile_image as profile_pic');
	    $this->db->from('ct_xmpp_single_chat xc');
	    $this->db->join('ct_users u','xc.sender_id=u.users_id','inner');
	    $model_data = $this->db->where($where_cond)->get()->result_array();

	   	return $model_data;
	}

	public function delete_xmpp_single_chat($data)
	{

    	$model_data = $this->db->delete('ct_xmpp_single_chat', array("receiver_id"=>$data['users_id'],"sender_id"=>$data['friend_id']));

	   	return TRUE;
	}

} // End xmpp chat model
