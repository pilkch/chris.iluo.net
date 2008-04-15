<?php
  define("icon_add",    "add.png");
  define("icon_delete", "delete.png");
  define("icon_edit",   "edit.png");

  require_once ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');
  require_once ($_SERVER['DOCUMENT_ROOT'] . '/triki_include/cTrikiPage.php');
  require_once ($_SERVER['DOCUMENT_ROOT'] . '/triki_include/cTrikiSection.php');

  // This is here because it really doesn't require a whole class to print this stuff
  // Print single page
  function PrintPage($result)
  {
    global $util;
    global $theme;

    $page_parentid=mysql_result($result, 0, "page_parentid");
    $page_title=mysql_result($result, 0, "page_title");
    $page_description=mysql_result($result, 0, "page_description");
    $page_content=mysql_result($result, 0, "page_content");
    $page_footnotes=mysql_result($result, 0, "page_footnotes");
    $page_references=mysql_result($result, 0, "page_references");
    $page_related=mysql_result($result, 0, "page_related");
    $page_links=mysql_result($result, 0, "page_links");


    //Article format:
    //Article parent
    //Article title, content etc.
    $theme->article_addline("<ul>");

      $theme->article_addline("<li>");

        //Get the parent of this section
        if(0==$page_parentid)
        {
          $theme->article_addline("<h2><a href=\"/triki/\">Triki</a></h2>");
        }
        else
        {
          $result=$util->db->Select("triki_section", "", "`section_id`='$page_parentid'"); //, "ORDER BY `section_parentid` ASC, `section_id` ASC");
          $num=$util->db->GetRows($result);

          if($num)
          {
            $parent_title=mysql_result($result,0,"section_title");
            $parent_title_lower=strtolower($parent_title);
            $theme->article_addline("<h2><a href=\"/triki/" . $parent_title_lower . "\">" . $parent_title . "</a></h2>");
          }
        }

        $theme->article_addline("<ul>");

          $theme->article_addline("<li>");

            $article="<h2><strong>" . $page_title . "</strong></h2>\n";

            if($page_description)
              $article.=$util->FormatContent($page_description);

            //TODO: Insert contents with links etc. here
            //$article.=$article . "<h2>Replace this with contents index</h2>\n";

            $article.=$util->FormatContent($page_content);

            if($page_footnotes)
            {
              $article.="<h2>Footnotes</h2>\n" .
                $util->FormatContent($page_footnotes);
            }

            if($page_references)
            {
              $article.="<h2>References</h2>\n" .
                $util->FormatContent($page_references);
            }

            if($page_related)
            {
              $article.="<h2>Related Articles</h2>\n" .
                $util->FormatContent($page_related);
            }

            if($page_links)
            {
              $article.="<h2>External Links</h2>\n" .
                $util->FormatContent($page_links);
            }

            $theme->article_addline($article);


          $theme->article_addline("</li>");
        $theme->article_addline("</ul>");
      $theme->article_addline("</li>");
    $theme->article_addline("</ul>");
  }

  function PrintNotFound()
  {
    global $util;
    global $theme;

    $theme->article_addline("<h2>" . $util->query . "</h2>\n" .
      "<p>Triki does not have an article with this exact name.<br />" .
      "<br />" .
      "You can<br />" .
      "<ul>" .
        "<li>Try browsing <a href=\"http://chris.iluo.net/triki\">categorically</a>.</li>" .
        "<li><a href=\"http://chris.iluo.net/triki/" . $util->query . "&action=request\">Request</a> that this article be written.</li>");

    if($util->user->isAdmin())
      $theme->article_addline("<li><a href=\"http://chris.iluo.net/triki/" . $util->query . "&action=edit\">Create</a> this article.</li>");
    else
      $theme->article_addline("Log in or <a href=\"http://chris.iluo.net/create_user.php\">create</a> an account to start the " . $util->query . " article.</li>");

    $theme->article_addline("</ul>");
    $theme->article_addline("</p>");
  }


  $util = new cUtil();

    $util->SetTheme();

    $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);

    if($util->query) $theme->header($util->query . " - Triki");
    else $theme->header("Triki");

      $theme->menu($util->user->loginForm());

      $theme->main_begin();

        if($util->query)
        {
          $util->query=rawurldecode($util->query);
          $util->query=ucwords(strtolower($util->query));


          $array = explode("&action=", $util->query);

          $util->query=$array[0];

          if(count($array)>0)
            $action=$array[1];

          /*Possible Actions
            "request"
            "edit"
            "save"

            else NULL or unexpected action results in
            NULL
          */

          if("request"==$action)
          {
            $theme->article_begin("Request - " . $util->query);

            //Search in the articles first
            $result=$util->db->Select("triki_page", "", "`page_title`='$util->query'", "ORDER BY `page_parentid` ASC, `page_id` ASC");
            $num=$util->db->GetRows($result);

            if(0!=$num)
            {
              $util->EmailAdmin("Request", "A request was made to upgrade article <a href=\"http://chris.iluo.net/triki/" . $util->query . "\">" . $util->query . "</a>");

              $theme->article_addline("Request has been sent to upgrade this article");
              PrintPage($result);
            }
            else
            {
              $pRoot = new cTrikiSection();
              if($pRoot->LoadSection(-1, $util->query))
              {
                $util->EmailAdmin("Request", "A request was made to upgrade section <a href=\"http://chris.iluo.net/triki/" . $util->query . "\">" . $util->query . "</a>");

                $theme->article_addline("Request has been sent to upgrade this section");

                $pRoot->PrintSection();
              }
              else
              {
                $util->EmailAdmin("Request", "A request was made to create article/section <a href=\"http://chris.iluo.net/triki/" . $util->query . "\">" . $util->query . "</a>");

                $theme->article_addline("Request has been sent to create this article/section");
              }
            }
          }
          else if("edit"==$action)
          {
            if("ADMIN"==$util->user->type)
            {
              $theme->article_begin("Edit - " . $util->query);

              $page_or_section=0;

              $page_id="";
              $page_parentid="";
              $page_title="";
              $page_description="";
              $page_content="";
              $page_footnotes="";
              $page_references="";
              $page_related="";
              $page_links="";

              //Search in the articles first
              $result=$util->db->Select("triki_page", "", "`page_title`='$util->query'", "ORDER BY `page_parentid` ASC, `page_id` ASC");
              $num=$util->db->GetRows($result);

              if($num)
              {
                $page_or_section=1;

                //TODO: Fill out form details with page title, parentid etc.
                //PrintPage($result);
              }
              else
              {
                //Search in the sections
                $result=$util->db->Select("triki_section", "", "`section_title`='$util->query'", "ORDER BY `section_parentid` ASC, `section_id` ASC");
                $num=$util->db->GetRows($result);

                if($num)
                {
                  $page_or_section=2;

                  //TODO: Fill out form details with section title, parentid etc.
                  //PrintResults($result);
                }
              }

              //We want to print the section form
              if(2!=$page_or_section)
              {
                /*section_parentid, section_title  char, section_description*/
                echo '<h2>Section</h2>
                      <form name="form1" method="post" action="http://chris.iluo.net/triki/' . $util->query . '&action=save">
                        <fieldset>
                          <input type="text" name="page_title" size="40" /><br />
                          <input type="submit" name="Submit" value="Save as a Section" />
                        </fieldset>
                      </form>' . "\n";
              }

              //We want to print the page form
              if(1!=$page_or_section)
              {
                //TODO: Selectable parent from a drop down list
                /*$page_parentid*/

                echo '<h2>Page</h2>
                      <form name="form1" method="post" action="http://chris.iluo.net/triki/' . $util->query . '&action=save">
                        <fieldset>
                          Title <input type="text" name="page_title" size="40" value="' . $page_title . '" /><br />
                          Description <textarea wrap="soft" style="width: 99%;" name="page_description" rows="3" cols="50" value="' . $page_description . '"></textarea>
                          Content <textarea wrap="soft" style="width: 99%;" name="page_content" rows="20" cols="50" value="' . $page_content . '"></textarea>
                          Footnotes <textarea wrap="soft" style="width: 99%;" name="page_footnotes" rows="20" cols="50" value="' . $page_footnotes . '"></textarea>
                          References <textarea wrap="soft" style="width: 99%;" name="page_references" rows="20" cols="50" value="' . $page_references . '"></textarea>
                          Related <textarea wrap="soft" style="width: 99%;" name="page_related" rows="20" cols="50" value="' . $page_related . '"></textarea>
                          Links <textarea wrap="soft" style="width: 99%;" name="page_links" rows="20" cols="50" value="' . $page_links . '"></textarea>
                          <input type="submit" name="Submit" value="Save as a Page" />
                        </fieldset>
                      </form>' . "\n";
              }
            }
            else
            {
              $theme->article_begin($util->query);
              $theme->article_addline("You are not authorised to edit this article");
            }
          }
          else if("save"==$action)
          {
            if("ADMIN"==$util->user->type)
            {
              $theme->article_begin("Save - " . $util->query);

              //TODO: Save entry

              //if(article)
              $theme->article_addline("Article Saved");
              //else
              //$theme->article_addline("Section Saved");
            }
            else
            {
              $theme->article_begin($util->query);
              $theme->article_addline("You are not authorised to save or edit this article");
            }
          }
          else
          {
            $theme->article_begin($util->query);

            //Search in the articles first
            $result=$util->db->Select("triki_page", "", "`page_title`='$util->query'", "ORDER BY `page_parentid` ASC, `page_id` ASC");
            $num=$util->db->GetRows($result);

            if($num)
              PrintPage($result);
            else
            {
              $pRoot = new cTrikiSection();
              if($pRoot->LoadSection(-1, $util->query))
                $pRoot->PrintSection();
              else
                PrintNotFound();
            }
          }

          $theme->article_end();
        }
        else //Article not requested, show welcome page with categories
        {
          $theme->article_begin("Triki");

            $theme->article_addline("<ul>");

              $theme->article_addline("<li>");
                $theme->article_addline("<h2><strong>Triki</strong></h2>");
                $theme->article_addline("<p>Wikipedia iluo.net style</p>");

                $theme->article_addline("<ul>");

                  //Child pages
                  $result=$util->db->Select("triki_page", "", "`page_parentid`='0'");//, "ORDER BY `page_parentid` ASC, `page_id` ASC");
                  $page_num=$util->db->GetRows($result);

                  for($i=0;$i<$page_num;$i++)
                  {
                    $tempPage=new cTrikiPage(
                      mysql_result($result,$i,"page_id"),
                      mysql_result($result,$i,"page_parentid"),
                      mysql_result($result,$i,"page_title"),
                      mysql_result($result,$i,"page_description"),
                      mysql_result($result,$i,"page_content"),
                      mysql_result($result,$i,"page_footnotes"),
                      mysql_result($result,$i,"page_references"),
                      mysql_result($result,$i,"page_related"),
                      mysql_result($result,$i,"page_links"));

                    $theme->article_addline("<li>");
                    $theme->article_addline("<a href=\"/triki/" . strtolower($tempPage->page_title) . "\">" . $tempPage->page_title . "</a>");
                    $theme->article_addline("</li>");
                  }



                    //Get the child sections of this section
                    $result=$util->db->Select("triki_section", "", "`section_parentid`='0'", "ORDER BY `section_parentid` ASC, `section_id` ASC");
                    $num=$util->db->GetRows($result);

                    //If there are any left, ride 'em up cowboy
                    for($i=0;$i<$num;$i++)
                    {
                      $tempSection=new cTrikiSection(
                        mysql_result($result,$i,"section_id"),
                        mysql_result($result,$i,"section_parentid"),
                        mysql_result($result,$i,"section_title"),
                        mysql_result($result,$i,"section_description"));
                      $tempSection->PrintSection($theme);
                    }

                $theme->article_addline("</ul>");

              $theme->article_addline("</li>");

            $theme->article_addline("</ul>");

          $theme->article_end();
        }

      $theme->main_end();
    $theme->footer();

  $util->Delete();
?>
