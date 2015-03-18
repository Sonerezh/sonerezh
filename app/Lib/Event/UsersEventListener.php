<?php

App::uses('CakeEventListener', 'Event');
App::uses('CakeEmail', 'Network/Email');

class UsersEventListener implements CakeEventListener {

    public function implementedEvents() {
        return array(
            'Model.User.add'                => 'sendUserCreationEmail',
            'Controller.User.resetPassword' => 'sendResetPasswordEmail'
        );
    }

    public function sendUserCreationEmail(CakeEvent $event) {
        $setting = ClassRegistry::init('Setting');
        $settings = $setting->find('first', array('fields' => array('Setting.enable_mail_notification')));
        $mail_notifications_enabled = $settings['Setting']['enable_mail_notification'];

        if ($mail_notifications_enabled) {
            $user_email = $event->subject()->data['User']['email'];
            $email = new CakeEmail('default');
            $email->to($user_email)
                ->subject(__('Welcome on Sonerezh!'))
                ->emailFormat('html')
                ->template('userAdd')
                ->viewVars(compact('user_email'))
                ->send();
        }
    }

    public function sendResetPasswordEmail(CakeEvent $event) {

        $setting = ClassRegistry::init('Setting');
        $settings = $setting->find('first', array('fields' => array('Setting.enable_mail_notification')));
        $mail_notifications_enabled = $settings['Setting']['enable_mail_notification'];

        if ($mail_notifications_enabled) {
            $event_data = $event->subject();
            $user_email = $event_data['email'];
            $token = $event_data['token'];

            $email = new CakeEmail('default');
            $email->to($user_email)
                ->subject(__('Forgot your password?'))
                ->emailFormat('html')
                ->template('sendToken')
                ->viewVars(compact('token'))
                ->send();
        }
    }
}