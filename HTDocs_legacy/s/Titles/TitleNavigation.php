<?php

function PrevTitlePagePosition()
{
    $i = CountFirst();
    $s_p = $_SESSION['start'] - $_SESSION['portion'] + $i;

    return max($s_p, 0);
}
function NextTitlePosition()
{
    if (count($_SESSION['title_param']) == 0) {
        return 0;
    }
    $curr = reset($_SESSION['title_param']);
    $start_title = TitlePartKey(key($_SESSION['title_param']));
    $curr_k = $start_title;
    for ($i = 0; $curr !== false && $curr_k == $start_title; $i++) {
        $curr = next($_SESSION['title_param']);
        $curr_k = TitlePartKey(key($_SESSION['title_param']));
    }

    return max($_SESSION['start'] + $i, 0);
}
function NextTitlePagePosition()
{
    $i = CountLast();

    return min($_SESSION['start'] + $_SESSION['portion'] - 1, $_SESSION['title_count'] - 1) - $i + 1;
}
function EndTitlePosition()
{
    if (count($_SESSION['title_param']) < $_SESSION['portion']) {
        if (count($_SESSION['title_param']) == 0) {
            return $_SESSION['start'];
        }
        $curr = reset($_SESSION['title_param']);
        $start_k = TitlePartKey(key($_SESSION['title_param']));
        $curr_k = $start_k;
        while ($curr !== false && $curr_k == $start_k) {
            $curr = next($_SESSION['title_param']);
            $curr_k = TitlePartKey(key($_SESSION['title_param']));
        }
        if ($curr === false) {
            return $_SESSION['start'];
        }

        return $_SESSION['title_count'] - 1;
    }

    return $_SESSION['title_count'] - 1;
}
function CountLast()
{
    $curr = end($_SESSION['title_param']);
    $end_k = TitlePartKey(key($_SESSION['title_param']));
    $curr_k = $end_k;
    for ($i = 0; $curr !== false && $curr_k == $end_k; $i++) {
        $curr = prev($_SESSION['title_param']);
        $curr_k = TitlePartKey(key($_SESSION['title_param']));
    }

    return $i;
}
function CountFirst()
{
    if (count($_SESSION['title_param']) == 0) {
        return 0;
    }
    $curr = reset($_SESSION['title_param']);
    $start_k = TitlePartKey(key($_SESSION['title_param']));
    $curr_k = $start_k;
    for ($i = 0; $curr !== false && $curr_k == $start_k; $i++) {
        $curr = next($_SESSION['title_param']);
        $curr_k = TitlePartKey(key($_SESSION['title_param']));
    }

    return $i;
}
function TitleNavigation($dbh, $nav)
{
    SetTitleFilterPosition($dbh);
    $s_p = $_SESSION['start'];
    switch ($nav) {
        case 'beg': $s_p = 0;
            break;
        case 'pgup': $s_p = ($_SESSION['selected_title_lang'][0] == 0 && $_SESSION['view_title_mode'][0] != 2) ? PrevTitlePagePosition() : max($_SESSION['start'] - $_SESSION['portion'] + 1, 0);
            break;
        case 'tlup': case 'lnup': $s_p = max($_SESSION['start'] - 1, 0);
            break;
        case 'lndn': $s_p = min($_SESSION['start'] + 1, $_SESSION['title_count'] - 1);
            break;
        case 'tldn': $s_p = ($_SESSION['selected_title_lang'][0] == 0 && $_SESSION['view_title_mode'][0] != 2) ? NextTitlePosition() : min($_SESSION['start'] + 1, $_SESSION['title_count'] - 1);
            break;
        case 'pgdn': $s_p = ($_SESSION['selected_title_lang'][0] == 0 && $_SESSION['view_title_mode'][0] != 2) ? NextTitlePagePosition() : min($_SESSION['start'] + $_SESSION['portion'] - 1, $_SESSION['title_count'] - 1);
            break;
        case 'end': $s_p = ($_SESSION['selected_title_lang'][0] == 0 && $_SESSION['view_title_mode'][0] != 2) ? EndTitlePosition() : $_SESSION['title_count'] - 1;
            break;
    }
    if ($s_p != $_SESSION['start']) {
        $_SESSION['start'] = $s_p;
        $_SESSION['title_param'] = GetTitlePortion($dbh, $nav);
        TestTitles($dbh);
    }
}
