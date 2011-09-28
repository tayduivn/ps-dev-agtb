<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once("include/SugarTheme/cssmin.php");
require_once("include/utils/file_utils.php");

class SugarSpriteBuilder
{
	var $isAvailable = false;
	var $silentRun = false;
    var $fromSilentUpgrade = false;
    var $writeToUpgradeLog = false;

	var $debug = false;
	var $fileName = 'sprites';
	var $cssMinify = true;

	// class supported image types
	var $supportedTypeMap = array(
		IMG_GIF => IMAGETYPE_GIF,
		IMG_JPG => IMAGETYPE_JPEG,
		IMG_PNG => IMAGETYPE_PNG,
	);

	// sprite settings
	var $pngCompression = 9;
	var $pngFilter = PNG_NO_FILTER;
	var $maxWidth = 50; 
	var $maxHeight = 50;
	var $rowCnt = 30;

	// processed image types
	var $imageTypes = array();

	// source files
	var $spriteSrc = array();
	var $spriteRepeat = array();

	// sprite resource images
	var $spriteImg;

	// sprite_config collection
	var $sprites_config = array();


    public function __construct()
    {
		// check if we have gd installed
		if(function_exists('imagecreatetruecolor'))
        {
			$this->isAvailable = true;
			$this->getSupportedTypes();
		}

        if(function_exists('logThis') && isset($GLOBALS['path']))
        {
            $this->writeToUpgradeLog = true;
        }
	}

	// load supported image types
	public function getSupportedTypes() {
		foreach($this->supportedTypeMap as $gd_bit => $imagetype) {
			if(imagetypes() & $gd_bit) {
				// swap gd_bit & imagetype
				$this->imageTypes[$imagetype] = $gd_bit;
			}
		}
	}

	// populate sprites array
	public function addDirectory($name, $dir) {

		// sprite namespace
		if(!array_key_exists($name, $this->spriteSrc))
        {
			$this->spriteSrc[$name] = array();
		}

		// add files from directory
		$this->spriteSrc[$name][$dir] = $this->getFileList($dir);
		
	}

	// process files in a directory and add them to the sprites array 
	private function getFileList($dir) {
		$list = array();
		if(is_dir($dir)) {
			if($dh = opendir($dir)) {

				// optional sprites_config.php file
				$this->loadSpritesConfig($dir);

			    while (($file = readdir($dh)) !== false)
                {
					if ($file != "." && $file != ".." && $file != "sprites_config.php")
                    {

						// file info & check supported image format 
						if($info = $this->getFileInfo($dir, $file)) {

							// skip excluded files
							if(isset($this->sprites_config[$dir]['exclude']) && array_search($file, $this->sprites_config[$dir]['exclude']) !== false)
                            {
                                global $mod_strings;
                                $msg = string_format($mod_strings['LBL_SPRITES_EXCLUDING_FILE'], array("{$dir}/{$file}"));
								$GLOBALS['log']->debug($msg);
                                $this->logMessage($msg);
							} else {
								// repeatable sprite ?
								$isRepeat = false;

								if(isset($this->sprites_config[$dir]['repeat']))
                                {
									foreach($this->sprites_config[$dir]['repeat'] as $repeat)
                                    {
										if($info['x'] == $repeat['width'] && $info['y'] == $repeat['height'])
                                        {
											$id = md5($repeat['width'].$repeat['height'].$repeat['direction']);
											$isRepeat = true;
											$this->spriteRepeat['repeat_'.$repeat['direction'].'_'.$id][$dir][$file] = $info;
										}
									}
								}

								if(!$isRepeat)
                                {
									$list[$file] = $info;
                                }
							}
						}
	   	     		}
   		 		}
			}
		    closedir($dh);
		}
		return $list;
	}

	// load sprites_config
	private function loadSpritesConfig($dir) {
		$sprites_config = array();
		if(file_exists("$dir/sprites_config.php"))
        {
			include("$dir/sprites_config.php");
			if(count($sprites_config)) {
				$this->sprites_config = array_merge($this->sprites_config, $sprites_config);
			}
		}
	}

	// return array of file info if image type is supported
	private function getFileInfo($dir, $file) {
		$result = false;
		$info = @getimagesize($dir.'/'.$file);
		if($info) {

			// supported image type ? 
			if(isset($this->imageTypes[$info[2]]))
            {
				$w = $info[0];
				$h = $info[1];
				$surface = $w * $h;

				// be sure we have an image size
				$addSprite = false;
				if($surface)
                {
					// sprite dimensions
					if($w <= $this->maxWidth && $h <= $this->maxHeight)
                    {
						$addSprite = true;
					}
				}

				if($addSprite)
                {
					$result = array();
					$result['x'] = $w;
					$result['y'] = $h;
					$result['type'] = $info[2];
				}
			}
		}
		return $result;
	}

	// create sprites
	public function createSprites() {

        global $mod_strings;

		if(!$this->isAvailable)
        {
			if(!$this->silentRun)
            {
                $msg = $mod_strings['LBL_SPRITES_NOT_SUPPORTED'];
                $GLOBALS['log']->warn($msg);
                $this->logMessage($msg);
            }
			return false;
		}

		// add repeatable sprites
		if(count($this->spriteRepeat))
        {
			$this->spriteSrc = array_merge($this->spriteSrc, $this->spriteRepeat);
        }

		foreach($this->spriteSrc as $name => $dirs)
        {
			if(!$this->silentRun)
            {
                $msg = string_format($mod_strings['LBL_SPRITES_CREATING_NAMESPACE'], array($name));
                $GLOBALS['log']->debug($msg);
				$this->logMessage($msg);
            }

			// setup config for sprite placement algorithm
			if(substr($name, 0, 6) == 'repeat')
            {
				$isRepeat = true;
                $type = substr($name, 7, 10) == 'horizontal' ? 'horizontal' : 'vertical';
				$config = array(
					'type' => $type,
				);
			} else {
				$isRepeat = false;
				$config = array(
					'type' => 'boxed',
					'width' => $this->maxWidth,
					'height' => $this->maxHeight,
					'rowcnt' => $this->rowCnt,
				);
			}

			// use seperate class to arrange the images
			$sp = new SpritePlacement($dirs, $config);
			$sp->processSprites();

			//if(! $this->silentRun)
			//	echo " (size {$sp->width()}x{$sp->height()})<br />";

			// we need a target image size
			if($sp->width() && $sp->height())
            {
				// init sprite image
				$this->initSpriteImg($sp->width(), $sp->height());

				// add sprites based upon determined coordinates
				foreach($dirs as $dir => $files)
                {
					if(!$this->silentRun)
                    {
                        $msg = string_format($mod_strings['LBL_SPRITES_PROCESSING_DIR'], array($dir));
                        $GLOBALS['log']->debug($msg);
                        $this->logMessage($msg);
                    }

					foreach($files as $file => $info)
                    {
						if($im = $this->loadImage($dir, $file, $info['type']))
                        {
							// coordinates
							$dst_x = $sp->spriteMatrix[$dir.'/'.$file]['x'];
							$dst_y = $sp->spriteMatrix[$dir.'/'.$file]['y'];

							imagecopy($this->spriteImg, $im, $dst_x, $dst_y, 0, 0, $info['x'], $info['y']);
							imagedestroy($im);

							if(!$this->silentRun)
                            {
                                $msg = string_format($mod_strings['LBL_SPRITES_ADDED'], array("{$dir}/{$file}"));
                                $GLOBALS['log']->debug($msg);
                                $this->logMessage($msg);
                            }
						}
					}
				}
	
				// dir & filenames 
				if($isRepeat)
                {
					$outputDir = sugar_cached("sprites/Repeatable");
					$spriteFileName = "{$name}.png";
					$cssFileName = "{$this->fileName}.css";
					$metaFileName = "{$this->fileName}.meta.php";
					$nameSpace = "Repeatable";
				} else { 
					$outputDir = sugar_cached("sprites/$name");
					$spriteFileName = "{$this->fileName}.png";
					$cssFileName = "{$this->fileName}.css";
					$metaFileName = "{$this->fileName}.meta.php";
					$nameSpace = "{$name}";
				}

				// directory structure
				if(!is_dir(sugar_cached("sprites/$nameSpace")))
                {
                    create_cache_directory("sprites/{$nameSpace}");
                }

				// save sprite image
				imagepng($this->spriteImg, "$outputDir/$spriteFileName", $this->pngCompression, $this->pngFilter);
				imagedestroy($this->spriteImg);

				/* generate css & metadata */

				$head = '';
				$body = '';
				$metadata = '';

				foreach($sp->spriteSrc as $id => $info)
                {
					// sprite id
					$hash_id = md5($id);

					// header
					$head .= "span.spr_{$hash_id},\n";

					// image size
					$w = $info['x'];
					$h = $info['y'];

					// image offset
					$offset_x = $sp->spriteMatrix[$id]['x'];
					$offset_y = $sp->spriteMatrix[$id]['y'];

					// sprite css
					$body .= "/* {$id} */
span.spr_{$hash_id} {
width: {$w}px;
height: {$h}px;
background-position: -{$offset_x}px -{$offset_y}px;
}\n";

					$metadata .= '$sprites["'.$id.'"] = array ("class"=>"'.$hash_id.'","width"=>"'.$w.'","height"=>"'.$h.'");'."\n";
				} 

				// common css header
				//$head .= "span.spr_bogus {background: url('{$GLOBALS['sugar_config']['site_url']}/index.php?entryPoint=getImage&imageName={$spriteFileName}&spriteNamespace={$nameSpace}') no-repeat;display: inline-block";
				$head .= "span.spr_bogus {background: url('../../../index.php?entryPoint=getImage&imageName={$spriteFileName}&spriteNamespace={$nameSpace}'); no-repeat;display:inline-block;\n";

				// append mode for repeatable sprites
                $fileMode = $isRepeat ? 'a' : 'w';

				// save css
				$css_content = "\n/* autogenerated sprites - $name */\n".$head.$body;
				if($this->cssMinify)
                {
					$css_content = cssmin::minify($css_content);
                }
				$fh = fopen("$outputDir/$cssFileName", $fileMode);
				fwrite($fh, $css_content);
				fclose($fh);

				/* save metadata */
				$add_php_tag = (file_exists("$outputDir/$metaFileName") && $isRepeat) ? false : true;
				$fh = fopen("$outputDir/$metaFileName", $fileMode);
				if($add_php_tag)
                {
					fwrite($fh, '<?php');
                }
				fwrite($fh, "\n/* sprites metadata - $name */\n");
				fwrite($fh, $metadata."\n");
				fclose($fh);

			// if width & height
			} else {

				if(!$this->silentRun)
                {
                    $msg = string_format($mod_strings['LBL_SPRITES_ADDED'], array($name));
                    $GLOBALS['log']->debug($msg);
                    $this->logMessage($msg);
                }

			}

		}
		return true;
	}

	// initialize sprite image
	private function initSpriteImg($w, $h) {
		$this->spriteImg = imagecreatetruecolor($w,$h);
		$transparent = imagecolorallocatealpha($this->spriteImg, 0, 0, 0, 127);
		imagefill($this->spriteImg, 0, 0, $transparent);
		imagealphablending($this->spriteImg, false);
		imagesavealpha($this->spriteImg, true);
	} 

	// load image resource
	private function loadImage($dir, $file, $type) {
		$path_file = $dir.'/'.$file;
		switch($type) {
			case IMAGETYPE_GIF:
				return imagecreatefromgif($path_file);
			case IMAGETYPE_JPEG:
				return imagecreatefromjpeg($path_file);
			case IMAGETYPE_PNG:
				return imagecreatefrompng($path_file);
			default:
				return false;
		}
	}

    private function logMessage($msg)
    {
        if(!$this->silentRun && !$this->fromSilentUpgrade)
        {
            echo $msg . '</br>';
        } else if ($this->fromSilentUpgrade && $this->writeToUpgradeLog) {
            logThis($msg, $GLOBALS['path']);
        } else if(!$this->silentRun) {
            echo $msg . "\n";
        }
    }
}


/**
 * SpritePlacement
 * 
 */
class SpritePlacement
{

	// occupied space
	var $spriteMatrix = array();

	// minimum surface
	var $minSurface = 0;

	// sprite src (flattened array)
	var $spriteSrc = array();

	// placement config array
	/*
		type = 	boxed
				horizontal
				vertical
		
		required params for
		type 1 	-> width
				-> height
				-> rowcnt

	*/
	var $config = array();

	function __construct($spriteSrc, $config) {

		// convert spriteSrc to flat array 
		foreach($spriteSrc as $dir => $files) {
			foreach($files as $file => $info) {
				// use full path as identifier
				$full_path = $dir.'/'.$file;
				$this->spriteSrc[$full_path] = $info;
			}
		}

		$this->config = $config;
	}

	function processSprites() {

		foreach($this->spriteSrc as $id => $info) {

			// dimensions
			$x = $info['x'];
			$y = $info['y'];
			
			// update min surface
			$this->minSurface += $x * $y;

			// get coordinates where to add this sprite
			if($coor = $this->addSprite($x, $y)) {
				$this->spriteMatrix[$id] = $coor;
			}
		}
	}

	// returns x/y coordinates to fit the sprite
	function addSprite($w, $h) {
		$result = false;

		switch($this->config['type']) {

			// boxed
			case 'boxed':

				$spriteX = $this->config['width'];
				$spriteY = $this->config['height'];
				$spriteCnt = count($this->spriteMatrix) + 1;
				$y = ceil($spriteCnt / $this->config['rowcnt']);
				$x = $spriteCnt - (($y - 1) * $this->config['rowcnt']);
				$result = array(
					'x' => ($x * $spriteX) + 1 - $spriteX, 
					'y' => ($y * $spriteY) + 1 - $spriteY);

				break;

			// horizontal -> align vertically
			case 'horizontal':
				$result = array('x' => 1, 'y' => $this->height() + 1);
				break;

			// vertical -> align horizontally
			case 'vertical':
				$result = array('x' => $this->width() + 1, 'y' => 1);
				break;

			default:
				$GLOBALS['log']->warn(__CLASS__.": Unknown sprite placement algorithm -> {$this->config['type']}");
				break;
		}

		return $result;
	}

	// calculate total width
	function width() {
		return $this->getMaxAxis('x');
	}

	// calculate total height
	function height() {
		return $this->getMaxAxis('y');
	}

	// helper function to get highest axis value
	function getMaxAxis($axis) {
		$val = 0;
		foreach($this->spriteMatrix as $id => $coor) {
			$new_val = $coor[$axis] + $this->spriteSrc[$id][$axis] - 1;
			if($new_val > $val) {
				$val = $new_val;
			}
		}
		return $val;
	}
}

?>