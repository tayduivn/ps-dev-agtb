<?php
require_once 'include/HTMLPurifier/HTMLPurifier.standalone.php';
require_once 'include/HTMLPurifier/HTMLPurifier.autoload.php';

class SugarCleaner
{
    /**
     * Singleton instance
     * @var SugarCleaner
     */
    static public $instance;

    /**
     * HTMLPurifier instance
     * @var HTMLPurifier
     */
    protected $purifier;

    function __construct()
    {
        global $sugar_config;
        $config = HTMLPurifier_Config::createDefault();

        if(!is_dir($sugar_config['cache_dir']."/htmlclean")) {
            mkdir($sugar_config['cache_dir']."/htmlclean", 0755);
        }
        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
        $config->set('Cache.SerializerPath', $sugar_config['cache_dir']."/htmlclean");
        $config->set('URI.Base', $sugar_config['site_url']);
        $config->set('CSS.Proprietary', true);
        $config->set('HTML.TidyLevel', 'light');
        $config->set('HTML.ForbiddenElements', array('body' => true, 'html' => true));
        $config->set('AutoFormat.RemoveEmpty', false);

/*
   "applet"
   "base"
   "embed"
   "form"
   "frame"
   "frameset"
   "iframe"
   "import"
   "layer"
   "link"
   "object"
   "style"
   "xmp"
 */
        // for style
        $config->set('Filter.ExtractStyleBlocks', true);
        // for object
        $config->set('HTML.SafeObject', true);
        // for embed
        $config->set('HTML.SafeEmbed', true);
        $config->set('Output.FlashCompat', true);
        // for iframe and xmp
        $config->set('Filter.Custom',  array(new HTMLPurifier_Filter_Xmp()));
        // for link
        $config->set('HTML.DefinitionID', 'enduser-customize.html tutorial');
        $config->set('HTML.DefinitionRev', 2);
        $config->set('Cache.DefinitionImpl', null); // remove this later!
        $def = $config->getHTMLDefinition(true);
        $form = $def->addElement(
  			'link',   // name
  			'Flow',  // content set
  			'Empty', // allowed children
  			'Core', // attribute collection
             array( // attributes
        		'href*' => 'URI',
        		'rel' => 'Enum#stylesheet', // only stylesheets supported here
        		'type' => 'Enum#text/css' // only CSS supported here
			)
        );
        $iframe = $def->addElement(
  			'iframe',   // name
  			'Flow',  // content set
  			'Optional: #PCDATA | Flow | Block', // allowed children
  			'Core', // attribute collection
             array( // attributes
        		'src*' => 'URI',
                'frameborder' => 'Enum#0,1',
                'marginwidth' =>  'Pixels',
                'marginheight' =>  'Pixels',
                'scrolling' => 'Enum#|yes,no,auto',
             	'align' => 'Enum#top,middle,bottom,left,right,center',
                'height' => 'Length',
                'width' => 'Length',
             )
        );
        $iframe->excludes=array('iframe');
        //? $uri = $config->getDefinition('URI');
        //? Add IMG SRC filtering? $uri->addFilter(new SugarURIFilter(), $config);

        $this->purifier = new HTMLPurifier($config);
    }

    /**
     * Get cleaner instance
     * @return SugarCleaner
     */
    public static function getInstance()
    {
        if(empty(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Clean string from potential XSS problems
     * @param string $html
     * @param bool $encoded Was it entity-encoded?
     * @return string
     */
    static public function cleanHtml($html, $encoded = true)
    {
        if(empty($html)) return '';

        if($encoded) {
            $html = from_html($html);
        }
        if(!preg_match('<[^-A-Za-z0-9 `~!@#$%^&*()_=+{}\[\];:\'",./\\?\r\n|]>', $html)) {
            /* if it only has "safe" chars, don't bother */
            $cleanhtml = $html;
        } else {
            $purifier = self::getInstance()->purifier;
            $cleanhtml = $purifier->purify($html);
            $styles = $purifier->context->get('StyleBlocks');
            if(count($styles) > 0) {
                $cleanhtml = "<style>".join("</style><style>", $styles)."</style>".$cleanhtml;
            }
        }
        if($encoded) {
            $cleanhtml = to_html($cleanhtml);
        }
        return $cleanhtml;
    }
}

/**
 * URI filter for HTMLPurifier
 * Approves only resource URIs that are in the list of trusted domains
 */
class SugarURIFilter extends HTMLPurifier_URIFilter
{
    public $name = 'SugarURIFilter';
//    public $post = true;
    protected $allowed = array();

    public function prepare($config)
    {
        global $sugar_config;
        if(isset($sugar_config['security_trusted_domains']) && !empty($sugar_config['security_trusted_domains']) && is_array($sugar_config['security_trusted_domains']))
        {
            $this->allowed = $sugar_config['security_trusted_domains'];
        }
        /* Allow this host?
        $def = $config->getDefinition('URI');
        if(!empty($def->base) && !empty($this->base->host)) {
            $this->allowed[] = $def->base->host;
        }
        */
    }

    public function filter(&$uri, $config, $context)
    {
        // skip non-resource URIs
        if (!$context->get('EmbeddedURI', true)) return true;

        if(empty($this->allowed)) return false;

//? Allow relative URLS?        if(empty($uri->host)) return true;

        foreach($this->allowed as $allow) {
            // must be equal to our domain or subdomain of our domain
            if($uri->host == $allow || substr($uri->host, -(strlen($allow)+1)) == ".$allow") {
                return true;
            }
        }
        return false;
    }
}

class HTMLPurifier_Filter_Xmp extends HTMLPurifier_Filter
{

    public $name = 'Xmp';

    public function preFilter($html, $config, $context) {
        return preg_replace("#<(/)?xmp>#i", "<\\1pre>", $html);
    }
}