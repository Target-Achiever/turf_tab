<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Referral_model extends CI_Model {

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

    /* ===========    To check whether the user submitted the referral code or not    ======= */
    public function check_device_user($user_id,$device_id,$ref_code)
    {

        $where_cond = '(users_id="'.$user_id.'" OR login_device_id="'.$device_id.'")';

        $model_data_count = $this->db->get_where('ct_referral',$where_cond)->num_rows();

        if($model_data_count == 0) {

            $check_same_user_referral = $this->db->get_where('ct_user_credits',array('users_id'=>$user_id,'user_referral_code'=>$ref_code))->num_rows();

            if($check_same_user_referral == 1) {
                return FALSE;
            }
            else {
                return TRUE;
            }
        }
        else {
            return FALSE;
        }
    }

    /* ===========     Referral code and referral bonus proccess     ======= */
    public function user_referral($data)
    {


        $referred_by_user = $this->db->select('users_id')->get_where('ct_user_credits',array('user_referral_code'=>$data['referral_code']))->row_array();

        if(!empty($referred_by_user['users_id'])) {

            $insert_data = array('users_id'=>$data['users_id'],'referred_by'=>$referred_by_user['users_id'],'login_device_id'=>$data['login_device_id'],'referral_status'=>1);
            $insert_user_referral = $this->db->insert('ct_referral',$insert_data);
            $model_data['status'] = "true";
            $model_data['referred_user'] = $referred_by_user['users_id'];
        }
        else {
            $model_data['status'] = "false";   
        }
        
        return $model_data;
    }

    /* =============       Fetch user device token        ============== */
    public function get_users_device_details($user_id) {

        $where_cond = '(logs_login_status=1 AND users_id ="'.$user_id.'")';
        $user_device_details = $this->db->select('logs_device_type,logs_device_token')->from('ct_user_logs')->where($where_cond)->get()->row_array();
        return $user_device_details;
    }

    /* ===========         Save notifications    ======= */
    public function save_notifications($data)
    {

        $insert_data = $this->db->insert('ct_notifications',$data);

        if($insert_data) {
            $model_data['insert_id'] = $this->db->insert_id();
        }
        else {
            $model_data['insert_id'] = '';
        }

        return $model_data;
    }

    /* ===========     User referral credits     ======= */
    public function user_referral_credits($data)
    {

        $referral_count = $this->db->get_where('ct_referral',array('referred_by'=>$data['users_id'],'referral_status'=>1))->num_rows();

        if($referral_count >= 5) {

            $redeem_val = 5;

            $reminder = $referral_count % $redeem_val;

            $limit = $referral_count - $reminder;

            $quotient = $limit / $redeem_val;

            $where_cond = '(referred_by="'.$data['users_id'].'" AND referral_status=1)';
            $referral_count = $this->db->where($where_cond)->limit($limit,0)->update('ct_referral',array('referral_status'=>2));
            $update_credits = $this->db->where('users_id',$data['users_id'])->set('redeem_credits','redeem_credits+'.$quotient.'',false)->set('total_credits','total_credits+'.$quotient.'',false)->update('ct_user_credits');
        }

        $model_data = $this->db->select('user_like_total,user_multimedia_total,redeem_credits,total_credits,user_referral_code,(select count(r.referral_id) from ct_referral r where r.referred_by="'.$data['users_id'].'" ) as total_referrals')->get_where('ct_user_credits',array('users_id'=>$data['users_id']))->row_array();
   
        return $model_data;       
    }

    /* ===========     User redeem credits - like    ======= */
    public function user_redeem_credits_like($data)
    {

        $user_credits = $this->db->select('redeem_credits')->get_where('ct_user_credits',array('users_id'=>$data['users_id']))->row_array();

        if(!empty($user_credits) && $user_credits['redeem_credits'] > 0) {

            $like_count = 8;

            $update_credits = $this->db->where('users_id',$data['users_id'])->set('user_like_count','user_like_count+'.$like_count.'',false)->set('user_like_total','user_like_total+'.$like_count.'',false)->set('redeem_credits','redeem_credits-1',false)->update('ct_user_credits');
            $model_data['status'] = "true";
        }
        else {
            $model_data['status'] = "false";
        }
          
        return $model_data;       
    }

    /* ===========     User redeem credits - post    ======= */
    public function user_redeem_credits_post($data)
    {

        $user_credits = $this->db->select('redeem_credits')->get_where('ct_user_credits',array('users_id'=>$data['users_id']))->row_array();

        if(!empty($user_credits) && $user_credits['redeem_credits'] > 0) {

            $post_count = 3;

            $update_credits = $this->db->where('users_id',$data['users_id'])->set('user_multimedia_post','user_multimedia_post+'.$post_count.'',false)->set('user_multimedia_total','user_multimedia_total+'.$post_count.'',false)->set('redeem_credits','redeem_credits-1',false)->update('ct_user_credits');
            $model_data['status'] = "true";
        }
        else {
            $model_data['status'] = "false";
        }
          
        return $model_data;       
    }

} // End referral model
