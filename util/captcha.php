<?PHP
  define("verify",  "monkeys");

  function captcha_printimage()
  {
    return '<p>
              To verify you are human, please enter the text as it appears in the image below<br />
              <image src="http://chris.iluo.net/images/verify.png">&nbsp;<input type="text" name="verify" size="12" />
            </p>';
  }

  function captcha_verify($request)
  {
    return (verify == $request);
  }
?>
