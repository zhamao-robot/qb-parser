<?php

/**
 * 从doc文档提取的题库文本转json格式的脚本
 */



$ss = "[_|(|（][ |_]*([A-Z| ]*[A-Z])[ |_]*[_|)|）]";

$ss = "[_(（][  　\t_]*([A-Z ]*[A-Z])[  　\t_]*[_)）]";
require "utils.php";
//[AＡ][ .、．]+(.*)[BＢ][ .、．]

$exclude_list = [
    "*多项选择*",
    "*多选*",
    "*第*章*",
    "*单选*",
    "*单项选择*"
];

//按照行取文本
$file = file_get_contents("maogai.txt");
$file = explode("\n", $file);

//结果集存放变量
$ans_list = [];
$current_id = -1;
$wrong_cnt = 0;
$wrong_ans = [];
$plus_line = false;
for ($k = 0; $k < count($file); $k++) {
    $v = $file[$k];
    foreach ($exclude_list as $pattern) {
        if (matchPattern($pattern, $v)) {
            Console::warning("跳过\"$v\"", "[WARN $k] ");
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
        //preg_match($ss, $v, $match);
        //var_dump($match);
        if (!isset($match[0])) {
            mb_ereg($ss, $file[$k + 1], $match);
            if (!isset($match[0])) {
                $wrong_cnt++;
                $wrong_ans[] = $v;
                Console::error($v, "[ERROR $k] ");
            } else {
                $file[$k] .= $file[$k + 1];
                $plus_line = true;
            }
        }
        $str = str_replace($match[0], "（ ）", $file[$k]);
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
        if ($plus_line) {
            $plus_line = false;
            ++$k;
        }
        //echo $str ."\n";
        //echo $match[1]."\n";
        //var_dump($match);
        //$ans_list[$num] = $match[1];
    } else {
        if (trim($v) == "") continue;
        if ($current_id == -1) continue;
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
    mb_ereg("[AＡ][ .、．]+(.*)[BＢ][ .、．]", $v["answer"], $match2);
    $ans_list[$k]["answer2"]["A"] = trim($match2[1]);
    mb_ereg("[BＢ][ .、．]+(.*)[CＣ]", $v["answer"], $match2);
    $ans_list[$k]["answer2"]["B"] = trim($match2[1]);
    mb_ereg("[CＣ][ .、．]+(.*)[DＤ]", $v["answer"], $match2);
    if ($match2 == []) {
        mb_ereg("[CＣ][ .、．]+(.*)", $v["answer"], $match2);
        $ans_list[$k]["answer2"]["C"] = trim($match2[1]);
        $ans_list[$k]["answer"] = $ans_list[$k]["answer2"];
        unset($ans_list[$k]["answer2"]);
        continue;
    }
    $ans_list[$k]["answer2"]["C"] = trim($match2[1]);
    mb_ereg("[DＤ][ .、．]+(.*)E", $v["answer"], $match2);
    if ($match2 == []) {
        mb_ereg("[DＤ][ .、．]+(.*)", $v["answer"], $match2);
        $ans_list[$k]["answer2"]["D"] = trim($match2[1]);
        $ans_list[$k]["answer"] = $ans_list[$k]["answer2"];
        unset($ans_list[$k]["answer2"]);
        continue;
    }
    $ans_list[$k]["answer2"]["D"] = trim($match2[1]);
    mb_ereg("E[ .、．]+(.*)", $v["answer"], $match2);
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
file_put_contents("maogai.json", $dat);
//echo "Done.\n";
