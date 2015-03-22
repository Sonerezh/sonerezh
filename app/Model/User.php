<?php

App::uses('AppModel', 'Model');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

/**
 * Class User
 *
 * @property Playlist $Playlist
 */
class User extends AppModel {
    public $hasMany = array(
        'Playlist' => array(
            'dependent' => true
        )
    );
    public $validate = array(
        'email' => array(
            'email'     => array(
                'rule'      => 'email',
                'required'  => true,
                'message'   => 'Login must be a valid email.'
            ),
            'isUnique'  => array(
                'rule'      => 'isUnique',
                'required'  => true,
                'message'   => 'Login already used.'
            ),
            'notEmpty'  => array(
                'rule'      => 'notEmpty',
                'required'  => true,
                'message'   => 'Login cannot be empty.'
            )
        ),
        'password' => array(
            'minLength' => array(
                'rule'      => array('minLength', 8),
                'required'  => true,
                'message'   => 'Password must be at least 8 characters long.'
            ),
            'notEmpty'  => array(
                'rule'      => 'notEmpty',
                'required'  => true
            )
        ),
        'confirm_password' => array(
            'notEmpty' => array(
                'rule'      => 'notEmpty',
                'message'   => 'Please confirm the new password',
                'required'  => true
            ),
            'confirmPassword' => array(
                'rule'      => 'confirmPassword',
                'message'   => 'Wrong confirmation password.'
            )
        ),
        'role'  => array(
            'inList'     => array(
                'rule'      => array('inlist', array('admin', 'listener')),
                'required'  => true,
                'message'   => 'Incorrect role.'
            )
        ),
        'avatar' => array(
            'uploadError' => array(
                'rule' => 'uploadError',
                'message' => 'Something went wrong with the upload.'
            ),
            'mimeType' => array(
                'rule' => array('mimeType', array('image/gif', 'image/jpeg', 'image/png')),
                'message' => 'Your avatar must be in a correct format (JPEG, PNG, GIF).'
            )
        )
    );

    public function beforeDelete($cascade = false) {
        $user = $this->find('first', array(
            'conditions'    => array('User.id' => $this->id)
            )
        );

        if (!empty($user['User']['avatar'])) {
            $avatar = IMAGES.'avatars'.DS.$user['User']['avatar'];
            if (file_exists($avatar)) {
                unlink($avatar);
            }
        }
        return true;
    }

    public function beforeSave($options = array()) {
        if (isset($this->data[$this->alias]['password'])) {
            $passwordHasher = new BlowfishPasswordHasher();
            $this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
        }

        if (isset($this->data[$this->alias]['avatar']) && is_array($this->data[$this->alias]['avatar'])) {
            $this->__uploadAvatar($this->data[$this->alias]['avatar']);
        }
        return true;
    }

    public function afterSave($created, $options = array()) {
        parent::afterSave($created, $options);
        if ($this->data[$this->alias]['id'] == AuthComponent::user('id')) {
            if (isset($this->data[$this->alias]['password'])) {
                unset($this->data[$this->alias]['password']);
            }
            $newData = array_merge(AuthComponent::user(), $this->data[$this->alias]);
            App::uses('SessionComponent', 'Controller/Component');
            CakeSession::write(AuthComponent::$sessionKey, $newData);
        }

        // Raise user creation event
        if ($created) {
            $event = new CakeEvent('Model.User.add', $this);
            $this->getEventManager()->dispatch($event);
        }
    }

    public function beforeValidate($options = array()) {
        // On vérifie que l'utilisateur a un ID
        if (isset($this->data[$this->alias]['id'])) {
            // Si aucun mot de passe n'est modifié, on supprime la validation
            if (empty($this->data[$this->alias]['password'])) {
                $validator = $this->validator();
                unset($validator['password'], $validator['confirm_password'], $this->data[$this->alias]['password'], $this->data[$this->alias]['confirm_password']);
            }
            //Si aucun avatar n'est envoyé, on supprime la validation
            if (isset($this->data[$this->alias]['avatar']) && $this->data[$this->alias]['avatar']['error'] === 4) {
                unset($this->data[$this->alias]['avatar']);
            }
            //Si aucun role n'est envoyé, on supprime la validation
            if (!isset($this->data[$this->alias]['role'])) {
                $validator = $this->validator();
                unset($validator['role']);
            }
        }
        return true;
    }

    public function confirmPassword () {
        if ($this->data[$this->alias]['password'] == $this->data[$this->alias]['confirm_password']) {
            return true;
        }
        return false;
    }

    private function __uploadAvatar($avatarData){
        $avatarFolder = IMAGES.AVATARS_DIR;
        $avatarId = md5(microtime(true));
        $ext = strtolower(substr(strrchr($avatarData['name'], "."), 1));
        $uploadPath = $avatarFolder.DS.$avatarId.'.'.$ext;

        if (!file_exists($avatarFolder)) {
            mkdir($avatarFolder);
        }
        if (isset($this->data[$this->alias]['id'])) {
            $oldAvatar = $this->find('first', array('fields' => array('avatar'), 'conditions' => array('User.id' => $this->data[$this->alias]['id'])));
            if (!empty($oldAvatar['User']['avatar'])) {
                $oldAvatar = explode('.', $oldAvatar['User']['avatar']);
                $avatarFinder = preg_grep('/^'.$oldAvatar[0].'\./', scandir(IMAGES.AVATARS_DIR));
                $resizedAvatar = preg_grep('/^'.$oldAvatar[0].'_/', scandir(RESIZED_DIR));

                if (!empty($avatarFinder)) {
                    foreach ($avatarFinder as $v) {
                        unlink($avatarFolder.DS.$v);
                    }
                }
                if (!empty($resizedAvatar)) {
                    foreach ($resizedAvatar as $v) {
                        unlink(RESIZED_DIR.DS.$v);
                    }
                }
            }
        }

        if (move_uploaded_file($avatarData['tmp_name'], $uploadPath)) {
            $this->data[$this->alias]['avatar'] = $avatarId.'.'.$ext;
            return true;
        }
        return false;
    }
}