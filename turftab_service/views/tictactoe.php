<?php

$game_id = $game['game_tictactoe_id'];
$tictactoe_status = $game['tictactoe_status'];
$game_status = ($tictactoe_status == 1) ? "Waiting for opponent" : ($tictactoe_status == 2 ? "Game started" : ($tictactoe_status == 3 ? "Game completed" : ''));
if($game['playing_user_id'] == $game['users_id']) {
	$player1_id = $game['users_id'];
	$player2_id = $game['opponent_id'];
	$player1_name = $game['user_name'];
	$player2_name = $game['opponent_name'];
	$value = "p1";
	$answered = "1";
}
else {
	$player1_id = $game['opponent_id'];
	$player2_id = $game['users_id'];
	$player1_name = $game['opponent_name'];
	$player2_name = $game['user_name'];
	$value = "p2";
	$answered = "2";
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8">  
		<title>Tic Tac Toe</title>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/style.css" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css"> -->

	</head>
	<body>
		<!-- load page !-->
		<section id="loading"> 
			<div id="text"> Game loading </div>
			<img src="<?php echo base_url(); ?>assets/images/load.GIF" id="gif" alt="loading"/>
		</section>
		<!-- end load page !-->
		<input type="hidden" value="<?php echo $value; ?>" id="player_val" />
		<input type="hidden" value="<?php echo $tictactoe_status; ?>" id="game_status_val" />
		<!-- 1- allow 2- restrict -->
		<input type="hidden" value="<?php echo $answered; ?>" id="answered_status" />

		<section id="allgame">	
			<header id="hd">
				<!-- <span id="hdp1">Hello, <b class="b1">player </b>(Total:3 wins and 3 defeats).</span><span id="your">You're playing with:</span>  -->
				<!-- <span id="hdp2">Player2 (Total: 2 wins and 2 defeats).</span> -->
				<span id="hdp1">Hello <b> <?php echo $game['user_name']; ?> </b> </span>
			</header>
			<div id="game_status">
				<span> <?php echo $game_status; ?> </span>
			</div>

			<!-- <div class="mes">Testando mensagem</div> -->
			<section id="score"></section><!-- section score !-->
			<section id="game">
				<div id="players">
					<div id="player1">Player 1 : <b class="b1"><?php echo $player1_name; ?></b> </div>
					<div id="player2">Player 2 : <b class="b2"><?php echo $player2_name; ?></b> </div>
					<div id="middle">
						<div id="p1"><b class="b1">X<b/></div><!-- div p1(player 1) !-->
						<div id="p2"><b class="b2">O<b/></div><!-- div p2(player 2) !-->
						<div id="line"></div><!-- line of middle !-->
					</div><!-- div middle !-->
				</div><!-- div players !-->

				<div id="box">
			
					<div id="sec0" class="boxs"></div> 
					<div id="sec1" class="boxs"></div> 
					<div id="sec2" class="boxs marginr0"></div>
					<div id="sec3" class="boxs"></div>
					<div id="sec4" class="boxs"></div>
					<div id="sec5" class="boxs marginr0"></div>
					<div id="sec6" class="boxs marginb0"></div>
					<div id="sec7" class="boxs marginb0"></div>
					<div id="sec8" class="boxs marginb0 marginr0"></div>
				</div> <!-- box(Box of the game) !-->
			</section><!--section game !-->
		</section><!-- Close all game !-->

		<div id="confirmBox_question" class="hover_bkgr_fricc">
			<span class="helper"></span>
	    	<div class="message">
	    		<!-- <div class="popupCloseButton">X</div> -->
	    		<p> Enter the question and answer </p>
	    		<br/>
	    		<form id="question_form">
	    			<label>Question</label>
					<div class="form-group">
						<input type="text" id="form1_ques" name="question" class="input-group" />
					</div>	
					<label>Answer</label>
					<div class="form-group">
						<input type="text" id="form1_ans" name="answer" class="input-group" />
					</div>
		    		<div class="button-group">
			    		<span class="yes alert_buttons">Sumbit</span>
			    		<!-- <span class="no alert_buttons">Skip</span> -->
			    	</div>
	    		</form>	
		   	</div>
	    </div>

	    <div id="confirmBox_answer" class="hover_bkgr_fricc">
			<span class="helper"></span>
	    	<div class="message">
	    		<p> Enter the answer </p>
	    		<form id="answer_form">
					<div class="qstn">
						<p>Question</p>					
						<p><span id="ques_label"> </span></p>
					</div>
					<label>Answer</label>
					<div class="form-group">
						<input type="text" id="form2_ans" value="" name="answer" class="input-group" />
					</div>
		    		<div class="button-group">
			    		<span class="yes alert_buttons">Submit</span>
			    	</div>
			    	<input type="hidden" value="" id="quiz_id" />
	    		</form>   			
		   	</div>
	    </div>

	    <div id="confirmBox_validate" class="hover_bkgr_fricc">
			<span class="helper"></span>
	    	<div class="message">
	    		<p> Enter the answer </p>
	    		<br/>
	    		<form id="validate_form">
		    		<label>Question</label><br/>
		    		<span id="validate_ques_label"> </span>
		    		<br/>
		    		<label>Answer</label><br/>
		    		<span id="validate_ans_label"> </span>
		    		<br/>
		    		<input type="hidden" value="" id="quiz_id_validate" />
		    		<div class="button-group">
			    		<span class="yes alert_buttons">Correct</span>
		    			<span class="no alert_buttons">Wrong</span>
			    	</div>    		
	    		</form>	
		   	</div>
	    </div>

	    <script type="text/javascript"> 
			var base_url = "<?php echo base_url(); ?>";
			var game_id = "<?php echo $game_id; ?>";
			var current_user_id = "<?php echo $game['users_id']; ?>";
			var opponent_user_id = "<?php echo $game['opponent_id']; ?>";
		</script>

		<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.js"> </script>
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/game.js"> </script>
		<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script> -->
	</body>
</html>