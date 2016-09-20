<?php

App::uses("AppController", "Controller");
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

/**
 * Class UsersController
 *
 * @property User $User
 */
class UsersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('setResetPasswordToken', 'resetPassword');
    }

    public function isAuthorized($user) {
        if ($user['role'] == "admin") {
            return true;
        } elseif ($this->action == 'logout') {
            return true;
        } elseif (in_array($this->action, array('edit', 'deleteAvatar')) && $this->passedArgs[0] == $this->Auth->user('id')) {
            return true;
        }
        return false;
    }

    public function index() {
        $users = $this->User->find('all');

        foreach ($users as $key => $user) {
            if (!empty($user['User']['avatar'])) {
                $users[$key]['User']['avatar'] = AVATARS_DIR.DS.$user['User']['avatar'];
            } else {
                $gavatarId = md5($user['User']['email']);
                $users[$key]['User']['gravatar'] = 'https://secure.gravatar.com/avatar/'.$gavatarId.'.png';
            }
        }
        $this->set('users', $users);
    }

    public function add() {
        // Send email on user creation
        App::uses('UsersEventListener', 'Event');
        $usersEventListener = new UsersEventListener();
        $this->User->getEventManager()->attach($usersEventListener);

        if ($this->request->is('post')) {
            $this->User->create();
            if ($this->User->save($this->request->data)) {
                $this->Flash->success(__('User created: '), array(
                    'params' => array($this->request->data['User']['email'])
                ));
            } else {
                $this->Flash->error(__('Unable to create a user. Make sure its email is not already used and his password is at least 8 characters long.'));
            }
            $this->redirect(array('action' => 'index'));
        }
    }

    public function edit($id = null) {

        if ($id === null) {
            $this->redirect($this->referer());
        }

        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }

        if ($this->request->is(array('post', 'put'))) {
            if ($this->User->save($this->request->data)) {
                $this->Flash->success(__('User updated: '), array(
                    'params' => array($this->request->data['User']['email'])
                ));
            } else {
                $this->Flash->error(__('Something went wrong!'));
            }
        }
        $user = $this->User->findById($id);
        if (empty($this->request->data)) {
            $this->request->data = $user;
            unset($this->request->data['User']['password']);
        }

        $this->set('user', $user);
    }

    public function delete($id) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }

        $user = $this->User->findById($id);

        if ($this->User->delete($id)) {
            $this->Flash->success(__('User deleted: '), array(
                'params' => array($user['User']['email'])
            ));
        }
        $this->redirect($this->referer());
    }

    public function deleteAvatar($id = null) {

        if (!isset($id)) {
            $this->redirect($this->referer());
        }

        $user = $this->User->findById($id);
        $this->User->id = $user['User']['id'];

        $avatar = IMAGES.AVATARS_DIR.DS.$user['User']['avatar'];

        if (file_exists($avatar)) {
            unlink($avatar);           
            if ($this->User->saveField('avatar', null)) {
                $this->Flash->success(__('Avatar has been successfully removed!'));
                $this->redirect(array('action' => 'edit/'.$id));
            }
        } else {
            $this->Flash->error(__('Something went wrong!'));
            $this->redirect(array('action' => 'edit/'.$id));
        }
    }

	public function login() {
	    $this->layout = 'login';

        $this->loadModel('Setting');
        $settings = $this->Setting->find('first', array('fields' => 'Setting.enable_mail_notification'));
        $this->set(compact('settings'));

        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                if ($this->request->data['User']['rememberme']) {
                    $user = $this->User->find('first', array('fields' => array('password'), 'conditions' => array('User.id' => $this->Auth->user('id'))));
                    $passwordHasher = new BlowfishPasswordHasher();
                    $this->Cookie->write('auth', $this->Auth->user('id').':'.$passwordHasher->hash($this->request->data['User']['email']).':'.$passwordHasher->hash($user['User']['password']));
                } else {
                    $this->Session->delete('auth');
                }
                return $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Flash->error(__('Wrong credentials!'));
                CakeLog::write('error', 'Failed authentication for ' . $this->request->data['User']['email'] . ' from ' . $this->request->clientIp());
            }
        }
	}

    public function logout() {
        $this->Session->delete('auth');
        $this->Cookie->delete('auth');
        return $this->redirect($this->Auth->logout());
    }

    /**
     * This function allows users to reset their password
     * A token is forged from user informations and send to the provided email
     * Thanks to @bdelespierre (http://bdelespierre.fr/article/bien-plus-quun-simple-jeton/)
     */
    public function setResetPasswordToken() {
        if ($this->request->is('POST')) {
            if (!empty($this->request->data['User']['email'])) {
                $user = $this->User->find('first', array(
                    'conditions' => array('User.email' => $this->request->data['User']['email'])
                ));
            }

            if (empty($user)) {
                $this->Flash->error(__('Unable to find your account'));
                $this->redirect(array('action' => 'login'));
            }

            $this->DateComponent = $this->Components->load('Date');
            $this->UrlComponent = $this->Components->load('Url');

            $id = (int)$user['User']['id'];
            $date = $this->DateComponent->date16_encode(date('y'), date('m'), date('d'));
            $entropy = mt_rand();
            $password_crc32 = crc32($user['User']['password']);
            $binary_token = pack('ISSL', $id, $date, $entropy, $password_crc32);
            $urlsafe_token =$this->UrlComponent->base64url_encode($binary_token);

            // Send the token
            if ($urlsafe_token) {
                App::uses('UsersEventListener', 'Event');

                $usersEventListener = new UsersEventListener();
                $event = new CakeEvent('Controller.User.resetPassword', array('email' => $user['User']['email'], 'token' => $urlsafe_token));

                $this->User->getEventManager()->attach($usersEventListener);
                $this->User->getEventManager()->dispatch($event);

                $this->Flash->success(__('Email successfully sent.'));
            } else {
                $this->Flash->error(__('Unable to generate a token.'));
            }
            $this->redirect(array('action' => 'login'));
        }
    }

    public function resetPassword() {
        $this->layout = 'login';

        $token = isset($this->request->query['t']) ? $this->request->query['t'] : null;

        if (!$token) {
            $this->Flash->error(__('You need to provide a token.'));
            $this->redirect(array('action' => 'login'));
        }

        $this->UrlComponent = $this->Components->load('Url');
        $binary_token = $this->UrlComponent->base64url_decode($token);

        if (!$binary_token) {
            $this->Flash->error(__('Unable to decode your token.'));
            $this->redirect(array('action' => 'login'));
        }

        $token_data = @unpack('Iid/Sdate/Sentropy/Lpassword_crc32', $binary_token);

        if (!$token_data) {
            $this->Flash->error(__('Unable to read your token.'));
            $this->redirect(array('action' => 'login'));
        }

        $this->DateComponent = $this->Components->load('Date');
        list($year, $month, $day) = $this->DateComponent->date16_decode($token_data['date']);
        $token_date = "{$year}-{$month}-{$day}";
        $today = date('y-n-d');

        if ($token_date != $today) {
            $this->Flash->error(__('The token has expired.'));
            $this->redirect(array('action' => 'login'));
        }

        $token_id = $token_data['id'];
        $user = $this->User->find('first', array(
            'fields'        => array('User.id', 'User.email', 'User.password'),
            'conditions'    => array('User.id' => $token_id)
        ));

        if (empty($user)) {
            $this->Flash->error(__('Unable to find your account.'));
            $this->redirect(array('action' => 'login'));
        } elseif (crc32($user['User']['password']) != $token_data['password_crc32']) {
            $this->Flash->errror(__('Wrong token.'));
            $this->redirect(array('action' => 'login'));
        }

        $this->set(compact('user'));

        if ($this->request->is(array('post', 'put'))) {
            $user['User']['password'] = $this->request->data['User']['password'];
            $user['User']['confirm_password'] = $this->request->data['User']['confirm_password'];

            if ($this->User->save($user)) {
                $this->Flash->success(__('Your password has been updated.'));
                $this->redirect(array('action' => 'login'));
            } else {
                $this->Flash->error(__('Unable to update your password.'));
            }
        }
    }
}