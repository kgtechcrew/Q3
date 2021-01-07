<?php

ini_set('memory_limit', '-1');

class SchedulerCommand extends CConsoleCommand
{

    public function run($args)
    {
        if (!stristr(PHP_OS, 'WIN'))
        {
            $log_dir = "/tmp/CronLogFiles";
            if (!is_dir($log_dir))
            {
                mkdir($log_dir);
            }
        }

        switch ($args[0])
        {
            case 'cc_process':
                $genesis = new Genesis();
                $genesis->genesisExec($args[0], $args[1]);
                break;
            case 'cc_breath':
                $breath  = new Breath();
                $breath->breathExec($args[0], $args[1], $args[2]);
                break;
            case 'activation_first_reminder':
                $model   = new AccountActivation();
                $model->sendActivationMail();
                break;
            case 'activation_second_reminder':
                $model   = new AccountActivation();
                $model->sendSecondReminder();
                break;
            case 'send_account_text_alerts':
                $model   = new AccountTextAlerts();
                break;
            // This scheduler is using for generating welcome packets to guarantor.
            case 'ao_welcome_letter':
                $model   = new WelcomeLetterScheduler();
                $model->sendWelcomeLetterMail();
                break;
            // This scheduler is used for generating the welcome letter as an attachment for active account(s).
            case 'ao_unique_welcome_letter':
                $model = new UniqueWelcomeLetterScheduler();
                $model->sendUniqueWelcomeLetter();
                break;
            case 'ao_welcome_letter_patient_mail_insert':
                $model = new WelcomePacketPatientMail();
                $model->welcomePacketPatientMailInsert();
                break;
            /*case 'ao_welcome_letter_update':
                $model   = new WelcomeLetterSchedulerUpdate();
                $model->sendWelcomeLetterUpdateMail();
                break;
            case 'ao_welcome_letter_update_temp_wl_pp':
                $model   = new WelcomeLetterSchedulerUpdateTemp();
                $model->tmpSendWelcomeLetterGen();
                break;
            case 'ao_welcome_letter_update_temp_aod':
                $model   = new WelcomeLetterSchedulerUpdateTemp();
                $model->tmpSendWelcomeLetterAODGen($args[1]);
                break;
            case 'ao_welcome_letter_update_temp_gen':
                $model   = new WelcomeLetterSchedulerUpdateTemp();
                $model->tmpSendWelcomeLetterUpdateMail($args[1]);
                break;*/
            
            default :
                break;
        }
    }

}

?>