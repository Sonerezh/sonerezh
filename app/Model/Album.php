<?php

App::uses('AppModel', 'Model');

/**
 * The Album Model. Represents an album.
 *
 * An album is defined with a unique ID, a name, a year and a cover. It belongs
 * to a Band identified by the "band_id" key and contains one or several Tracks.
 */
class Album extends AppModel
{
    private $bandId;
    public $actsAs = array('Containable');
    public $belongsTo = array('Band');
    public $hasMany = array('Track');
    public $validate = array(
        'name' => array(
            'rule' => array('maxLength', 255),
            'required' => 'create',
            'allowEmpty' => false,
            'message' => 'Album\'s name cannot be empty or exceed 255 characters.'
        ),
        'cover' => array(
            'rule' => array('maxLength', 37),
            'allowEmpty' => true,
            'message' => 'Album\'s cover cannot exceed 37 characters.'
        ),
        'year' => array(
            'rule' => array('date', 'y'),
            'allowEmpty' => true,
            'message' => 'Invalid album year.'
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

    /**
     * Stores the Band id the deleted album belongs to, to perform further
     * checks once the record is deleted (the ID is used in the afterDelete()
     * method).
     *
     * @param bool $cascade
     * @return bool
     */
    public function beforeDelete($cascade = false)
    {
        $album = $this->find('first', array(
            'fields' => array('band_id'),
            'conditions' => array('id' => $this->id)
        ));

        if (isset($album)) {
            $this->bandId = $album[$this->alias]['band_id'];
        }
        return true;
    }

    /**
     * Checks if the Band the deleted album belongs to still have albums, and
     * deletes it if it's not the case.
     */
    public function afterDelete()
    {
        if (!empty($this->bandId)) {
            $neighbours = $this->find('count', array(
                'conditions' => array('band_id' => $this->bandId)
            ));

            if ($neighbours == 0) {
                $band = ClassRegistry::init('Band');
                $band->delete($this->bandId);
            }
        }
    }
}