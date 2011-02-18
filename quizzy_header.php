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

  // Include the configuration
  include 'quizzy_config.php';

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

<script type="text/javascript" src="quizzy/lib/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="quizzy/lib/jquery.loading.1.6.4.min.js"></script>
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
    #quizzy_load img{ height: 12pt; }
    .quizzy_quiz_desc img {display: none;}
    .quizzy_q_body img {width: 45%; }
    .quizzy_q_opts img {height: 12pt;}
    .quizzy_q_exp img {width: 45%; height: auto;}
    .quizzy_done img {height: 15%;}
    .quizzy_q {overflow: hidden;}
    #quizzy_load{overflow: hidden;}
  </style>
<![endif]-->
