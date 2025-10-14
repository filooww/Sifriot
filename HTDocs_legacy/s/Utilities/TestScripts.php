<?php
function TestBool($b)
{
	if ($b) return "Y";
	else return "N";
}
function TestArray($k, $a, $o)
{
	if (is_array($a))
	{
		echo "<br>".str_repeat("--", $o).$k." =>";
		$os = $o + 2;
		foreach ($a as $ka => $va) TestArray($ka, $va, $os);
	}
	else
	{
		$f = false;
		switch (gettype($a))
		{	
			case "boolean": $b = TestBool($a); $f = true; break;
			case "integer": $b = (string)$a; $f = true; break;
			default       : $b = $a; $f = true; break;
		}
		if ($f) echo "<br>".str_repeat("--", $o).$k." => ".$b;
	}
}
function ArrayToFile($f_name, $k, $a, $o)
{
	$log_file = fopen($f_name, "a");
	if (is_array($a))
	{
		fwrite($log_file, str_repeat("--", $o).$k." =>\r\n");
		fclose($log_file);
		$os = $o + 2;
		foreach ($a as $ka => $va) ArrayToFile($f_name, $ka, $va, $os);
	}
	else
	{
		$f = false;
		switch (gettype($a))
		{	
			case "boolean": $b = TestBool($a); $f = true; break;
			case "integer": $b = (string)$a; $f = true; break;
			default       : $b = $a; $f = true; break;
		}
		if ($f) fwrite($log_file, str_repeat("--", $o).$k." => ".$b."\r\n");
		fclose($log_file);
	}
}
function TestSession($fn, $nn, $nm, $out)
{
	$log_file = fopen($fn, "a");
	if ($log_file)
	{
		fwrite($log_file, "\r\n\****************** ".$nm."---".(string)$nn."\r\n");
		fclose($log_file);
		ArrayToFile("", $out, 0);
	}
}
function EchoTestParam($k)
{
$lf = fopen("D:\\Test.txt", "a"); fwrite($lf, "\r\ntest TestUser 00 ***".$k." ");
    for ($e = 0; $e < 9; $e++)
    {
        fwrite($lf, " >>".$_SESSION['user_list'][$k][$e]);
    }
fclose($lf);
}
function EchoTestFlags($k, $n)
{
$lf = fopen("D:\\Test.txt", "a"); fwrite($lf, "\r\ntest TestUser ".$n." ***");
    for ($e = 0; $e < 11; $e++)
    {
        fwrite($lf, " >>");
        if ($e == 1 || $e == 2) for ($r = 0; $r < 3; $r++) fwrite($lf, " ".TestBool($_SESSION['user_flag'][$k][$e][$r]));
        elseif ($e == 7) for ($r = 0; $r < 2; $r++) fwrite($lf, " ".TestBool($_SESSION['user_flag'][$k][$e][$r]));
        else fwrite($lf, " ".TestBool($_SESSION['user_flag'][$k][$e]));
    }
fclose($lf);
}

?>
