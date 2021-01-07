<?php
/**
* $Id: SessionUser.php 43 2015-12-08 14:32:20Z chrdel $
*          
* EXCLUSIVE LICENSE
* THE INFORMATION AND COMPUTER SOURCE CODE CONTAINED WITHIN THIS PROGRAM SCRIPT IS
* THE EXCLUSIVE PROPERTY OF HEALTHFIRST FINANCIAL, LLC. USE MUST BE AUTHORIZED UNDER WRITTEN
* LICENSE OBTAINED FROM HEALTHFIRST FINANCIAL, LLC. USE AT YOUR OWN RISK. NO WARANTY EITHER
* EXPRESSED OR IMPLIED.
*
* UNAUTHORIZED USE, ALTERATION, COPYING, OR REDISTRIBUTION IS STRICTLY PROHIBITED.
*
* @copyright Copyright (c) 2015 HealthFirst Financial, LLC.
*
* @author KG Financial Software Pvt Ltd (www.kgfsl.com), Chris DeLess
*
*/
 session_start();
class SessionUser extends CFormModel
{
    public static function sessionUpdate($username=null,$pin=null)
    {       
        $session_id=session_id();
        $_SESSION['seqno']=$session_id;
        $_SESSION[$_SESSION['seqno']]=array('cardNumber'=>$username,'pinNumber'=>$pin);              
    }    
    
    public static function sessionFetch()
    {        
        if(isset($_SESSION['seqno'])){
            $sessionid=$_SESSION['seqno'];
            if(!empty($_SESSION[$sessionid])){
                return $_SESSION[$sessionid]['cardNumber'];
            }else{
                return '';
            }
        }else{
            return '';
        }    
            
    }
    public static function sessionKill()
    {
        try{      
	unset($_SESSION['seqno']);
        } catch (Exception $ex) {
        return '';
        }   
    }        
}
