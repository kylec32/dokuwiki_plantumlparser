<?php

if (!defined('DOKU_INC')) die();

include_once 'PlantUmlDiagram.php';

class syntax_plugin_plantumlparser_injector extends DokuWiki_Syntax_Plugin {
    private $TAG = 'uml';

    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'substition';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 199; // In case we are operating in a Dokuwiki that has the other PlantUML plugin we want to beat it.
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<'.$this->TAG.'>\n*.*?\n*</'.$this->TAG.'>',$mode,'plugin_plantumlparser_injector');
    }

    /**
     * Handle matches of the plantumlparser syntax
     *
     * @param string          $match   The match of the syntax
     * @param int             $state   The state of the handler
     * @param int             $pos     The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $markup        = str_replace('</' . $this->TAG . '>', '', str_replace('<' . $this->TAG . '>', '', $match));
        $plantUmlUrl   = trim($this->getConf('PlantUMLURL'));
		if(!$plantUmlUrl)
		{
			$plantUmlUrl = "https://www.plantuml.com/plantuml/";
		}
		else
		{
			$plantUmlUrl = trim($plantUmlUrl, '/') . '/';
		}
        $diagramObject = new PlantUmlDiagram($markup,$plantUmlUrl);

        return [
            'svg' => strstr($diagramObject->getSVG(), "<svg"),
            'markup' => $diagramObject->getMarkup(),
            'id' => sha1($diagramObject->getSVGDiagramUrl()),
            'include_links' => $this->getConf('DefaultShowLinks'),
            'url' => [
                'svg' => $diagramObject->getSVGDiagramUrl(),
                'png' => $diagramObject->getPNGDiagramUrl(),
                'txt' => $diagramObject->getTXTDiagramUrl(),
            ],
        ];
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        switch($mode) {
        case 'xhtml':
			$renderer->doc .= "<div id='plant-uml-diagram-".$data['id']."'>";
            if(strlen($data['svg']) > 0) {
				if(preg_match("/(@startlatex|@startmath|<math|<latex)/", $data['markup'])){
					$renderer->doc .= "<img src='".$data['url']['png']."'>";
				}
				else {
					$renderer->doc .= $data['svg'];
				}
			} else {
			    if(preg_match("/(ditaa)/", $data['markup'])){
					$renderer->doc .= "<img src='".$data['url']['png']."'>";
				}
				else {
				    $renderer->doc .= "<object data='".$data['url']['svg']."' type='image/svg+xml'>";
				    $renderer->doc .= "<span>".$data['markup']."</span>";
				    $renderer->doc .= "</object>";
				}
			}
        
            if($data['include_links'] == "1") {
                $renderer->doc .= "<div id=\"plantumlparse_link_section\">";
                $renderer->doc .= "<a target='_blank' href='".$data['url']['svg']."'>SVG</a> | ";
                $renderer->doc .= "<a target='_blank' href='".$data['url']['png']."'>PNG</a> | ";
                $renderer->doc .= "<a target='_blank' href='".$data['url']['txt']."'>TXT</a>";
                $renderer->doc .= "</div>";
            }
        
            $renderer->doc .= "</div>";
        break;
        case 'odt': case 'odt_pdf':
            return $this->_render_odt($renderer, $state, $data);
        break;
        }

        return true;
    }

    /**
     * Render odt output.
     *
     * @param Doku_Renderer  $renderer  The renderer
     * @param int            $state     The state
     * @param string         $txtdata   The data from the render() function
     * @param string         $align     img align
     * @return bool If rendering was successful.
     */
    protected function _render_odt(Doku_Renderer $renderer, $state, $txtdata) {
        // if($state === DOKU_LEXER_UNMATCHED) {
			if(preg_match("/(@startlatex|@startmath|<math|<latex|ditaa)/", $txtdata['markup'])){
			    $im = imagecreatefromstring((new DokuHTTPClient())->get($txtdata['url']['png']));
				$width = imagesx($im);
				$height = imagesy($im);
				$renderer->_odtAddImage($txtdata['url']['png'], $width, $height);
			} else {
				$dim=$this->_extract_XY_4svg( $txtdata['svg'] );
				// $renderer->unformatted("Width: ".$dim[0]."px");
				// $renderer->unformatted("Height: ".$dim[1]."px");
				list($width, $height) = $this->_odtGetImageSize(NULL, $dim[0], $dim[1]);
				// $renderer->unformatted("Width: ".$width."cm");
				// $renderer->unformatted("Height: ".$height."cm");
				$renderer->_addStringAsSVGImage($txtdata['svg'], $width, $height);
			}
        // }

        return true;
    }

    /**
     * Find the SVG X and Y dimensions in the svg string of the image.
     * it searches for 'width="nnnpx" height="mmmpx"' in the first
     * given string and returns the dimension in inch.
     *
     * @param String  $svgtxt  The svg string to inspect
     * @return array the X and Y dimensions suitable as SVG dimensions
     */
    protected function _extract_XY_4svg( $svgtxt ) {
        // $sizes=array();
        // preg_match( '/width="(.*?)px" height="(.*?)px"/', $svgtxt, $sizes );
		// array_shift($sizes);

		$matchesWidth=array();
		$matchesHeight=array();
		preg_match( '/<svg.*?width="((\d+))/', $svgtxt, $matchesWidth);
		preg_match( '/<svg.*?height="((\d+))/', $svgtxt, $matchesHeight);
		$sizes = array($matchesWidth[1], $matchesHeight[1]);

        // assume a 96 dpi screen
        // return array_map( function($v) { return ($v/96.0)."in"; }, $sizes );

		return $sizes;
    }

	protected function _odtGetImageSize($src, $width = NULL, $height = NULL){
        if (file_exists($src)) {
            $info  = getimagesize($src);
            if(!$width){
                $width  = $info[0];
                $height = $info[1];
            }else{
                $height = round(($width * $info[1]) / $info[0]);
            }
        }

        // convert from pixel to centimeters
        if ($width) $width = (($width/96.0)*2.54);
        if ($height) $height = (($height/96.0)*2.54);

        if ($width && $height) {
            // Don't be wider than the page
            if ($width >= 17){ // FIXME : this assumes A4 page format with 2cm margins
                $width = $width.'cm"  style:rel-width="100%';
                $height = $height.'cm"  style:rel-height="scale';
            } else {
                $width = $width.'cm';
                $height = $height.'cm';
            }
        } else {
            // external image and unable to download, fallback
            if ($width) {
                $width = $width."cm";
            } else {
                $width = '" svg:rel-width="100%';
            }
            if ($height) {
                $height = $height."cm";
            } else {
                $height = '" svg:rel-height="100%';
            }
        }
        return array($width, $height);
    }
}
