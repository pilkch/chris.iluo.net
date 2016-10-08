<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util=new cUtil();
    $util->SetTheme();

    $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);

    $theme->header("Dropbox query=" . $util->query);
      $theme->menu($util->user->loginForm());

      $theme->main_begin();
        $theme->article_begin("Dropbox");

          if($util->user->isAdmin())
          {
            $str = '<form name="form1" method="post" action="http://chris.iluo.net/dropbox/index.php?action=email">
                      <fieldset>
                        <p>
                          <input type="submit" name="Email" value="Email Now" onClick="this.disabled=1; this.form.submit();" />
                        </p>
                      </fieldset>
                    </form>';
            $theme->article_addline($str);
          }

          if($util->query)
          {
            $util->query=rawurldecode($util->query);
            $util->query=ucwords(strtolower($util->query));


            $array = explode("?action=", $util->query);

            $util->query=$array[0];

            if(count($array)>0)
              $action=$array[1];

            if("email"==$action && $util->user->isAdmin())
            {
              // Send every entry via email
              $result=$util->db->Select("dropbox", "");
              $num=$util->db->GetRows($result);

              // Email
              if($num)
              {
                $message = "";

                for ($i = 0; $i < $num; $i++)
                {
                  $message .= "<p><table>" .
                    "<tr><td>Timestamp</td><td>" . mysql_result($result, $i, "dropbox_timestamp") . "</td></tr>" .
                    "<tr><td>IP</td><td>" . mysql_result($result, $i, "dropbox_ip") . "</td></tr>" .
                    "<tr><td>Host</td><td>" . mysql_result($result, $i, "dropbox_host") . "</td></tr>" .
                    "<tr><td>Text</td><td>" . str_replace("\n", "<br/>", mysql_result($result, $i, "dropbox_text")) . "</td></tr>" .
                    "</table>";
                }

                $util->EmailAdmin("Dropbox Automated Email - " . $util->GetTimef(), $message);

                $util->db->Remove("dropbox");
              }
            }
            else if("submit"==$action)
            {
              if(Captcha_Verify($_REQUEST['verify']))
              {
                $dropbox_text = $_REQUEST['dropbox'];

                if($dropbox_text != "")
                {
                  $dropbox_timestamp = date("ymdHis");
                  $dropbox_ip = $util->GetIP();
                  $dropbox_host = $util->GetHost();

                  $result=$util->db->query("INSERT INTO dropbox (dropbox_id, dropbox_timestamp, dropbox_ip, "
                    . "dropbox_host, dropbox_text) " .
                    "VALUES ('', '$dropbox_timestamp', '$dropbox_ip', '$dropbox_host', '$dropbox_text');");
                  $theme->article_addline("<p>Message submitted.  </p>");
                }
                else
                  $theme->article_addline("<p>Please enter some text.  </p>");
              }
              else
                $theme->article_addline("<p>Incorrect verification string.  </p>");
            }
          }

          // Send each that is older than a day via email
          {
            $seconds_in_a_day = 86400;
            $timestamp = date("ymd H:i:s");
            $past = $util->SubtractSecondsFromDate($timestamp, 2 * $seconds_in_a_day);

            $result=$util->db->Select("dropbox", "", "dropbox_timestamp < '$past'");
            $num=$util->db->GetRows($result);

            // Email
            if($num)
            {
              $message = "";

              for ($i = 0; $i < $num; $i++)
              {
                $message .= "<p><table>" .
                  "<tr><td>Timestamp</td><td>" . mysql_result($result, $i, "dropbox_timestamp") . "</td></tr>" .
                  "<tr><td>IP</td><td>" . mysql_result($result, $i, "dropbox_ip") . "</td></tr>" .
                  "<tr><td>Host</td><td>" . mysql_result($result, $i, "dropbox_host") . "</td></tr>" .
                  "<tr><td>Text</td><td>" . str_replace("\n", "<br/>", mysql_result($result, $i, "dropbox_text")) . "</td></tr>" .
                  "</table>";
              }

              $util->EmailAdmin("Dropbox Automated Email - " . $util->GetTimef(), $message);

              $past = date("Y-m-d", strtotime("-2 day"));
              $util->db->Remove("dropbox", "`dropbox_timestamp` < '$past'");
            }
          }

          $theme->article_addline("<p>Anything you type here will be sent to Chris, you are free to say anything you want; tips, questions, poems, stories, passwords, hate mail, movie recommendations, love, praise ...</p>");
          $str = '<form name="form1" method="post" action="http://chris.iluo.net/dropbox/index.php?action=submit">
                    <fieldset>
                      <p>
                        Message<br />
                        <textarea rows="12" cols="40" name="dropbox"></textarea>
                      </p>' . Captcha_PrintImage() . '<p>
                        <input type="submit" name="Submit" value="Drop" onClick="this.disabled=1; this.form.submit();" />
                      </p>
                    </fieldset>
                  </form>';
          $theme->article_addline($str);
        $theme->article_end();
      $theme->main_end();

    $theme->footer();

  $util->Delete();
?>
