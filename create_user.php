<?php
	require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

	$util=new cUtil();
		
		if(isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['passagain']))
		{
			$pass=$_POST['pass'];
			$user=$_POST['user'];
			if($pass==$_POST['passagain'])
			{
				$pass=sha1($pass);
				$result=$util->CreateUser($user, $pass);
				
				if(0==$result)
				{
					session_register("user");
					session_register("pass");
					$_SESSION['user']=$user;
					$_SESSION['pass']=$pass;
					header('Location: http://chris.iluo.net/');
				}
				else if(1==$result)
					$failed_select=true;
				else
					$failed_create=true;
			}
			else
				$failed_passwords=true;			
		}
	
		$util->SetTheme();
	    
		$theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);
    $theme->header("chris.iluo.net");
		$theme->menu("");

			$theme->main_begin();
				$theme->article_begin("Create User");
					if($failed_passwords)
						$theme->article_addline("<strong>Passwords did not match</strong><br />");
					if($failed_select)						
						$theme->article_addline("<strong>Username already in use</strong><br />");
					if($failed_create)
						$theme->article_addline("<strong>Could not add your account to the database</strong><br />");
						
					$theme->article_addline("<form name=\"fieldset1\" method=\"post\" action=\"http://chris.iluo.net/create_user.php\">");
					$theme->article_addline("<fieldset>");
					$theme->article_addline("Name <input type=\"text\" name=\"user\" size=\"16\" /><br />");
					$theme->article_addline("Password <input type=\"password\" name=\"pass\" size=\"16\" maxlength=\"20\" /><br />");
					$theme->article_addline("Password Again <input type=\"password\" name=\"passagain\" size=\"16\" maxlength=\"20\" /><br />");
					$theme->article_addline("<input type=\"submit\" name=\"Submit\" value=\"Create\" />");
					$theme->article_addline("</fieldset>");
					$theme->article_addline("</form>");
				$theme->article_end();
			$theme->main_end();
    $theme->footer();

	$util->Delete();   
?>
