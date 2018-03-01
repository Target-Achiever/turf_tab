<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile_model extends CI_Model {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
    }

    /* =============       Unique id verification        ============== */
    public function unique_id_verification($data) {
    	$where_cond = array("users_id"=>$data['users_id'],"unique_id"=>$data['unique_id'],"logs_login_status"=>1);
    	$record_count = $this->db->get_where('ct_user_logs',$where_cond)->num_rows();
    	return $record_count;
    }

    /* =================     Insert album       =========== */
    public function insert_user_album($data) {

    	if(!empty($data)) {
    		$insert_album_batch = $this->db->insert_batch('ct_albums',$data);
            $insert_count = $this->db->affected_rows();
            $first_id = $this->db->insert_id();
            $model_data['album_data'] = $this->db->select('albums_id,albums_path')->from('ct_albums')->where('albums_id >=',$first_id)->limit($insert_count,0)->get()->result_array();
    	}
    	else {
    		$model_data['album_data'] = array();
    	}
    	return $model_data;
    }

    /* =================     Insert album       =========== */
    public function remove_user_album($data) {

        $album_update = $this->db->where_in('albums_id',$data)->update('ct_albums',array('albums_status'=>0));
        return TRUE;
    }


    /* =================     User blocked count      =========== */
    public function user_blocked_count($from_id,$to_id) {

        $where_cond = '((blocklist_from_id="'.$from_id.'" AND blocklist_to_id="'.$to_id.'") OR (blocklist_from_id="'.$to_id.'" AND blocklist_to_id="'.$from_id.'"))';
        $blocked_count = $this->db->get_where('ct_blocklist',$where_cond)->num_rows();
        return $blocked_count;
    }
    

    /* =================     Get user profile data       =========== */
    public function user_profile_data($data) {

        $blocked_count = $this->user_blocked_count($data['users_id'],$data['action_id']);
        $model_data = array();

        if($blocked_count == 0) {
            $this->db->select('u.users_id,u.user_name,u.user_country_code,u.user_email,u.user_mobile,u.user_gender,u.user_dob,u.user_hobbies,u.user_sexual_preference,u.user_ethnicity,u.user_religion,u.user_profile_status,u.user_profile_image,u.user_description,u.user_register_type,u.user_profile_updated_date,u.user_profile_created_date,a.albums_id,a.albums_path,f.friends_status,f.sender_id');
            $this->db->from('ct_users u');
            $this->db->join('ct_albums a','u.users_id=a.users_id AND a.albums_status=1','left');
            $this->db->join('ct_friends f','(u.users_id=f.receiver_id AND f.sender_id="'.$data['users_id'].'") OR (u.users_id=f.sender_id AND f.receiver_id="'.$data['users_id'].'")','left');
            $this->db->where('u.users_id',$data['action_id']);
            $model_data['data'] = $this->db->get()->result_array();
            $model_data['status'] = "true";
        }
        else {
            $model_data['status'] = "false";
        }
        return $model_data;
    }

    /* =================     To block the user       =========== */
    public function user_block($data) {

        $blocked_count = $this->user_blocked_count($data['blocklist_from_id'],$data['blocklist_to_id']);

        if($blocked_count == 0) {
            $block_data = $this->db->insert('ct_blocklist',$data);
            $where_cond = '((sender_id="'.$data['blocklist_from_id'].'" AND receiver_id="'.$data['blocklist_to_id'].'") OR (sender_id="'.$data['blocklist_to_id'].'" AND receiver_id="'.$data['blocklist_from_id'].'"))';
            $update_friendlist = $this->db->where($where_cond)->update('ct_friends',array(" friends_status"=>3));
            $model_data['message'] = "User blocked successfully";
            $model_data['status'] = "true";
        }
        else {
            $model_data['message'] = "User already blocked";
            $model_data['status'] = "false";
        }
        return $model_data;
    }

    /* =================     To unblock the user       =========== */
    public function user_unblock($data) {

       $blocked_count = $this->user_blocked_count($data['blocklist_from_id'],$data['blocklist_to_id']);

        if($blocked_count == 0) {
            $model_data['message'] = "No records found";
            $model_data['status'] = "false";
        }
        else {

            $user_block_data = $this->db->delete('ct_blocklist',$data);
            $where_cond = '((sender_id="'.$data['blocklist_from_id'].'" AND receiver_id="'.$data['blocklist_to_id'].'") OR (sender_id="'.$data['blocklist_to_id'].'" AND receiver_id="'.$data['blocklist_from_id'].'"))';
            $update_friendlist = $this->db->set('friends_status','friends_temp_status',FALSE)->where($where_cond)->update('ct_friends');
            $model_data['message'] = "User unblocked successfully";
            $model_data['status'] = "true";
        }
        return $model_data;
    }

    /* =================     To unblock the user       =========== */
    public function user_blocklist($data) {

        $this->db->select('u.users_id as blocked_user_id,u.user_name,u.user_profile_image');
        $this->db->from('ct_blocklist b');
        $this->db->join('ct_users u','b.blocklist_to_id=u.users_id','inner');
        $this->db->where('b.blocklist_from_id',$data['users_id']);
        $model_data = $this->db->get()->result_array();
        return $model_data;
    }

    /* =================     Add friend       =========== */
    public function user_add_friend($data) {

       $blocked_count = $this->user_blocked_count($data['users_id'],$data['friend_id']);

        if($blocked_count == 0) {

            $where_cond = '((sender_id="'.$data['users_id'].'" AND receiver_id="'.$data['friend_id'].'") OR (sender_id="'.$data['friend_id'].'" AND receiver_id="'.$data['users_id'].'"))';            
            $user_friend_data = $this->db->get_where('ct_friends',$where_cond)->num_rows();
            if($user_friend_data == 0) {

                $insert_data = array("sender_id"=>$data['users_id'],"receiver_id"=>$data['friend_id'],"friends_temp_status"=>1,"friends_status"=>1);
                $insert_friend_data = $this->db->insert('ct_friends',$insert_data);
                $model_data['message'] = "Friend request sent successfully";
                $model_data['status'] = "true"; 
            }
            else {
                $model_data['message'] = "Already sent request";
                $model_data['status'] = "false"; 
            }         
        }
        else {
            $model_data['message'] = "Something went wrong. Please try again later";
            $model_data['status'] = "false";            
        }
        return $model_data;
    }

    /* =================     Accept friend       =========== */
    public function user_accept_friend($data) {

       $blocked_count = $this->user_blocked_count($data['users_id'],$data['friend_id']);

        if($blocked_count == 0) {

            $where_cond = '(sender_id="'.$data['friend_id'].'" AND receiver_id="'.$data['users_id'].'" AND friends_status=1)';            
            $user_friend_data = $this->db->get_where('ct_friends',$where_cond)->num_rows();
            if($user_friend_data == 0) {

                $model_data['message'] = "No request found";
                $model_data['status'] = "false"; 
            }
            else {

                $update_friend_data = $this->db->where($where_cond)->update('ct_friends',array("    friends_temp_status"=>2,"friends_status"=>2));
                $model_data['message'] = "Friend request accepted successfully";
                $model_data['status'] = "true";             
            }         
        }
        else {
            $model_data['message'] = "Something went wrong. Please try again later";
            $model_data['status'] = "false";            
        }
        return $model_data;
    }

    /* =================     Cancel friend       =========== */
    public function user_cancel_friend($data) {

       $blocked_count = $this->user_blocked_count($data['users_id'],$data['friend_id']);

        if($blocked_count == 0) {

            $where_cond = '((sender_id="'.$data['friend_id'].'" AND receiver_id="'.$data['users_id'].'" AND friends_status=1) OR (receiver_id="'.$data['friend_id'].'" AND sender_id="'.$data['users_id'].'" AND friends_status=1))';            
            $user_friend_data = $this->db->get_where('ct_friends',$where_cond)->num_rows();
            if($user_friend_data == 0) {

                $model_data['message'] = "No request found";
                $model_data['status'] = "false"; 
            }
            else {

                $delete_friend_data = $this->db->where($where_cond)->delete('ct_friends');
                $model_data['message'] = "Friend request cancelled successfully";
                $model_data['status'] = "true";             
            }         
        }
        else {
            $model_data['message'] = "Something went wrong. Please try again later";
            $model_data['status'] = "false";            
        }
        return $model_data;
    }

    /* =================     Remove friend       =========== */
    public function user_remove_friend($data) {

       $blocked_count = $this->user_blocked_count($data['users_id'],$data['friend_id']);

        if($blocked_count == 0) {

            $where_cond = '((sender_id="'.$data['users_id'].'" AND receiver_id="'.$data['friend_id'].'" AND friends_status=2) OR (sender_id="'.$data['friend_id'].'" AND receiver_id="'.$data['users_id'].'" AND friends_status=2))';     
            $user_friend_data = $this->db->get_where('ct_friends',$where_cond)->num_rows();

            if($user_friend_data == 0) {

                $model_data['message'] = "Friend not exist";
                $model_data['status'] = "false"; 
            }
            else {

                $delete_friend_data = $this->db->where($where_cond)->delete('ct_friends');
                $model_data['message'] = "Friend removed successfully";
                $model_data['status'] = "true";             
            }         
        }
        else {
            $model_data['message'] = "Something went wrong. Please try again later";
            $model_data['status'] = "false";            
        }
        return $model_data;
    }

    /* =================     Friend request sent list       =========== */
    public function user_request_sent($data) {

        $where_cond = '(f.sender_id="'.$data['users_id'].'" AND f.friends_status=1)';     
        $this->db->select('u.users_id,u.user_name,u.user_profile_image');
        $this->db->from('ct_friends f');
        $this->db->join('ct_users u','f.receiver_id=u.users_id','inner');
        $user_friend_data = $this->db->where($where_cond)->get()->result_array();

        if(!empty($user_friend_data)) {

            $model_data['data'] = $user_friend_data;
            $model_data['status'] = "true";
        }
        else {

            $model_data['status'] = "false";             
        }
        return $model_data;
    }

    /* =================     Friend request received list       =========== */
    public function user_request_received($data) {

        $where_cond = '(f.receiver_id="'.$data['users_id'].'" AND f.friends_status=1)';     
        $this->db->select('u.users_id,u.user_name,u.user_profile_image');
        $this->db->from('ct_friends f');
        $this->db->join('ct_users u','f.sender_id=u.users_id','inner');
        $user_friend_data = $this->db->where($where_cond)->get()->result_array();

        if(!empty($user_friend_data)) {

            $model_data['data'] = $user_friend_data;
            $model_data['status'] = "true";
        }
        else {

            $model_data['status'] = "false";             
        }
        return $model_data;
    }

    /* =================     User friendlist       =========== */
    public function user_friendlist($data) {

        $model_data['friendslist'] = array();
        $model_data['mutual_frndlist'] = array();

        $blocked_count = $this->user_blocked_count($data['users_id'],$data['action_id']);

        if($blocked_count == 0) {

            $where_cond = '((f.sender_id="'.$data['action_id'].'" OR f.receiver_id="'.$data['action_id'].'") AND f.friends_status=2)';
            $this->db->select('(CASE WHEN us.users_id!='.$data['action_id'].' THEN us.user_name ELSE ur.user_name END) as user_name,(CASE WHEN us.users_id!='.$data['action_id'].' THEN us.users_id ELSE ur.users_id END) as user_id,(CASE WHEN us.users_id!='.$data['action_id'].' THEN us.user_profile_image ELSE ur.user_profile_image END) as user_image');
            $this->db->from('ct_friends f');
            $this->db->join('ct_users us','f.sender_id=us.users_id','left');
            $this->db->join('ct_users ur','f.receiver_id=ur.users_id','left');
            $this->db->having('user_id NOT IN (select (CASE WHEN blocklist_from_id!='.$data['users_id'].' THEN blocklist_from_id ELSE blocklist_to_id END) as block_ids from ct_blocklist where blocklist_from_id = '.$data['users_id'].' OR blocklist_to_id = '.$data['users_id'].')', NULL, FALSE); 
            $this->db->where($where_cond);
            $model_data['friendslist'] = $this->db->get()->result_array();

            if(!empty($model_data['friendslist']) && $data['users_id'] != $data['action_id']) {

                $frnd_list = implode(',',array_column($model_data['friendslist'],'user_id'));
                $where_cond_mutual = '(((sender_id="'.$data['users_id'].'" AND receiver_id IN('.$frnd_list.')) OR (receiver_id="'.$data['users_id'].'" AND sender_id IN('.$frnd_list.')))  AND friends_status!=3)';
                $this->db->select('(CASE WHEN sender_id!='.$data['users_id'].' THEN sender_id ELSE receiver_id END) as user_id,(CASE WHEN sender_id='.$data['users_id'].' THEN "sender" ELSE "receiver" END) as user_request,friends_status');
                $this->db->from('ct_friends');
                // $this->db->having('mutual_frnd_ids NOT IN', NULL, FALSE); 
                $this->db->where($where_cond_mutual);
                $model_data['mutual_frndlist'] = $this->db->get()->result_array();
            }
            $model_data['status'] = "true";
        }
        else {
            $model_data['status'] = "false";
        }
        return $model_data;
    }

    /* =============         Update profile details        ======== */
    public function update_profile($user_id,$data)
    {
        $model_data = $this->db->where('users_id',$user_id)->update('ct_users',$data);
        return TRUE;
    }


} // End profile model
