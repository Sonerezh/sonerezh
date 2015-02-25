<?php

App::uses("AppController", "Controller");

/**
 * Class UsersController
 *
 * @property User $User
 */
class UsersController extends AppController{


    public function isAuthorized($user){
        if($user['role'] == "admin"){
            return true;
        }else if (in_array($this->action, array('logout', 'edit', 'deleteAvatar'))) {
            return true;
        }
        return false;
    }

    public function index(){
        $users = $this->User->find('all');

        foreach($users as $key => $user){
            if(!empty($user['User']['avatar'])){
                $users[$key]['User']['avatar'] = AVATARS_DIR.DS.$user['User']['avatar'];
            }else{
                $gavatarId = md5($user['User']['email']);
                $users[$key]['User']['gravatar'] = 'https://secure.gravatar.com/avatar/'.$gavatarId.'.png';
            }
        }

        $this->set('users', $users);
    }

    public function add(){

        // (Not implemented yet) Send email on user creation
        //App::uses('UsersEventListener', 'Event');
        //$usersEventListener = new UsersEventListener();
        //$this->User->getEventManager()->attach($usersEventListener);

        if($this->request->is('post')){
            $this->User->create();
            if($this->User->save($this->request->data)){
                $this->Session->setFlash(__('A new user has been created!'), 'flash_success');
            }else{
                $this->Session->setFlash(__('Unable to create a user. Make sure his email is not already used and his password is at least 8 characters long.'), 'flash_error');
            }
            $this->redirect(array('action' => 'index'));
        }
    }

    public function edit($id = null){

        if($id === null){
            $this->redirect($this->referer());
        }

        $this->User->id = $id;
        if(!$this->User->exists()){
            throw new NotFoundException(__('Invalid user'));
        }

        if($this->request->is(array('post', 'put'))){
            if($this->User->save($this->request->data)){
                $this->Session->setFlash(__('User '.$id.' ('.$this->request->data['User']['email'].') has been successfully updated!'), 'flash_success');
            }else{
                $this->Session->setFlash(__('Something went wrong!'), 'flash_error');
            }
        }
        $user = $this->User->findById($id);
        if(empty($this->request->data)){
            $this->request->data = $user;
            unset($this->request->data['User']['password']);
        }

        $this->set('user', $user);
    }

    public function delete($id){
        if($this->request->is('get')){
            throw new MethodNotAllowedException();
        }

        $user = $this->User->findById($id);

        if($this->User->delete($id)){
            $this->Session->setFlash(__('User '.$id.' ('.$user['User']['email'].') has been successfully deleted!'), 'flash_success');
        }
        $this->redirect($this->referer());
    }

    public function deleteAvatar($id = null){

        if(!isset($id)){
            $this->redirect($this->referer());
        }

        $user = $this->User->findById($id);
        $this->User->id = $user['User']['id'];

        $avatar = IMAGES.AVATARS_DIR.DS.$user['User']['avatar'];

        if(file_exists($avatar)){
            unlink($avatar);           
            if($this->User->saveField('avatar', null)){
                $this->Session->setFlash(__('Avatar has been successfully removed!'), 'flash_success');
                $this->redirect(array('action' => 'edit/'.$id));
            }
        }else{
            $this->Session->setFlash(__('Something went wrong!'), 'flash_error');
            $this->redirect(array('action' => 'edit/'.$id));
        }
    }

	public function login(){
		$this->layout = 'login';
        
        if($this->request->is('post')){
            if($this->Auth->login()){
                return $this->redirect($this->Auth->redirectUrl());
            }else{
                $this->Session->setFlash(__('Wrong credentials!'), 'flash_error');
            }
        }
	}

    public function logout(){
        return $this->redirect($this->Auth->logout());
    }
}