<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util=new cUtil();
    $util->SetTheme();

    $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);
    $theme->header("chris.iluo.net");
      $theme->menu($util->user->loginForm());

      $theme->main_begin();
        $theme->article_begin("TODO");
          $theme->article_addline("<ol>");

            $result=$util->db->Select("todo", "", "", " ORDER BY `todo_priority` ASC");
            $num=$util->db->GetRows($result);

            for ($i = 0; $i < $num; $i++) {
              $todo_priority = mysql_result($result, $i, "todo_priority") + 1;
              $todo_id = mysql_result($result, $i, "todo_id");
              $todo_content = mysql_result($result, $i, "todo_content");
              $todo_status = mysql_result($result, $i, "todo_status");

              $theme->article_addline("<li>" . $todo_content . "</li>");
            }

          $theme->article_addline("</ol>");
        $theme->article_end();
        $theme->journal("");
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
