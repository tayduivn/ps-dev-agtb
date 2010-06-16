<?php
class printer {
    var $debug      = false;
    var $newline    = "\n";
    var $prefix     = "";
    var $timestamp  = true;

    var $fh_output  = NULL;
    var $filename   = "";

    // constructor
    function printer(){
    }

    // accessor methods
    function getTimestamp(){ return( $this->timestamp ); }
    function setTimestamp( $bValue ){
        $this->timestamp = $bValue;
    }

    function getOutputFile(){ return( $this->filename ); }
    function setOutputFile( $filename ){
        if( !is_null( $this->fh_output ) ){
            fclose( $this->fh_output );
        }

        $this->fh_output = fopen( $filename, 'a+' );
        $this->filename = $filename;
    }


    // utility methods follow

    function generatePrefix(){
        $value = $this->prefix;

        if( $this->timestamp ){
            $value .= date( "Y-m-d G:i:s" );
        }

        if( strlen($value) > 0 ){
            $value .= ":";
        }
        return( $value );
    }

    function debug( $data ){
        if( $this->debug == true ){
            $this->output( $data );
        }
    }

    function output( $raw ){
        $data = "" . $this->generatePrefix() . " " . $raw . $this->newline;
        print( $data );

        if( !is_null( $this->fh_output ) ){
            fwrite( $this->fh_output, $data );
        }
    }
}

function &getPrinterInstance(){
    static $instance = NULL;
    if( is_null( $instance ) ){
        $instance = new printer();
    }
    return( $instance );
}
?>
