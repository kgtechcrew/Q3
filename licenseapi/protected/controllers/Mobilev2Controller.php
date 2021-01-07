<?php

class Mobilev2Controller extends Controller
{

    /** Login Authentication * */
    public function actionLogin()
    {
        /** retrieving the username and password * */
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
        {
            $username = trim($_SERVER['PHP_AUTH_USER']);
            $pin      = trim($_SERVER['PHP_AUTH_PW']);
            
            /** Login Validations **/
            $this->loginValidations($username, $pin);
            
        }
        else
        {
            $this->_sendResponse(500);
        }
    }

    /** Logout Process * */
    public function actionLogout()
    {
        $headers        = getallheaders();/** retrieving all the headers * */
        if(isset($headers['Authorization']))
        {
            $jwt_header     = explode(' ', $headers['Authorization']);
            $jwt_auth_token = trim($jwt_header[1]);

            /** Decoding the JSON Web Authentication Token **/
            $payload        = Yii::app()->JWT->logoutDecode($jwt_auth_token);
            $account        = trim($payload->profiles[0]->hfid);/** Taking the firstcard number to destroy the token * */
            $jti            = trim($payload->jti);
            $hf_acc_nos_arr = $payload->profiles;
            

            /** Destroying / Deleting the existing guid/jti in the credentials table(guid / jti is only stored as a reference here) * */
            $group_id = $this->destroyExistingToken($account);

            /** Updating the logout history notes for each card numbers **/
            foreach($hf_acc_nos_arr as $key => $value)
            {
                $user_model = new User();
                $user_model->processLogout($value->hfid,2,$jti);
            }

            /** Log Requests For Logout Function **/
            $this->storeRequestLog('LOGGED OUT SUCCESSFULLY FOR MOBILE APP V2');

            /** entering the guid / jti in the blacklist table * */
            $model                        = new BlackList();
            $model->group_id              = $group_id;
            $model->json_token_identifier = $jti;
            $model->save();
        } 
    }

    /** Password Reset Request * */
    public function actionPasswordresetrequest()
    {
        $request = Yii::app()->request;
        $email   = trim($request->getParam('email', ''));
        if (Credentials::model()->exists('patient_email = :patient_email', array(":patient_email" => $email)))
        {

            /** selecting the guarantor name * */
            $credentials = Credentials::model()->find(array('condition' => 'patient_email = "' . $email . '"'));
            $guar_id     = $credentials->patient_guar_id;
            $guar_det    = Guarantor::model()->find(array('condition' => 'id = "' . $guar_id . '"'));
            $guar_fname  = ucwords(strtolower($guar_det->gmt_guar_fname));

            $generator          = new RandomStringGenerator;
            $rand_code_length   = 8;/** Token Length * */
            $alpha_numeric_code = $generator->generate($rand_code_length);/** Generating a random alphanumeric string * */
            $alpha_numeric_hash = hash('sha256', $alpha_numeric_code);

            /** Password Reset Token  * */
            $token_model     = new Token();
            $jwtPWResetToken = $token_model->jwtPWResetToken($alpha_numeric_hash, $email);

            /** Password Policy * */
            $pwd_pattern = $token_model->passwordPolicy();

            /** sending an email to the guarantor * */
            $this->sendAlphaNumericCodeAsEmail($alpha_numeric_code, $email, $guar_fname);


            /** Response as json * */
            header('Content-Type: application/json');
            $response = array("hash" => $jwtPWResetToken, "pattern" => $pwd_pattern);
            echo CJSON::encode($response);
            Yii::app()->end();
        }
    }

    /** Password Reset * */
    public function actionPasswordreset()
    {
        $headers = getallheaders();/** retrieving all the headers * */
        $jwt_pwd_reset_header     = explode(' ', $headers['Authorization']);
        $jwt_pwd_reset_auth_token = trim($jwt_pwd_reset_header[1]);

        /** Decoding the JSON Web Authentication Token * */
        try
        {
            $payload           = Yii::app()->JWT->decode($jwt_pwd_reset_auth_token);
            $json              = file_get_contents('php://input');
            $email_information = CJSON::decode($json, true);
            $password          = trim($email_information['password']);


            $user          = new User();
            $hash_password = $user->hashPassword($password,'Y');/** Generating the password hash * */
            $guar_email = trim($payload->email);
            if (Credentials::model()->exists('patient_email = :patient_email', array(":patient_email" => $guar_email)))
            {
                $credentials = Credentials::model()->find(array('condition' => 'patient_email = "' . $guar_email . '"'));
                $group_id    = $credentials->group_id;
                $group_info  = Credentials::model()->findAll(array('condition' => 'group_id = "' . $group_id . '"'));
                foreach ($group_info as $key => $value)
                {
                    $guar_info              = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $value->patient_guar_id . '"'));
                    $guar_info->patient_pwd = $hash_password;
                    $guar_info->old_algorithm_for_reset = 'Y';
                    $guar_info->update();
                }

                /** Generating jwtAuthToken in response * */
                $token_model  = new Token();
                $jwtAuthToken = $token_model->jwtAuthToken($group_id, 1);

                header('Content-Type: text/plain');
                echo $jwtAuthToken;
                Yii::app()->end();
            }
            else
            {
                $this->_sendResponse(401);
            }
        } catch (Exception $ex)
        {
            $exception_message = $ex->getMessage();
            $this->_sendResponse(401, $exception_message);
        }
    }

    /** Password Policy * */
    public function actionPasswordpolicy()
    {
        $token_model = new Token();
        $pwd_pattern = $token_model->passwordPolicy();
        $response    = array("pattern" => $pwd_pattern);
        
        header('Content-Type: application/json');
        echo CJSON::encode($response);
        Yii::app()->end();
    }
    
    
    public function actionSignup()
    {
        $json           = file_get_contents('php://input');
        $signup_details = CJSON::decode($json, true);

        $signup_lastname  = trim($signup_details['lastname']);
        $hff_account      = trim($signup_details['hff_card_id']);
        $signup_email     = trim($signup_details['email']);
        $signup_pwd       = trim($signup_details['password']);
        $signup_status    = 0;
        
      /** Before signup check the patient email already exists. * */
      $patient_email_exists = Credentials::model()->find(array('condition' => 'patient_email = "' . $signup_email . '"'));

      if (empty($patient_email_exists))
      {
        if(Loan::model()->exists('lmt_card_no = :lmt_card_no', array(":lmt_card_no" => $hff_account)))
        {
            $criteria = new CDbCriteria();
            $criteria->condition = "lmt_card_no = '".$hff_account."' and lmt_loan_status in('A','P','PIF')";
            $loan_details = Loan::model()->find($criteria);
            
            if(!empty($loan_details))
            {
                $guar_id = $loan_details->lmt_guar_id;
                $guarantor_details = Guarantor::model()->find(array('condition' => 'id = "' . $guar_id . '"'));
                $guarantor_lastname = trim($guarantor_details->gmt_guar_lname);

                /** Check the guarantor Already singup. **/
                $guar_id_exists = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $guar_id . '"'));
                
              if(empty($guar_id_exists))
              {
                if(strtolower($signup_lastname) == strtolower($guarantor_lastname))
                {
                    if(Credentials::model()->exists('patient_guar_id = :patient_guar_id', array(":patient_guar_id" => $guar_id)))
                    {  
                       $credential_details = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $guar_id . '"'));
                       if(empty($credential_details->patient_email) && empty($credential_details->patient_pwd))
                       {
                           $uniq_group_id = $credential_details->group_id;
                           $cred_grp_details = Credentials::model()->findAll(array('condition' => 'group_id = "' . $uniq_group_id . '"'));
                           foreach($cred_grp_details as $key => $value)
                           {
                               $cred_sub_details = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $value->patient_guar_id . '"'));
                               $cred_sub_details->delete();  
                           }
                           $signup_status = 1;
                       }
                       else
                       {
                           if($credential_details->patient_signup_active_status == 'N')
                           {
                               $uniq_group_id = $credential_details->group_id;
                               $cred_grp_details = Credentials::model()->findAll(array('condition' => 'group_id = "' . $uniq_group_id . '"'));
                               foreach($cred_grp_details as $key => $value)
                               {
                                    $cred_sub_details = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $value->patient_guar_id . '"'));
                                    $cred_sub_details->delete();  
                               }
                               $signup_status = 1;
                           } 
                       }

                       if($signup_status == 1)
                       {
                            $signup_groupid = $this->getSequenceNo();
                            $this->saveSignUpDetails($guar_id,$signup_email,$signup_pwd,$signup_groupid,$hff_account); 
                       }
                       else
                       {
                            $guid_token_model = new Token();
                            $guid   = $guid_token_model->generateGuid();
                            $signup_groupid = $credential_details->group_id;
                            $this->generateSignupToken($signup_groupid, $guid);
                       }
                    }
                    else
                    {
                        $credential_criteria = new CDbCriteria();
                        $credential_criteria->condition = "patient_email = '".$signup_email."' and patient_signup_active_status = 'Y'";
                        $credential_email_details = Credentials::model()->find($credential_criteria);
                        if(!empty($credential_email_details))
                        {
                            $credential_db_email = trim($credential_email_details->patient_email);
                            if($credential_db_email == $signup_email)
                            {
                                $signup_groupid =  $credential_email_details->group_id;
                            }
                            else
                            {
                                $signup_groupid = $this->getSequenceNo();
                            }
                        }
                        else
                        {
                            $signup_groupid = $this->getSequenceNo();
                        }
                        $this->saveSignUpDetails($guar_id,$signup_email,$signup_pwd,$signup_groupid,$hff_account);
                    }
                }
                else
                {
                   $response = array('message' => 'Unable to match registration with existing AccessOne account.');
                   $this->_sendResponse(404, $response, 'application/json');
                }
            }
            else
            {
                $response = array('message' => 'Unable to match registration with existing AccessOne account.');
                $this->_sendResponse(404, $response, 'application/json');
            }
        }
        else
        {
            $response = array('message' => 'Unable to match registration with existing AccessOne account.');
            $this->_sendResponse(404, $response, 'application/json');   
        }  
    }
        else
        {
            $response = array('message' => 'Unable to match registration with existing AccessOne account.');
            $this->_sendResponse(404, $response, 'application/json');   
        }  
      }
      else
      {
            $response = array('message' => 'Unable to match registration with existing AccessOne account.');
            $this->_sendResponse(404, $response, 'application/json');
      }
    }
    
    
    /** Getting the sequence number and updating it as a groupid  **/
    public function getSequenceNo()
    {
        $connection = Yii::app()->db;
        $sql        = "select getnextseq('PATIENT_GROUP_ID')";
        $command    = $connection->createCommand($sql);
        $data       = $command->queryScalar();
        return $data;
    }
    
    /** Saving the signup details and generating the signup token **/
    public function saveSignUpDetails($guar_id, $signup_email, $signup_pwd, $signup_groupid, $hff_account)
    {
        $user_model                                     = new User();
        $credential_model                               = new Credentials();
        $guid_token_model                               = new Token();
        $guid                                           = $guid_token_model->generateGuid();
        $credential_model->patient_guar_id              = $guar_id;
        $credential_model->patient_email                = $signup_email;
        $credential_model->patient_pwd                  = $user_model->hashPassword($signup_pwd);
        $credential_model->patient_signup_active_status = 'Y';
        $credential_model->reg_comp_via_mobile          = 'Y';
        $credential_model->mobile_signup                = 'Y';
        $credential_model->group_id                     = $signup_groupid;
        $credential_model->jti_mobile                   = $guid;
        $credential_model->patient_signup_stage         = 1;
        $credential_model->save();
        
        /** Updating the email id against the guarantor in the contact master table **/
        $criteria = new CDbCriteria();
        $criteria->condition = "cnt_guar_id = '".$guar_id."' and cnt_contact_ctgy = 'G'";
        $contact_details = AuthorizedContact::model()->findAll($criteria);
        if(empty($contact_details))
        {
            $auth_contact                   = new AuthorizedContact();
            $auth_contact->cnt_cont_email1  = trim($signup_email);
            $auth_contact->cnt_contact_ctgy = 'G';
            $auth_contact->cnt_guar_id      = $guar_id;
            $auth_contact->save();
            $comments_text = 'Profile Changes : Guarantor Email has added as '.trim($signup_email).' during signup process via mobile application.';
            $this->insertComments($guar_id, $hff_account, $comments_text);
        }
        else
        {
            $old_email = $contact_details[0]->cnt_cont_email1;
            foreach($contact_details as $key => $value)
            {
                $sub_criteria = new CDbCriteria();
                $sub_criteria->condition = "cnt_guar_id = '".$value->cnt_guar_id."' and cnt_contact_ctgy = 'G'";
                $contact_sub_details = AuthorizedContact::model()->find($sub_criteria);
                $contact_sub_details->cnt_cont_email1 = trim($signup_email);
                $contact_sub_details->update();  
            }
            if(trim($old_email) != trim($signup_email))
            {
                if(empty($old_email))
                {
                   $comments_text = 'Profile Changes : Guarantor Email has added as '.trim($signup_email).' during signup process via mobile application.'; 
                }
                else
                {
                   $comments_text = 'Profile Changes : Guarantor Email has changed from '.trim($old_email).' to '.trim($signup_email).' during signup process via mobile application.'; 
                }  
            }
            else
            {
                $comments_text = 'Profile Changes : Guarantor Email has added as '.trim($signup_email).' during signup process via mobile application.';
            }
            $this->insertComments($guar_id, $hff_account, $comments_text);
        }
        
        /** Updating the history notes as registration process completed after successful signup **/
        $sql     = "CALL sp_patient_portal('PATIENT_REG_COMMENTS'," . $signup_groupid . ",'','','','','','','','','','','','','','','','','','','','','M','','','', @p_out);";
        $command = Yii::app()->db->createCommand($sql);
        $command->execute();
        
        
        $this->generateSignupToken($signup_groupid, $guid); 
    }
    
    /** insert email comments **/
    public function insertComments($guar_id, $hff_account, $comments_text)
    {
        $loan_details = Loan::model()->find(array('condition' => 'lmt_card_no = "' . $hff_account . '"'));
        $comment_det   = array(
            ":p_comment"   => $comments_text,
            ":p_user_id"   => 0,
            ":p_guar_id"   => $guar_id,
            ":p_loan_id"   => $loan_details->lmt_loan_id,
            ":p_client_id" => $loan_details->lmt_clnt_id,
            ":p_event_id"  => 1419
        );

        $keys        = Controller::bindParamArray($comment_det);
        $comm_insert = "CALL sp_create_comment_by_event(" . $keys . ")";
        $command     = Yii::app()->db->createCommand($comm_insert);
        $command->bindValues($comment_det);
        $command->execute();
    }
    
    /** signup token generation after saving the password **/
    public function generateSignupToken($signup_groupid, $guid)
    {
        $token = new Token();
        $token_account = $token->fetchAppropriateAccount($signup_groupid);
        $iat   = time();
        $exp   = $iat + 24 * 60 * 60;
        $token = array(
            "iat"  => $iat,
            "exp"  => $exp,
            "jti"  => $guid,
            "profiles" => $token_account,
        );
        
        $jwt = Yii::app()->JWT->encode($token);
        
        header('Content-Type: text/plain');
        echo $jwt;
        Yii::app()->end();
    }
    
    

    /** sending the alphanumeric code as email to the guarantor in order to reset the password * */
    public function sendAlphaNumericCodeAsEmail($alpha_numeric_code, $email, $guar_fname)
    {
        ini_set('error_reporting', 0);
        $hffmail   = new HffMailer;
        $hffmail->SetFromAddress("noresponse@myaccessone.com", 'AccessOne');
        $hffmail->SetSubject('AccessOne - Mobile Application Password Reset Information - ' . date("m/d/Y H:i:s"));
        $cus_phone = $this->getRepAssistance();
        $filepath  = Yii::getPathOfAlias('application.components.views') . '/' . 'email.php';
        $mailcontent   = CConsoleCommand::renderFile($filepath, array('cus_phone' => $cus_phone, 'alpha_numeric_code' => $alpha_numeric_code, 'guar_fname' => $guar_fname), true);
        $templatepath = Yii::getPathOfAlias('application.components.views') . '/' . 'aoMailTemplate.php';
        $controller  = new Controller('context');
        $content      = $controller->renderInternal($templatepath, array('mail_content' => $mailcontent), true);
        $hffmail->AddToAddress($email);
        $hffmail->SetAltBody("");
        $hffmail->SetBody($content);
        $hffmail->ClearReplyTos();
        try
        {
            $hffmail->SendWithDbConfirmation();
        } catch (Exception $e)
        {
            return false;
        }
    }

    /** Get HealthFirst Financial Patient Assistance * */
    public function getRepAssistance()
    {
        $sql          = "select trim(ety_description) from etm_entity_master etm where etm.ety_key like '%phone1%'";
        $phone_number = Yii::app()->db->createCommand($sql)->queryScalar();
        return $phone_number;
    }

    /** Deleting the existing signed up incomplete guarantors * */
    public function delSigIncGuar($group_id)
    {
        /** Deleting the incomplete signed guarantor record from plc_patient_login_credentials table * */
        Credentials::model()->deleteAll(array('condition' => 'group_id = "' . $group_id . '"'));
    }

    
    
    /** Login Validations For Mobile App Email & Password To Increase The Speed **/
    public function loginValidations($username, $pin)
    {
        if (Credentials::model()->exists('patient_email = :patient_email', array(":patient_email" => $username)))
        {
            $credentials = Credentials::model()->find(array('condition' => 'patient_email = "' . $username . '"'));
            $password    = $credentials->patient_pwd;
            $group_id    = $credentials->group_id;
            $algorithm_status = !empty($credentials->old_algorithm_for_reset) ? $credentials->old_algorithm_for_reset : 'N';

            $user_model = new User();
            $valid_pwd  = $user_model->validatePassword($password, $pin, $algorithm_status);
            if ($valid_pwd)
            {
                $active_status = $credentials->patient_signup_active_status;

                /** jwtAuth Token Generation * */
                $token_model   = new Token();
                $encoded_token = $token_model->jwtAuthToken($group_id);

                if ($active_status != 'Y')
                {
                    /** Edit Tracking Notes For History Updation * */
                    $sql     = "CALL sp_patient_portal('PATIENT_REG_COMMENTS'," . $group_id . ",'','','','','','','','','','','','','','','','','','','','','M','','','', @p_out);";
                    $command = Yii::app()->db->createCommand($sql);
                    $command->execute();
                }

                /** Log Requests For Login Function * */
                $this->storeRequestLog('LOGGED IN SUCCESSFULLY FOR MOBILE APP V2');

                header('Content-Type: text/plain');
                echo $encoded_token;
                Yii::app()->end();
            }
            else
            {
                $this->_sendResponse(401, 'Invalid login');
            }
        }
        else
        {
            $model    = new User();
            /** Checking the old account & pin exists with respective to the account * */
            $record = $model->loginProcess($username, $pin);
            if(!empty($record))
            {
                $loan_details = Loan::model()->find(array('condition' => 'lmt_card_no = "' . trim($record['pgt_cardnumber']) . '"'));
                $guar_id      = $loan_details->lmt_guar_id;
                if (Credentials::model()->exists('patient_guar_id = :patient_guar_id', array(":patient_guar_id" => $guar_id)))
                {
                    $credentials                  = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $guar_id . '"'));
                    $patient_email                = $credentials->patient_email;
                    $patient_pwd                  = $credentials->patient_pwd;
                    if (!empty($patient_email) && !empty($patient_pwd))
                    {
                        $this->_sendResponse(307, 'User has new v2 credential profile but attempted to log in with old method.');
                    }
                    else
                    {
                        $group_id = $credentials->group_id;
                        /** Deleting the existing incomplete signedup records without email & password * */
                        $this->delSigIncGuar($group_id);
                        $this->_sendResponse(303, 'Credentials are still old model and user needs to register with the new patient portal.');
                    }
                }
                else
                {
                    $this->_sendResponse(303, 'Credentials are still old model and user needs to register with the new patient portal.');
                }
            }
            else
            {
                 $this->_sendResponse(401, 'Invalid login');
            }
        }
    }
    
    /**
     * Destroying the guid against the credentials table 
     * The guid / jti is only stored as a reference here in the credentials table 
     * * */
    public function destroyExistingToken($account)
    {
        /** fetching the guarantor id * */
        $loan_details = Loan::model()->find(array('condition' => 'lmt_card_no = "' . $account . '"'));
        $guar_id      = $loan_details->lmt_guar_id;

        /** fetching the group id to destroy the token against the login credentials * */
        $cre_det  = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $guar_id . '"'));
        $group_id = $cre_det->group_id;

        /** destroying the token * */
        $cred_grp_det = Credentials::model()->findAll(array('condition' => 'group_id = "' . $group_id . '"'));
        foreach ($cred_grp_det as $key => $value)
        {
            $cred_det             = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $value->patient_guar_id . '"'));
            $cred_det->jti_mobile = '';
            $cred_det->update();
        }

        return $group_id;
    }

}
