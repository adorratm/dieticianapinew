<?php
namespace App\Helpers;

function mb_ucfirst($string, $encoding)
{
    $strlen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strlen - 1, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
}

// Improved Strto
function strto($to = null, $str = null)
{
    if (!function_exists('rp')) :
        function rp($i = null, $str = null)
        {
            $B = array('I', 'Ğ', 'Ü', 'Ş', 'İ', 'Ö', 'Ç');
            $k = array('ı', 'ğ', 'ü', 'ş', 'i', 'ö', 'ç');
            $Bi = array(' I', ' ı', ' İ', ' i');
            $ki = array(' I', ' I', ' İ', ' İ');
            if ($i == 1) :
                return str_replace($B, $k, $str);
            elseif ($i == 2) :
                return str_replace($k, $B, $str);
            elseif ($i == 3) :
                return str_replace($Bi, $ki, $str);
            endif;
        }
    endif;
    if (!function_exists('cf')) :
        function cf($c = [], $str = null)
        {
            foreach ($c as $cc) {
                $s = explode($cc, $str);
                foreach ($s as $k => $ss) {
                    $s[$k] = strto('ucfirst', $ss);
                }
                $str = implode($cc, $s);
            }
            return $str;
        }
    endif;
    if (!function_exists('te')) :
        function te()
        {
            return trigger_error('Lütfen geçerli bir strto() parametresi giriniz.', E_USER_ERROR);
        }
    endif;
    $to = explode('|', $to);
    if ($to) :
        foreach ($to as $t) {
            if ($t == 'lower') :
                $str = mb_strtolower(rp(1, $str), "utf-8");
            elseif ($t == 'upper') :
                $str = mb_strtoupper(rp(2, $str), "utf-8");
            elseif ($t == 'ucfirst') :
                $str = mb_strtoupper(rp(2, mb_substr($str, 0, 1, "utf-8")), "utf-8") . mb_substr($str, 1, mb_strlen($str, "utf-8") - 1, "utf-8");
            elseif ($t == 'ucwords') :
                $str = ltrim(mb_convert_case(rp(3, ' ' . $str), MB_CASE_TITLE, "utf-8"));
            elseif ($t == 'capitalizefirst') :
                $str = cf(array('. ', '.', '? ', '?', '! ', '!', ': ', ':'), $str);
            else :
                $str = te();
            endif;
        }
    else :
        $str = te();
    endif;
    return $str;
}
