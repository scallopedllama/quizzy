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
quizzyState.file = "";
quizzyState.index = -1;
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
quizzyState.width = -1;
// NOTE: This variable isn't used in here anywhere.
quizzyState.height = -1;

/**
 * This function is called when the page is fully loaded and ready to run javaScript.
 * It initializes the loading plugin, resets the quizzy state,
 * and finishes loading up quizzy.
 */
$(document).ready(function() {
  // Get the JavaScript variables via JSON from quizzy.php
  $.getJSON('quizzy/quizzy.php', {quizzy_op: 'config'}, function (data) {
    // Merge the data object into the quizzyState object
    for (var key in data) {
      quizzyState[key] = data[key];
    }
  });
  
  $.loading.pulse = quizzyState.loadingPulse;
  $.loading.align = quizzyState.loadingAlign;
  // $.loading.delay = quizzyState.loadingDelay;
  $.loading.onAjax = true;	// don't change this!
  
  // reset all the variables
  quizzyState.file = "";
  quizzyState.index = -1;
  quizzyState.currentQuestion = -1;
  quizzyState.score = 0;
  quizzyState.selectedOption = 0;
  quizzyState.correctOption = -1;
  quizzyState.addScore = 0;

  // put up a loading message
  $('#quizzy').loading(true);

  // load the quiz list
  // the buttons have onClick events so they're handled up there
  /*$.get('quizzy/serveQuizzes.php', function(data){
    $('#quizzy_load').html(data);
    
    // hide the descriptions
    $('.quizzy_quiz_desc').hide();
    
    // add a click event to the radio buttons' label
    $('.quizzy_quiz_lbl').click(function () {
      // the user clicked on one of the options
      // get the id
      var thisId = $(this).attr('id');
      
      // hack out the index and set quizzyState.selectedOption to it
      var selQuiz = thisId.substring(thisId.lastIndexOf("lbl") + 3) * 1;
      
      // make sure that the radio button is selected
      $('#quizzy_quiz_opt'+selQuiz).click();
    });
    
    // add another click event handler to the radio buttons
    $('.quizzy_quiz_opt').click(function() {
      // the user clicked on one of the options
      // get the id
      var thisId = $(this).attr('id');
      
      // hack out the index and set quizzyState.selectedOption to it
      var selQuiz = thisId.substring(thisId.lastIndexOf("opt") + 3) * 1;
      
      // slide up all other descriptions while sliding down the correct one
      $('.quizzy_quiz_desc[id!=quizzy_quiz_desc'+selQuiz+']').slideUp(slideSpeed, function() {
        $('#quizzy_quiz_desc' + selQuiz).slideDown(slideSpeed);
      });
    });
    
    // set the click event on the submit button
    $('#quizzy_start_b').click(startQuiz);
  });*/

});

/*
// requests a quiz setup from the server
function startQuiz()
{
  // make sure that there's a quiz that is selected
  if(quizzyState.index < 0)
    return;
    
  // unbind the click events for this button
  $(this).unbind();

  // globals were already set when the user clicked on the radio buttons

  // fade out quiz options
  $('.quizzy_quiz_b').fadeOut(fadeSpeed);

  // put up throbber
  $('#quizzy').loading(true);

  // parameters passed in GET:
  //   _GET['quizzyState.file']       xml file to open
  //   _GET['quizzyState.index']      index of requested quiz in xml file
  $.get('quizzy/serveQuiz.php', {quizzyState.file: quizzyState.file, quizzyState.index: quizzyState.index}, function(data){
    // put up throbber
    $('#quizzy').loading(true);
    
    // we got our quiz datas, just dump them into the correct div
    $('#quizzy_quiz').html(data);
    
    // we also got a quizzyState.numQuestions set, need to resize a few divs.
    $('#quizzy_c').width((quizzyState.numQuestions + 3) * quizzyState.width);
    $('#quizzy_quiz').width((quizzyState.numQuestions + 2) * quizzyState.width);
    $('.quizzy_title').width(quizzyState.width);
    
    // now request the next question
    requestNextQuestion();
  });
}

// requests a question from the server
function requestNextQuestion()
{
  $('#quizzy_q' + quizzyState.currentQuestion + '_foot_nxt').fadeOut(fadeSpeed, function() {
    $(this).attr('disabled', true);
  });

  // parameters passed in GET:
  //   _GET["quizzyState.file"]       xml file to open
  //   _GET["quizzyState.index"]      index of requested quiz in xml file
  //   _GET["questNo"]        question to return [first question is number 0]
  //   _GET['score']          score the player currently has (needed for serving last page)
  $.get('quizzy/serveQuestion.php', {quizzyState.file: quizzyState.file, quizzyState.index: quizzyState.index, questNo: (quizzyState.currentQuestion + 1), score: quizzyState.score}, function(data){
    // we are now on the next question
    quizzyState.currentQuestion++;
    
    // set necessary styles
    $('.quizzy_q').width(quizzyState.width);
    
    // dump the recieved data into the correct question div
    $("#quizzy_q" + quizzyState.currentQuestion).html(data);

    // hide and disable the check and next buttons, the explanation div, and the value spans
    $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').attr('disabled', true).hide();
    $('#quizzy_q' + quizzyState.currentQuestion + '_foot_nxt').attr('disabled', true).hide();
    $('#quizzy_q' + quizzyState.currentQuestion + '_exp').hide();
    $('.quizzy_q_opt_val').hide();
    
    // add click handlers so that when a user clicks on any first option, it sets quizzyState.selectedOption to 0
    // and if they click on any 2nd option, it sets quizzyState.selectedOption to 1, etc.
    $('.quizzy_q_opt').click(function (){
      // the user clicked on one of the options
      // get the id
      var thisId = $(this).attr('id');
      
      // hack out the index and set quizzyState.selectedOption to it
      quizzyState.selectedOption = thisId.substring(thisId.lastIndexOf("opt") + 3) * 1;
      
      // make sure that the radio button is selected
      $('#quizzy_q'+quizzyState.currentQuestion+'_opt'+quizzyState.selectedOption+'_b').attr("checked", "checked");
    });
    
    // add the click event to the check and next buttons
    $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').click(checkQuestion);
    $('#quizzy_q' + quizzyState.currentQuestion + '_foot_nxt').click(function (){
      $('#quizzy').loading(true);   
      $(this).unbind();
      requestNextQuestion();
    });
    
    // slide quizzy_c to the right if we're on question 0, quizzy_q_c otherwise
    var scrollSel = (quizzyState.currentQuestion == 0) ? '#quizzy_c' : '#quizzy_q_c';
    var scrollAmt = (quizzyState.currentQuestion == 0) ? (-quizzyState.width * (quizzyState.currentQuestion + 1)) : (-quizzyState.width * (quizzyState.currentQuestion));
    $(scrollSel).animate({left: scrollAmt + "px"}, slideSpeed, animateStyle, function(){
      // uncheck the last question's buttons
      $('.quizzy_q_opt_b').attr('checked', false);
      
      // fade in the check button
      $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').attr('disabled', false).fadeIn(fadeSpeed);
    });
  });
}

function checkQuestion()
{ 
  // the user has quizzyState.selectedOption selected on question quizzyState.currentQuestion
  // on the quizzyState.index'th quiz in quizzyState.file

  // make sure the user selected one
  if( $('.quizzy_q_opt_b:checked').length == 0 )
    return;

  // unbind the click event
  $(this).unbind();

  // hide the button
  $('#quizzy_q' + quizzyState.currentQuestion + '_foot_chk').fadeOut(fadeSpeed, function() {
    $(this).attr('disabled', true);
  });

  // put up throbber
  $('#quizzy').loading(true);

  // get the explanation for this option, it will set the quizzyState.correctOption variable
  // parameters passed in GET:
  //   _GET['quizzyState.file']       xml file to open
  //   _GET['quizzyState.index']      index of requested quiz in xml file
  //   _GET['questNo']        question to return (first is 1)
  // 	_GET['']				 the option for which to retrieve the explanation
  $.get('quizzy/serveExplanation.php',  {quizzyState.file: quizzyState.file, quizzyState.index: quizzyState.index, questNo: quizzyState.currentQuestion, sel_opt: quizzyState.selectedOption}, function(data) {
    
    // have the data returned by that ajax query, set the proper div info
    $('#quizzy_q' + quizzyState.currentQuestion + '_exp').html(data);
    // that should have set the quizzyState.correctOption and add variables
    
    // add to quizzyState.score
    quizzyState.score += quizzyState.addScore;
    
    // determine if this question has partial credit
    var partialCredit = false;
    for(var i in quizzyState.optionValues)
      if(quizzyState.optionValues[i] != 0 && quizzyState.optionValues[i] != quizzyState.bestScore)
        partialCredit = true;
      
    // show the values
    for( i in quizzyState.optionValues ) {
      
      // if the question no partial credit, use an X or a ✓ to indicate correctness
      var toWrite = quizzyState.optionValues[i];
      if(!partialCredit)
        toWrite = (quizzyState.optionValues[i] == quizzyState.bestScore) ? '✓' : 'X';
      
      // if it was best score, use quizzy_opt_best
      // in between best and worst, use quizzy_opt_mid
      // or the worst, use quizzy_opt_worst
      var useClass = 'quizzy_opt_worst';
      if(quizzyState.optionValues[i] == quizzyState.bestScore)
        useClass = 'quizzy_opt_best';
      if(quizzyState.optionValues[i] > 0 && quizzyState.optionValues[i] < quizzyState.bestScore)
        useClass = 'quizzy_opt_mid';
      
      $('#quizzy_q' + quizzyState.currentQuestion + '_opt' + i + '_val').html('<span class="' + useClass + '">' + toWrite + '</span>');
    }
    $('.quizzy_q_opt_val').fadeIn(fadeSpeed);
    
    
    // wait slideUpWait millisec
    setTimeout(function() {
      // scroll up all but the selected answer and the best answer
      var correctSel = '[id!=quizzy_q' + quizzyState.currentQuestion + '_opt' + quizzyState.correctOption + ']';
      var pickedSel = '[id!=quizzy_q' + quizzyState.currentQuestion + '_opt' + quizzyState.selectedOption + ']';
      if(quizzyState.addScore == quizzyState.bestScore)
        correctSel = '';
      $('.quizzy_q_opt' + correctSel + pickedSel).slideUp(slideSpeed);
      
      // wait expFadeInWait millisec
      setTimeout(function() {
        
        // fade in explanation
        $('#quizzy_q' + quizzyState.currentQuestion + '_exp').fadeIn(fadeSpeed);
        
        // wait nextFadeInWait millisec
        setTimeout(function() {
          
          // fade in next button
          $('#quizzy_q' + quizzyState.currentQuestion + '_foot_nxt').attr('disabled', false).fadeIn(fadeSpeed);
          
        }, nextFadeInWait); // wait nextFadeInWait ms to fade in the next button
        
      }, expFadeInWait); 		// wait expFadeInWait ms to fade in explanation
      
    }, slideUpWait); 			// wait scrollupwait ms to scroll up all but best answer
    
  });
}

function restartQuizzy()
{
  // figure out how much of the animation is in scrolling the questions back
  var firstRatio = quizzyState.currentQuestion / (quizzyState.currentQuestion + 1);
  // and how much is in scrolling the big container over
  var secondRatio = 1.0 - firstRatio;

  // reset all the state variables
  quizzyState.file = "";
  quizzyState.index = -1;
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
  $('#quizzy_q_c').animate({left: "0px"}, firstRatio * restartSpeed, animateStyle, function(){
    
    // scroll the quizzy_c back to the start
    $('#quizzy_c').animate({left: "0px"}, secondRatio * restartSpeed, animateStyle, function(){

      // reset the click event on the submit button
      $('#quizzy_start_b').click(startQuiz);
      
      // fade the quiz select buttons back in
      $('.quizzy_quiz_b').fadeIn(fadeSpeed);
      
    }); // quizzy_c
  }); // quizzy_q_c
}
*/