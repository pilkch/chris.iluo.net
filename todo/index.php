<?php

  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  function GetStatus($status)
  {
    $status_string = array(
      'not_started' => 'Not Started',
      'in_progress' => 'In Progress',
      'complete' => 'Complete',
      'cancelled' => 'Cancelled',
      'work_halted' => 'Work Halted');

    return $status_string[$status];
  }

  function ActionAdd($theme)
  {
    return $theme->form_action("add$id", "Add", "todo/index.php", "add$id", icon_add);
  }

  function ActionRemove($theme, $id)
  {
    return $theme->form_action("remove$id", "Remove", "todo/index.php", "remove&id=$id", icon_delete);
  }

  function ActionPriorityRaise($theme, $id)
  {
    return $theme->form_action("priority_raise$id", "Raise Priority", "todo/index.php", "priority_raise&id=$id", icon_priority_raise);
  }

  function ActionPriorityLower($theme, $id)
  {
    return $theme->form_action("priority_lower$id", "Lower Priority", "todo/index.php", "priority_lower&id=$id", icon_priority_lower);
  }

  function ActionEdit($theme, $id)
  {
    return $theme->form_action("edit$id", "Edit", "todo/index.php", "edit&id=$id", icon_edit);
  }


  function PrintAddForm($theme, $add_action, $add_text)
  {
    return '<form name="' . $add_action . '" method="post" action="http://chris.iluo.net/todo/index.php?action=' . $add_action . '">
              <tr>
                <td>-</td>
                <td><input text cols="80" id="content" name="content" style="width:100%"/></td>
                <td>-</td>
                <td>' . $theme->form_submitButton($add_text, $add_action, icon_add) . '

                </td>
              </tr>
            </form>';
  }

  function PrintLine($util, $theme, $priority, $id, $content, $status, $bIsInEditMode)
  {
    if ($util->user->isAdmin() && $bIsInEditMode)
    {
      return "<tr>" .
                "<td>" . $priority . "</td>" .
                "<td>" . $content . "</td>" .
                "<td>" . GetStatus($status) . "</td>" .
                "<td><table><tr><td>" . ActionPriorityRaise($theme, $id) .
                  "</td><td>" . ActionPriorityLower($theme, $id) .
                  "</td><td>" . ActionRemove($theme, $id) .
                  "</td><td>" . ActionEdit($theme, $id) . "</td></tr></table></td>" .
              "</tr>";
    }

    return "<tr>" .
              "<td>" . $priority . "</td>" .
              "<td>" . $content . "</td>" .
              "<td>" . GetStatus($status) . "</td>" .
            "</tr>";
  }

  // This is for displaying the line that we wish to edit
  function PrintEditableLineForm($util, $theme, $priority, $id, $content, $status)
  {
    $action = "edit_save";
    return '<form name="' . $action . '" method="post" action="http://chris.iluo.net/todo/index.php?action=' . $action . '">
              <input type="hidden" id="id" name="id" value="' . $id . '"/>
              <tr>
                <td>' . $priority . '</td>
                <td><input text cols="80" id="content" name="content" value="' . $content . '" style="width:100%"/></td>
                <td>' . GetStatus($status) . '</td>
                <td>' . $theme->form_submitButton("Save", $action, icon_edit) . '

                </td>
              </tr>
            </form>';
  }

  function Swap($util, $theme, $id1, $id2)
  {
    // Priority1
    $result = $util->db->Select("todo", "", "todo_id = '$id1'");
    $num = $util->db->GetRows($result);
    if ($num == 0) return;
    $priority1 = mysql_result($result, 0, "todo_priority");


    // Priority2
    $result = $util->db->Select("todo", "", "todo_id = '$id2'");
    $num = $util->db->GetRows($result);
    if ($num == 0) return;
    $priority2 = mysql_result($result, 0, "todo_priority");


    // Ok, now do our updates
    $util->db->Query("UPDATE todo SET todo_priority='$priority1' WHERE todo_id='$id2'");
    $util->db->Query("UPDATE todo SET todo_priority='$priority2' WHERE todo_id='$id1'");
  }

  function FindAndSwap($util, $id, $compare, $sort)
  {
    // Get our priority
    $result = $util->db->Select("todo", "", "todo_id = '$id'");
    $num = $util->db->GetRows($result);

    if ($num > 0)
    {
      $our_priority = mysql_result($result, 0, "todo_priority");

      // Get the next priority
      $result = $util->db->Select("todo", "", "todo_priority $compare '$our_priority'", " ORDER BY `todo_priority` $sort");
      $num = $util->db->GetRows($result);

      if ($num > 0)
      {
        $next_id = mysql_result($result, 0, "todo_id");
        Swap($util, $theme, $id, $next_id);
      }
    }
  }



  $util=new cUtil();
    $util->SetTheme();

    $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);

    $theme->header("TODO");
      $theme->menu($util->user->loginForm());

      $theme->main_begin();
        $theme->article_begin("TODO");

          if ($util->user->isAdmin())
            $theme->article_addline("<table border=\"1\">" .
              "<tr><td><strong>Priority</strong></td><td><strong>Content</strong></td><td><strong>Status</strong></td><td><strong>Action</strong></td></tr>");
          else
            $theme->article_addline("<table border=\"1\">" .
              "<tr><td><strong>Priority</strong></td><td><strong>Content</strong></td><td><strong>Status</strong></td></tr>");

          if($util->query)
          {
            $util->query=rawurldecode($util->query);
            $util->query=ucwords(strtolower($util->query));

            $array = explode("?action=", $util->query);

            $util->query=$array[0];

            if (count($array) > 0)
            {
              $array = explode("&", $array[1]);
              $action = $array[0];

              if (count($array) > 0)
              {
                $array = explode("=", $array[1]);
                $id = $array[1];
              }
            }

            $str = "";
            for ($i = 0; $i < count($array); $i++)
              $str .= " $i=\"$array[$i]\"";
            $theme->article_addline("QUERY=$util->query, paramters=$str");


            if ($util->user->isAdmin())
            {
              if ("add_top"==$action)
              {
                $todo_content = $_REQUEST['content'];
                $theme->article_addline("add_top " . $util->query . " content=" . $todo_content);

                if($todo_content != "")
                {
                  // Find out if we have any other entries with priority 0, if we do, increment the whole table
                  $result = $util->db->Select("todo", "", "", " ORDER BY `todo_priority` ASC");
                  $num = $util->db->GetRows($result);

                  // Increment all priorities
                  if (($num > 0) && (mysql_result($result, 0, "todo_priority") < 1))
                    $util->db->Query("UPDATE todo SET todo_priority=todo_priority + 1 WHERE 1");

                  $result = $util->db->query("INSERT INTO todo (todo_id, todo_content, todo_priority) " .
                    "VALUES ('', '$todo_content', '0');");
                } else $theme->article_addline("No Content Entered.  ");
              }
              else if("add_bottom" == $action)
              {
                $todo_content = $_REQUEST['content'];
                $theme->article_addline("add_bottom " . $util->query . " content=" . $todo_content);

                if($todo_content != "")
                {
                  // Now find our last priority and add one to it;
                  $result = $util->db->Select("todo", "", "", " ORDER BY `todo_priority` DESC");
                  $num = $util->db->GetRows($result);

                  if ($num > 0) $todo_priority = mysql_result($result, 0, "todo_priority") + 1;
                  else $todo_priority = 0;

                  $result = $util->db->query("INSERT INTO todo (todo_id, todo_content, todo_priority) " .
                    "VALUES ('', '$todo_content', '$todo_priority');");

                } else $theme->article_addline("No Content Entered.  ");
              }
              else if("remove"==$action)
              {
                // Get our priority
                $result = $util->db->Select("todo", "", "todo_id = '$id'");
                $num = $util->db->GetRows($result);

                if ($num > 0) $todo_priority = mysql_result($result, 0, "todo_priority");
                else $todo_priority = 0;

                // Remove this id
                $util->db->Remove("todo", "todo_id = '$id'");

                // Shift all priorities after this one up one
                $util->db->Query("UPDATE todo SET todo_priority=todo_priority - 1 WHERE todo_priority > '$todo_priority'");
              }
              else if("edit"==$action)
              {
                $theme->article_addline("edit " . $util->query);
              }
              else if("edit_save"==$action)
              {
                $todo_content = $_REQUEST['content'];
                $todo_id = $_REQUEST['id'];

                if(($todo_id != "") && ($todo_content != "")) {
                  // Now just update our row
                  $result = $util->db->query("UPDATE `todo` SET `todo_content` = '" . $todo_content . "' WHERE `todo_id` = " . $todo_id . " LIMIT 1;");
                } else $theme->article_addline("No Content Entered.  ");
              }
              else if("priority_raise"==$action)
              {
                FindAndSwap($util, $id, "<", "DESC");
              }
              else if("priority_lower"==$action)
              {
                FindAndSwap($util, $id, ">", "ASC");
              } else $theme->article_addline("no action " . $util->query);
            }
          }

          $bIsInEditMode = ("edit" == $action);

          if (!$bIsInEditMode && $util->user->isAdmin()) $theme->article_addline(PrintAddForm($theme, "add_top", "Add to top of list"));

          {
            $result=$util->db->Select("todo", "", "", " ORDER BY `todo_priority` ASC");
            $num=$util->db->GetRows($result);

            $str = "id=" . $id;

            for ($i = 0; $i < $num; $i++) {
              $todo_priority = mysql_result($result, $i, "todo_priority") + 1;
              $todo_id = mysql_result($result, $i, "todo_id");
              $todo_content = mysql_result($result, $i, "todo_content");
              $todo_status = mysql_result($result, $i, "todo_status");

              if ($bIsInEditMode && ($todo_id == $id)) $str .= PrintEditableLineForm($util, $theme, $todo_priority, $todo_id, $todo_content, $todo_status);
              else $str .= PrintLine($util, $theme, $todo_priority, $todo_id, $todo_content, $todo_status, !$bIsInEditMode);
            }

            $theme->article_addline($str);
          }

          if (!$bIsInEditMode && $util->user->isAdmin()) $theme->article_addline(PrintAddForm($theme, "add_bottom", "Add to end of list"));

          $theme->article_addline("</table>");

        $theme->article_end();
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
