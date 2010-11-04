<?php

  // Global variables
  
  // This string represents where this quiz's pictures should be found
  $pic_dir = $quizzy_cwd . '/' . $quiz_folder . '/' . $pic_folder . '/';

  /**
   *  Default behavior: Just add the quizzy container
   */
  
  if (!isset($_GET['quizzy_op']) || empty($_GET['quizzy_op'])) {
?>
<div id="quizzy" style="width: <?php echo $quizzy_quiz_width; ?>px; height: <?php echo $quizzy_quiz_height; ?>px">
  <div id="quizzy_c" style="width: <?php echo ($quizzy_quiz_width * 3); ?>px">
    <div id="quizzy_load" style="width: <?php echo $quizzy_quiz_width; ?>px"></div>
    <div id="quizzy_quiz" style="width: <?php echo ($quizzy_quiz_width * 3); ?>px"></div>
  </div>
</div>
<?php
    
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
    
    case 'quizzes':
      serve_quizzes();
      break;
      
    case 'quiz':
      serve_quiz();
      break;
      
    case 'question':
      serve_question();
      break;
    
    case 'explanation':
      serve_explanation();
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
    $quiz_file = $quizzy_cwd . '/' . $quizzy_quiz_folder . '/' . $_GET['quizzy_file'];
    $quiz_index = intval($_GET['quizzy_index']);
    $quiz_xml = simplexml_load_file($quiz_file);
    $quiz = $quiz_xml->quiz[$quiz_index];
    return $quiz;
  }

  
  /**
   * The serve_quizzes function will return an HTML string that lists all of the
   * quizzes that are available in the $quizzy_quiz_folder.
   * 
   * @return an HTML formatted string displaying a list of quizzes available
   * @author Joe Balough
   */
  function serve_quizzes() {
    // Open the quizzes dir
    $quiz_dir = dir($quizFolder);

    // Begin formatting the list
    $output = '<div class="quizzy_load_body">';
    $output .= '<h1>' . $quizzy_pick_quiz_message . '</h1>';

    // Loop through all the files in the directory, making sure they're not . or ..
    $file_no = 0;
    while (($file = $quiz_dir->read()) !== false) {
      if ( ($file == '.') || ($file == '..') ) continue;
      
      // Make sure it's an XML file
      if (!strpos(strtolower($file), 'xml'))
        continue;
      
      // Open that file and parse its xml
      $filename = $cwd.'/'.$quizFolder.'/'.$file;
      $quiz_xml= simplexml_load_file($filename);
      
      // Generate a list of all the quizzes in this xml file
      $quiz_no=0;
      foreach ($quiz_xml->quiz as $cur_quiz){
        $output .= '<p>';
        $output .= '<input type="radio" class="quizzy_quiz_opt" id="quizzy_quiz_opt' . $file_no . '" onClick="quizFile = \'' . basename($filename) . '\'; quizIndex = ' . $quiz_no . ';" name="quizzy_quiz_sel">';
        $output .= '<label class="quizzy_quiz_lbl" id="quizzy_quiz_lbl' . $file_no . '">' . $cur_quiz->title . '</label>';
        
        // Add an image after the label if one was set
        if(isset($cur_quiz->img)) {
          $output .= '<img src="' . $pic_dir . $cur_quiz->img['src'] . '" alt="' . $cur_quiz->img['alt'] . '">'; 
        }
        
        // Add a description if one was set
        if(isset($cur_quiz->description)) { 
          $output .= '<br>';
          $output .= '<div id="quizzy_quiz_desc' . $file_no . '" class="quizzy_quiz_desc">';
          
          // Add an image to the description if one was set
          if(isset($cur_quiz->description->img)) { 
            $output .= '<img src="' . $pic_dir . $cur_quiz->description->img['src'] . '" alt="' . $cur_quiz->description->img['alt'] . '" >';
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
    // All the following variable is returned as JSON output.
    $output = array();

    // Find the number of questions and quiz title and add it to the return
    $output['numQuestions'] = count($quiz->question);
    $output['quizTitle'] = $quiz->title;

    // Build the quiz container
    $output['quiz']  = '<div class="quizzy_title">' . $quiz_title . '</div>';
    $output['quiz'] .= '<div id="quizzy_q_c">';
    
    // Every question <div>. Note that we're making one extra for the results page.
    for($qi = 0; $qi < $num_questions + 1; $qi++)
      $output['quiz'] .= '<div class="quizzy_q" id="quizzy_q' . $qi . '" style="width: ' . $quizzy_quiz_width . '">&nbsp;</div>';
    
    // Close up the quiz div
    $output['quiz'] .= '</div>';
    
    return json_encode($output);
  }
  
  
  /**
   * The serve_explanation function will return the HTML explanation for the requested
   * question and option. It's return is formatted in JSON including several variables.
   * 
   * @param string $_GET['quizzy_file']
   *   The filename of the xml file containing the currently running quiz
   * @param int $_GET['quizzy_index']
   *   The quiz index in that file (first quiz is index 0)
   * @param string $_GET['quest_no']
   *   question to return (first is 0)
   * @param string $_GET['sel_opt']
   *   the option for which to retrieve the explanation
   * @return JSON formatted output containing the following variables:
   *     optValues   - An array specifiying how many points each of the options were worth
   *     addScore    - How many points should be added to the score
   *     correctOpt  - Which was the best option
   *     bestScore   - What was the best possible score
   *     explanation - HTML formatted string representing the explanation text
   * @author Joe Balough
   */
  function serve_explanation() {
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
    $output['optValues'] = array();
    
    // Figure out what the highest possible score
    $i = 0;
    $output['best_score'] = 0;
    $output['correct_opt'] = -1;
    foreach($quest->option as $opt)
    {
      $cur_score = intval($opt->score);
      
      // Set the value for this question
      $output['optValues'][$i] = $cur_score;
      
      // Replace $output['best_score'] if it's better
      if($cur_score > $output['best_score'])
      {
        $output['best_score'] = $cur_score;
        $output['correct_opt'] = $i;
      }
      
      ++$i;
    }
    
    // Build explanation text
    if (isset($exp->img)) {
      $output['explanation'] = '<img src="' . $picDir . $exp->img['src'] . '" alt="' . $exp->img['alt'] . '">';
    }
    $output['explanation'] .= '<p>' . $exp->text . '</p>';
    
    return json_encode($output);
  }
  
?>