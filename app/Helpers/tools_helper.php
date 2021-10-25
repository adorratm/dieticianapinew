<?php


namespace App\Helpers;


class tools_helper
{

    public static function strto($to = null, $str = null)
    {

        $to = explode('|', $to);
        if ($to) :
            foreach ($to as $t) {
                if ($t == 'lower') :
                    $str = mb_strtolower(tools_helper::rp(1, $str), "utf-8");
                elseif ($t == 'upper') :
                    $str = mb_strtoupper(tools_helper::rp(2, $str), "utf-8");
                elseif ($t == 'ucfirst') :
                    $str = mb_strtoupper(tools_helper::rp(2, mb_substr($str, 0, 1, "utf-8")), "utf-8") . mb_substr($str, 1, mb_strlen($str, "utf-8") - 1, "utf-8");
                elseif ($t == 'ucwords') :
                    $str = ltrim(mb_convert_case(tools_helper::rp(3, ' ' . $str), MB_CASE_TITLE, "utf-8"));
                elseif ($t == 'capitalizefirst') :
                    $str = tools_helper::cf(array('. ', '.', '? ', '?', '! ', '!', ': ', ':'), $str);
                else :
                    $str = tools_helper::te();
                endif;
            }
        else :
            $str = tools_helper::te();
        endif;
        return $str;
    }
    public static  function te()
    {
        return trigger_error('Lütfen geçerli bir strto() parametresi giriniz.', E_USER_ERROR);
    }
    public static function cf($c = [], $str = null)
    {
        foreach ($c as $cc) {
            $s = explode($cc, $str);
            foreach ($s as $k => $ss) {
                $s[$k] = tools_helper::strto('ucfirst', $ss);
            }
            $str = implode($cc, $s);
        }
        return $str;
    }

    public  static  function rp($i = null, $str = null)
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

}
