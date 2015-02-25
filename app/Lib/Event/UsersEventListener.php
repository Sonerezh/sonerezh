<?php

App::uses('CakeEventListener', 'Event');

class UsersEventListener implements CakeEventListener {

    public function implementedEvents() {
        return array(
            'Model.User.add'    => 'sendEmail'
        );
    }

    public function sendEmail($event) {
        $user_email = $event->subject()->data['User']['email'];

        App::uses('CakeEmail', 'Network/Email');
        $email = new CakeEmail('default');
        $email->to($user_email)
            ->subject(__('Welcome on Sonerezh !'))
            ->emailFormat('html')
            ->template('userAdd')
            ->send();
    }
}