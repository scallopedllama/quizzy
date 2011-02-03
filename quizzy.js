/*
 * This file is part of quizzy.
 *
 * quizzy is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * quizzy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with quizzy. If not, see <http://www.gnu.org/licenses/>.
 */

// current quiz state
quizzyState = new Object;
quizzyState.quizFile = "";
quizzyState.quizIndex = -1;
quizzyState.currentQuestion = -1;
quizzyState.score = 0;

// this is set by jquery when the user clicks on one of the radio buttons
quizzyState.selectedOption = 0;
// these are set when the explantion data is dropped into its div
quizzyState.correctOption = -1;
quizzyState.addScore = 0;
quizzyState.bestScore = 0;
quizzyState.optionValues;
// these are set at other times by dropped in php code from ajax calls
quizzyState.numQuestions = -1;
quizzyState.quizWidth = -1;
// NOTE: This variable isn't used in here anywhere.
quizzyState.height = -1;

/**
 * This function is called when the page is fully loaded and ready to run javaScript.
 * It initializes the loading plugin, resets the quizzy state,
 * and finishes loading up quizzy.
 *
 * @author Joe Balough
 */
$(document).ready(function() {
  // Get the JavaScript variables via JSON from quizzy.php
  $.getJSON('quizzy/quizzy.php', {quizzy_op: 'config'}, function (data) {
    // Merge the data object into the quizzyState object
    for (var key in data) {
      quizzyState[key] = data[key];
    }

    $.loading.pulse = quizzyState.loadingPulse;
    $.loading.align = quizzyState.loadingAlign;
    // $.loading.delay = quizzyState.loadingDelay;
    $.loading.onAjax = true;  // don't change this!
  });

  // hide the quiz descriptions, uncheck all of the options
  $('.quizzy_quiz_desc').hide();
  $('.quizzy_quiz_opt').attr('checked', false);

  // add another click event handler to the radio buttons
  $('.quizzy_quiz_opt').click(function() {
    // the user clicked on one of the options
    // get the id
    var thisId = $(this).attr('id');

    // hack out the index and set quizzyState.selectedOption to it
    var selQuiz = thisId.substring(thisId.lastIndexOf("opt") + 3) * 1;

    // Slide the explanation for the selected quiz down.
    // If there is more than one quiz in the list, slide all the others up first
    if ($('.quizzy_quiz_desc[id!=quizzy_quiz_desc' + selQuiz + ']').length > 0) {
      $('.quizzy_quiz_desc[id!=quizzy_quiz_desc' + selQuiz + ']').slideUp(quizzyState.slideSpeed, function() {
        $('#quizzy_quiz_desc' + selQuiz).slideDown(quizzyState.slideSpeed);
      });
    }
    else
      $('#quizzy_quiz_desc' + selQuiz).slideDown(quizzyState.slideSpeed);
  });

  // set the click event on the submit button
  $('#quizzy_start_b').click(startQuiz);

});


/**
 * Event handler for the bbegin quiz button, this function will
 * load the requested quiz using AJAX.
 *
 * @author Joe Balough
 */
function startQuiz()
{
  // make sure that there's a quiz that is selected
  if(quizzyState.quizIndex < 0)
    return;

  // unbind the click events for this button
  $(this).unbind();

  // globals were already set when the user clicked on the radio buttons

  // fade out quiz options
  $('.quizzy_quiz_b').fadeOut(quizzyState.fadeSpeed);

  // put up throbber
  $('#quizzy').loading(true);

  // Request the quiz from quiz.php. That returns a JSON formatted output containing the following variables:
  //   numQuestions  - The number of questions in this quiz
  //   quiz          - The HTML formatted string representing the start of the requested quiz
  $.getJSON('quizzy/quizzy.php', {quizzy_op: 'quiz', quizzy_file: quizzyState.quizFile, quizzy_index: quizzyState.quizIndex}, function(data){
    // put up throbber
    $('#quizzy').loading(true);

    // we got our quiz datas, just dump them into the correct div
    $('#quizzy_quiz').html(data.quiz);
    quizzyState.numQuestions = data.numQuestions;

    // we also got a quizzyState.numQuestions set, need to resize a few divs.
    $('#quizzy_c').width((quizzyState.numQuestions + 3) * quizzyState.quizWidth);
    $('#quizzy_quiz').width((quizzyState.numQuestions + 2) * quizzyState.quizWidth);
    $('.quizzy_title').width(quizzyState.quizWidth);

    // now request the next question
    requestNextQuestion();
  });
}

/**
 * Event handler for the next button at the bottom of the quiz
 * @author Joe Balough
 */
function requestNextQuestion()
{
  $('#quizzy_q' + quizzyState.currentQuestion + '_foot_nxt').fadeOut(quizzyState.fadeSpeed, function() {
    $(this).attr('disabled', true);
  });

  // Request the question data from quizzy.php. It will return an HTML string that represents the question.
  $.get('quizzy/quizzy.php', {quizzy_op: 'question', quizzy_file: quizzyState.quizFile, quizzy_index: quizzyState.quizIndex, quest_no: (quizzyState.currentQuestion + 1), score: quizzyState.score}, function(data){
    // we are now on the next question
    quizzyState.currentQuestion++;

    // dump the recieved data into the correct question div
    $("#quizzy_q" + quizzyState.currentQuestion).html(data);

    // set necessary styles
    $('.quizzy_q').width(quizzyState.quizWidth);

    // hide and disable the check and next buttons, the explanation div, and the value spans
    $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').attr('disabled', true).hide();
    $('#quizzy_q' + quizzyState.currentQuestion + '_foot_nxt').attr('disabled', true).hide();
    $('#quizzy_q' + quizzyState.currentQuestion + '_exp').hide();
    $('.quizzy_q_opt_val').hide();
    // Change the text on the check button to next if the answers aren't to be shown
    if (!quizzyState.showAnswer)
      $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').val('Next');

    // add the click event to the check and next buttons
    $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').click(checkQuestion);
    $('#quizzy_q' + quizzyState.currentQuestion + '_foot_nxt').click(function (){
      $('#quizzy').loading(true);
      $(this).unbind();
      requestNextQuestion();
    });

    // Add a keypress handler to the textbox (if there is one) to make it click 'check' when enter is pressed.
    $('.quizzy_q_txt').keypress(function (event) {
      if (event.keyCode == '13') {
        event.preventDefault();
        $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').click();
      }
    });

    // slide quizzy_c to the right if we're on question 0, quizzy_q_c otherwise
    var scrollSel = (quizzyState.currentQuestion == 0) ? '#quizzy_c' : '#quizzy_q_c';
    var scrollAmt = (quizzyState.currentQuestion == 0) ? (-quizzyState.quizWidth * (quizzyState.currentQuestion + 1)) : (-quizzyState.quizWidth * (quizzyState.currentQuestion));
    $(scrollSel).animate({left: scrollAmt + "px"}, quizzyState.slideSpeed, quizzyState.animateStyle, function(){
      // uncheck the last question's buttons
      $('.quizzy_q_opt_b').attr('checked', false);

      // fade in the proper button based on the value of the showAnswer setting
      $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').attr('disabled', false).fadeIn(quizzyState.fadeSpeed);

      // Focus the text field
      // TODO: or the first button?
      $('#quizzy_q' + quizzyState.currentQuestion + '_txt').focus();
    });
  });
}


/**
 * Event handler for the Check Answer button. Queries quizzy.php for the explanation and score
 * for the current question and quiz.
 * @author Joe Balough
 */
function checkQuestion()
{
  // Get the question type and determine what the user's response was
  var questionType = $('#quizzy_q' + quizzyState.currentQuestion + '_type').val();
  var userResponse = "";
  switch (questionType) {
    default:
    case 'radio':
    case 'checkbox':
      userResponse = $('.quizzy_q' + quizzyState.currentQuestion + '_opt_b:checked').map(function () {return $(this).attr('id');}).get();
      break;
    case 'input':
      userResponse = $('#quizzy_q' + quizzyState.currentQuestion + '_txt').val();
      break;
  }

  // make sure the user provided an answer
  if(userResponse.length == 0 || userResponse == "")
    return;

  // unbind the click event
  $(this).unbind();

  // hide the button
  $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').fadeOut(quizzyState.fadeSpeed, function() {
    $(this).attr('disabled', true);
  });

  // put up throbber
  $('#quizzy').loading(true);

  // get the explanation for this option, it will set the quizzyState.correctOption variable
  // information received in JSON:
  //     optionValues   - An array specifiying how many points each of the options were worth
  //     addScore       - How many points should be added to the score
  //     correctOption  - Which was the best option
  //     explanation    - HTML formatted string representing the explanation text
  //     bestScore      - Which index is the best possible score
  var passingOptions = {
    quizzy_op: 'explanation',
    quizzy_file: quizzyState.quizFile,
    quizzy_index: quizzyState.quizIndex,
    quest_no: quizzyState.currentQuestion,
    response: userResponse,
  };
  $.getJSON('quizzy/quizzy.php', passingOptions , function(data) {
    // Merge the data object into the quizzyState object
    for (var key in data) {
      quizzyState[key] = data[key];
    }
    // add to quizzyState.score
    quizzyState.score += quizzyState.addScore;

    // See if the answers should be shown and if not, request the next question and return
    if (!quizzyState.showAnswer) {
      $('#quizzy').loading(true);
      requestNextQuestion();
      return;
    }

    // have the data returned by that ajax query, set the proper div info
    $('#quizzy_q' + quizzyState.currentQuestion + '_exp').html(data.explanation);

    // Reveal the correct answers
    switch (questionType) {
      default:
      case 'radio':
      case 'checkbox':
        for( i in quizzyState.optionValues ) {

          // if it was best score, use quizzy_opt_best
          // in between best and worst, use quizzy_opt_mid
          // or the worst, use quizzy_opt_worst
          var useClass = 'quizzy_opt_worst';
          if(quizzyState.optionValues[i] == quizzyState.bestScore || quizzyState.optionValues[i] == '✓')
            useClass = 'quizzy_opt_best';
          if(quizzyState.optionValues[i] > 0 && quizzyState.optionValues[i] < quizzyState.bestScore)
            useClass = 'quizzy_opt_mid';

          $('#quizzy_q' + quizzyState.currentQuestion + '_opt' + i + '_val').html('<span class="' + useClass + '">' + quizzyState.optionValues[i] + '</span>');
        }
        $('.quizzy_q_opt_val').fadeIn(quizzyState.fadeSpeed);
        break;

      case 'input':
        // Using same classes as above. Comparing to bestScore
        var useClass = 'quizzy_opt_worst';
        var toPrint = '×';
        if (quizzyState.addScore == quizzyState.bestScore) {
          useClass = 'quizzy_opt_best';
          toPrint = '✓';
        }
        else if (quizzyState.addScore > 0) {
          useClass = 'quizzy_opt_mid';
          toPrint = '+' + quizzyState.addScore;
        }

        // Reveal the user's score.
        $('#quizzy_q' + quizzyState.currentQuestion + '_txt_val').html('<span class="' + useClass + '">' + toPrint + '</span>').hide();
        $('#quizzy_q' + quizzyState.currentQuestion + '_txt').animate({width: '87%'}, quizzyState.slideSpeed, quizzyState.animateStyle, function () {
          $('.quizzy_q_txt_val').fadeIn(quizzyState.fadeSpeed);
        });
    }

    setTimeout(function() {
      // Need to build a bit of a tricky jQuery selector here. Basically, It's going to scroll up ALL the quizzy_q_opt class items
      // Except the correct answers and the one the user chose.
      // Here we add all the correct options
      var correctSel = '';
      for (var i in quizzyState.correctOptions)
        correctSel += '[id!=quizzy_q' + quizzyState.currentQuestion + '_opt' + quizzyState.correctOptions[i] + ']';
      // And here we add all the options the user selected
      var pickedSel = '';
      for (var i in passingOptions.sel_opt)
        pickedSel += '[id!=' + passingOptions.sel_opt[i].substring(0, passingOptions.sel_opt[i].length - 2) + ']';

      $('.quizzy_q_opt' + correctSel + pickedSel).slideUp(quizzyState.slideSpeed);

      // wait expFadeInWait millisec
      setTimeout(function() {

        // fade in explanation
        $('#quizzy_q' + quizzyState.currentQuestion + '_exp').fadeIn(quizzyState.fadeSpeed);

        // wait nextFadeInWait millisec
        setTimeout(function() {

          // fade in next button
          $('#quizzy_q' + quizzyState.currentQuestion + '_foot_nxt').attr('disabled', false).fadeIn(quizzyState.fadeSpeed).focus();

        }, quizzyState.nextFadeInWait);              // to fade in the next button

      }, quizzyState.expFadeInWait); 		             // to fade in explanation

    }, (questionType == 'input') ? 0 : slideUpWait); // to scroll up all but best answer unless the question is of input type

  });
}


/**
 * Event handler for the Restart Quiz button on the last page of any quiz.
 * It resets the state variables and scrolls everything back to the quiz select page.
 * @author Joe Balough
 */
function restartQuizzy()
{
  // figure out how much of the animation is in scrolling the questions back
  var firstRatio = quizzyState.currentQuestion / (quizzyState.currentQuestion + 1);
  // and how much is in scrolling the big container over
  var secondRatio = 1.0 - firstRatio;

  // reset all the state variables
  quizzyState.quizFile = "";
  quizzyState.quizIndex = -1;
  quizzyState.currentQuestion = -1;
  quizzyState.score = 0;
  quizzyState.selectedOption = 0;
  quizzyState.correctOption = -1;
  quizzyState.addScore = 0;

  // unselect any selected quiz
  $('.quizzy_quiz_opt').attr('checked', false);
  // hide all the descriptions
  $('.quizzy_quiz_desc').hide();

  // scroll the quizzy_q_c back to the start
  $('#quizzy_q_c').animate({left: "0px"}, firstRatio * quizzyState.restartSpeed, quizzyState.animateStyle, function(){

    // scroll the quizzy_c back to the start
    $('#quizzy_c').animate({left: "0px"}, secondRatio * quizzyState.restartSpeed, quizzyState.animateStyle, function(){

      // reset the click event on the submit button
      $('#quizzy_start_b').click(startQuiz);

      // fade the quiz select buttons back in
      $('.quizzy_quiz_b').fadeIn(quizzyState.fadeSpeed);

    }); // quizzy_c
  }); // quizzy_q_c
}
