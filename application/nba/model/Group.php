<?php
namespace app\nba\model;
use think\Model;
// NBA比赛竞猜
class Group extends Model
{   
    /*
    *$chuan    int    串法
    *$touz     array  投注选项数据
    *$status   int    1:返回值(array 排列组合的数据) | 2:返回值(int 条数)
    */
    public function order_group($chuan,$touz,$status)
    {
        for ($i=0; $i < count($touz); $i++) { 
            for ($j=0; $j < count($touz[$i]); $j++) { 
                if($chuan == 1){
                    $order_group[] = $touz[$i][$j];
                }else{
                    for ($a=1; $a < count($touz); $a++) { 
                        if(isset($touz[$i+$a])){
                            for ($b=0; $b < count($touz[$i+$a]); $b++) { 
                                if($chuan == 2){
                                    $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] ]);
                                }else{
                                    for ($c=1; $c < count($touz); $c++) { 
                                        if(isset($touz[$i+$a+$c])){
                                            for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
                                                if($chuan == 3){
                                                    $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d]]);
                                                }else{
                                                    for ($e=1; $e < count($touz); $e++) { 
                                                        if(isset($touz[$i+$a+$c+$e])){
                                                            for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
                                                                if($chuan == 4){
                                                                    $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f]]);
                                                                }else{
                                                                    for ($g=1; $g < count($touz); $g++) { 
                                                                        if(isset($touz[$i+$a+$c+$e+$g])){
                                                                            for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
                                                                                if($chuan == 5){
                                                                                    $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h]]);
                                                                                }else{
                                                                                    for ($k=1; $k < count($touz); $k++) { 
                                                                                        if(isset($touz[$i+$a+$c+$e+$g+$k])){
                                                                                            for ($l=0; $l < count($touz[$i+$a+$c+$e+$g+$k]); $l++) { 
                                                                                                if($chuan == 6){
                                                                                                    $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l]]);
                                                                                                }else{
                                                                                                    for ($m=1; $m < count($touz); $m++) { 
                                                                                                        if(isset($touz[$i+$a+$c+$e+$g+$k+$m])){
                                                                                                            for ($n=0; $n < count($touz[$i+$a+$c+$e+$g+$k+$m]); $n++) { 
                                                                                                                if($chuan == 7){
                                                                                                                    $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l] , $touz[$i+$a+$c+$e+$g+$k+$m][$n]]);
                                                                                                                }else{
                                                                                                                    for ($o=1; $o < count($touz); $o++) { 
                                                                                                                        if(isset($touz[$i+$a+$c+$e+$g+$k+$m+$o])){
                                                                                                                            for ($p=0; $p < count($touz[$i+$a+$c+$e+$g+$k+$m+$o]); $p++) {
                                                                                                                                if($chuan == 8){ 
                                                                                                                                    $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l] , $touz[$i+$a+$c+$e+$g+$k+$m][$n] , $touz[$i+$a+$c+$e+$g+$k+$m+$o][$p]]);
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
                                }
                            }
                        }
                    }    
                }
            }
        }

        $count = count($order_group);
        if($status == 1){
            return $order_group;
        }else if($status == 2){
            return $count;
        }
        
    }

    // public function order_group($chuan,$touz,$status)
    // {
    //     if($chuan == 1){
    //         for ($i=0; $i < count($touz); $i++) { 
    //             for ($j=0; $j < count($touz[$i]); $j++) { 
    //                 $order_group[] = $touz[$i][$j];
    //             }
    //         }    
    //     }
    //     if($chuan == 2){
    //         for ($i=0; $i < count($touz); $i++) { 
    //             for ($j=0; $j < count($touz[$i]); $j++) { 
    //                 for ($a=1; $a < count($touz); $a++) { 
    //                     if(isset($touz[$i+$a])){
    //                         for ($b=0; $b < count($touz[$i+$a]); $b++) { 
    //                             $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] ]);
    //                         }    
    //                     }
    //                 }
    //             }
    //         }    
    //     }
    //     if($chuan == 3){
    //         echo 1;
    //         for ($i=0; $i < count($touz); $i++) { 
    //             for ($j=0; $j < count($touz[$i]); $j++) { 
    //                 for ($a=1; $a < count($touz); $a++) { 
    //                     if(isset($touz[$i+$a])){
    //                         echo 2;
    //                         for ($b=0; $b < count($touz[$i+$a]); $b++) { 
    //                             for ($c=1; $c < count($touz); $c++) { 
    //                                 if(isset($touz[$i+$a+$c])){
    //                                     echo 3;
    //                                     for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
    //                                         $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d]]);
    //                                     }
    //                                 }
    //                             }                                
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     if($chuan == 4){
    //         for ($i=0; $i < count($touz); $i++) { 
    //             for ($j=0; $j < count($touz[$i]); $j++) { 
    //                 for ($a=1; $a < count($touz); $a++) { 
    //                     if(isset($touz[$i+$a])){
    //                         for ($b=0; $b < count($touz[$i+$a]); $b++) { 
    //                             for ($c=1; $c < count($touz); $c++) { 
    //                                 if(isset($touz[$i+$a+$c])){
    //                                     for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
    //                                         for ($e=1; $e < count($touz); $e++) { 
    //                                             if(isset($touz[$i+$a+$c+$e])){
    //                                                 for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
    //                                                     $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f]]);
    //                                                 }
    //                                             }
    //                                         }
    //                                     }
    //                                 }
    //                             }                                
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     if($chuan == 5){
    //         for ($i=0; $i < count($touz); $i++) { 
    //             for ($j=0; $j < count($touz[$i]); $j++) { 
    //                 for ($a=1; $a < count($touz); $a++) { 
    //                     if(isset($touz[$i+$a])){
    //                         for ($b=0; $b < count($touz[$i+$a]); $b++) { 
    //                             for ($c=1; $c < count($touz); $c++) { 
    //                                 if(isset($touz[$i+$a+$c])){
    //                                     for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
    //                                         for ($e=1; $e < count($touz); $e++) { 
    //                                             if(isset($touz[$i+$a+$c+$e])){
    //                                                 for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
    //                                                     for ($g=1; $g < count($touz); $g++) { 
    //                                                         if(isset($touz[$i+$a+$c+$e+$g])){
    //                                                             for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
    //                                                                 $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h]]);
    //                                                             }
    //                                                         }
    //                                                     }
    //                                                 }
    //                                             }
    //                                         }
    //                                     }
    //                                 }
    //                             }                                
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     if($chuan == 6){
    //         for ($i=0; $i < count($touz); $i++) { 
    //             for ($j=0; $j < count($touz[$i]); $j++) { 
    //                 for ($a=1; $a < count($touz); $a++) { 
    //                     if(isset($touz[$i+$a])){
    //                         for ($b=0; $b < count($touz[$i+$a]); $b++) { 
    //                             for ($c=1; $c < count($touz); $c++) { 
    //                                 if(isset($touz[$i+$a+$c])){
    //                                     for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
    //                                         for ($e=1; $e < count($touz); $e++) { 
    //                                             if(isset($touz[$i+$a+$c+$e])){
    //                                                 for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
    //                                                     for ($g=1; $g < count($touz); $g++) { 
    //                                                         if(isset($touz[$i+$a+$c+$e+$g])){
    //                                                             for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
    //                                                                 for ($k=1; $k < count($touz); $k++) { 
    //                                                                     if(isset($touz[$i+$a+$c+$e+$g+$k])){
    //                                                                         for ($l=0; $l < count($touz[$i+$a+$c+$e+$g+$k]); $l++) { 
    //                                                                             $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l]]);
    //                                                                         }
    //                                                                     }
    //                                                                 }
    //                                                             }
    //                                                         }
    //                                                     }
    //                                                 }
    //                                             }
    //                                         }
    //                                     }
    //                                 }
    //                             }                                
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     if($chuan == 7){
    //         for ($i=0; $i < count($touz); $i++) { 
    //             for ($j=0; $j < count($touz[$i]); $j++) { 
    //                 for ($a=1; $a < count($touz); $a++) { 
    //                     if(isset($touz[$i+$a])){
    //                         for ($b=0; $b < count($touz[$i+$a]); $b++) { 
    //                             for ($c=1; $c < count($touz); $c++) { 
    //                                 if(isset($touz[$i+$a+$c])){
    //                                     for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
    //                                         for ($e=1; $e < count($touz); $e++) { 
    //                                             if(isset($touz[$i+$a+$c+$e])){
    //                                                 for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
    //                                                     for ($g=1; $g < count($touz); $g++) { 
    //                                                         if(isset($touz[$i+$a+$c+$e+$g])){
    //                                                             for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
    //                                                                 for ($k=1; $k < count($touz); $k++) { 
    //                                                                     if(isset($touz[$i+$a+$c+$e+$g+$k])){
    //                                                                         for ($l=0; $l < count($touz[$i+$a+$c+$e+$g+$k]); $l++) { 
    //                                                                             for ($m=1; $m < count($touz); $m++) { 
    //                                                                                 if(isset($touz[$i+$a+$c+$e+$g+$k+$m])){
    //                                                                                     for ($n=0; $n < count($touz[$i+$a+$c+$e+$g+$k+$m]); $n++) { 
    //                                                                                         $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l] , $touz[$i+$a+$c+$e+$g+$k+$m][$n]]);
    //                                                                                     }
    //                                                                                 }
    //                                                                             }
    //                                                                         }
    //                                                                     }
    //                                                                 }
    //                                                             }
    //                                                         }
    //                                                     }
    //                                                 }
    //                                             }
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     if($chuan == 8){
    //         for ($i=0; $i < count($touz); $i++) { 
    //             for ($j=0; $j < count($touz[$i]); $j++) { 
    //                 for ($a=1; $a < count($touz); $a++) { 
    //                     if(isset($touz[$i+$a])){
    //                         for ($b=0; $b < count($touz[$i+$a]); $b++) { 
    //                             for ($c=1; $c < count($touz); $c++) { 
    //                                 if(isset($touz[$i+$a+$c])){
    //                                     for ($d=0; $d < count($touz[$i+$a+$c]); $d++) { 
    //                                         for ($e=1; $e < count($touz); $e++) { 
    //                                             if(isset($touz[$i+$a+$c+$e])){
    //                                                 for ($f=0; $f < count($touz[$i+$a+$c+$e]); $f++) { 
    //                                                     for ($g=1; $g < count($touz); $g++) { 
    //                                                         if(isset($touz[$i+$a+$c+$e+$g])){
    //                                                             for ($h=0; $h < count($touz[$i+$a+$c+$e+$g]); $h++) { 
    //                                                                 for ($k=1; $k < count($touz); $k++) { 
    //                                                                     if(isset($touz[$i+$a+$c+$e+$g+$k])){
    //                                                                         for ($l=0; $l < count($touz[$i+$a+$c+$e+$g+$k]); $l++) { 
    //                                                                             for ($m=1; $m < count($touz); $m++) { 
    //                                                                                 if(isset($touz[$i+$a+$c+$e+$g+$k+$m])){
    //                                                                                     for ($n=0; $n < count($touz[$i+$a+$c+$e+$g+$k+$m]); $n++) { 
    //                                                                                         for ($o=1; $o < count($touz); $o++) { 
    //                                                                                             if(isset($touz[$i+$a+$c+$e+$g+$k+$m+$o])){
    //                                                                                                 for ($p=0; $p < count($touz[$i+$a+$c+$e+$g+$k+$m+$o]); $p++) { 
    //                                                                                                     $order_group[] = implode(',', [ $touz[$i][$j] , $touz[$i+$a][$b] , $touz[$i+$a+$c][$d] , $touz[$i+$a+$c+$e][$f] , $touz[$i+$a+$c+$e+$g][$h] , $touz[$i+$a+$c+$e+$g+$k][$l] , $touz[$i+$a+$c+$e+$g+$k+$m][$n] , $touz[$i+$a+$c+$e+$g+$k+$m+$o][$p]]);
    //                                                                                                 }
    //                                                                                             }
    //                                                                                         }
    //                                                                                     }
    //                                                                                 }
    //                                                                             }
    //                                                                         }
    //                                                                     }
    //                                                                 }
    //                                                             }
    //                                                         }
    //                                                     }
    //                                                 }
    //                                             }
    //                                         }
    //                                     }
    //                                 }
    //                             }                                
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     $count = count($order_group);
    //     if($status == 1){
    //         return $order_group;
    //     }else if($status == 2){
    //         return $count;
    //     }
        
    // }


}
