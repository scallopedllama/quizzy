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
   * @param
   * @return an HTML formatted string displaying a list of quizzes available
   * @author Joe Balough
   */
  function serve_quizzes() {
    
  }
  
  
  /**
   * The serve_explanation function will return the HTML explanation for the requested
   * question and option. It's return is formatted in JSON including 2 variables.
   * 
   * @param string _GET['questNo']
   *   question to return (first is 0)
   * @param string _GET['selOpt']
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
    
  } // serve_explanation
?>