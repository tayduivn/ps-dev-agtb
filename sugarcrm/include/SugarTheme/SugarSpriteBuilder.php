<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once("include/SugarTheme/cssmin.php");

class SugarSpriteBuilder {

	var $isAvailable = false;
	var $silentRun = false;
	var $debug = false;
	var $fileName = 'sprites';

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

	// processed image types
	var $imageTypes = array();

	// source files
	var $spriteSrc = array();

	// sprite resource images
	var $spriteImg;


	public function __construct() {
		
		// check if we have gd installed
		if(function_exists('imagecreatetruecolor')) {
			$this->isAvailable = true;
			$this->getSupportedTypes();
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
		if(! array_key_exists($name, $this->spriteSrc)) {
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
			    while (($file = readdir($dh)) !== false) {
					if ($file != "." && $file != ".." && $file != "sprites_config.php") {

						// add sprite if supported format
						if($info = $this->getFileInfo($dir, $file)) {
							$list[$file] = $info;
						}
	   	     		}
   		 		}
			}
		    closedir($dh);
		}
		return $list;
	}

	// return array of file info if image type is supported
	private function getFileInfo($dir, $file) {
		$result = false;
		$info = @getimagesize($dir.'/'.$file);
		if($info) {

			// supported image type ? 
			if(isset($this->imageTypes[$info[2]])) {

				$w = $info[0];
				$h = $info[1];
				$surface = $w * $h;

				// be sure we have an image size
				$addSprite = false;
				if($surface) {

					// sprite dimensions
					if($w <= $this->maxWidth && $h <= $this->maxHeight) {
						$addSprite = true;
					}

					// other parameters ???
				}

				if($addSprite) {
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

		if(! $this->isAvailable) {
			return false;
		}

		foreach($this->spriteSrc as $name => $dirs) {

			if(! $this->silentRun) 
				echo "<u>Creating sprite namespace \"$name\"</u> ";

			// config sprite placement config
			$config = array(
				'type' => 1,
				'width' => $this->maxWidth,
				'height' => $this->maxHeight,
			);

			// use seperate class to arrange the images
			$sp = new SpritePlacement($dirs, $config);
			$sp->processSprites();

			if(! $this->silentRun)
				echo " (size {$sp->width()}x{$sp->height()})<br />";

			// we need a target image size
			if($sp->width() & $sp->height()) {

				// init sprite image
				$this->initSpriteImg($sp->width(), $sp->height());

				// add sprites based upon determined coordinates
				foreach($dirs as $dir => $files) {

					if(! $this->silentRun)
						echo "&nbsp;&nbsp;* Processing $dir <br />";

					foreach($files as $file => $info) {
						if($im = $this->loadImage($dir, $file, $info['type'])) {
	
							// coordinates
							$dst_x = $sp->spriteMatrix[$dir.'/'.$file]['x'];
							$dst_y = $sp->spriteMatrix[$dir.'/'.$file]['y'];

							imagecopy($this->spriteImg, $im, $dst_x, $dst_y, 0, 0, $info['x'], $info['y']);
							imagedestroy($im);

							if(! $this->silentRun && $this->debug) 
								echo "&nbsp;&nbsp;--> added sprite $dir/$file <br />";
						}
					}
				}
	
				// create dirs if not exist
				if(!is_dir("cache/sprites/$name"))
                	mkdir("cache/sprites/$name", 0750, true);

				// save sprite image
				imagepng($this->spriteImg, "cache/sprites/$name/{$this->fileName}.png", $this->pngCompression, $this->pngFilter);
				imagedestroy($this->spriteImg);

				/* generate css & metadata */

				$head = '';
				$body = '';
				$metadata = '';

				foreach($sp->spriteSrc as $id => $info) {
					// sprite id
					$hash_id = md5($id);

					// header
					$head .= "span.spr_$hash_id,\n";

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

					// metadata TODO do we actually need this besides debugging ???
					/*
					$metadata[$hash_id] = array(
						'sprite' => $id,
						'width' => $w,
						'height' => $h,
						'offset_x' => $offset_x,
						'offset_y' => $offset_y,
						'source' => $name.'.png',
					);
					*/
					$metadata .= '$sprites["'.$hash_id.'"] = array ("image"=>"'.$id.'","sprite"=>"cache/sprites/'.$name.'/'.$this->fileName.'.png");'."\n";
				} 

				// common css header
				$head .= "span.spr_bogus {
background: url('../../../index.php?entryPoint=getImage&imageName={$this->fileName}.png&spriteNamespace={$name}') no-repeat;
display: inline-block;
}\n";

				// save css
				$css_content = cssmin::minify("/* autogenerated sprites - $name */\n".$head.$body);
				$fh = fopen("cache/sprites/$name/{$this->fileName}.css", "w");
				fwrite($fh, $css_content);
				fclose($fh);

				/* save metadata */

				$fh = fopen("cache/sprites/$name/{$this->fileName}.meta.php", "w");
				fwrite($fh, '<?php'."\n/* sprites metadata - $name */\n");
				fwrite($fh, $metadata);
				fclose($fh);

			// if width & height
			} else {

				if(! $this->silentRun) 
					echo "Skipping namespace, no sprites available ! <br />";

			}

			if(! $this->silentRun)
				echo "<br />"; 

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
}

class SpritePlacement {

	// occupied space
	var $spriteMatrix = array();

	// minimum surface
	var $minSurface = 0;

	// sprite src (flattened array)
	var $spriteSrc = array();

	// placement config array
	/*
		type = 	1 -> boxed
				2 -> horizontal //TODO
				3 -> vertical //TODO
		
		required params for
		type 1 	-> width
				-> height

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
			case 1:

				// we put 30 sprites on a row 
				$spritesPerRow = 30;
				$spriteX = $this->config['width'];
				$spriteY = $this->config['height'];
				$spriteCnt = count($this->spriteMatrix) + 1;
				$y = ceil($spriteCnt / $spritesPerRow);
				$x = $spriteCnt - (($y - 1) * $spritesPerRow);
				$result = array(
					'x' => ($x * $spriteX) + 1 - $spriteX, 
					'y' => ($y * $spriteY) + 1 - $spriteY);

				break;

			// horizontal
			case 2:
				break;

			// vertical
			case 3:
				break;

			// other algorithms instead of boxed ???

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
