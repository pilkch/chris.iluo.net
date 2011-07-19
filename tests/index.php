<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util=new cUtil();
    $util->SetTheme();

    $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);
    $theme->header("chris.iluo.net");
      $theme->menu($util->user->loginForm());

      $theme->main_begin();
        $theme->article_begin("Tests Submission");

          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['secret'] == "secret") {
              if (($_FILES["file"]["type"] == "application/json") && ($_FILES["file"]["size"] < 20000)) {
                if ($_FILES["file"]["error"] > 0) {
                  echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
                } else {
                  echo "Name: " . $_FILES["file"]["name"] . "<br />\n";
                  echo "Type: " . $_FILES["file"]["type"] . "<br />\n";
                  echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />\n";
                  echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />\n";
                  $fileText = $_FILES["file"]["tmp_name"];


                  echo "File=" . $fileText . "\n";
                  echo "Contents=" . file_get_contents($fileText) . "\n";
                  echo "Decoding JSON file" . "\n";
                  var_dump($util->JSONDecode(file_get_contents($fileText), true));
                  $json = $util->JSONDecode(file_get_contents($fileText), true);


                  // Clear our test tables
                  $result = $util->db->Remove("test_project");
                  $result = $util->db->Remove("test_target");
                  $result = $util->db->Remove("test_result");

                  // Add our new entries to the test tables
                  $projects = $json[projects];
                  var_dump($projects);
                  foreach ($projects as $project) {
                    $project_name = $project["name"];
                    $util->db->Add("test_project", "project_name", "'$project_name'");
                    $project_id = $util->db->GetLastInsertID();
                    foreach ($project["targets"] as $target) {
                      $target_name = $target["name"];
                      $util->db->Add("test_target", "target_name, target_projectid", "'$target_name', '$project_id'");
                      $target_id = $util->db->GetLastInsertID();
                      foreach ($target["results"] as $result) {
                        $result_name = $result["name"];
                        $result_state = $result["status"];
                        $util->db->Add("test_result", "result_name, result_state, result_targetid", "'$result_name', '$result_state', '$target_id'");
                      }
                    }
                  }
                }
              } else {
                $theme->article_addline("Invalid file");
              }
            } else {
              $theme->article_addline("Incorrect secret");
            }
          } else {
            $theme->article_addline("Results from <a href=\"https://github.com/pilkch/buildall\">buildall</a><br />");

            $theme->article_addline("<table border=\"1\">");

              $theme->article_addline("<tr><td><strong>Project</strong></td><td><strong>Target</strong></td><td><strong>Test</strong></td><td><strong>Result</strong></td></tr>");

              $result_project = $util->db->Select("test_project");
              $nProjects = $util->db->GetRows($result_project);

              for ($iProject = 0; $iProject < $nProjects; $iProject++) {
                $project_id = mysql_result($result_project, $iProject, "project_id");
                $project_name = mysql_result($result_project, $iProject, "project_name");

                $theme->article_addline("<tr><td>$project_name</td><td></td><td></td><td></td></tr>");
                $result_target = $util->db->Select("test_target", "", "`target_projectid`='$project_id'");
                $nTargets = $util->db->GetRows($result_target);

                for ($iTarget = 0; $iTarget < $nTargets; $iTarget++) {
                  $target_id = mysql_result($result_target, $iTarget, "target_id");
                  $target_name = mysql_result($result_target, $iTarget, "target_name");

                  $theme->article_addline("<tr><td></td><td>$target_name</td><td></td><td></td></tr>");
                  $result_result = $util->db->Select("test_result", "", "`result_targetid`='$target_id'");
                  $nResults = $util->db->GetRows($result_result);

                  for ($iResult = 0; $iResult < $nResults; $iResult++) {
                    $result_id = mysql_result($result_result, $iResult, "result_id");
                    $result_name = mysql_result($result_result, $iResult, "result_name");
                    $result_state = mysql_result($result_result, $iResult, "result_state");

                    $sResultContent = "-";
                    if ($result_state != "notrun") {
                      if ($result_state == "passed") $sText = "Passed";
                      else $sText = "Failed";
                      $sFile = $result_state;

                      $sResultContent = "<img alt=\"$sText\" title=\"$sText\" src=\"http://chris.iluo.net/images/status/" . $sFile . ".png\" width=\"16\" height=\"16\"/>";
                    }
                    $theme->article_addline("<tr><td></td><td></td><td>$result_name</td><td>$sResultContent</td></tr>");
                  }
                }
              }

            $theme->article_addline("</table>");
          }

        $theme->article_end();
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
