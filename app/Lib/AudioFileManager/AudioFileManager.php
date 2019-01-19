<?php

App::uses('Folder', 'Utility');

class AudioFileManager {

    protected $album = array();
    protected $band = array();
    protected $track = array();
    protected $file;
    protected $raw_data;

    public function __construct($song) {
        $this->file = new File($song);
        $getID3 = new getID3();
        $this->raw_data = $getID3->analyze(($this->file->path));
        getid3_lib::CopyTagsToComments($this->raw_data);
    }

    public function parse()
    {
        $result = array(
            'status' => 0,      // 0, or 1 (SUCCESS or FAILURE)
            'status-msg' => '', // Debug message
            'data' => array()   // The data ($metadata array below)
        );

        if (empty($this->raw_data['comments'])) {
            $result['status'] = 1;
            $result['status_msg'] = __('Metadata are unreadable or empty. Skipping.');
            return $result;
        }

        // Order matters here!
        $this->fetchBand();
        $this->fetchAlbum();
        $this->fetchTrack();

        $metadata = array(
            'Band' => $this->band,
            'Album' => $this->album,
            'Track' => $this->track
        );

        $result['data'] = $metadata;
        return $result;
    }

    private function fetchAlbum ()
    {
        if (empty($this->band)) {
            $this->fetchBand();
        }

        if (!empty($this->raw_data['comments']['album'])) {
            $this->album['name'] = end($this->raw_data['comments']['album']);
        } else {
            $this->album['name'] = 'Unknown album';
        }

        $this->album['cover'] = $this->fetchCover();
        $this->album['year'] = $this->fetchYear();
    }

    private function fetchBand ()
    {
        if (!empty($this->raw_data['comments']['band'])) { // MP3 tags
            $this->band['name'] = end($this->raw_data['comments']['band']);
        } elseif (!empty($this->raw_data['comments']['ensemble'])) { // OGG tags
            $this->band['name'] = end($this->raw_data['comments']['ensemble']);
        } elseif (!empty($this->raw_data['comments']['albumartist'])) { // OGG or FLAC tags
            $this->band['name'] = end($this->raw_data['comments']['albumartist']);
        } elseif (!empty($this->raw_data['comments']['album artist'])) {// OGG or FLAC Tags
            $this->band['name'] = end($this->raw_data['comments']['album artist']);
        } elseif (!empty($this->raw_data['comments']['artist'])) {
            $this->band['name'] = end($this->raw_data['comments']['artist']);
        } else {
            $this->band['name'] = 'Unknown';
        }
    }


    private function fetchCover ()
    {
        if (empty($this->band) || empty($this->album)) {
            return null;
        }

        if (!file_exists(IMAGES . THUMBNAILS_DIR)) {
            new Folder(IMAGES . THUMBNAILS_DIR, true, 0755);
        }

        if (!empty($this->raw_data['comments']['picture'])) {
            if (!empty(end($this->raw_data['comments']['picture'])['image_mime'])) {
                $mime = preg_split('/\//', end($this->raw_data['comments']['picture'])['image_mime']);
                $extension = $mime[1];
            } else {
                $extension = 'jpg';
            }

            $cover = $this->fetchCoverName($extension);
            $path = new File(IMAGES . THUMBNAILS_DIR . DS . $cover);

            if ($path->exists() || $path->write(end($this->raw_data['comments']['picture'])['data'])) {
                return $cover;
            } else {
                return null;
            }
        } else { // Fallback to the filesystem if the cover was not in the metadata
            $pattern = '^(folder|cover|front.*|albumart_.*_large)\.(jpg|jpeg|png)$';
            $covers = $this->file->Folder->find($pattern);

            if (!empty($covers)) {
                $img_source_path = $this->file->Folder->addPathElement(
                    $this->file->Folder->path,
                    $covers[0]
                );
                $img = new File($img_source_path);
                $extension = $img->info()['extension'];
                $cover = $this->fetchCoverName($extension);
                $path = new File( IMAGES . THUMBNAILS_DIR . DS . $cover);

                if ($path->exists() || $img->copy($path->path)) {
                    return $cover;
                } else {
                    return null;
                }
            }
        }
        return null;
    }

    private function fetchCoverName($extension)
    {
        return md5($this->band['name'] . $this->album['name']) . '.' . $extension;
    }

    private function fetchTrack()
    {
        $this->track = array(
            'source_path' => $this->file->path,
            'year' => $this->fetchYear()
        );

        if (!empty($this->raw_data['comments']['title'])) {
            $this->track['title'] = end($this->raw_data['comments']['title']);
        } elseif (!empty($this->raw_data['filename'])) {
            $this->track['title'] = $this->raw_data['filename'];
        } else {
            $this->track['title'] = $this->file->name();
        }

        if (!empty($this->raw_data['comments']['artist'])) {
            $this->track['artist'] = end($this->raw_data['comments']['artist']);
        } else {
            $this->track['artist'] = null;
        }

        if (!empty($this->raw_data['playtime_string'])) {
            $this->track['playtime'] = $this->raw_data['playtime_string'];
        }

        if (!empty($this->raw_data['comments']['track'])) { // MP3 Tags
            $this->track['track_number'] = (string)end($this->raw_data['comments']['track']);
        } elseif (!empty($this->raw_data['comments']['track_number'])) { // MP3 Tags
            // Some tags look like '1/10'
            $track_number = explode('/', (string)end($this->raw_data['comments']['track_number']));
            $this->track['track_number'] = intval($track_number[0]);
            if (!empty($track_number[1])) {
                $this->track['max_track_number'] = intval($track_number[1]);
            }
        } elseif(!empty($this->raw_data['comments']['tracknumber'])){ // OGG Tags
            $this->track['track_number'] = intval(end($this->raw_data['comments']['tracknumber']));
        }

        if (!empty($this->raw_data['comments']['part_of_a_set'])) { // MP3 Tags
            $part_of_a_set = explode('/', (string)end($this->raw_data['comments']['part_of_a_set']));
            $this->track['disc_number'] = intval($part_of_a_set[0]);
            if (!empty($part_of_a_set[1])) {
                $this->track['max_disc_number'] = intval($part_of_a_set[1]);
            }
        } elseif (!empty($this->raw_data['comments']['discnumber'])) { // OGG Tags
            $this->track['disc_number'] = intval(end($this->raw_data['comments']['discnumber']));
            if (!empty($this->raw_data['comments']['disctotal'])) {
                $this->track['max_disc_number'] = intval(end($this->raw_data['comments']['disctotal']));
            }
        }

        if (!empty($this->raw_data['comments']['genre'])) {
            $this->track['genre'] = end($this->raw_data['comments']['genre']);
        }
    }

    private function fetchYear()
    {
        if (!empty($this->raw_data['comments']['year'])) {
            $date = $this->raw_data['comments']['year'];
        } elseif (!empty($this->raw_data['comments']['date'])) {
            $date = $this->raw_data['comments']['date'];
        } else {
            $date = array();
        }

        if (!empty($date)) {
            $strptime = strptime(end($date), "%Y");
            if ($strptime) {
                return $strptime['tm_year'] + 1900;
            }
        }

        return null;
    }
}
