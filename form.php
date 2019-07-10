<?php

function pr($arr){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

/**
 * @param $arr
 * @return string
 */
function arrToStr($arr){
    if(is_array($arr)){
        return join(',', array_map('arrToStr', $arr));
    }
    return $arr;
}
function multiArr2oneArr($arr){
    return explode(',', arrToStr($arr));
}

$arr = [1,2,['a','b',['x','y',['k','j']]]];

pr($arr);

pr(multiArr2oneArr($arr));
pr($string = arrToStr($arr));
