<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');


  // http://php.net/manual/en/function.imagecreate.php

  function CreateGraph($title_y_axis, $title_x_axis, $points_x, $points_y, $titles, &$filename)
  {
    $left = 0;
    $top = 0;
    $x_size = 400;
    $y_size = 400;

    $char_width = 8;
    $char_height = 11;

    $x_start = $x_left + 100;
    $y_start = $top + $char_height * 1.5;
    $x_end = $x_start + $x_size;
    $y_end = $y_start + $y_size;
    $right = $x_start + $x_size + 40;
    $bottom = $y_start + $y_size + $char_height * 1.5;



    // Find the minimum and maximum points on the graph
    $min_x = 9e99;
    $min_y = 9e99;
    $max_x = -9e99;
    $max_y = -9e99;

    $avg_y = 0.0;

    $graph_n = count($titles);

    $points_n = count($points_x[0]);

    for ($i = 0; $i < $points_n; $i++) {
      if ($points_x[0][$i] < $min_x)
        $min_x = $points_x[0][$i];

      if ($points_x[0][$i] > $max_x)
        $max_x = $points_x[0][$i];

      if ($points_y[0][$i] < $min_y)
        $min_y = $points_y[0][$i];

      if ($points_y[0][$i] > $max_y)
        $max_y = $points_y[0][$i];

      $avg_y += $points_y[0][$i];
    }

    $avg_y = $avg_y / $points_n;

    $min_x = 0;
    $min_y = 0;
    $max_x += $max_x * 0.05;
    $max_y += $max_y * 0.05;


    // Now we know what size to make the image, let's create it
    $image = ImageCreate($right - $left, $bottom - $top);
    $background_color = imagecolorallocate($image, 255, 255, 255);
    $text_color = imagecolorallocate($image, 233, 14, 91);

    $grey = ImageColorAllocate($image, 204, 204, 204);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    $colour = array(imagecolorallocate($image, 255, 0, 0), imagecolorallocate($image, 0, 255, 0), imagecolorallocate($image, 0, 0, 255), imagecolorallocate($image, 255, 255, 0), imagecolorallocate($image, 0, 255, 255));



    imagerectangle($image, $left, $top, $right - 1, $bottom - 1, $black);
    imagerectangle($image, $x_start, $y_start, $x_end, $y_end, $grey);

    for ($j = 0; $j < $graph_n; $j++) {
      for ($i = 0; $i < $points_n; $i++) {
        $pt_x = $x_start + ($x_end - $x_start) * ($points_x[$j][$i]-$min_x)/($max_x-$min_x);
        $pt_y = $y_end - ($y_end - $y_start) * ($points_y[$j][$i]-$min_y)/($max_y-$min_y);

        imagechar($image, 2, $pt_x - 3, $pt_y - 10, '.', $colour[$j]);
      }
    }

    $string = sprintf("%2.5f", $max_y);
    imagestring($image, 4, $x_start - strlen($string) * $char_width, $y_start - $char_width, $string, $black);

    $string = sprintf("%2.5f", $min_y);
    imagestring($image, 4, $x_start - strlen($string) * $char_width, $y_end - $char_height, $string, $black);

    $string = sprintf("%2.5f", $min_x);
    imagestring($image, 4, $x_start - (strlen($string) * $char_width)/2, $y_end, $string, $black);

    $string = sprintf("%2.5f", $max_x);
    imagestring($image, 4, $x_end - (strlen($string) * $char_width) / 2, $y_end, $string, $black);



    // Add the titles for the x and y axes
    imagestring($image, 4, $x_start + ($x_end - $x_start) / 2 - strlen($title_x_axis) * $char_width / 2, $y_end, $title_x_axis, $black);

    imagestring($image, 4, $char_width, ($y_end - $y_start) / 2, $title_y_axis, $black);


    foreach($titles as $key => $value) {
      imagestring($image, 4, $char_width, 40 + ($key * 20), $value, $colour[$key]);
    }


    // Write image to a file and return the path to the file
    $filename = sprintf("%d.png", time());
    ImagePNG($image, $filename);
    ImageDestroy($image);
  }
?>
<?php
  $util = new cUtil();
    $util->SetTheme();

    $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);
    $theme->header("chris.iluo.net");
      $theme->menu($util->user->loginForm());

      $theme->main_begin();
        $theme->article_begin("Operating System");
          $titles = array();
          $points_x = array();
          $points_y = array();
          for ($line = 0; $line < 4; $line++) {
            $titles[$line] = $title_line;

            $points_x_line = array();
            $points_y_line = array();
            for ($i = 0; $i < 100; $i++) {
              $points_x_line[$i] = $i;
              $points_y_line[$i] = ($line * 50.0) + (0.1 * $i * $i);
            }

            $points_x[$line] = $points_x_line;
            $points_y[$line] = $points_y_line;
          }
          CreateGraph("Count", "Time", $points_x, $points_y, $titles, $filename);
          $theme->article_addline("<img src=\"$filename\" alt=\"Operating System Graph\" />");
        $theme->article_end();
        $theme->article_begin("Browser");
          CreateGraph("Count", "Time", $points_x, $points_y, $titles, $filename);
          $theme->article_addline("<img src=\"$filename\" alt=\"Browser Graph\" />");
        $theme->article_end();
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
