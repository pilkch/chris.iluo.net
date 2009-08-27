<?PHP
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  // Pre PHP 5.3.0
  function strstr_before($haystack, $needle)
  {
    return substr($haystack, 0, strpos($haystack, $needle));
  }

  // Pre PHP 5.3.0
  function strstr_after($haystack, $needle)
  {
    return substr($haystack, strpos($haystack, $needle) + strlen($needle));
  }


  // This contains some interesting lines, so hopefully this will still work
  $util = new cUtil();

    // "/update_counter_from_wordpress.php?ip=60.241.63.19&host=..."
    $full_request = $util->GetRequest();


    // "60.241.63.19&host=..."
    $parameters = strstr_after($full_request, "/update_counter_from_wordpress.php?ip=");

    // "60.241.63.19&host=..."
    $ip = strstr_before($parameters, "&host=");


    // "&host=..."
    $parameters = strstr_after($parameters, "&host=");

    $host = strstr_before($parameters, "&referer=");


    // "&referer=..."
    $parameters = strstr_after($parameters, "&referer=");

    $referer = strstr_before($parameters, "&request=");
    $referer = urldecode($referer);


    // "&request=..."
    $parameters = strstr_after($parameters, "&request=");

    $request = strstr_before($parameters, "&user_agent=");
    $request = urldecode($request);


    // "&user_agent=..."
    $parameters = strstr_after($parameters, "&user_agent=");

    $user_agent = $parameters;
    $user_agent = urldecode($user_agent);


    // Add an entry to the counter table
    $util->AddCounterEntry($ip, $host, $referer, $request, $user_agent);

  $util->Delete();
?>
