<?PHP
  include_once $_SERVER['DOCUMENT_ROOT'] . "/util/user.php";

  class cLogin
  {
    var $id;
    var $user;
    var $pass;
    var $email;
    var $theme;
    var $type;
    var $typestr;
    var $util;
    var $form;
    var $bLoggedIn;

    function cLogin($util)
    {
      $this->util=$util;
      $this->id=(int)-1;
      $this->user="";
      $this->pass="";
      $this->email="";
      $this->theme="";
      $this->type="GUEST";
      $this->typestr='Guest';
      $this->form="Error No Form Selected";
      $this->bLoggedIn=false;


      //Modes
      if($_REQUEST['logout'])
      {
        //Attempting to logout
        $this->user="";
        $this->pass="";
        $this->bLoggedIn=false;

        session_destroy();

        $this->form=$this->formNormal();
      }
      else if(session_is_registered('user') && session_is_registered('pass'))
      {
        //Already logged in and now just browsing
        $this->user=$_SESSION['user'];
        $this->pass=$_SESSION['pass'];

        if($this->findUser($this->user, $this->pass))
        {
          $this->form=$this->formLoggedIn();
          $this->bLoggedIn=true;
        }
        else
        {
          $this->form=$this->formLoginFailed();
          $this->bLoggedIn=false;
        }
      }
      else if($_REQUEST['user'] && $_REQUEST['pass'])
      {
        //Attempting to login
        $this->user = $_REQUEST['user'];
        $this->pass = SHA1($_REQUEST['pass']);

        if($this->findUser($this->user, $this->pass))
        {
          session_register("user");
          session_register("pass");
          $_SESSION['user']=$this->user;
          $_SESSION['pass']=$this->pass;

          $this->form=$this->formLoggedIn();
          $this->bLoggedIn=true;
        }
        else
        {
          $this->form=$this->formLoginFailed();
          $this->bLoggedIn=false;
        }
      }
      else
      {
        //Just browsing
        $this->form=$this->formNormal();
        $this->bLoggedIn=false;
      }
    }

    function findUser($user, $pass)
    {
      $result=$this->util->db->Select("user", "", "`user_login`='$user' AND `user_pass`='$pass'");
      $num=$this->util->db->GetRows($result);

      if($num)
      {
        $this->id=mysql_result($result,0,"user_id");
        $this->user=mysql_result($result,0,"user_login");
        $this->pass=mysql_result($result,0,"user_pass");
        $this->type=mysql_result($result,0,"user_type");

        if($this->type=="ADMIN")
          $this->typestr="Administrator";
        else
          $this->typestr="User";

        return true;
      }
      else
      {
        $this->type="GUEST";
        $this->typestr="Guest";
        return false;
      }
    }

    function formNormal()
    {
      if("/index.php" != $this->util->full_query)
        $s=$this->util->full_query;

      return '<strong>Not logged in</strong><br />

                <div>
                  <form id="form1" method="post" action="http://chris.iluo.net' . $s . '">
                    <fieldset>
                      <input type="text" name="user" size="16" /><br />
                      <input type="password" name="pass" size="16" maxlength="20" /><br />
                      <input type="submit" name="Submit" value="Login" />
                    </fieldset>
                  </form>
                  <a href="http://chris.iluo.net/create_user.php">Create a new account</a>
                </div>';
    }

    function formLoggedIn()
    {
      if("/index.php" != $this->util->full_query)
        $s=$this->util->full_query;

      return '<strong>Logged in</strong><br />
                <div>' .
                  $this->user . '<br />' .
                  $this->type . '<br />' .
                  $this->typestr . '<br />
                  <form id="form1" method="post" action="' . "http://chris.iluo.net" . $s . '">
                    <fieldset>
                      <input type="submit" name="logout" value="Logout" />
                    </fieldset>
                  </form>
                </div>';
    }

    function formLoginFailed()
    {
      if("/index.php" != $this->util->full_query)
        $s=$this->util->full_query;

      return '<strong>Username or Password Incorrect</strong><br />
                <strong>Not logged in</strong><br />

                <div>
                  <form id="form1" method="post" action="http://chris.iluo.net' . $s . '">
                    <fieldset>
                      <input type="text" name="user" size="16" /><br />
                      <input type="password" name="pass" size="16" maxlength="20" /><br />
                      <input type="submit" name="Submit" value="Login" />
                    </fieldset>
                  </form><br />
                  <a href="http://chris.iluo.net/create_user.php">Create a new account</a>
                </div>';
    }

    function loginForm()
    {
      return $this->form;
    }

    function isLoggedIn()
    {
      return $this->bLoggedIn;
    }

    function isAdmin()
    {
      return $this->type=="ADMIN";
    }
  }
?>
