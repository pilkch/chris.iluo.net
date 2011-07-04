<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util=new cUtil();
    $util->SetTheme();

    $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);
    $theme->header("chris.iluo.net");
      $theme->menu($util->user->loginForm());

      $theme->main_begin();
        $theme->article_begin("Tests Submission");

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
        $theme->article_end();
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
