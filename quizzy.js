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
var quizzyFile = "";
var quizzyIndex = -1;
var quizzyCurrentQuestion = -1;
var quizzyScore = 0;

// this is set by jquery when the user clicks on one of the radio buttons
var quizzySelectedOption = 0;
// these are set when the explantion data is dropped into its div
var quizzyCorrectOption = -1;
var quizzyAddScore = 0;
var quizzyBestScore = 0;
var quizzyOptionValues;
// these are set at other times by dropped in php code from ajax calls
var quizzyNumQuestions = -1;
var quizzyWidth = -1;
// NOTE: This variable isn't used in here anywhere.
var quizzyHeight = -1;

// When the document is read, start loading up the quiz
$(document).ready(function() {
  $.loading.pulse = loadingPulse;
  $.loading.align = loadingAlign;
  $.loading.onAjax = true;	// don't change this!
  // $.loading.delay = loadingDelay;
  // reset all the variables
  quizzyFile = "";
  quizzyIndex = -1;
  quizzyCurrentQuestion = -1;
  quizzyScore = 0;
  quizzySelectedOption = 0;
  quizzyCorrectOption = -1;
  quizzyAddScore = 0;

  // put up a loading message
  $('#quizzy').loading(true);

  // load the quiz list
  // the buttons have onClick events so they're handled up there
  $.get('quizzy/serveQuizzes.php', function(data){
    $('#quizzy_load').html(data);
    
    // hide the descriptions
    $('.quizzy_quiz_desc').hide();
    
    // add a click event to the radio buttons' label
    $('.quizzy_quiz_lbl').click(function () {
      // the user clicked on one of the options
      // get the id
      var thisId = $(this).attr('id');
      
      // hack out the index and set quizzySelectedOption to it
      var selQuiz = thisId.substring(thisId.lastIndexOf("lbl") + 3) * 1;
      
      // make sure that the radio button is selected
      $('#quizzy_quiz_opt'+selQuiz).click();
    });
    
    // add another click event handler to the radio buttons
    $('.quizzy_quiz_opt').click(function() {
      // the user clicked on one of the options
      // get the id
      var thisId = $(this).attr('id');
      
      // hack out the index and set quizzySelectedOption to it
      var selQuiz = thisId.substring(thisId.lastIndexOf("opt") + 3) * 1;
      
      // slide up all other descriptions while sliding down the correct one
      $('.quizzy_quiz_desc[id!=quizzy_quiz_desc'+selQuiz+']').slideUp(slideSpeed, function() {
        $('#quizzy_quiz_desc' + selQuiz).slideDown(slideSpeed);
      });
    });
    
    // set the click event on the submit button
    $('#quizzy_start_b').click(startQuiz);
  });

});

// requests a quiz setup from the server
function startQuiz()
{
  // make sure that there's a quiz that is selected
  if(quizzyIndex < 0)
    return;
    
  // unbind the click events for this button
  $(this).unbind();

  // globals were already set when the user clicked on the radio buttons

  // fade out quiz options
  $('.quizzy_quiz_b').fadeOut(fadeSpeed);

  // put up throbber
  $('#quizzy').loading(true);

  // parameters passed in GET:
  //   _GET['quizzyFile']       xml file to open
  //   _GET['quizzyIndex']      index of requested quiz in xml file
  $.get('quizzy/serveQuiz.php', {quizzyFile: quizzyFile, quizzyIndex: quizzyIndex}, function(data){
    // put up throbber
    $('#quizzy').loading(true);
    
    // we got our quiz datas, just dump them into the correct div
    $('#quizzy_quiz').html(data);
    
    // we also got a quizzyNumQuestions set, need to resize a few divs.
    $('#quizzy_c').width((quizzyNumQuestions + 3) * quizzyWidth);
    $('#quizzy_quiz').width((quizzyNumQuestions + 2) * quizzyWidth);
    $('.quizzy_title').width(quizzyWidth);
    
    // now request the next question
    requestNextQuestion();
  });
}

// requests a question from the server
function requestNextQuestion()
{
  $('#quizzy_q' + quizzyCurrentQuestion + '_foot_nxt').fadeOut(fadeSpeed, function() {
    $(this).attr('disabled', true);
  });

  // parameters passed in GET:
  //   _GET["quizzyFile"]       xml file to open
  //   _GET["quizzyIndex"]      index of requested quiz in xml file
  //   _GET["questNo"]        question to return [first question is number 0]
  //   _GET['score']          score the player currently has (needed for serving last page)
  $.get('quizzy/serveQuestion.php', {quizzyFile: quizzyFile, quizzyIndex: quizzyIndex, questNo: (quizzyCurrentQuestion + 1), score: quizzyScore}, function(data){
    // we are now on the next question
    quizzyCurrentQuestion++;
    
    // set necessary styles
    $('.quizzy_q').width(quizzyWidth);
    
    // dump the recieved data into the correct question div
    $("#quizzy_q" + quizzyCurrentQuestion).html(data);

    // hide and disable the check and next buttons, the explanation div, and the value spans
    $('#quizzy_q' + quizzyCurrentQuestion + '_foot_chk').attr('disabled', true).hide();
    $('#quizzy_q' + quizzyCurrentQuestion + '_foot_nxt').attr('disabled', true).hide();
    $('#quizzy_q' + quizzyCurrentQuestion + '_exp').hide();
    $('.quizzy_q_opt_val').hide();
    
    // add click handlers so that when a user clicks on any first option, it sets quizzySelectedOption to 0
    // and if they click on any 2nd option, it sets quizzySelectedOption to 1, etc.
    $('.quizzy_q_opt').click(function (){
      // the user clicked on one of the options
      // get the id
      var thisId = $(this).attr('id');
      
      // hack out the index and set quizzySelectedOption to it
      quizzySelectedOption = thisId.substring(thisId.lastIndexOf("opt") + 3) * 1;
      
      // make sure that the radio button is selected
      $('#quizzy_q'+quizzyCurrentQuestion+'_opt'+quizzySelectedOption+'_b').attr("checked", "checked");
    });
    
    // add the click event to the check and next buttons
    $('#quizzy_q' + quizzyCurrentQuestion + '_foot_chk').click(checkQuestion);
    $('#quizzy_q' + quizzyCurrentQuestion + '_foot_nxt').click(function (){
      $('#quizzy').loading(true);   
      $(this).unbind();
      requestNextQuestion();
    });
    
    // slide quizzy_c to the right if we're on question 0, quizzy_q_c otherwise
    var scrollSel = (quizzyCurrentQuestion == 0) ? '#quizzy_c' : '#quizzy_q_c';
    var scrollAmt = (quizzyCurrentQuestion == 0) ? (-quizzyWidth * (quizzyCurrentQuestion + 1)) : (-quizzyWidth * (quizzyCurrentQuestion));
    $(scrollSel).animate({left: scrollAmt + "px"}, slideSpeed, animateStyle, function(){
      // uncheck the last question's buttons
      $('.quizzy_q_opt_b').attr('checked', false);
      
      // fade in the check button
      $('#quizzy_q' + quizzyCurrentQuestion + '_foot_chk').attr('disabled', false).fadeIn(fadeSpeed);
    });
  });
}

function checkQuestion()
{ 
  // the user has quizzySelectedOption selected on question quizzyCurrentQuestion
  // on the quizzyIndex'th quiz in quizzyFile

  // make sure the user selected one
  if( $('.quizzy_q_opt_b:checked').length == 0 )
    return;

  // unbind the click event
  $(this).unbind();

  // hide the button
  $('#quizzy_q' + quizzyCurrentQuestion + '_foot_chk').fadeOut(fadeSpeed, function() {
    $(this).attr('disabled', true);
  });

  // put up throbber
  $('#quizzy').loading(true);

  // get the explanation for this option, it will set the quizzyCorrectOption variable
  // parameters passed in GET:
  //   _GET['quizzyFile']       xml file to open
  //   _GET['quizzyIndex']      index of requested quiz in xml file
  //   _GET['questNo']        question to return (first is 1)
  // 	_GET['']				 the option for which to retrieve the explanation
  $.get('quizzy/serveExplanation.php',  {quizzyFile: quizzyFile, quizzyIndex: quizzyIndex, questNo: quizzyCurrentQuestion, sel_opt: quizzySelectedOption}, function(data) {
    
    // have the data returned by that ajax query, set the proper div info
    $('#quizzy_q' + quizzyCurrentQuestion + '_exp').html(data);
    // that should have set the quizzyCorrectOption and add variables
    
    // add to quizzyScore
    quizzyScore += quizzyAddScore;
    
    // determine if this question has partial credit
    var partialCredit = false;
    for(var i in quizzyOptionValues)
      if(quizzyOptionValues[i] != 0 && quizzyOptionValues[i] != quizzyBestScore)
        partialCredit = true;
      
    // show the values
    for( i in quizzyOptionValues ) {
      
      // if the question no partial credit, use an X or a ✓ to indicate correctness
      var toWrite = quizzyOptionValues[i];
      if(!partialCredit)
        toWrite = (quizzyOptionValues[i] == quizzyBestScore) ? '✓' : 'X';
      
      // if it was best score, use quizzy_opt_best
      // in between best and worst, use quizzy_opt_mid
      // or the worst, use quizzy_opt_worst
      var useClass = 'quizzy_opt_worst';
      if(quizzyOptionValues[i] == quizzyBestScore)
        useClass = 'quizzy_opt_best';
      if(quizzyOptionValues[i] > 0 && quizzyOptionValues[i] < quizzyBestScore)
        useClass = 'quizzy_opt_mid';
      
      $('#quizzy_q' + quizzyCurrentQuestion + '_opt' + i + '_val').html('<span class="' + useClass + '">' + toWrite + '</span>');
    }
    $('.quizzy_q_opt_val').fadeIn(fadeSpeed);
    
    
    // wait slideUpWait millisec
    setTimeout(function() {
      // scroll up all but the selected answer and the best answer
      var correctSel = '[id!=quizzy_q' + quizzyCurrentQuestion + '_opt' + quizzyCorrectOption + ']';
      var pickedSel = '[id!=quizzy_q' + quizzyCurrentQuestion + '_opt' + quizzySelectedOption + ']';
      if(quizzyAddScore == quizzyBestScore)
        correctSel = '';
      $('.quizzy_q_opt' + correctSel + pickedSel).slideUp(slideSpeed);
      
      // wait expFadeInWait millisec
      setTimeout(function() {
        
        // fade in explanation
        $('#quizzy_q' + quizzyCurrentQuestion + '_exp').fadeIn(fadeSpeed);
        
        // wait nextFadeInWait millisec
        setTimeout(function() {
          
          // fade in next button
          $('#quizzy_q' + quizzyCurrentQuestion + '_foot_nxt').attr('disabled', false).fadeIn(fadeSpeed);
          
        }, nextFadeInWait); // wait nextFadeInWait ms to fade in the next button
        
      }, expFadeInWait); 		// wait expFadeInWait ms to fade in explanation
      
    }, slideUpWait); 			// wait scrollupwait ms to scroll up all but best answer
    
  });
}

function restartQuizzy()
{
  // figure out how much of the animation is in scrolling the questions back
  var firstRatio = quizzyCurrentQuestion / (quizzyCurrentQuestion + 1);
  // and how much is in scrolling the big container over
  var secondRatio = 1.0 - firstRatio;

  // reset all the state variables
  quizzyFile = "";
  quizzyIndex = -1;
  quizzyCurrentQuestion = -1;
  quizzyScore = 0;
  quizzySelectedOption = 0;
  quizzyCorrectOption = -1;
  quizzyAddScore = 0;

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
