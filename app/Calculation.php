<?php

namespace UbimetTask;

class Calculation {
	public $classArray;

	function setArray($array1) {
		$this->classArray = $array1;
	}

	function calculate_median() {
	    $count = count($this->classArray);                //total numbers in array
	    sort($this->classArray);                          //sorting array
	    $middlevel = floor(($count-1)/2);       		  // find the middle value, or the lowest middle value
	    if($count % 2 == 1) {                   		  // odd number, middle is the median
	        $median = $this->classArray[$middlevel];
	    } else {                                		  // even number, calculate avg of 2 medians
	        $low = $this->classArray[$middlevel-1];
	        $high = $this->classArray[$middlevel];
	        $median = (($low+$high)/2);
	    }
	    return $median;
	}

	//Average calculation
    function calculate_average($array1) {
         //$a = $array1;
         $average = array_sum($array1)/count($array1);
         return $average;
    }
}