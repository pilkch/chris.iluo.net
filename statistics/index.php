<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  // http://php.net/manual/en/function.imagecreate.php

  function CreateGraph($title_y_axis, $title_x_axis, $points_y, $titles, &$filename)
  {
    // How many graphs
    $graph_n = count($titles);

    // How many points in each graph
    $points_n = count($points_y[0]);

    // Find the minimum and maximum points on the graph
    $min_x = 9e99;
    $min_y = 9e99;
    $max_x = -9e99;
    $max_y = -9e99;

    for ($i = 0; $i < $points_n; $i++) {
      $this_y = 0;

      for ($j = 0; $j < $graph_n; $j++) {
        $this_y += $points_y[$j][$i];
      }

      if ($this_y > $max_y) $max_y = $this_y;
    }

    $min_x = 0;
    $min_y = 0;
    $max_x = $points_n;

    $left = 0;
    $top = 0;
    $x_size = $max_x;
    $y_size = 400;

    $char_width = 8;
    $char_height = 11;

    $x_start = $left + 100;
    $y_start = $top + $char_height * 1.5;
    $x_end = $x_start + $x_size;
    $y_end = $y_start + $y_size;
    $right = $x_start + $x_size + 40;
    $bottom = $y_start + $y_size + $char_height * 1.5;


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

    for ($i = 0; $i < $points_n; $i++) {
      $last_y = $y_end;

      for ($j = 0; $j < $graph_n; $j++) {
        $pt_x = $x_start + $i;
        $pt_y = $last_y - ($y_end - $y_start) * ($points_y[$j][$i] - $min_y) / ($max_y - $min_y);

        imageline($image, $pt_x, $last_y, $pt_x, $pt_y, $colour[$j]);

        $last_y = $pt_y;
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

        $seconds_in_a_day = 60 * 60 * 24;
        $now = time();
        $duration_n = 365;
        $interval = 7; // 7 day interval between samples

        $theme->article_begin("Operating System");
          {
            $titles = array("Fedora", "MacOS", "Ubuntu", "Windows");
            $points_y = array();
            $totals = array();
            $n = count($titles);
            for ($line = 0; $line < $n; $line++) {
              $title = $titles[$line];
              $totals[$line] = 0;

              $points_y_line = array();
              for ($i = 0; $i < $duration_n; $i++) {
                $start = date("ymdHis", $now - ($seconds_in_a_day * ($duration_n - $i + 1)));
                $end = date("ymdHis", $now - ($seconds_in_a_day * ($duration_n - $i)));

                // Count the number on this day
                $result = $util->db->Select("counter", "DISTINCT counter_ip", "counter_os = '$title' AND counter_timestamp > '$start' AND counter_timestamp < '$end'");
                $num = $util->db->GetRows($result);

                $points_y_line[$i] = $num;
                $totals[$line] += $num;
              }

              $points_y[$line] = $points_y_line;
            }

            // Sort the arrays in descending order
            for ($x = 0; $x < $n; $x++) {
              for ($y = 0; $y < $n; $y++) {
                if ($totals[$x] > $totals[$y]) {
                  // Swap the titles
                  $hold = $titles[$x];
                  $titles[$x] = $titles[$y];
                  $titles[$y] = $hold;

                  // Swap the points y
                  $hold = $points_y[$x];
                  $points_y[$x] = $points_y[$y];
                  $points_y[$y] = $hold;

                  // Swap the totals
                  $hold = $totals[$x];
                  $totals[$x] = $totals[$y];
                  $totals[$y] = $hold;
                }
              }
            }

            // Create the graph
            CreateGraph("Count", "Date", $points_y, $titles, $filename);
            $theme->article_addline("<img src=\"$filename\" alt=\"Operating System Graph\" />");
          }
        $theme->article_end();
        $theme->article_begin("Browser");
          {
            $titles = array("Chrome", "Firefox", "Internet Explorer", "Opera", "Safari");
            $points_y = array();
            $totals = array();
            $n = count($titles);
            for ($line = 0; $line < $n; $line++) {
              $title = $titles[$line];
              $totals[$line] = 0;

              $points_y_line = array();
              for ($i = 0; $i < $duration_n; $i++) {
                $start = date("ymdHis", $now - ($seconds_in_a_day * ($i + 1)));
                $end = date("ymdHis", $now - ($seconds_in_a_day * $i));

                // Count the number on this day
                $result = $util->db->Select("counter", "DISTINCT counter_ip", "counter_browser = '$title' AND counter_timestamp > '$start' AND counter_timestamp < '$end'");
                $num = $util->db->GetRows($result);

                $points_y_line[$i] = $num;
                $totals[$line] += $num;
              }

              $points_y[$line] = $points_y_line;
            }

            // Sort the arrays in descending order
            for ($x = 0; $x < $n; $x++) {
              for ($y = 0; $y < $n; $y++) {
                if ($totals[$x] > $totals[$y]) {
                  // Swap the titles
                  $hold = $titles[$x];
                  $titles[$x] = $titles[$y];
                  $titles[$y] = $hold;

                  // Swap the points y
                  $hold = $points_y[$x];
                  $points_y[$x] = $points_y[$y];
                  $points_y[$y] = $hold;

                  // Swap the totals
                  $hold = $totals[$x];
                  $totals[$x] = $totals[$y];
                  $totals[$y] = $hold;
                }
              }
            }

            // Create the graph
            CreateGraph("Count", "Date", $points_y, $titles, $filename);
            $theme->article_addline("<img src=\"$filename\" alt=\"Browser Graph\" />");
          }
        $theme->article_end();
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
