<?php

/**
 * 从doc文档提取的题库文本转json格式的脚本
 */

$ss = "[_|(|（][ |_]*([A-Z| ]*[A-Z])[ |_]*[_|)|）]";
$ss = "[_(（][  　\t_]*([A-Z ]*[A-Z])[  　\t_]*[_)）]";

require "utils.php";

$exclude_list = [
    "*多项选择*",
    "*多选*",
    "*第*章*",
    "*单选*",
    "*单项选择*",
    "*辨析题*"
];

$ss_right = "[_(（][  　\t_]*([对错 ]*[对错])[  　\t_]*[_)）]";

//[AＡ][ .、．]+(.*)[BＢ][ .、．]

$file = file_get_contents("makesi.txt");
$file = explode("\n", $file);

$ans_list = [];
$cnt = 0;
$current_id = -1;
$wrong_cnt = 0;
$wrong_ans = [];
$plus_line = false;
foreach ($file as $k => $v) {
    foreach ($exclude_list as $pattern) {
        if (matchPattern($pattern, $v)) {
            Console::warning("跳过".$v, "[WARN ".($k+1)."] ");
            $plus_line = true;
            break;
        }
    }
    if($plus_line){
        $plus_line = false;
        continue;
    }
    if (is_numeric(substr(trim($v), 0, 1))) {
        $current_id++;
        //echo $v . "\n";
        // var_dump("哈哈哈");
        $v = trim($v);
        $match = [];
        mb_ereg($ss, $v, $match);
        if (!isset($match[0])) {
            mb_ereg($ss_right, $v, $match);
            if(!isset($match[0])) Console::error("错误: ".$v);
            $str = str_replace($match[0], "（ ）", $v);
            //$str = str_replace("？?", "", $str);
            //$str = str_replace("??", "", $str);
            $ans_type = 0;
            $match[1] = str_replace(" ", "", $match[1]);
            $ans_list[$current_id] = [
                "question" => $str,
                "answer" => "A.对   B.错",
                "key" => ($match[1] == "对" ? "A" : "B"),
                "answer_type" => $ans_type
            ];
        } else {
        //preg_match($ss, $v, $match);
        //var_dump($match);
            if (!isset($match[0])) {
                $wrong_cnt++;
                $wrong_ans[] = $v;
            }
            $str = str_replace($match[0], "（ ）", $v);
        //$str = str_replace("？?", "", $str);
        //$str = str_replace("??", "", $str);
            $ans_type = (strlen($match[1]) > 1 ? 1 : 0);
            $match[1] = str_replace(" ", "", $match[1]);
            $ans_list[$current_id] = [
                "question" => $str,
                "answer" => "",
                "key" => $match[1],
                "answer_type" => $ans_type
            ];
        //echo $str ."\n";
        //echo $match[1]."\n";
        //var_dump($match);
        //$ans_list[$num] = $match[1];
        }
    } else {
        if (trim($v) == "") continue;
        if ($ans_list[$current_id]["answer"] == "") {
            $ans_list[$current_id]["answer"] .= trim($v);
        } else {
            $ans_list[$current_id]["answer"] .= "\n" . trim($v);
        }
    }
}


//echo "[" . count($ans_list) . "]\n";
//echo json_encode($wrong_ans, 128 | 256) . PHP_EOL;
foreach ($ans_list as $k => $v) {
    mb_ereg("[AＡ][ .、．]{0,}(.*)[BＢ][ .、．]{0,}", $v["answer"], $match2);
    if (!isset($match2[1])) echo "WrongA: " . $v["question"] . PHP_EOL;
    $ans_list[$k]["answer2"]["A"] = trim($match2[1]);
    mb_ereg("[BＢ][ .、．]{0,}(.*)[CＣ]", $v["answer"], $match2);
    if ($match2 == []) {
        mb_ereg("[BＢ][ .、．]{0,}(.*)", $v["answer"], $match2);
        if (!isset($match2[1])) echo "WrongB: " . $v["answer"] . PHP_EOL;
        $ans_list[$k]["answer2"]["B"] = trim($match2[1]);
        $ans_list[$k]["answer"] = $ans_list[$k]["answer2"];
        unset($ans_list[$k]["answer2"]);
        continue;
    }
    if (!isset($match2[1])) echo "WrongB: " . $v["answer"] . PHP_EOL;
    $ans_list[$k]["answer2"]["B"] = trim($match2[1]);
    mb_ereg("[CＣ][ .、．]{0,}(.*)[DＤ]", $v["answer"], $match2);
    //if(!isset($match2[1])) echo "Wrong: ".$v["answer"].PHP_EOL;
    if ($match2 == []) {
        mb_ereg("[CＣ][ .、．]{0,}(.*)", $v["answer"], $match2);
        if (!isset($match2[1])) echo "WrongC: " . $v["answer"] . PHP_EOL;
        $ans_list[$k]["answer2"]["C"] = trim($match2[1]);
        $ans_list[$k]["answer"] = $ans_list[$k]["answer2"];
        unset($ans_list[$k]["answer2"]);
        continue;
    }
    $ans_list[$k]["answer2"]["C"] = trim($match2[1]);
    mb_ereg("[DＤ][ .、．]{0,}(.*)E", $v["answer"], $match2);
    //if(!isset($match2[1])) echo "Wrong: ".$v["answer"].PHP_EOL;
    if ($match2 == []) {
        mb_ereg("[DＤ][ .、．]{0,}(.*)", $v["answer"], $match2);
        if (!isset($match2[1])) echo "WrongD: " . $v["answer"] . PHP_EOL;
        $ans_list[$k]["answer2"]["D"] = trim($match2[1]);
        $ans_list[$k]["answer"] = $ans_list[$k]["answer2"];
        unset($ans_list[$k]["answer2"]);
        continue;
    }
    $ans_list[$k]["answer2"]["D"] = trim($match2[1]);
    mb_ereg("E[ .、．]{0,}(.*)", $v["answer"], $match2);
    if (!isset($match2[1])) echo "WrongE: " . $v["answer"] . PHP_EOL;
    $ans_list[$k]["answer2"]["E"] = trim($match2[1]);
    $ans_list[$k]["answer"] = $ans_list[$k]["answer2"];
    unset($ans_list[$k]["answer2"]);
}

/*foreach($ans_list as $k => $v){
    if(mb_strpos($v["answer"], "A") !== false || mb_strpos($v["answer"], "Ａ") !== false){
        if(!isset($v["answer2"]["A"])) {
            echo "Error! A\n";
        }
    }
    if(mb_strpos($v["answer"], "B") !== false || mb_strpos($v["answer"], "Ｂ") !== false){
        if(!isset($v["answer2"]["B"])) {
            echo "Error! B\n";
        }
    }if(mb_strpos($v["answer"], "C") !== false || mb_strpos($v["answer"], "Ｃ") !== false){
        if(!isset($v["answer2"]["C"])) {
            echo "Error! C\n";
        }
    }if(mb_strpos($v["answer"], "D") !== false || mb_strpos($v["answer"], "Ｄ") !== false){
        if(!isset($v["answer2"]["D"])) {
            echo "Error! D\n";
        }
    }if(mb_strpos($v["answer"], "E") !== false){
        if(!isset($v["answer2"]["E"])) {
            echo "Error! E\n";
        }
    }
}*/
//var_dump($ans_list);
$dat = json_encode((object)$ans_list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
file_put_contents("makesi.json", $dat);
//echo "Done.\n";
