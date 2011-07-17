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
                  var_dump($util->JSONDecode(file_get_contents($fileText)));



                  // Clear our test tables
                  $result = $util->db->Remove("test_project");
                  $result = $util->db->Remove("test_target");
                  $result = $util->db->Remove("test_result");

                  // Add our new entries to the test tables
                  $result = $util->db->Add("test_project", "project_name", "'project'");
                  $project_id = $util->db->GetLastInsertID();

                  $theme->article_addline("project_id=$project_id");


                  $result = $util->db->Add("test_target", "target_name, target_projectid", "'target', '$project_id'");
                  $target_id = $util->db->GetLastInsertID();

                  $theme->article_addline("target_id=$target_id");


                  $result = $util->db->Add("test_result", "result_name, result_state, result_targetid", "'result', 'passed', '$target_id'");
                  $result_id = $util->db->GetLastInsertID();

                  $theme->article_addline("result_id=$result_id");

                  if ($result) $theme->article_addline("Succeeded");
                  else $theme->article_addline("Failed");
                }
              } else {
                $theme->article_addline("Invalid file");
              }
            } else {
              $theme->article_addline("Incorrect secret");
            }
          } else {
            $theme->article_addline("Use <a href=\"https://github.com/pilkch/buildall\">buildall</a> to submit test results to this page");
          }

        $theme->article_end();
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
