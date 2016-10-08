<?php	
	include ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

	$content_before_beautify = "";

	$util=new cUtil();
		$util->SetTheme();
		
		$theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);
		
    $theme->header("Beautify");
			$theme->menu($util->user->loginForm());

      $theme->main_begin();
					
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
						
						if(Captcha_Verify($_REQUEST['verify']))
						{
							if ("beautify"==$action)
							{
								$language = $_REQUEST['language'];
								if ("java" != $language &&
										"c++" != $language &&
										"php" != $language && 
										"sql" != $language) $language = "c++";
								
								$content_before_beautify = $_REQUEST['content'];
								
								if("" != $content_before_beautify)
								{
									$theme->article_begin("Beautiful");
					
										// We use this to tell the translate function what language we have submitted								
										$content = "<code class=\"" . $language . "\">" . $content_before_beautify . "</code>";
										
										// Now do the actual translation
										$content = translate($content);
										
										// Now print it out, first in code tags to show what it looks like, then print out the source
										$theme->article_addline_raw("<code class=\"" . $language . "\">" . $content . "</code>");
									$theme->article_end();
									$theme->article_begin("Source Code");
										$theme->article_addline($content);
									$theme->article_end();
								}
								else
									$theme->article_addline("No Content Entered.  ");
							}
						}
						else
						{
							$theme->article_begin("Submission Error");
							$theme->article_addline("<p>Incorrect verification string.  </p>");
							$theme->article_end();
						}
					}
					
			$theme->article_begin("Beautify");
					$str = '<form name="form1" method="post" action="http://chris.iluo.net/beautify/index.php?action=beautify">
										<fieldset>
											<p>
												Code<br />
												<textarea rows="12" cols="40" name="content">' . $content_before_beautify .'</textarea>
												Language<br />
												<select name="language">
													<option value="c++" selected="selected">C++</option>
													<option value="php">PHP</option>
													<option value="java">Java</option>
													<option value="sql">SQL</option>
												</select>
											</p>' . Captcha_PrintImage() . '<p>
												<input type="submit" name="Beautify" value="Beautify" onClick="this.disabled=1; this.form.submit();" />
											</p>
										</fieldset>
									</form>';
					$theme->article_addline($str);
					
				$theme->article_end();
			$theme->main_end();
			
    $theme->footer();

	$util->Delete();
?>
