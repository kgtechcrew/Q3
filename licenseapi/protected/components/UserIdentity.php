<?php
/**
* $Id: UserIdentity.php 43 2015-12-08 14:32:20Z chrdel $
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
/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    public $_id;
    public $account;
    public $pin;
        
    public function __construct($account,$pin)
    {
        $this->account = $account;
        $this->pin     = $pin;
        parent::__construct($this->account,$this->pin);

    }
    	
    public function authenticate()
    {
        $usrmodel = new User;
        $usrmodel->attributes;
        $record = Authenticate::model()->findAll('pgt_cardnumber=:userName OR pgt_line_item_id=:userName',array(':userName' => $this->account));
        $pin    = CHtml::listData($record,'pgt_line_item_id','pgt_pin');

        if($record===null)
        {
            $this->errorCode = static::ERROR_USERNAME_INVALID;
        }
        else if(!in_array($this->password,$pin)) 
        {
            $this->errorCode = static::ERROR_PASSWORD_INVALID;
        }
        else
        {
            $this->_id     = $record[0]->pgt_id;    
            $this->account  = $record[0]->pgt_cardnumber;
            $this->setState('id',$this->_id);
            $this->setState('account',$this->account);                       
            
            $this->errorCode = static::ERROR_NONE;
        }
        return !$this->errorCode;

    }        
    
    public function authenticateAo()
    {
        $users           = array(
            'aodemo'  => 'cykabwfjcs',
            'aoadmin' => 'gpdhukdrjh',
        );        
        if (!isset($users[$this->username]))
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        elseif ($users[$this->username] !== $this->password)
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        else
            $this->errorCode = self::ERROR_NONE;
        return !$this->errorCode;
    }
        /**
	 * @return integer the ID of the user record
	 */
	public function getId()
	{
		return $this->_id;
	}
}