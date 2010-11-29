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
   
  // Keeps track of the current working directory.
  // This bit here will prevent breakage on windows by replacing '\' characters with '/' characters
  $quizzy_cwd = str_replace('\\', '/', dirname ( realpath (__FILE__) ) );
  
  // Include the config
  include_once $quizzy_cwd . '/quizzy_config.php';
  
  // This string represents where this quiz's pictures should be found
  $quizzy_pic_dir = 'quizzy/' . $quizzy_quiz_folder . '/' . $quizzy_pic_folder . '/';
  
  
  /**
   * Default behavior: Just add the quizzy container and available quizzes
   * @author Joe Balough
   */
  
  if (!isset($_GET['quizzy_op']) || empty($_GET['quizzy_op'])) {
    // Wrapper for everything in the quiz (overflow: hidden)
    $output = '<div id="quizzy" style="width: ' . $quizzy_quiz_width . 'px; height: ' . $quizzy_quiz_height . 'px">';
    // Wrapper that contains two panes: quiz select screen on left and the selected quiz on right
    $output .= '<div id="quizzy_c" style="width: ' . ($quizzy_quiz_width * 3) . 'px">';
    // The quiz select wrapper (the left side above)
    $output .= '<div id="quizzy_load" style="width: ' . $quizzy_quiz_width . 'px">';
    
    // Drop the available quizzes in there
    $output .= serve_quizzes();
    $output .= '</div>';
    
    // And the quiz wrapper (the right side above)
    $output .= '<div id="quizzy_quiz" style="width: ' . ($quizzy_quiz_width * 2) . 'px"></div>';
    $output .= '</div>';
    $output .= '</div>';
    
    // Print the generated output
    echo $output;
    
    // Nothing else to be done, so simply stop running this script right now.
    return;
    
  } // Default behavior
    
  /** 
   * Quizzy operator handeler.
   * At this point, $_GET['quizzy_opt'] must have something in there so quizzy is running on this client and requesting
   * specific data so switch that data and serve up what quizzy needs.
   * 
   * @param string $_GET['quizzy_op']
   *   A string indicating what operation is being requested
   * @author Joe Balough
   */
  switch ($_GET['quizzy_op']) {
  
    case 'config':
      echo serve_config();
      break;
    
    case 'quizzes':
      echo serve_quizzes();
      break;
      
    case 'quiz':
      echo serve_quiz();
      break;
      
    case 'question':
      echo serve_question();
      break;
    
    case 'explanation':
      echo serve_explanation();
      break;
  }
  
  // There shouldn't be any code below to run but this is here just in case.
  return;
  
  
  /**
   *  Quiz XML file opening and parsing
   *
   * Every time that this file is called and execution makes it this far,
   * there should have been a passed quizfile and index for the quiz to load.
   * We'll do this now since we will definitely need to do it eventually.
   * 
   * @param string $_GET['quizzy_file']
   *   The filename of the quiz to open
   * @param string $_GET['quizzy_index']
   *   The index of the quiz to open in that file
   * @return object $quiz
   *   The tinyXML object representing the requested quiz
   * @author Joe Balough
   */
  function load_quiz() {
    global $quizzy_cwd, $quizzy_quiz_folder;
    
    $quiz_file = $quizzy_cwd . '/' . $quizzy_quiz_folder . '/' . $_GET['quizzy_file'];
    $quiz_index = intval($_GET['quizzy_index']);
    $quiz_xml = simplexml_load_file($quiz_file);
    $quiz = $quiz_xml->quiz[$quiz_index];
    return $quiz;
  }
  
  
  /**
   * The serve_config function simply returns the javaScript variables in JSON format.
   * 
   * @return JSON formatted string containing all the javaScript variables
   * @author Joe Balough
   */
  function serve_config() {
    global $quizzy_js_variables;
    return json_encode($quizzy_js_variables);
  }

  
  /**
   * The serve_quizzes function will return an HTML string that lists all of the
   * quizzes that are available in the $quizzy_quiz_folder.
   * 
   * @return an HTML formatted string displaying a list of quizzes available
   * @author Joe Balough
   */
  function serve_quizzes() {
    global $quizzy_cwd, $quizzy_quiz_folder, $quizzy_pick_quiz_message, $quizzy_pic_dir;
    
    // Open the quizzes dir
    $quiz_dir = dir($quizzy_cwd . '/' . $quizzy_quiz_folder);

    // Begin formatting the list
    //$output  = '<form action="quizzy.php" method="GET" style="height: 100%;">';
    $output .= '<div class="quizzy_load_body">';
    $output .= '<h1>' . $quizzy_pick_quiz_message . '</h1>';

    // Loop through all the files in the directory, making sure they're not . or ..
    $file_no = 0;
    while ( ($file = $quiz_dir->read()) !== false ) {
      if ( ($file == '.') || ($file == '..') ) continue;
      
      // Make sure it's an XML file
      if (!strpos(strtolower($file), 'xml'))
        continue;
      
      // Open that file and parse its xml
      $filename = $quizzy_cwd . '/' . $quizzy_quiz_folder . '/' . $file;
      $quiz_xml= simplexml_load_file($filename);
      
      // Generate a list of all the quizzes in this xml file
      $quiz_no=0;
      foreach ($quiz_xml->quiz as $cur_quiz) {
        $output .= '<p>';
        $output .= '<input type="radio" class="quizzy_quiz_opt" id="quizzy_quiz_opt' . $file_no . '" onClick="quizzyState.quizFile = \'' . basename($filename) . '\'; quizzyState.quizIndex = ' . $quiz_no . ';" name="quizzy_quiz_sel">';
        $output .= '<label class="quizzy_quiz_lbl" id="quizzy_quiz_lbl' . $file_no . '">' . $cur_quiz->title . '</label>';
        
        // Add an image after the label if one was set
        if (isset($cur_quiz->img)) {
          $output .= '<img src="' . $quizzy_pic_dir . $cur_quiz->img['src'] . '" alt="' . $cur_quiz->img['alt'] . '">'; 
        }
        
        // Add a description if one was set
        if (isset($cur_quiz->description)) { 
          $output .= '<br>';
          $output .= '<div id="quizzy_quiz_desc' . $file_no . '" class="quizzy_quiz_desc">';
          
          // Add an image to the description if one was set
          if (isset($cur_quiz->description->img)) { 
            $output .= '<img src="' . $quizzy_pic_dir . $cur_quiz->description->img['src'] . '" alt="' . $cur_quiz->description->img['alt'] . '" >';
          }
          
          // Description text
          $output .= $cur_quiz->description->text;
          $output .= '</div>';
        }
        
        $output .= '</p>';
        ++$quiz_no; ++$file_no;
      }
    }
    
    // Finish up output for the quiz list
    $output .= '</div>';
    $output .= '<div class="quizzy_load_foot"><input type="submit" class="quizzy_b" id="quizzy_start_b" value="Start Quiz"></div>';
    //$output .= '</form>';
    return $output;
  }
  
  
  /**
   * The serve_quiz function will return the HTML formatted string that represents the start
   * of a quiz. It's formatted using JSON to include a some javaScript variables.
   *
   * @param string $_GET['quizzy_file']
   *   The filename of the xml file containing the currently running quiz
   * @param int $_GET['quizzy_index']
   *   The quiz index in that file (first quiz is index 0)
   * @return JSON formatted output containing the following variables:
   *   numQuestions  - The number of questions in this quiz
   *   quizTitle     - The name of the quiz
   *   quiz          - The HTML formatted string representing the start of the requested quiz
   * @author Joe Balough
   */
  function serve_quiz() {
    global $quizzy_quiz_width;
    
    // All the following variable is returned as JSON output.
    $output = array();
    
    // Load up the XML file
    $quiz = load_quiz();
    $quiz_title = $quiz->title;
    
    // Find the number of questions and quiz title and add it to the return
    $output['numQuestions'] = count($quiz->question);

    // Build the quiz container
    $output['quiz']  = '<div class="quizzy_title">' . $quiz_title . '</div>';
    $output['quiz'] .= '<div id="quizzy_q_c">';
    
    // Every question <div>. Note that we're making one extra for the results page.
    for ($i = 0; $i < $output['numQuestions'] + 1; $i++)
      $output['quiz'] .= '<div class="quizzy_q" id="quizzy_q' . $i . '" style="width: ' . $quizzy_quiz_width . '"></div>';
    
    // Close up the quiz div
    $output['quiz'] .= '</div>';
    
    return json_encode($output);
  }
  
  
  /**
   * The serve_qustion function will return the HTML that represents the current question in the quiz.
   *
   * @param string $_GET['quizzy_file']
   *   The filename of the xml file containing the currently running quiz
   * @param int $_GET['quizzy_index']
   *   The quiz index in that file (first quiz is index 0)
   * @param int $_GET['quest_no']
   *   the question to return (first is 0)
   * @return HTML encoded string that represents the current question
   * @author Joe Balough
   */
  function serve_question() {
    global $quizzy_pic_dir;
    
    // What will be outputted
    $output = '';
    
    // Load up the XML file
    $quiz = load_quiz();
    
    $question_no = intval($_GET['quest_no']);

    // Check the bounds on the requested question number. If it's larger than the nubmer of questions in the quiz, serve up a results page.
    if ($question_no >= count($quiz->question))
    {
      return serve_results($quiz);
    }

    // Get the requested question
    $quest = $quiz->question[$question_no];
    
    // Add the question itself
    $output .= '<div class="quizzy_q_body">';
    
    // Picture goes first for the float: right business
    if (isset( $quest->img )) {
      $output .= '<img src="' . $quizzy_pic_dir . $quest->img['src'] . '" alt="' . $quest->img['alt'] . '">';
    }
    
    $output .= '<p>' . $quest->text . '</p>';
    $output .= '</div>';
    
    // Add the proper user input for the question
    $output .= '<div class="quizzy_q_opts">';
    switch ($quest['type']) {
      
      
      case 'checkbox':
      case 'radio':
      default:
        $option_no = 0;
        foreach ($quest->option as $opt)
        {
          // Start paragraph wrapper
          $output .= '<p class="quizzy_q_opt" id="quizzy_q' . $question_no . '_opt' . $option_no . '">';
          
          // Radio / check button
          $input_type = empty($quest['type']) ? 'radio' : $quest['type'];
          $output .= '<input type="' . $input_type . '" name="quizzy_q' . $question_no . '" class="quizzy_q_opt_b" id="quizzy_q' . $question_no . '_opt' . $option_no . '_b">';
          
          // Label
          $output .= '<label>' . $opt->text;
          
          // Picture for label if exists
          if (isset($opt->img)) {
            $output .= '<img src="' . $quizzy_pic_dir . $opt->img['src'] . '" alt="' . $opt->img['alt'] . '">';
          }
          
          // Span that will be filled with the option's score after the user clicks 'check score'
          $output .= '<span class="quizzy_q_opt_val" id="quizzy_q' . $question_no . '_opt' . $option_no . '_val"></span>';
          
          // Finish off label and paragrah wrappers
          $output .= '</label>';
          $output .= '</p>';

          $option_no++;
        }
        break;
    }
    
    // Add an empty <div> that will be filled with the question's explanation
    $output .= '<div class="quizzy_q_exp" id="quizzy_q' . $question_no . '_exp"></div>';
    
    // Finish off options wrapper
    $output .= '</div>';

    // Footer <div> with Check Answer buttno and Next button
    $output .= '<div class="quizzy_q_foot">';
    $output .= '<input type="submit" class="quizzy_q_foot_b" id="quizzy_q' . $question_no . '_foot_chk" value="Check Answer">';
    $output .= '<input type="submit" class="quizzy_q_foot_b" id="quizzy_q' . $question_no . '_foot_nxt" value="Next">';
    $output .= '</div>';
    
    return $output;
  }
  
  
  /**
   * The serve_results function will return the HTML that represents the results screen to be
   * presented to the user for finishing the quiz.
   *
   * @param tinyXML object &$quiz
   *   The tinyXML object representing the quiz (passed by reference to save memory)
   * @param int $_GET['score']
   *   The score the player currently has (needed for serving last page)
   * @return HTML encoded string that represents the current question
   * @author Joe Balough
   */
  function serve_results(&$quiz) {
    global $quizzy_end_quiz_message, $quizzy_pic_dir;
    $output = '';
    
    // Find the max possible score for the quiz
    // Check each question
    $max_score = 0;
    foreach ($quiz->question as $quest) {
      $quest_max = 0;
      
      // And each option in those questions
      foreach ($quest->option as $opt) {
      
        // Find the highest scoring option
        if (intval($opt->score) > $quest_max)
          $quest_max = $opt->score;
      }
      // Add the highest scoring option to the max_score
      $max_score += intval($quest_max);
    }
    
    // Begin formatting the output
    $score = intval($_GET['score']);
    $output .= '<div class="quizzy_result">';
    $output .= '<h1>' . $quizzy_end_quiz_message . '</h1>';
    $output .= '<p>You scored <span class="quizzy_result_score">' . $score . '</span> out of <span class="quizzy_result_max">' . $max_score . '</span> possible points!</p>';

    // Calculate a percentage score, then use the grading information in the xml data to put some more stuff up
    $percentage = ($score / $max_score) * 100;

    // Find the correct score range
    $score_range = NULL;
    foreach ($quiz->grading->range as $range) {
      // Drop the range that starts a 0 to -1 so that it WILL be less than the percentage
      if (intval($range['start']) == 0) $range['start'] = -1;
      
      // Check the range
      if (intval($range['start']) < $percentage && intval($range['end']) >= $percentage) {
        $score_range = $range;
        break;
      }
    }

    // Finish up the output with the grading information
    $output .= '<p>Grade: <span class="quizzy_result_grade quizzy_result_grade_' . $score_range->grade . '">' . $score_range->grade . '</span> (' . sprintf('%.1f%%', $percentage) . ')</p>';
    // Add picture if defined
    if (isset($score_range->img)) {
      $output .= '<div class="quizzy_result_img"><img src="' . $quizzy_pic_dir . $score_range->img['src'] . '" alt="' . $score_range->img['alt'] . '" ></div>';
    }
    
    $output .= '<p class="quizzy_result_rank quizzy_result_rank_' . $score_range->rank . '">' . $score_range->rank . '</p>';
    $output .= '<div class="quizzy_result_foot"><input type="submit" onclick="restartQuizzy();" value="Do a different Quiz"></div>';
    $output .= '</div>';
    
    return $output;
  }
  
  
  /**
   * The serve_explanation function will return the HTML explanation for the requested
   * question and option. Its return is formatted in  including several variables.
   * 
   * @param string $_GET['quizzy_file']
   *   The filename of the xml file containing the currently running quiz
   * @param int $_GET['quizzy_index']
   *   The quiz index in that file (first quiz is index 0)
   * @param int $_GET['quest_no']
   *   The question to return (first is 0)
   * @param int $_GET['sel_opt']
   *   The option for which to retrieve the explanation
   * @return JSON formatted output containing the following variables:
   *     optionValues   - An array specifiying how many points each of the options were worth
   *     addScore       - How many points should be added to the score
   *     correctOption  - Which was the best option
   *     explanation    - HTML formatted string representing the explanation text
   *     bestScore      - Which index is the best possible score
   * @author Joe Balough
   */
  function serve_explanation() {
    global $quizzy_pic_dir;
    
    // The output array that will eventually be passed to json_encode.
    $output = array();
    
    // Load up the quiz
    $quiz = load_quiz();
    
    // Get those other needed variables
    $quest_no = intval($_GET['quest_no']);
    $sel_opt = intval($_GET['sel_opt']);
    
    // Get the requested question, option, and explanation
    $quest = $quiz->question[$quest_no];
    $opt = $quest->option[$sel_opt];
    $exp = $opt->explanation;
    
    // Get how much to add to the score and keep track of the values for the other options
    $output['addScore'] = intval($opt->score);
    $output['optionValues'] = array();
    
    // Figure out what the highest possible score
    $i = 0;
    $output['bestScore'] = 0;
    $output['correctOption'] = -1;
    foreach($quest->option as $opt)
    {
      $cur_score = intval($opt->score);
      
      // Set the value for this question
      $output['optionValues'][$i] = $cur_score;
      
      // Replace $output['best_score'] if it's better
      if($cur_score > $output['bestScore'])
      {
        $output['bestScore'] = $cur_score;
        $output['correctOption'] = $i;
      }
      
      ++$i;
    }
    
    // Build explanation text
    if (isset($exp->img)) {
      $output['explanation'] = '<img src="' . $quizzy_pic_dir . $exp->img['src'] . '" alt="' . $exp->img['alt'] . '">';
    }
    $output['explanation'] .= '<p>' . $exp->text . '</p>';
    
    return json_encode($output);
  }
  
?>