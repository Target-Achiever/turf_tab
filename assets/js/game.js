$(document).ready(function() {

  // Initiate variable with selectors
  var boxs = $('.boxs');
  var all = $('#allgame');
  var load = $('#loading');
  var game_status = $('#game_status_val').val();
  var win = true;
  if(game_status == 2) {
    var win = false;
  }
  var ver = true;
  all.hide();
  
  setTimeout(function() {
    load.fadeOut('fast');
    all.fadeIn('slow');
  },1000);


  var game_status = function() {

    $.ajax({

      url : base_url+"game_update",
      type : "POST",
      data : {game_id:game_id},
      dataType : "json",
      success : function(res) {

        if(res) {

          if(res.tictactoe_status == 2) {

            if($('#game_status_val').val() == 1 ) {
              $('#game_status').html('Game started');
              $('#game_status_val').val('2');
            }         
            var val = (res.playing_user == "p1") ? 'X' : 'O';
            var var_clr = (res.playing_user == "p1") ? 'b1' : 'b2';
            if(var_clr != $('#score').find('b').attr('class')) {
              $('#score').html('Playing Now : <b class='+var_clr+'> '+val+' </b>');
            }
          }
          delete res.game_id;
          delete res.playing_user;
          delete res.tictactoe_status;
          delete res.tictactoe_updated_date;
          
          for (i in res) {
            if(res[i] == 'p1'){
              $('#'+i).html('<b class="b1">X<b/>')
              $('#'+i).css("background","#CCCCCC");
              $('#'+i).addClass('selected');
            }
            else if(res[i] == 'p2') {
              $('#'+i).html('<b class="b2">O<b/>')
              $('#'+i).css("background","#CCCCCC");
              $('#'+i).addClass('selected');
            }
            else {
              $('#'+i).html('')
              $('#'+i).css("background","#E8E8E8");
              $('#'+i).removeClass('selected');
            }
          }
        }
      }
    });
  };

// game_status();
setInterval(game_status, 2000);

boxs.click(function() {

  if($(this).hasClass('selected')) {
    return false;
  }

  var hidden_val = $('#player_val').val();
  var answered_status = $('#answered_status').val();
  var total_selected_count = $('.selected').length;
  var game_status = $('#game_status_val').val();
  if(game_status != 2) {
    alert("You can't play now.");
    return false;
  }
  if(hidden_val == "p1" &&  (total_selected_count % 2) !== 0) {
    alert("Please wait for your turn.");
    return false;
  }
  else if(hidden_val == "p2" &&  (total_selected_count % 2) === 0) {
    alert("Please wait for your turn.");
    return false;
  }

  var return_val = check_have_questions(current_user_id,game_id);

  if(return_val) {
    var sec = $(this).attr('id');
    var divt = $(this);  
    var confirm = doConfirm_question(function yes()
    {
      var ques = $('#question_form').find('#form1_ques').val();
      var ans = $('#question_form').find('#form1_ans').val();
      $.ajax({

        url : base_url+"update_game_status",
        async : false,
        type : "POST",
        data : {game_id:game_id,sec:sec,player:hidden_val,user_id:opponent_user_id,ques:ques,ans:ans},
        dataType : "json",
        cache:false,
        success : function(res) {

            if(res.status == "true") {

                if(hidden_val == 'p1') {
                  $(divt).html('<b class="b1">X<b/>');
                  $(divt).css("background","#CCCCCC");
                  $(divt).addClass('selected');
                }
                else if(hidden_val == 'p2') {
                  $(divt).html('<b class="b2">O<b/>');
                  $(divt).css("background","#CCCCCC");
                  $(divt).addClass('selected');
                }
            }
            else {
                alert(res.message);  
            }
          }
      });
    },
    function no()
    {
      var ques = '';
      var ans = '';
      $.ajax({

        url : base_url+"update_game_status",
        async : false,
        type : "POST",
        data : {game_id:game_id,sec:sec,player:hidden_val,user_id:opponent_user_id,ques:ques,ans:ans},
        dataType : "json",
        cache:false,
        success : function(res) {

            if(res.status == "true") {

                if(hidden_val == 'p1') {
                  $(divt).html('<b class="b1">X<b/>');
                  $(divt).css("background","#CCCCCC");
                  $(divt).addClass('selected');
                }
                else if(hidden_val == 'p2') {
                  $(divt).html('<b class="b2">O<b/>');
                  $(divt).css("background","#CCCCCC");
                  $(divt).addClass('selected');
                }
            }
            else {
                alert(res.message);  
            }
          }
      });
    });
  }
});
 
  setInterval(function() {

    if(win == false) {

      var value0 = $('#sec0').html();
      var value1 = $('#sec1').html();
      var value2 = $('#sec2').html();
      var value3 = $('#sec3').html();
      var value4 = $('#sec4').html();
      var value5 = $('#sec5').html();
      var value6 = $('#sec6').html();
      var value7 = $('#sec7').html();
      var value8 = $('#sec8').html();
   
    // 0,1,2 
    if(value0 != '' && value1 !='' && value2 !='' && value0 === value1 && value1 === value2 && value2 ===value0) {
      // alert('0,1,2');
      win = true;
      var hidden_val = $('#player_val').val();
      var selected_val = strip_tags(value0);
      var winning_player = (hidden_val=="p1" && selected_val=="X") ? "p1" : ((hidden_val=="p2" && selected_val=="O") ? "p2" : "");
      if(hidden_val == winning_player) {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You won the game');  
      }
      else {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You lose the game');
      }
      update_winning_status(winning_player);
    }
    // 0,3,6
    else if(value0 != '' && value3 != '' && value6 != '' && value0 === value3 && value3 === value6 && value6 === value0) { 
      // alert('0,3,6');
      win = true;
      var hidden_val = $('#player_val').val();
      var selected_val = strip_tags(value0);
      var winning_player = (hidden_val=="p1" && selected_val=="X") ? "p1" : ((hidden_val=="p2" && selected_val=="O") ? "p2" : "");

      if(hidden_val == winning_player) {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You won the game');  
      }
      else {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You lose the game');
      }
      update_winning_status(winning_player);
    }
    // 0,4,8
    else if(value0 != '' && value4 != '' && value8 != '' && value0 === value4 && value4 === value8 && value8 === value0) {    
      // alert('0,4,8');
      win = true;
      var hidden_val = $('#player_val').val();z
      var selected_val = strip_tags(value0);
      var winning_player = (hidden_val=="p1" && selected_val=="X") ? "p1" : ((hidden_val=="p2" && selected_val=="O") ? "p2" : "");

      if(hidden_val == winning_player) {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You won the game');  
      }
      else {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You lose the game');
      }
      update_winning_status(winning_player);
    }
    // 1,4,7
    else if(value1 != '' && value4 != '' && value7 != '' && value1 === value4 && value4 === value7 && value7 === value1) {    
      // alert('1,4,7');
      win = true;
      var hidden_val = $('#player_val').val();
      var selected_val = strip_tags(value1);
      var winning_player = (hidden_val=="p1" && selected_val=="X") ? "p1" : ((hidden_val=="p2" && selected_val=="O") ? "p2" : "");

      if(hidden_val == winning_player) {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You won the game');  
      }
      else {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You lose the game');
      }
      update_winning_status(winning_player);
    }
    // 2,5,8
    else if(value2 != '' && value5 != '' && value8 != '' && value2 === value5 && value5 === value8 && value8 === value2) {    
      // alert('2,5,8');
      win = true;
      var hidden_val = $('#player_val').val();
      var selected_val = strip_tags(value2);
      var winning_player = (hidden_val=="p1" && selected_val=="X") ? "p1" : ((hidden_val=="p2" && selected_val=="O") ? "p2" : "");

      if(hidden_val == winning_player) {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You won the game');  
      }
      else {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You lose the game');
      }
      update_winning_status(winning_player);
    }
    // 2,4,6
    else if(value2 != '' && value4 != '' && value6 != '' && value2 === value4 && value4 === value6 && value6 === value2) {    
      // alert('2,4,6');
      win = true;
      var hidden_val = $('#player_val').val();
      var selected_val = strip_tags(value2);
      var winning_player = (hidden_val=="p1" && selected_val=="X") ? "p1" : ((hidden_val=="p2" && selected_val=="O") ? "p2" : "");

      if(hidden_val == winning_player) {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You won the game');  
      }
      else {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You lose the game');
      }
      update_winning_status(winning_player);
    }
    // 3,4,5
    else if(value3 != '' && value4 != '' && value5 != '' && value3 === value4 && value4 === value5 && value5 === value3) {    
      // alert('3,4,5');
      win = true;
      var hidden_val = $('#player_val').val();
      var selected_val = strip_tags(value3);
      var winning_player = (hidden_val=="p1" && selected_val=="X") ? "p1" : ((hidden_val=="p2" && selected_val=="O") ? "p2" : "");

      if(hidden_val == winning_player) {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You won the game');  
      }
      else {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You lose the game');
      }
      update_winning_status(winning_player);
    }
    // 6,7,8
    else if(value6 != '' && value7 != '' && value8 != '' && value6 === value7 && value7 === value8 && value8 === value6) {    
      // alert('6,7,8');
      win = true;
      var hidden_val = $('#player_val').val();
      var selected_val = strip_tags(value6);
      var winning_player = (hidden_val=="p1" && selected_val=="X") ? "p1" : ((hidden_val=="p2" && selected_val=="O") ? "p2" : "");

      if(hidden_val == winning_player) {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You won the game');  
      }
      else {
        $('#game_status_val').val('3');
        $('#game_status').html('Game finished. You lose the game');
      }
      update_winning_status(winning_player);
    }
    else if(value0 != '' && value1 != '' && value2 != '' && value3 != '' && value4 != '' && value5 != '' && value6 != '' && value7 != '' && value8 != ''){
      // alert('die');
      win = true;
      $('#game_status_val').val('3');
      $('#game_status').html('Game tie. Both players played well. Congratulations!');
      update_winning_status("none");
    }
}
  },500);


$('.popupCloseButton').click(function(){
  $('.hover_bkgr_fricc').hide();
});

  // functions ------------------

  function strip_tags(string_val) {
    var string = string_val.toString();
    return string.replace(/<\/?[^>]+>/gi, '');
  }

  // update winning status
  function update_winning_status(player_name) {

    $('#confirmBox_answer').hide();

    $.ajax({

      url : base_url+"game_end",
      type : "POST",
      data : {game_id:game_id,player_name:player_name},
      dataType : "json",
      success : function(res) {
        alert("Game completed");
      }
    });
  }

  // Check the user has the question
  function check_have_questions(user_id,game_id) {

    var return_value = '';

    $.ajax({

      url : base_url+"check_question_status",
      type : "POST",
      async: false,
      data : {user_id:user_id,game_id:game_id},
      dataType : "json",
      cache: false,
      success : function(res) {
                if(res.status == "false") {
                  return_value = false;
                }
                else {
                  return_value = true;
                }
            }
    });

    return return_value;
  }

  // confirm box - question
  function doConfirm_question(yesFn, noFn)
  {
      var confirmBox = $("#confirmBox_question");
      confirmBox.find('.input-group').val('');
      confirmBox.show();
      confirmBox.find(".yes,.no").unbind().click(function()
      {
          confirmBox.hide();
      });
      confirmBox.find(".yes").click(yesFn);
      confirmBox.find(".no").click(noFn);
      confirmBox.show();

      return true;
  }

  // confirm box - answer
  function doConfirm_answer(yesFn, noFn)
  {

      var confirmBox = $("#confirmBox_answer");
      confirmBox.find('.input-group').val('');
      confirmBox.show();
      confirmBox.find(".yes,.no").unbind().click(function()
      {
          confirmBox.hide();
      });
      confirmBox.find(".yes").click(yesFn);
      confirmBox.find(".no").click(noFn);
      confirmBox.show();

      return true;
  }

  var question_raise_status = function() {

  $.ajax({

    url : base_url+"check_question_raise",
    type : "POST",
    data : {game_id:game_id,user_id:current_user_id},
    dataType : "json",
    success : function(res) {

        if(res.status == "true") {
          var  question = res.tictactoe_question;
          var  quiz_id = res.tictactoe_quiz_id;
          $('#ques_label').text(question);
          $('#quiz_id').val(quiz_id);
          
          var confirm = doConfirm_answer(function yes()
          {
            var ans = $('#answer_form').find('#form2_ans').val();
            var quiz_id = $('#answer_form').find('#quiz_id').val();
            update_answer(ans,quiz_id);
          },
          function no()
          {
            //
          });
        }
      }
    });
  };

  setInterval(question_raise_status, 2000);

  // update answer
  function update_answer(answer,quiz_id) {

    $.ajax({

      url : base_url+"update_answer",
      type : "POST",
      data : {quiz_id:quiz_id,answer:answer},
      dataType : "json",
      success : function(res) {
        // alert("Answer updated");
      }
    });
  }

  var answer_update_status = function() {

  $.ajax({

    url : base_url+"answer_update_status",
    type : "POST",
    data : {game_id:game_id,user_id:opponent_user_id},
    dataType : "json",
    success : function(res) {

        if(res.status == "true") {

          var  question = res.tictactoe_question;
          var  answer = res.tictactoe_answer;
          var  quiz_id = res.tictactoe_quiz_id;

          $('#validate_ques_label').text(question);
          $('#validate_ans_label').text(answer);
          
          var confirm = doConfirm_validate(function yes()
          {
            update_answer_validation("correct",quiz_id);
          },
          function no()
          {
            update_answer_validation("wrong",quiz_id);
          });
        }
      }
    });
  };

  setInterval(answer_update_status, 2000);  

  // confirm box - validate
  function doConfirm_validate(yesFn, noFn)
  {
      var confirmBox = $("#confirmBox_validate");
      confirmBox.show();
      confirmBox.find(".yes,.no").unbind().click(function()
      {
          confirmBox.hide();
      });
      confirmBox.find(".yes").click(yesFn);
      confirmBox.find(".no").click(noFn);
      confirmBox.show();

      return true;
  }

  // update answer validation
  function update_answer_validation(status_val,quiz_id) {

    $.ajax({

      url : base_url+"update_answer_validation",
      type : "POST",
      async : false,
      data : {quiz_id:quiz_id,status_val:status_val},
      dataType : "json",
      cache : false,
      success : function(res) {
        // alert("validation updated");
        if(status_val == "wrong") {
          raise_multiple_question();
        }
      }
    });
  }

 var answer_validate_status = function() {

  $.ajax({

    url : base_url+"answer_validate_status",
    type : "POST",
    async : false,
    data : {game_id:game_id,user_id:current_user_id},
    dataType : "json",
    cache : false,
    success : function(res) {

        if(res.status == "true") {
          if(res.answer_correct == 1) {
            alert("Your answer is correct. Now you can continue with the game");
          }
          else {
            alert("Your answer is wrong. Please wait for next question");
          }
        }
      }
    });
  };

  setInterval(answer_validate_status, 2000);



  function raise_multiple_question() {

    var confirm = doConfirm_question(function yes()
    {
      var ques = $('#question_form').find('#form1_ques').val();
      var ans = $('#question_form').find('#form1_ans').val();
      $.ajax({
          url : base_url+"raise_multiple_question",
          async : false,
          type : "POST",
          data : {game_id:game_id,user_id:opponent_user_id,ques:ques,ans:ans},
          dataType : "json",
          cache:false,
          success : function(res) {

          }
      });
    },
    function no()
    {
      var ques = '';
      var ans = '';
      $.ajax({

        url : base_url+"raise_multiple_question",
        async : false,
        type : "POST",
        data : {game_id:game_id,sec:sec,player:hidden_val,user_id:opponent_user_id,ques:ques,ans:ans},
        dataType : "json",
        cache:false,
        success : function(res) {

            
        }
      });
    });
  }


   
         
  







});// End of read the ready function.