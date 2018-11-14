<?php

    $data = [
        'chuan' => 1,
        'tz' => [
            0 => [
                'game_id' => 1,
                'tz_result' => [
                    '1',
                    '2',
                    '3',
                    '4'
                ]
            ],
            1 => [
                'game_id' => 2,
                'tz_result' => [
                    '5',
                    '6',
                    '7'
                ]
            ],
            2 => [
                'game_id' => 3,
                'tz_result' => [
                    '8',
                    '9',
                    '10'
                ]
            ],
            3 => [
                'game_id' => 4,
                'tz_result' => [
                    '11',
                    '12',
                    '13'
                ]
            ],
            4 => [
                'game_id' => 5,
                'tz_result' => [
                    '14',
                    '15',
                    '16'
                ]
            ],
            5 => [
                'game_id' => 6,
                'tz_result' => [
                    '17',
                    '18',
                    '19'
                ]
            ],
            6 => [
                'game_id' => 7,
                'tz_result' => [
                    '20',
                    '21',
                    '22'
                ]
            ],
            7 => [
                'game_id' => 8,
                'tz_result' => [
                    '23',
                    '24',
                    '25'
                ]
            ],
        ],
    ];

    $order_info = array();
    $tz = $data['tz'];
    foreach ($tz as $key => $value) {
        foreach ($tz[$key]['tz_result'] as $ke => $val) {
            $touz[$key][$ke] = $tz[$key]['tz_result'][$ke];
        }
    }
    $tz_num = 0;

    if($data['chuan'] == 1){
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                $order_info[] = $touz[$i][$j];
            }
        }    
    }
    if($data['chuan'] == 2){
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                for ($a=1; $a < count($touz); $a++) { 
                    if(isset($touz[$i+$a])){
                        for ($b=0; $b < count($touz[$i+$a]); $b++) { 
                            $order_info[] = [ $touz[$i][$j] , $touz[$i+$a][$b] ];
                        }    
                    }
                }
            }
        }    
    }
    if($data['chuan'] == 3){
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                for ($a=1; $a < count($touz); $a++) { 
                    if(isset($touz[$i+$a])){
                        for ($b=0; $b < count($touz[$i+$a]); $b++) { 
                            for ($c=1; $c < count($touz); $c++) { 
                                if(isset($touz[$i+$a+$c])){
                                    for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
                                        $order_info[] = [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d]];
                                    }
                                }
                            }                                
                        }
                    }
                }
            }
        }
    }
    if($data['chuan'] == 4){
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                for ($a=1; $a < count($touz); $a++) { 
                    if(isset($touz[$i+$a])){
                        for ($b=0; $b < count($touz[$i+$a]); $b++) { 
                            for ($c=1; $c < count($touz); $c++) { 
                                if(isset($touz[$i+$a+$c])){
                                    for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
                                        for ($e=1; $e < count($touz); $e++) { 
                                            if(isset($touz[$i+$a+$c+$e])){
                                                for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
                                                    $order_info[] = [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f]];
                                                }
                                            }
                                        }
                                    }
                                }
                            }                                
                        }
                    }
                }
            }
        }
    }
    if($data['chuan'] == 5){
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                for ($a=1; $a < count($touz); $a++) { 
                    if(isset($touz[$i+$a])){
                        for ($b=0; $b < count($touz[$i+$a]); $b++) { 
                            for ($c=1; $c < count($touz); $c++) { 
                                if(isset($touz[$i+$a+$c])){
                                    for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
                                        for ($e=1; $e < count($touz); $e++) { 
                                            if(isset($touz[$i+$a+$c+$e])){
                                                for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
                                                    for ($g=1; $g < count($touz); $g++) { 
                                                        if(isset($touz[$i+$a+$c+$e+$g])){
                                                            for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
                                                                $order_info[] = [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h]];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }                                
                        }
                    }
                }
            }
        }
    }
    if($data['chuan'] == 6){
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                for ($a=1; $a < count($touz); $a++) { 
                    if(isset($touz[$i+$a])){
                        for ($b=0; $b < count($touz[$i+$a]); $b++) { 
                            for ($c=1; $c < count($touz); $c++) { 
                                if(isset($touz[$i+$a+$c])){
                                    for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
                                        for ($e=1; $e < count($touz); $e++) { 
                                            if(isset($touz[$i+$a+$c+$e])){
                                                for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
                                                    for ($g=1; $g < count($touz); $g++) { 
                                                        if(isset($touz[$i+$a+$c+$e+$g])){
                                                            for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
                                                                for ($k=1; $k < count($touz); $k++) { 
                                                                    if(isset($touz[$i+$a+$c+$e+$g+$k])){
                                                                        for ($l=0; $l < count($touz[$i+$a+$c+$e+$g+$k]); $l++) { 
                                                                            $order_info[] = [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l]];
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }                                
                        }
                    }
                }
            }
        }
    }
    if($data['chuan'] == 7){
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                for ($a=1; $a < count($touz); $a++) { 
                    if(isset($touz[$i+$a])){
                        for ($b=0; $b < count($touz[$i+$a]); $b++) { 
                            for ($c=1; $c < count($touz); $c++) { 
                                if(isset($touz[$i+$a+$c])){
                                    for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
                                        for ($e=1; $e < count($touz); $e++) { 
                                            if(isset($touz[$i+$a+$c+$e])){
                                                for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
                                                    for ($g=1; $g < count($touz); $g++) { 
                                                        if(isset($touz[$i+$a+$c+$e+$g])){
                                                            for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
                                                                for ($k=1; $k < count($touz); $k++) { 
                                                                    if(isset($touz[$i+$a+$c+$e+$g+$k])){
                                                                        for ($l=0; $l < count($touz[$i+$a+$c+$e+$g+$k]); $l++) { 
                                                                            for ($m=1; $m < count($touz); $m++) { 
                                                                                if(isset($touz[$i+$a+$c+$e+$g+$k+$m])){
                                                                                    for ($n=0; $n < count($touz[$i+$a+$c+$e+$g+$k+$m]); $n++) { 
                                                                                        $order_info[] = [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l] , $touz[$i+$a+$c+$e+$g+$k+$m][$n]];
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if($data['chuan'] == 8){
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                for ($a=1; $a < count($touz); $a++) { 
                    if(isset($touz[$i+$a])){
                        for ($b=0; $b < count($touz[$i+$a]); $b++) { 
                            for ($c=1; $c < count($touz); $c++) { 
                                if(isset($touz[$i+$a+$c])){
                                    for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
                                        for ($e=1; $e < count($touz); $e++) { 
                                            if(isset($touz[$i+$a+$c+$e])){
                                                for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
                                                    for ($g=1; $g < count($touz); $g++) { 
                                                        if(isset($touz[$i+$a+$c+$e+$g])){
                                                            for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
                                                                for ($k=1; $k < count($touz); $k++) { 
                                                                    if(isset($touz[$i+$a+$c+$e+$g+$k])){
                                                                        for ($l=0; $l < count($touz[$i+$a+$c+$e+$g+$k]); $l++) { 
                                                                            for ($m=1; $m < count($touz); $m++) { 
                                                                                if(isset($touz[$i+$a+$c+$e+$g+$k+$m])){
                                                                                    for ($n=0; $n < count($touz[$i+$a+$c+$e+$g+$k+$m]); $n++) { 
                                                                                        for ($o=1; $o < count($touz); $o++) { 
                                                                                            if(isset($touz[$i+$a+$c+$e+$g+$k+$m+$o])){
                                                                                                for ($p=0; $p < count($touz[$i+$a+$c+$e+$g+$k+$m+$o]); $p++) { 
                                                                                                    $order_info[] = [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l] , $touz[$i+$a+$c+$e+$g+$k+$m][$n] , $touz[$i+$a+$c+$e+$g+$k+$m+$o][$p]];
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }                                
                        }
                    }
                }
            }
        }
    }
    
    dump($touz);
    dump($order_info);
    die;

}
