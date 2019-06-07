<?php


class Console
{
    static function setColor($string, $color = "") {
        switch ($color) {
            case "red":
                return "\x1b[38;5;203m" . $string . "\x1b[m";
            case "green":
                return "\x1b[38;5;83m" . $string . "\x1b[m";
            case "yellow":
                return "\x1b[38;5;227m" . $string . "\x1b[m";
            case "blue":
                return "\033[34m" . $string . "\033[0m";
            case "lightpurple":
                return "\x1b[38;5;207m" . $string . "\x1b[m";
            case "lightblue":
                return "\x1b[38;5;87m" . $string . "\x1b[m";
            case "gold":
                return "\x1b[38;5;214m" . $string . "\x1b[m";
            case "gray":
                return "\x1b[38;5;59m" . $string . "\x1b[m";
            case "pink":
                return "\x1b[38;5;207m" . $string . "\x1b[m";
            case "lightlightblue":
                return "\x1b[38;5;63m" . $string . "\x1b[m";
            default:
                return $string;
        }
    }


    static function error($obj, $head = null) {
        if ($head === null) $head = date("[H:i:s ") . "ERROR] ";
        if (!is_string($obj)) {
            if (isset($trace)) {
                var_dump($obj);
                return;
            } else $obj = "{Object}";
        }
        echo(self::setColor($head . ($trace ?? "") . $obj, "red") . "\n");
    }

    static function warning($obj, $head = null) {
        if ($head === null) $head = date("[H:i:s") . " WARN] ";
        if (!is_string($obj)) {
            if (isset($trace)) {
                var_dump($obj);
                return;
            } else $obj = "{Object}";
        }
        echo(self::setColor($head . ($trace ?? "") . $obj, "yellow") . "\n");
    }

    static function info($obj, $head = null) {
        if ($head === null) $head = date("[H:i:s ") . "INFO] ";
        if (!is_string($obj)) {
            if (isset($trace)) {
                var_dump($obj);
                return;
            } else $obj = "{Object}";
        }
        echo(self::setColor($head . ($trace ?? "") . $obj, "lightblue") . "\n");
    }

    static function log($obj, $color = "") {
        if (!is_string($obj)) var_dump($obj);
        else echo(self::setColor($obj, $color) . "\n");
    }
}

function matchPattern($pattern, $context) {
    if (mb_substr($pattern, 0, 1) == "" && mb_substr($context, 0, 1) == "")
        return true;
    if ('*' == mb_substr($pattern, 0, 1) && "" != mb_substr($pattern, 1, 1) && "" == mb_substr($context, 0, 1))
        return false;
    if (mb_substr($pattern, 0, 1) == mb_substr($context, 0, 1))
        return matchPattern(mb_substr($pattern, 1), mb_substr($context, 1));
    if (mb_substr($pattern, 0, 1) == "*")
        return matchPattern(mb_substr($pattern, 1), $context) || matchPattern($pattern, mb_substr($context, 1));
    return false;
}
