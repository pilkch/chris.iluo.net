<?PHP
  include_once $_SERVER['DOCUMENT_ROOT'] . "/util/database.php";

  class User
  {
    var $id;
    var $name;
    var $pass;
    var $email;
    var $theme;
    var $admin;

    function User($Id, $Name, $Pass, $Email, $Theme, $Admin=false)
    {
      $id=$Id;
      $name=$Name;
      $pass=$Pass;
      $email=$Email;
      $theme=$Theme;
      $admin=$Admin;
    }
  }
?>