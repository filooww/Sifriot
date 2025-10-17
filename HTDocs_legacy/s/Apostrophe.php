<?php header('Content-Type: text/html; charset=utf-8'); ?>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<?php
if (file_exists('al.txt')) {
    $fhand = fopen('al.txt', 'r');
    if ($fhand) {
        $str = fgets($fhand);
        echo '<br>'.$str;
        echo '<br>'.mb_strlen($str, 'utf-8');
        for ($i = 0; $i < mb_strlen($str, 'utf-8'); $i++) {
            echo '<br>'.$i.'==='.mb_substr($str, $i, 1, 'utf-8');
            echo ' --- '.mb_strlen(mb_substr($str, $i, 1, 'utf-8'));
        }
        fclose($fhand);
    }
}

?>

