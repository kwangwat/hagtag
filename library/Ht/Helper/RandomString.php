<?php
/**
 * 
 * @author chatupon
 * @since 22 June 2015
 */
class Ht_Helper_RandomString extends Zend_Controller_Action_Helper_Abstract {
    
    
    public function direct($length = 32, $chars = '1234567890abcdef') {
        // length of character list
        $charsLength = (strlen($chars) - 1);
        
        // start our string
        $string = $chars{rand(0, $charsLength)};
        
        // generate random string
        for($i = 1; $i < $length; $i = strlen($string)){
            // grab a random character from our list
            $r = $chars{rand(0, $charsLength)};
            // make sure the same two characters don't appear next to each other
            if($r != $string{$i - 1}){
                $string .= $r;
            }else{
                $i --;
            }
        }
        
        // return the string
        return $string;
    }
}