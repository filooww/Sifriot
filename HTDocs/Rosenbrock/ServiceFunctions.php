<?php
function WriteTable($f_name, $res_format, $i, $M, $N, $Cz, $k, $df)
{
	$fh = fopen($f_name.".txt", "a");
	fwrite($fh, "\r\n".sprintf($res_format, (string)$i, NormNum($M, 20), NormNum($N, 20), NormNum($Cz, 20), NormNum($k, 16), $df));
	fclose($fh);
}

function WriteKTable($f_name, $res_format, $t, $f)
{
	$fh = fopen($f_name.".txt", "a");
	fwrite($fh, "\r\n".sprintf($res_format, (string)$t, NormNum($f, 20)));
	fclose($fh);
}

function WriteTerminal($f_name, $w)
{
	$fhand = fopen($f_name."_terminal.txt", "w");
	if ($fhand)
	{
		fwrite($fhand, (string)$w[0].chr(9).$w[3].chr(9).$w[2].chr(9).$w[1]."\r\n");
		fclose($fhand);
	}
}
function WriteTitle($f_name, $n_pl)
{
	$fh = fopen($f_name.".txt", "w");
    fwrite($fh, "\r\n".str_repeat(" ", 9)."t       M analtic".str_repeat(" ", $n_pl - 5)."M".str_repeat(" ", $n_pl + 3)."N".str_repeat(" ", $n_pl + 3)."k".str_repeat(" ", $n_pl + 3)."k`");
    fwrite($fh, "\r\n".str_repeat("-", 21 + 5 * $n_pl));
	fclose($fh);
}
function RoundPrecision($x)
{
    $xs = rtrim((string)$x, "0 ");
    $pos = strpos($xs, ".");
    if ($pos === false) return 0;
    else return strlen($xs) - $pos - 1;
}
?>
