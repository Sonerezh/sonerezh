<?php

App::uses('CakeEventListener', 'Event');
App::uses('CakeEmail', 'Network/Email');

class UsersEventListener implements CakeEventListener {

    public function implementedEvents() {
        return array(
            'Model.User.add'    => 'sendUserCreationEmail'
        );
    }

    public function sendUserCreationEmail(CakeEvent $event) {
        $mail_notifications_enabled = true;

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
}