<?php
echo get_perfect(12);
function get_perfect($n){
    if($n==0)
        return 0;
    $visited = array($n=>true);
    $q = array(
        array('num'=>$n,'step'=>0),
    );
    while(!empty($q)){
        $node = array_pop($q);
        $num = $node['num'];
        $step = $node['step'];
        if($num==0){
            return $step;
        }
        for($i=0;;$i++){
            $a = $num-$i*$i;
            if($a<0){
                break;
            }
            if(!isset($visited[$a])){
                if($a==0){
                    return $step+1;
                }
                array_push($q,array('num'=>$a,'step'=>$step+1));
                $visited[$a] = true;
            }
        }
    }
}
