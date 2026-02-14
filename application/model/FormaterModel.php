<?php

/**
 * NoteModel
 * This is basically a simple CRUD (Create/Read/Update/Delete) demonstration.
 */
class FormaterModel
{

    /**
     * Remove all non numeric character
     *
     * @param $user_name string username
     *
     * @return number
     */
    public static function getNumberOnly($number)
    {
        return trim(preg_replace( "/[^0-9]/", "", $number));
    }

    /**
    * Remove all characters except letters.
    *
    * @param string $string
    * @return string
    */
    public static function getLetterOnly( $string ) {
        return preg_replace( "/[^a-z]/i", "", $string );
    }

    public static function sanitize($string) {
        $string = htmlspecialchars($string, ENT_QUOTES);
        return trim(strip_tags($string));
    }

}
