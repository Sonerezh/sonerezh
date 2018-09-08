<?php

App::uses('AppModel', 'Model');

/**
 * The Track Model. Represents a track.
 *
 * A track is identified with a unique ID and several fields extracted from the
 * metadata. Only the "source_path" field is mandatory.
 * A track belongs to an album, identified by the album_id key.
 */
class Track extends AppModel
{
    private $albumId;
    public $belongsTo = array('Album');
    public $validate = array(
        'title' => array(
            'rule' => array('maxLength', 255),
            'message' => 'The title of a track cannot exceed 255 characters.'
        ),
        'source_path' => array(
            'rule' => array('maxLength', 4096),
            'allowEmpty' => false,
            'required' => 'create',
            'message' => 'The source path of a file cannot be empty or exceed 4096 characters.'
        ),
        'playtime' => array(
            'rule' => array('maxLength', 9),
            'allowEmpty' => true,
            'message' => 'The playtime cannot exceed 9 characters.'
        ),
        'track_number' => array(
            'rule' => array('naturalNumber', true),
            'allowEmpty' => true,
            'message' => 'The track number must be a natural integer.'
        ),
        'max_track_number' => array(
            'rule' => array('naturalNumber', true),
            'allowEmpty' => true,
            'message' => 'The max track number must be a natural integer.'
        ),
        'disc_number' => array(
            'rule' => array('naturalNumber', true),
            'allowEmpty' => true,
            'message' => 'The disc number must be a natural integer.'
        ),
        'max_disc_number' => array(
            'rule' => array('naturalNumber', true),
            'allowEmpty' => true,
            'message' => 'The max disc number must be a natural integer.'
        ),
        'year' => array(
            'rule' => array('date', 'y'),
            'allowEmpty' => true,
            'message' => 'Invalid track year.'

        ),
        'genre' => array(
            'rule' => array('maxLength', 255),
            'message' => 'The genre of a track cannot exceed 255 characters.'
        ),
        'artist' => array(
            'rule' => array('maxLength', 255),
            'message' => 'The artist value cannot exceed 255 characters.'
        )
    );

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
     * Stores the Album id the deleted track belongs to, to perform further
     * checks once the record is deleted (the ID is used in the afterDelete()
     * method).
     *
     * @param bool $cascade
     * @return bool
     */
    public function beforeDelete($cascade = false)
    {
        $track = $this->find('first', array(
            'fields' => array('album_id'),
            'conditions' => array('id' => $this->id)
        ));

        if (isset($track)) {
            $this->albumId = $track[$this->alias]['album_id'];
        }
        return true;
    }

    /**
     * Checks if the Album the deleted track belongs to still have tracks, and
     * deletes it if it's not the case.
     */
    public function afterDelete()
    {
        if (!empty($this->albumId)) {
            $neighbours = $this->find('count', array(
                'conditions' => array('album_id' => $this->albumId)
            ));

            if ($neighbours == 0) {
                $album = ClassRegistry::init('Album');
                $album->delete($this->albumId);
            }
        }
    }
}