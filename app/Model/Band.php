<?php

App::uses('AppModel', 'Model');

/**
 * The Band Model. Represents a band.
 *
 * A band is defined with a unique ID and a name. It has one or several albums.
 */
class Band extends AppModel
{
    public $actsAs = array('Containable');
    public $hasMany = array('Album');
    public $validate = array(
        'name' => array(
            'rule' => array('maxLength', 255),
            'required' => 'create',
            'allowEmpty' => false,
            'message' => 'The band\'s name cannot be empty or exceed 255 characters.'
        )
    );

    /**
     * Allow empty value for the "name" field on update.
     *
     * @param array $options
     * @return bool|void
     */
    public function beforeValidate($options = array())
    {
        if (isset($this->data[$this->alias]['id'])) {
            $this->validator()->getField('name')->getRule(0)->allowEmpty = true;
        }
    }

    /**
     * Override the "created" and the "updated" fields to ensure they are
     * properly filled.
     *
     * @param array $options
     * @return bool
     */
    public function beforeSave($options = array())
    {
        if (isset($this->data[$this->alias]['id'])) {
            $this->data[$this->alias]['updated'] = date('Y-m-d H:i:s');
            unset($this->data[$this->alias]['created']);
        } else {
            $this->data[$this->alias]['created'] = date('Y-m-d H:i:s');
            unset($this->data[$this->alias]['updated']);
        }
        return true;
    }
}