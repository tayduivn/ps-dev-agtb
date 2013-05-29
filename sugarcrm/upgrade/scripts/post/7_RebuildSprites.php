<?php
/**
 * Rebuild image sprites
 */
class SugarUpgradeRebuildSprites extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        require_once('modules/Administration/SugarSpriteBuilder.php');
        $sb = new SugarSpriteBuilder();
        $sb->cssMinify = true;
        $sb->fromSilentUpgrade = true;
        $sb->silentRun = true;

        // add common image directories
        $sb->addDirectory('default', 'include/images');
        $sb->addDirectory('default', 'themes/default/images');
        $sb->addDirectory('default', 'themes/default/images/SugarLogic');

        // add all theme image directories
        foreach(array('themes', 'custom/themes') as $themedir) {
            if(!file_exists($themedir)) continue;
            foreach(new DirectoryIterator($themedir) as $fileInfo) {
                if($fileInfo->isDot() || !$fileInfo->isDir()) continue;
                $dir = $fileInfo->getFilename();
                if($dir == 'default' || !is_dir("$themedir/{$dir}/images")) continue;
                $sb->addDirectory($dir, "$themedir/{$dir}/images");
            }
        }

        // generate the sprite goodies
        // everything is saved into cache/sprites
        $sb->createSprites();
    }
}
