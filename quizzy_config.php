<?php
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
  
  // the dimensions of the quiz in pixels
  $quizzy_quiz_width = 200;
  $quizzy_quiz_height = 300;
  
  // The message to display above the list of quiz names that the user would select
  // this is put in an h1 tag
  $quizzy_pick_quiz_message = 'Please Select a Quiz';
  
  // The message displayed at the end of the quiz before the user's score, grade, and rank
  // this is put in an h1 tag
  $quizzy_end_quiz_message = 'Done!';

  // Keeps track of the current working directory.
  // This bit here will prevent breakage on windows by replacing '\' characters with '/' characters
  $quizzy_cwd = str_replace('\\', '/', getcwd());
  
  /**
   *  JavaScript-Only Global Variables
   *  
   *  This PHP array contains all of the variables that are needed by the JavaScript side of
   *  quizzy. It is kept here for ease of configuration and is passed to jQuery via JSON encoding.
   */
  $quizzy_js_variables = array(
  
    // how fast fading animations should be completed (in ms)
    'quizzyFadeSpeed' => 'def',

    // how fast sliding animations should be completed (in ms)
    'quizzySlideSpeed' => 'def',

    // how to animate movement. can be linear or swing
    'quizzyAnimateStyle' => 'swing',

    // how long to wait in ms before scrolling up non-100% options
    'quizzySlideUpWait' => 500,

    // how long to wait in ms before fading in the explanation
    'quizzyExpFadeInWait' => 200,

    // how long to wait in ms before fading in the next button
    'quizzyNextFadeInWait' => 500,

    // time in ms it takes for the quiz to restart
    'quizzyRestartSpeed' => 500,


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
    'quizzy_LoadingPulse' => 'ellipsis',

    // where to put the loading message. in format of '[vertical align]-[horizontal align]' unless center center then it's just 'center'
    // vertical line can be 'top', 'center', or 'bottom', horizontal align can be 'left', 'center', or 'right'
    // so if you want it in the top left, you set loadingAlign to 'top-left'.
    'quizzyLoadingAlign' => 'bottom-left',

    // how long to wait before putting the loading message up in milliseconds.
    // This setting will not do anything as of this version because the delay feature of loading is broken.
    'quizzyLoadingDelay' => 300,
    
    // The width and height of the quiz window. These are set above so their PHP variable values are simply used here.
    // You shouldn't change this.
    'quizzyQuizWidth' => $quiz_width,
    'quizzyQuizHeight' => $quiz_height,
    
  ); // js_variables
  
  /**
   *  quizzy header
   *
   *  Below is the HTML that needs to be added to the header of the HTML file in order for quizzy to operate
   *  correctly. Instead of breaking this out into a separate file, it is added here to minimize the number of
   *  files needed by quizzy.
   *
   *  Don't modify anything below unless you know what you're doing.
   */
?>

<script type="text/javascript" src="quizzy/lib/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="quizzy/lib/jquery.loading.min.js"></script>
<script type="text/javascript" src="quizzy/quizzy.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="quizzy/quizzy.css" charset="utf-8">
<link rel="stylesheet" type="text/css" href="quizzy/quizzy_skin.css" charset="utf-8">
<!-- overflow:hidden in IE is currently breaking mask calcs :( -->
<!--[if IE]>
  <style type="text/css">
    .loading-masked { overflow: visible; }
    /*required to make overflow hidden for quizzy in ie 6/7*/
    #quizzy {position:relative;}
  </style>
<![endif]-->
<!--[if lt IE 7]>
  <style type="text/css">
    /*Max width and height are not suppored by ie 6 so for those browsers, we're just going
      to set the images' width and height to the max percentages.
    */
    #quizzy_load img{ width: 30%; }
    .quizzy_q_body img {width: 45%; }
    .quizzy_q_opts img {height: 12pt;}
    .quizzy_q_exp img {width: 45%; height: auto;}
    .quizzy_done img {height: 15%;}
  </style>
<![endif]-->