<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util=new cUtil();
    $util->SetTheme();

    $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);
    $theme->header("chris.iluo.net");
      $theme->menu($util->user->loginForm());

      $theme->main_begin();
        $theme->article_begin("Tests Submission");

          /*
          test_project
          project_id
          project_name

          test_target
          target_id
          target_name
          target_projectid

          test_result
          result_id
          result_name
          result_state
          result_targetid
          */

          // Clear our test tables
          $result = $util->db->Remove("test_project");
          $result = $util->db->Remove("test_target");
          $result = $util->db->Remove("test_result");

          // Add our new entries to the test tables
          $result = $util->db->Add("test_project", "project_name", "'name'");

          //$result=$this->db->query("INSERT INTO user (user_id, user_login, user_pass, user_type) " .
          // "VALUES ('', '$user', '$password', 'USER');");

          if ($result) $theme->article_addline("Succeeded");
          else $theme->article_addline("Failed");
        $theme->article_end();
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
