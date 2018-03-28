<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tictactoe extends CI_Controller {

	/* Constructor */
	public function __construct()
    {
        parent::__construct();
		$this->load->model('tictactoe_model');
	    $this->load->library('../controllers/common');
    }

	public function index()
	{

		// commented by siva. just use this for testing purpose

		// 374

		// // static_data
		// $data['users_id'] = 374;
		// $data['user_name'] = "ibrahim";
		// $data['opponent_id'] = 375;
		// $data['opponent_name'] = "Diptiranjan Mallick";
		// $data['playing_user_id'] = 375;
		// $data['api_action'] = "game_start";
		// $data['game_tictactoe_id'] = 4;

		// $game_status = $this->tictactoe_model->get_game_status($data['game_tictactoe_id']);
		// $data['tictactoe_status'] = $game_status;
		// $input_data['game'] = $data;
		// $this->load->view('tictactoe',$input_data);
	}

	public function index2()
	{

		// commented by siva. just use this for testing purpose

		// 375
		
		// static_data
		// $data['users_id'] = 375;
		// $data['user_name'] = "Diptiranjan Mallick";
		// $data['opponent_id'] = 374;
		// $data['opponent_name'] = "ibrahim";
		// $data['playing_user_id'] = 375;
		// $data['api_action'] = "game_start";
		// $data['game_tictactoe_id'] = 4;

		// $game_status = $this->tictactoe_model->get_game_status($data['game_tictactoe_id']);
		// $data['tictactoe_status'] = $game_status;
		// $input_data['game'] = $data;
		// $this->load->view('tictactoe',$input_data);

		// echo "welcome";
	}

	/* ============         Tic tac toe game         =========== */
	public function user_tictactoe_activities() {

		$data = json_decode(file_get_contents('php://input'),true);

		// static_data
		// $data['users_id'] = 374;
		// $data['user_name'] = "ibrahim";
		// $data['opponent_id'] = 375;
		// $data['opponent_name'] = "Diptiranjan Mallick";
		// $data['playing_user_id'] = 375;
		// $data['api_action'] = "game_start";
		// $data['game_tictactoe_id'] = 4;

  		if(!empty($data)) {

			if($data['api_action'] == "initiate") {

				$user_id = $data['users_id'];
				$game_id = $data['game_tictactoe_id'];

				$game_details = $this->tictactoe_model->check_game_details($user_id,$game_id);

				if($game_details['status'] == "true") {
					$response = array('status'=>true,'status_code'=>'200','server_data'=>$game_details['data'],'message'=>"Listed successfully.");
					echo json_encode($response);

				}
				else {
					$response = array('status'=>false,'status_code'=>'400','message'=>"Something went wrong. Please try again later.");
					echo json_encode($response);
				}
			}
			else if($data['api_action'] == "game_start") {

				$game_status = $this->tictactoe_model->get_game_status($data['game_tictactoe_id']);
				$data['playing_user_id'] = $data['beginner_id'];
				$data['tictactoe_status'] = $game_status;
				$input_data['game'] = $data;
				$this->load->view('tictactoe',$input_data);
			}
		}
		else {
			$response = array("status"=>"false","status_code"=>"404","message"=>"Fields must not be Empty");
			echo json_encode($response);
		}
	}

	/* ============         Tic tac toe game status for each 5sec      =========== */
	public function user_game_update() {

		$game_id = $this->input->post('game_id');
		$fetch_data = $this->tictactoe_model->user_game_update_status($game_id);

		echo json_encode($fetch_data);
	}

	/* ============         Tic tac toe game status update      =========== */
	public function user_update_game_status() {

		$data = $this->input->post();
		$update_status = $this->tictactoe_model->user_update_game_status($data);

		echo json_encode($update_status);
	}

	/* ============         Check the user has questions or not      =========== */
	public function user_check_question_status() {

		$data = $this->input->post();
		$ques_status = $this->tictactoe_model->user_check_question_status($data);

		echo json_encode($ques_status);
	}

	/* ============         Tic tac toe game end stauts update      =========== */
	public function user_game_end() {

		$data = $this->input->post();
		$update_status = $this->tictactoe_model->user_game_end($data);

		echo json_encode($update_status);
	}

	/* ============         Check question raised or not      =========== */
	public function user_check_question_raise() {

		$data = $this->input->post();
		$ques_status = $this->tictactoe_model->check_question_raise($data);

		echo json_encode($ques_status);
	}

	/* ============         Check question raised or not      =========== */
	public function user_update_answer() {

		$data = $this->input->post();
		$ques_status = $this->tictactoe_model->user_update_answer($data);

		echo json_encode($ques_status);
	}

	/* ============         Check answer update alert     =========== */
	public function user_answer_update_status() {

		$data = $this->input->post();
		$ques_status = $this->tictactoe_model->user_answer_update_status($data);

		echo json_encode($ques_status);
	}

	/* ============         Update answer is correct or wrong once validated     =========== */
	public function user_update_answer_validation() {

		$data = $this->input->post();
		$ques_status = $this->tictactoe_model->user_update_answer_validation($data);

		echo json_encode($ques_status);
	}

	/* ============         Check validate answer status     =========== */
	public function user_answer_validate_status() {

		$data = $this->input->post();
		$ques_status = $this->tictactoe_model->user_answer_validate_status($data);

		echo json_encode($ques_status);
	}

	/* ============         Multiple question     =========== */
	public function user_raise_multiple_question() {

		$data = $this->input->post();
		$ques_status = $this->tictactoe_model->user_raise_multiple_question($data);

		echo json_encode($ques_status);
	}
	

} // End tictac toe controller

