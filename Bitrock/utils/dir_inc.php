<?php
if( is_file( "printer.php" ) ){
    require_once( "printer.php" );
}
else {
    require_once( "utils/printer.php" );
}

function copy_recursive( $source, $dest ){
    if( is_file( $source ) ){
        return( copy( $source, $dest ) );
    }
    if( !is_dir($dest) ){
        mkdir( $dest );
    }

    $status = true;

    $d = dir( $source );
    while( $f = $d->read() ){
        if( $f == "." || $f == ".." ){
            continue;
        }
        $status &= copy_recursive( "$source/$f", "$dest/$f" );
    }
    $d->close();
    return( $status );
}

function generate_file_md5s( $filename_array ) {
    $return_array = array();
    foreach( $filename_array as $filename ) {
    	if(strpos($filename, '.php')) {
    		$fp = fopen($filename, 'r');
    		$fileContents = fread($fp, filesize($filename));
    		$the_md5 = md5($fileContents);
    	} else {
        	$the_md5 = md5_file( $filename );
    	}
        $return_array += array( "$filename" => "$the_md5" );
    }
    return( $return_array );
}

function mkdir_recursive( $path ){
    $lpr = getPrinterInstance();

    if( is_dir( $path ) ){
        return( true );
    }
    if( is_file( $path ) ){
        $lpr->output( "ERROR: mkdir_recursive(): argument $path is already a file." );
        return( false );
    }
    $parent_dir = substr( $path, 0, strrpos( $path, "/" ) );
    if( mkdir_recursive( $parent_dir ) ){
        if( !file_exists( $path )) {
            return( mkdir( $path ) );
        }
    }
    return( false );
}

function rmdir_recursive( $path ){
    $lpr = getPrinterInstance();

    if( is_file( $path ) ){
        return( unlink( $path ) );
    }
    if( !is_dir( $path ) ){
        $lpr->output( "ERROR: rmdir_recursive(): argument $path is not a file or a dir." );
        return( false );
    }

    $status = true;

    $d = dir( $path );
    while( $f = $d->read() ){
        if( $f == "." || $f == ".." ){
            continue;
        }
        $status &= rmdir_recursive( "$path/$f" );
    }
    $d->close();
    rmdir( $path );
    return( $status );
}

function findTextFiles( $the_dir, $the_array ){
    $lpr = getPrinterInstance();

    $d = dir( $the_dir );
    while( $f = $d->read() ){
        if( $f == "." || $f == ".." ){
            continue;
        }
        if( is_dir( "$the_dir/$f" ) ){
            // i think depth first is ok, given our cvs repo structure -Bob.
            $the_array = findTextFiles( "$the_dir/$f", $the_array );
        }
        else {
            $path_parts = pathinfo( "$the_dir/$f" );
            $extension  = "";
            if( isset( $path_parts['extension'] ) ){
                $extension = strToLower( $path_parts['extension'] );
            }
            switch( $extension ){
                // we take action on these cases
                case "css":
                case "html":
                case "js":
                case "php":
                case "sql":
                case "tpl":
                case "txt":
                    array_push( $the_array, "$the_dir/$f" );
                    break;
                // we consciously skip these types
                case "afm":
                case "db":
                case "fla":
                case "gif":
                case "ico":
                case "jpeg":
                case "jpg":
                case "pdf":
                case "png":
                case "rtf":
                case "swf":
                case "zip":
                    break;
                default:
                    $lpr->debug( "no type handler for $the_dir/$f with extension: $extension" );
            }
        }
    }
    return( $the_array );
}

function findAllDirs( $the_dir, $the_array ){
    $d = dir( $the_dir );
    while( $f = $d->read() ){
        if( $f == "." || $f == ".." ){
            continue;
        }
        if( is_dir( "$the_dir/$f" ) ){
            // i think depth first is ok, given our cvs repo structure -Bob.
            $the_array = findAllDirs( "$the_dir/$f", $the_array );
            array_push( $the_array, "$the_dir/$f" );
        }
    }
    return( $the_array );
}

function findAllFiles( $the_dir, $the_array ){
    $d = dir( $the_dir );
    while( $f = $d->read() ){
        if( $f == "." || $f == ".." || $f ==".svn" || $f ==".git"){
            continue;
        }
        if( is_dir( "$the_dir/$f" ) ){
            // i think depth first is ok, given our cvs repo structure -Bob.
            $the_array = findAllFiles( "$the_dir/$f", $the_array );
        }
        else {
            array_push( $the_array, "$the_dir/$f" );
        }
    }
    return( $the_array );
}

function removeMatchingDirs( $the_array, $regex_array, $verbose=false ){
    $lpr = getPrinterInstance();

    foreach( $the_array as $the_dir ){
        foreach( $regex_array as $the_regex ){
            if( preg_match( '#'. $the_regex . '#', $the_dir ) ){
                if( is_dir( $the_dir ) ){
                    if( $verbose ){
                        $lpr->output( "Deleting dir: $the_dir" );
                    }
                    rmdir_recursive( $the_dir );
                }
            }
        }
    }
}

function removeMatchingFiles( $the_array, $regex_array, $verbose=false ){
    $lpr = getPrinterInstance();
    foreach( $the_array as $the_file ){
        foreach( $regex_array as $the_regex ){
            if( preg_match( '#'. $the_regex . '#', $the_file ) ){
                if( is_file( $the_file ) ){
                    if( $verbose ){
                        $lpr->output( "Deleting file: $the_file" );
                    }
                    unlink( $the_file );
                }
            }
        }
    }
}

function write_md5_file( $the_array, $file ){
    $md5_file = "<?php\n" .
    '// created: ' . date('Y-m-d H:i:s') . "\n" .
    '$md5_string = ' .
    var_export( $the_array, true ) .
    ";\n" .
    "?>\n";

    $fh = fopen( $file, "w" );
    fputs( $fh, $md5_file, strlen( $md5_file ) );
    fclose( $fh );
}

function mk_temp_dir( $base_dir, $prefix="" ){
    $temp_dir = tempnam( $base_dir, $prefix );
    if( !$temp_dir || !unlink( $temp_dir ) ){
        return( false );
    }

    if( mkdir( $temp_dir ) ){
        return( $temp_dir );
    }

    return( false );
}

function unlinkIfExists( $file ){
    if( file_exists( $file ) ){
        return( unlink( $file ) );
    }
    return( true );
}

function rename_with_unlink( $file1, $file2 ){
    return( unlinkIfExists( $file2 ) && rename( $file1, $file2 ) );
}


?>
