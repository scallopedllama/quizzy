<?php
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


  /**
   *  PHP global variables
   *
   *  Change these variables to customize quizzy to be exactly what you want.
   */

  // where the quiz xml files are stored under the /quizzy directory
  // default is /quizzy/quizzes so by default the variable is set to 'quizzes'
  $quizzy_quiz_folder = 'quizzes';

  // where the quiz picture files are stored under /quizzy/$quizFolder
  // by default, it looks in the same folder as the quizzes. this might get
  // messy with a large number of quizzes so the option to move it out is given here
  $quizzy_pic_folder = '.';

  // Whether or not strings obtained from the quiz should be converted to using html entities
  // Set to FALSE if you want to add HTML tags to your quiz to modify the text (for example adding
  // &lt;b&gt;...&lt;/b&gt; to bold some text). If you do this, to add an html entity, you must
  // escape it twice. So to add a '<' character to a question, you'd need to put &amp;lt; to your quiz.
  $quizzy_html_entities = TRUE;

  // Whether or not the user should get a 'check answers' but or just a 'next' button.
  // If set to FALSE, the user will enter their answer, click next, and they will never be told
  // whether or not they answered that one question correctly.
  $quizzy_show_answer = TRUE;

  // the dimensions of the quiz in pixels
  $quizzy_quiz_width = 280;
  $quizzy_quiz_height = 400;

  // The message to display above the list of quiz names that the user would select
  // this is put in an h1 tag
  $quizzy_pick_quiz_message = 'Please Select a Quiz';

  // The message displayed at the end of the quiz before the user's score, grade, and rank
  // this is put in an h1 tag
  $quizzy_end_quiz_message = 'Done!';

  // Determines how strict number questions should be. Quizzy will accept an
  // answer that is +- this value so if the answer is 1.00, it would accept 0.95 < answer < 1.05.
  $quizzy_number_strictness = 0.05;

  // For input-type questions with a text-type answer, the answer provided in the XML file and the response
  // provided by the user are first made lowercase then stripped of various characters before being compared.
  // This is an array of all the characters that should be removed before doing the comparison.
  $quizzy_strip_characters = array(' ', '\t',
                                   ',', '<', '.', '>', '/', '?', ';', ':', '\'', '\"', '[', '{', ']', '}', '\\', '|',
                                   '`', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+', '=');


  /**
   *  JavaScript-Only Global Variables
   *
   *  This PHP array contains all of the variables that are needed by the JavaScript side of
   *  quizzy. It is kept here for ease of configuration and is passed to jQuery via JSON encoding.
   */
  $quizzy_js_variables = array(

    // how fast fading animations should be completed (in ms)
    'fadeSpeed' => 'def',

    // how fast sliding animations should be completed (in ms)
    'slideSpeed' => 'def',

    // how to animate movement. can be linear or swing
    'animateStyle' => 'swing',

    // how long to wait in ms before scrolling up non-100% options
    'slideUpWait' => 500,

    // how long to wait in ms before fading in the explanation
    'expFadeInWait' => 200,

    // how long to wait in ms before fading in the next button
    'nextFadeInWait' => 500,

    // time in ms it takes for the quiz to restart
    'restartSpeed' => 500,


    // SETTINGS for jQuery Loading

    // The pulse animation to use. Can be one of the following:
    // 'working error'		-- displays 'Loading...' for 10 seconds, then changes to 'Still Working...' for 100 seconds,
    // 										 and changes to 'Task may have failed'. All messages are static
    // 'error' 				 	-- displays 'Loading...' for 100 seconds, then changes to 'Task may have failed'. All messages are static
    // 'type' 						-- "types" the text 'Loading...', so it displays 'L', then 'Lo', then 'Loa', then 'Load', etc.
    // 'ellipsis'				-- "types" the epllipsis after 'Loading', so it displays 'Loading', then 'Loading.', then 'Loading..', etc
    // 'fade'						-- displays 'Loading...' and fades the div in and out
    // 'fade error'			-- displays 'Loading...' and fades the div in and out for 100 seconds and changes the message to a static 'Task may have failed.'
    // 'working type' 		-- "types" 'Loading...' for 10 seconds then changes to "type" 'Still Working...'
    //  note that these can generally be combined to produce the desired effect like how 'working type' is a combination of 'working' and 'type'
    'loadingPulse' => 'ellipsis',

    // where to put the loading message. in format of '[vertical align]-[horizontal align]' unless center center then it's just 'center'
    // vertical line can be 'top', 'center', or 'bottom', horizontal align can be 'left', 'center', or 'right'
    // so if you want it in the top left, you set loadingAlign to 'top-left'.
    'loadingAlign' => 'bottom-left',

    // how long to wait before putting the loading message up in milliseconds.
    // This setting will not do anything as of this version because the delay feature of loading is broken.
    'loadingDelay' => 300,

    // The width and height of the quiz window. These are set above so their PHP variable values are simply used here.
    // You shouldn't change this.
    'quizWidth' => $quizzy_quiz_width,
    'quizHeight' => $quizzy_quiz_height,

    // Should the answers be shown or not
    'showAnswer' => $quizzy_show_answer,

  ); // js_variables

?>