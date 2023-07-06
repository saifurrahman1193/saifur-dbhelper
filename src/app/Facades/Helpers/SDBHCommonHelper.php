<?php

namespace Saifur\DBHelper\app\Facades\Helpers;

class SDBHCommonHelper {

    public function convertDataSize( $sizeInBytes ) {
        $units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
        $unitIndex = 0;

        while ( $sizeInBytes >= 1024 && $unitIndex < count( $units ) - 1 ) {
            $sizeInBytes /= 1024;
            $unitIndex++;
        }

        return round( $sizeInBytes, 2 ) . ' ' . $units[ $unitIndex ];
    }

    public function replaceBetweenStrings( $originalString, $startString, $endString, $replacement ) {
        $startPos = strpos( $originalString, $startString );
        $endPos = strpos( $originalString, $endString );

        // Check if the start and end strings exist in the original string
        if ( $startPos === false || $endPos === false ) {
            return $originalString;
            // Return the original string if either string is not found
        }

        // Calculate the length of the string between the start and end positions
        $length = $endPos - $startPos + strlen( $endString );

        // Extract the substring to be replaced
        $substring = substr( $originalString, $startPos, $length );

        // Replace the extracted substring with the replacement string
        $modifiedString = str_replace( $substring, $replacement, $originalString );

        return $modifiedString;
    }

    public function YmdTodmYPm( $datetime ) {
        if ( $datetime ) {
            $date = \Carbon\Carbon::parse( $datetime )->format( 'd-m-Y  g:i A' );
            return $date;
        } else {
            return '';
        }
    }

    public function getNow() {
        return \Carbon\Carbon::now( '+06:00' );
    }

}
