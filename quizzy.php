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
   * Legacy specific behavior:
   *   Convert the quizzy_quiz_sel value to correctly corresponding quizzy_file and quizzy_index variables
   *   Set a question number if one isn't already set
   *   Build the response value
   * @author Joe Balough
   */
  if (isset($_GET['quizzy_quiz_sel'])) {
    $quiz_sel = explode(' ', $_GET['quizzy_quiz_sel']);
    $_GET['quizzy_file'] = $quiz_sel[0];
    $_GET['quizzy_index'] = $quiz_sel[1];
  }
  if (!isset($_GET['quest_no']))
    $_GET['quest_no'] = 0;
  // Look for all the $_GET['quizzy_optXX'] == 'on' values and convert them into
  // an array of strings in the format quizzy_qXX_opt . $opt . _b
  $_GET['response'] = array();
  foreach ($_GET as $key => $value) {
    if ($value != 'on')
      continue;

    // See if this $key is one of the option values and add it to the response variable if it is
    if (preg_match('/quizzy_opt(.+)/', $key, $matches))
      $_GET['response'][] = 'quizzy_qLL_opt' . $matches[1] . '_b';
  }

  /**
   * Default behavior: Just add the quizzy container and available quizzes
   * @author Joe Balough
   */

  if (!isset($_GET['quizzy_op']) || empty($_GET['quizzy_op']) || isset($_GET['quizzy_legacy'])) {
    // Wrapper for everything in the quiz (overflow: hidden)
    $output = '<div id="quizzy" style="width: ' . $quizzy_quiz_width . 'px; height: ' . $quizzy_quiz_height . 'px">';
    // Wrapper that contains two panes: quiz select screen on left and the selected quiz on right
    $output .= '<div id="quizzy_c" style="width: ' . ($quizzy_quiz_width * 3) . 'px">';
    // The quiz select wrapper (the left side above)
    $output .= '<div id="quizzy_load" style="width: ' . $quizzy_quiz_width . 'px">';

    // If in legacy, add the next step. otherwise, add the quizzes.
    if (isset($_GET['quizzy_legacy']))
      $output .= serve_quiz();
    else
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
   * Utility function to get a string from a quiz file
   *
   * This function is a wrapper to the htmlentities() function. It is used in any situation
   * where text is being used from a quiz (such as the question text, etc.)
   * It checks the value of the $quizzy_html_entities variable set in quizzy_config.php
   * to determine whether or not to run the string through the htmlentities() function.
   * There is also the ability to override that with a second optional parameter but it is not
   * used at the moment.
   *
   * @param string $text
   *   The string from the $quiz variable to use
   * @param boolean $override
   *   If this param is set to TRUE, the text will be escaped regardless of what is set in the config
   * @return string
   *   That string properly escaped (or not)
   * @author Joe Balough
   */
  function get_quiz_string(&$text, $override = FALSE) {
    global $quizzy_html_entities;
    if ($quizzy_html_entities || $override)
      return htmlentities($text);
    else
      return $text;
  }


  /**
   * The serve_config function simply returns the javaScript variables in JSON format.
   *
   * @return JSON formatted string containing all the javaScript variables
   * @author Joe Balough
   */
  function serve_config() {
    global $quizzy_js_variables;
    return _json_encode($quizzy_js_variables);
  }


  /**
   * This function returns the <form> elements that are needed for legacy support.
   *
   * @param string $op
   *   What value should be dropped in the quizzy_op hidden field
   * @return an HTML formatted string containing the <form>, legacy hidden value, and op hidden value
   * @author Joe Balough
   */
  function legacy_form($op = 'question') {
    $output  = '<form method="GET" style="height: 100%;" id="quizzy_legacy_form">';
    $output .= '<input type="hidden" name="quizzy_legacy" class="quizzy_legacy">';
    $output .= '<input type="hidden" name="quizzy_op" value="' . $op . '" class="quizzy_legacy">';
    if (isset($_GET['quizzy_file']))
      $output .= '<input type="hidden" name="quizzy_file" value="' . $_GET['quizzy_file'] . '" class="quizzy_legacy">';
    if (isset($_GET['quizzy_index']))
      $output .= '<input type="hidden" name="quizzy_index" value="' . $_GET['quizzy_index'] . '" class="quizzy_legacy">';
    if (isset($_GET['quest_no']))
      $output .= '<input type="hidden" name="quest_no" value="' . $_GET['quest_no'] . '" class="quizzy_legacy">';
    return $output;
  }


  /**
   * The serve_quizzes function will return an HTML string that lists all of the
   * quizzes that are available in the $quizzy_quiz_folder.
   *
   * @return an HTML formatted string displaying a list of quizzes available
   * @author Joe Balough
   */
  function serve_quizzes() {
    global $quizzy_cwd, $quizzy_quiz_folder, $quizzy_pick_quiz_message, $quizzy_pic_dir, $quizzy_quiz_height;

    // Open the quizzes dir
    $quiz_dir = dir($quizzy_cwd . '/' . $quizzy_quiz_folder);

    // Begin formatting the list
    $output  = legacy_form();
    $output .= '<div class="quizzy_load_body">';

    // A Helpful warning for people who don't have json enabled in their php configuration
    if (!function_exists('json_encode') && !file_exists($quizzy_cwd . "/Zend/Json.php")) {
      $output .= '<p><h1 class="quizzy_opt_worst">quizzy Error!</h1></p>';
      $output .= '<p>The JSON extension is not enabled in this PHP installation and the Zend JSON Framework was not found.</p>';
      $output .= '<p>quizzy <i>will not</i> function without a JSON framework.</p>';
      $output .= '<p style="margin-bottom: 150px">See the README.</p>';
    }

    // Welcome message
    $output .= '<h1>' . get_quiz_string($quizzy_pick_quiz_message) . '</h1>';

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
        $output .= '<input type="radio" class="quizzy_quiz_opt" id="quizzy_quiz_opt' . $file_no . '" onClick="quizzyState.quizFile = \'' . basename($filename) . '\'; quizzyState.quizIndex = ' . $quiz_no . ';" name="quizzy_quiz_sel" value="' . basename($filename) . ' ' . $quiz_no . '">';
        $output .= '<label for="quizzy_quiz_opt' . $file_no . '" class="quizzy_quiz_lbl" id="quizzy_quiz_lbl' . $file_no . '">' . $cur_quiz->title . '</label>';

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
          $output .= get_quiz_string($cur_quiz->description->text);
          $output .= '</div>';
        }

        $output .= '</p>';
        ++$quiz_no; ++$file_no;
      }
    }

    // Finish up output for the quiz list
    $output .= '</div>';
    $output .= '<div class="quizzy_load_foot"><input type="submit" class="quizzy_b" id="quizzy_start_b" value="Start Quiz"></div>';
    $output .= '</form>';
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
    $quiz_title = get_quiz_string($quiz->title);

    // Build the quiz container
    $output['quiz']  = '<div class="quizzy_title">' . $quiz_title . '</div>';
    $output['quiz'] .= '<div id="quizzy_q_c">';

    // Handle legacy
    if (isset($_GET['quizzy_legacy'])) {
      // Add the form stuff and the question
      $op = array('question' => 'explanation', 'explanation' => 'question');
      $output['quiz'] .= legacy_form($op[$_GET['quizzy_op']]);
      $output['quiz'] .= serve_question();

      // Close the div and the form from above before returning
      $output['quiz'] .= '</form>';
      $output['quiz'] .= '</div>';

      // Return only the quiz HTML text
      return $output['quiz'];
    }

    // Find the number of questions and quiz title and add it to the return
    $output['numQuestions'] = count($quiz->question);

    // Every question <div>. Note that we're making one extra for the results page.
    for ($i = 0; $i < $output['numQuestions'] + 1; $i++)
      $output['quiz'] .= '<div class="quizzy_q" id="quizzy_q' . $i . '" style="width: ' . $quizzy_quiz_width . '"></div>';

    // Close up the quiz div
    $output['quiz'] .= '</div>';

    return _json_encode($output);
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
    $question_type = empty($quest['type']) ? 'radio' : $quest['type'];

    // Add the question itself
    $output .= '<div class="quizzy_q_body">';

    // Picture goes first for the float: right business
    if (isset( $quest->img )) {
      $output .= '<img src="' . $quizzy_pic_dir . $quest->img['src'] . '" alt="' . $quest->img['alt'] . '">';
    }

    $output .= '<p>' . get_quiz_string($quest->text) . '</p>';
    $output .= '</div>';

    // Legacy stuff: get the explanation here
    $explanation = serve_explanation();

    // Add the proper user input for the question
    $output .= '<div class="quizzy_q_opts">';
    // Drop a clue as to what kind of question this is
    $output .= '<input type="hidden" value="' . $question_type . '" id="quizzy_q' . $question_no . '_type">';
    switch ($question_type) {
      case 'input':
        // Don't need much for the input-type questions. Add the input field
        $output .= '<input type="text" name="quizzy_q' . $question_no . '" class="quizzy_q_txt" id="quizzy_q' . $question_no . '_txt"';
        // Add the default value if it was set
        if (isset($quest->default))
          $output .= 'value="' . get_quiz_string($quest->default) . '"';
        $output .= '>';

        // Span that will be filled with the option's score after the user clicks 'check score'
        $output .= '<span class="quizzy_q_txt_val" id="quizzy_q' . $question_no . '_txt_val">';

        // Add the explanation if that's the step we're on
        if ((isset($_GET['quizzy_legacy']) && $_GET['quizzy_op'] == 'explanation'))
          $output .= $explanation['addScore'];

        // Closing the explanation span
        $output .= '</span>';

        break;

      case 'checkbox':
      case 'radio':
      default:
        $option_no = 0;
        foreach ($quest->option as $opt)
        {
          // Start paragraph wrapper
          $output .= '<p class="quizzy_q_opt" id="quizzy_q' . $question_no . '_opt' . $option_no . '">';

          // Radio / check button
          $output .= '<input type="' . $question_type . '" name="quizzy_opt' . $option_no . '" class="quizzy_q_opt_b quizzy_q' . $question_no . '_opt_b" id="quizzy_q' . $question_no . '_opt' . $option_no . '_b">';

          // Label
          $output .= '<label for="quizzy_q' . $question_no . '_opt' . $option_no . '_b">' . get_quiz_string($opt->text);

          // Picture for label if exists
          if (isset($opt->img)) {
            $output .= '<img src="' . $quizzy_pic_dir . $opt->img['src'] . '" alt="' . $opt->img['alt'] . '">';
          }

          // Span that will be filled with the option's score after the user clicks 'check score'
          $output .= '<span class="quizzy_q_opt_val" id="quizzy_q' . $question_no . '_opt' . $option_no . '_val">';

          // Add the explanation if that's the step we're on
          if ((isset($_GET['quizzy_legacy']) && $_GET['quizzy_op'] == 'explanation'))
            $output .= $explanation['optionValues'][$option_no];

          // Closing the explanation span
          $output .= '</span>';

          // Finish off label and paragrah wrappers
          $output .= '</label>';
          $output .= '</p>';

          $option_no++;
        }
        break;
    }

    // Add a <div> that will be filled with the question's explanation
    $output .= '<div class="quizzy_q_exp" id="quizzy_q' . $question_no . '_exp">';

    // Add the explanation in legacy mode
    if (isset($_GET['quizzy_legacy']))
      $output .= $explanation['explanation'];

    // close the explanation div
    $output .= '</div>';

    // Finish off options wrapper
    $output .= '</div>';

    // Footer <div> with Check Answer buttno and Next button
    $output .= '<div class="quizzy_q_foot">';

    if (!isset($_GET['quizzy_legacy']) || (isset($_GET['quizzy_legacy']) && $_GET['quizzy_op'] != 'explanation'))
      $output .= '<input type="submit" class="quizzy_q_foot_b" id="quizzy_q' . $question_no . '_foot_chk" value="Check Answer">';
    if (!isset($_GET['quizzy_legacy']) || (isset($_GET['quizzy_legacy']) && $_GET['quizzy_op'] == 'explanation'))
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
      $quest_max = question_best_score($quest);

      // Add the highest scoring option to the max_score
      $max_score += $quest_max['max_score'];
    }

    // Begin formatting the output
    $score = intval($_GET['score']);
    $output .= '<div class="quizzy_result">';
    $output .= '<h1>' . get_quiz_string($quizzy_end_quiz_message) . '</h1>';
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
    $output .= '<p>Grade: <span class="quizzy_result_grade quizzy_result_grade_' . get_quiz_string($score_range->grade, TRUE) . '">' . get_quiz_string($score_range->grade) . '</span> (' . sprintf('%.1f%%', $percentage) . ')</p>';
    // Add picture if defined
    if (isset($score_range->img)) {
      $output .= '<div class="quizzy_result_img"><img src="' . $quizzy_pic_dir . $score_range->img['src'] . '" alt="' . $score_range->img['alt'] . '" ></div>';
    }

    $output .= '<p class="quizzy_result_rank">' . get_quiz_string($score_range->rank) . '</p>';
    $output .= '<div class="quizzy_result_foot"><input type="submit" id="quizzy_reset_b" value="Do a different Quiz"></div>';
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
   * @param int $_GET['response']
   *   The user's response. Either an array of ids that the user selected / checked for radio and checkbox type or a string for input type
   * @return JSON formatted output containing the following variables:
   *     optionValues   - An array specifiying how many points each of the options were worth or simply containing a check / x
   *     addScore       - How many points should be added to the score
   *     correctOptions - Which were the best options (array)
   *     explanation    - HTML formatted string representing the explanation text
   *     bestScore      - What is the highest score possible from any one option
   * @author Joe Balough
   */
  function serve_explanation() {
    global $quizzy_pic_dir, $quizzy_strip_characters, $quizzy_number_strictness, $quizzy_show_answer;

    // The output array that will eventually be passed to _json_encode.
    $output = array();

    // Load up the quiz
    $quiz = load_quiz();

    // Get the question data
    $quest_no = intval($_GET['quest_no']);
    $quest = $quiz->question[$quest_no];

    // Should be set below to the proper simpleXML explanation node object.
    $exp = NULL;

    // Process what the user input according to the question type
    // $output['addScore'], $output['bestScore'], $output['correctOptions'], $output['optionValues'], and $exp need to be set here.
    switch ($quest['type']) {
      case 'input':
        // All checking here starts with the user-provided response and the xml-provided answer
        // but they may be changed before running the comparison.
        $response = $_GET['response'];

        // Get the best score information and set some output variables
        $best_score = question_best_score($quest);
        $output['bestScore'] = $best_score['best_score'];
        $output['addScore'] = 0;
        $exp = $quest->explanation;

        // Check against all answers
        foreach ($quest->answer as $answer) {
          $answer_text = $answer->value;

          // Within the input-type question, there three types of answers, handle them appropriately
          switch ($answer['type']) {
            // 'text' and 'exact' essentiall do the same thing but text modifies the text a bit first.
            case 'text':
              // Strip whitespace, make it all lowercase, and remove any non-text character like
              // punctionation from both the response and answer
              $response = str_replace($quizzy_strip_characters, '', strtolower($response));
              $answer_text = str_replace($quizzy_strip_characters, '', strtolower($answer_text));

            case 'exact':
              if ($response == $answer_text) {
                $output['addScore'] = intval($answer->score);
                if (isset($answer->explanation))
                  $exp = $answer->explanation;
              }
              break;

            case 'number':
              // The answer and response need to be parsed into numbers before comparing.
              $response = parse_float($response);
              $answer_float = parse_float($answer_text);

              // If response is $answer +/- $quizzy_number_strictness, the answer is correct.
              if ($response > $answer_float - $quizzy_number_strictness && $response < $answer_float + $quizzy_number_strictness) {
                $output['addScore'] = intval($answer->score);
                if (isset($answer->explanation))
                  $ans = $answer->explanation;
              }
              break;
          }
        }
        break;

      case 'checkbox':
      case 'radio':
      default:
        // Parse the passed array of option ids into an array of tinyXML option nodes
        $sel_opts = array();
        foreach ($_GET['response'] as $sel_opt) {
          preg_match('/quizzy_q.+_opt(.+)_b/', $sel_opt, $matches);
          $sel_opts[] = $quest->option[intval($matches[1])];
        }

        // Calculate how much to add to the score
        $output['addScore'] = 0;
        foreach ($sel_opts as $sel_opt)
          $output['addScore'] += intval($sel_opt->score);

        // Use the question_best_score function to find the highest possible score for this question and the correct answers
        $best_score = question_best_score($quest);
        $output['bestScore'] = $best_score['best_score'];
        $output['correctOptions'] = $best_score['correct_options'];

        // Generate the array of values that each option is worth.
        $output['optionValues'] = array();
        foreach($quest->option as $opt)
          $output['optionValues'][] = intval($opt->score);

        // Determine whether or not to display values
        $score_values = array();
        $print_values = FALSE;
        foreach ($quest->option as $opt) {
          // Add the score to the arry of score values if it's not already in there
          if (!in_array(intval($opt->score), $score_values))
            $score_values[] = intval($opt->score);

          // If there are more than 2 score values, enable the value output
          if (count($score_values) > 2)
            $print_values = TRUE;
        }
        // Switch the optionValues to a × or a ✓ by sorting the array and changing the value to an
        // X if there are 2 scores in the questino and the current option's score is the first one in the array.
        if (!$print_values) {
          sort($score_values);
          foreach ($output['optionValues'] as &$option_value) {
            if (count($score_values) == 2 && $option_value == $score_values[0])
              $option_value = '×';
            else
              $option_value = '✓';
          }
        }

        // Get the explanation
        $exp = ($quest['type'] == 'checkbox') ? $quest->explanation : $sel_opts[0]->explanation;

        break;
    }

    // Build explanation text
    $output['explanation'] = '';
    if (!empty($exp) && isset($exp->img)) {
      $output['explanation'] .= '<img src="' . $quizzy_pic_dir . $exp->img['src'] . '" alt="' . $exp->img['alt'] . '">';
    }

    $output['explanation'] .= '<p>' . get_quiz_string($exp->text) . '</p>';

    // If showing the answers is off, only return the addScore variable
    if (!$quizzy_show_answer) {
      $addScore = $output['addScore'];
      unset($output);
      $output = array('addScore' => $addScore);
    }

    // Don't JSON encode the string if we're running legacy mode
    if (isset($_GET['quizzy_legacy']))
      return $output;
    else
      return _json_encode($output);
  }


  /**
   * Returns the highest possible score for the passed question
   *
   * @param Object $question
   *   The question to work on
   * @return array
   *   'max_score'       => The highest possible score for this question
   *   'best_score'      => The highest single score available for this question
   *   'correct_options' => An array of the 'correct' option indices
   * @author Joe Balough
   */
  function question_best_score(&$quest) {
    $return = array(
      'max_score' => 0,
      'best_score' => 0,
      'correct_options' => array(),
    );

    switch ($quest['type']) {

      // Input-type questions' max score is simply the score set in the question tag.
      case 'input':
        $i = 0;
        foreach ($quest->answer as $answer) {
          if ($answer->score > $return['max_score']) {
            $return['max_score'] = $return['best_score'] = intval($answer->score);

            $return['correct_options'] = array();
            $return['correct_options'][] = $i;
          }
          if ($answer->score == $return['max_score']) {
            $return['correct_options'][] = $i;
          }
          ++$i;
        }
        break;
      // Checkbox-type questions' max score is the sum of the scores of all options that are > 0
      case 'checkbox':
        $i = 0;
        foreach ($quest->option as $opt) {
          if (intval($opt->score) > 0) {
            $return['max_score'] += intval($opt->score);
            $return['correct_options'][] = $i;
          }
          if (intval($opt->score) > $return['best_score'])
            $return['best_score'] = intval($opt->score);
          ++$i;
        }
        break;

      // Radio-type questions' max score is the greatest score of all the options
      default:
      case 'radio':
        $i = 0;
        foreach ($quest->option as $opt) {
          if (intval($opt->score) > $return['max_score']) {
            $return['max_score'] = $return['best_score'] = intval($opt->score);

            // Since this indicates that there is a new champ in terms of best answer, we need
            // to reset the correct_options array.
            $return['correct_options'] = array();
            $return['correct_options'][] = $i;
          }
          // If this option's score is equal to that of the best, add its index to the correct_options array
          elseif (intval($opt->score) == $return['max_score']) {
            $return['correct_options'][] = $i;
          }
          ++$i;
        }
        break;
    }
    return $return;
  }


  /**
   * Wrapper function for json output encoding
   * Typically just passes its input to the json_encode function. It is wrapped for
   * people who are forced to use an old version of PHP and cannot enable the json extension.
   * See the README for more information.
   *
   * @param mixed $value
   *   The array / object / etc to encode into JSON
   * @return string
   *   The JSON string representing the input
   * @author Joe Balough
   */
  function _json_encode($value) {
    global $quizzy_cwd;
    if (function_exists('json_encode'))
      return json_encode($value);
    else {
      require_once "Zend/Json.php";
      return Zend_Json::encode($value);
    }
  }


  /**
   * Utility function, parse float value from string.
   * This function performs a locale-aware parse of a string to a float. It will ensure that the float is
   * parsed properly regardless of whether the user uses a , or . to separate thousands or decimals.
   * This function is borrowed from the PHP documenation page for floatval and was modified to fit the code style.
   *
   * @param string $float_string
   *   The string to parse a float value from
   * @return float
   *   The float value of the passed string
   * @author chris at georgakopoulos dot com
   */
  function parse_float($float_string) {
    $locale_info = localeconv();
    // Account for the locale by removing thousands separator and making sure the decimal is a .
    $float_string = str_replace($locale_info["mon_thousands_sep"] , "", $float_string);
    $float_string = str_replace($locale_info["mon_decimal_point"] , ".", $float_string);
    // Remove any characters that aren't a number, '-', or '.'
    $float_string = preg_replace("/[^-0-9\.]/", "", $float_string);
    return floatval($float_string);
  }

?>