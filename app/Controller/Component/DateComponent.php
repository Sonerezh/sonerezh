<?php

App::uses('Component', 'Controller');

class DateComponent extends Component {

    /**
     * Encode a given date on 16 bits
     * 7 bits for the year (0 - 99)
     * 4 bits for the month (1 - 12)
     * 5 bits for the day (1 - 31)
     * Thanks to @bdelespierre (http://bdelespierre.fr/article/compacter-une-date-sur-2-octets/)
     *
     * @param $year The year
     * @param $month The month
     * @param $day The day
     * @return int Signed integer between 0 and 51103
     */
    function date16_encode ($year, $month, $day) {
        $day    &= 0b00011111;
        $month  &= 0b00001111;
        $year   &= 0b01111111;

        return ($year << 9) | ($month << 5) | $day;
    }

    /**
     * Decode a date encoded on 16 bits
     * Thanks to @bdelespierre (http://bdelespierre.fr/article/compacter-une-date-sur-2-octets/)
     *
     * @param $date The date to decode
     * @return array Array of integers (YY MM DD)
     */
    function date16_decode ($date) {
        $year   = ($date >> 9) & 0b01111111;
        $month  = ($date >> 5) & 0b00001111;
        $day    = ($date)      & 0b00011111;

        return [$year, $month, $day];
    }
}