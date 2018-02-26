<?php

App::uses('Component', 'Controller');

/**
 * Class SortComponent
 *
 * This class contains methods for sorting arrays songs returned by the database.
 */
class SortComponent extends Component {

    /**
     * Sort an array of song by band name, album, disc and track number.
     * See /artists to visualize it.
     * @see https://php.net/manual/en/function.array-multisort.php
     *
     * @param array $songs Array of songs returned by the database.
     * @return array An array of sorted songs.
     */
    public function sortByBand($songs) {
        foreach ($songs as $key => $row) {
            $s_discs = explode('/', $row['Song']['disc']);
            $s_band[$key]           = $row['Song']['band'];
            $s_album[$key]          = $row['Song']['album'];
            $s_track_number[$key]   = $row['Song']['track_number'];
            $s_disc[$key]           = $s_discs[0];
        }

        if (!empty($s_band) && !empty($s_album) && !empty($s_track_number) && !empty($s_disc)) {
            array_multisort($s_band, $s_album, $s_disc, $s_track_number, $songs);
        }

        return $songs;
    }

    /**
     * Sort an array of songs by disc and track number.
     * See /albums to visualize it
     * @see https://php.net/manual/en/function.array-multisort.php
     *
     * @param array $songs Array of songs.
     * @return array An array of sorted songs.
     */
    public function sortByDisc($songs) {
        foreach ($songs as $key => $row) {
            $s_discs = explode('/', $row['Song']['disc']);
            $s_track_number[$key]   = $row['Song']['track_number'];
            $s_disc[$key]           = $s_discs[0];
        }

        if (!empty($s_discs) && !empty($s_track_number)) {
            array_multisort($s_disc, $s_track_number, $songs);
        }

        return $songs;
    }
}
