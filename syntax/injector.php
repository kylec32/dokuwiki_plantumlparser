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
        $this->Lexer->addSpecialPattern('<uml.*?>\n.*?\n</uml>', $mode, 'plugin_plantumlparser_injector');
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
        $re = '/(?P<tag1><uml.*>)(?P<markup>(?s).*)(?P<tag2>\<\/uml\>)/m';
        preg_match($re, $match, $matches0);
        $markup = $matches0['markup'];
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
        
        # Get height and width information from uml tag
        $height = '';
        $re = '/height="(?P<height>[0-9]+%?)"/m';
        if (preg_match($re, $matches0['tag1'], $matches)) {
                $height = $matches['height'];
        }

        $width = '';
        $re = '/width="(?P<width>[0-9]+%?)"/m';
        if (preg_match($re, $matches0['tag1'], $matches)) {
                $width = $matches['width'];
        }
        
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
            'height' => $height,
            'width' => $width,
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
        if($mode != 'xhtml') return false;
        
        $renderer->doc .= "<div id='plant-uml-diagram-".$data['id']."'>";
        if(strlen($data['svg']) > 0) {
            if(is_a($renderer,'renderer_plugin_dw2pdf') && (preg_match("/(@startlatex|@startmath|<math|<latex)/", $data['markup']))){
                $renderer->doc .= "<img src='".$data['url']['png']."'>";
            }
            else {
                # check for scale attribute, and replace it.
                if($data['height'] != '' || $data['width'] != '') {
                    $replacementSize = 'style="';
                    if($data['height'] != '') {
                        $replacementSize .= 'height:'.$data['height'].';';
                    }
                    if($data['width'] != '') {
                        $replacementSize .= 'width:'.$data['width'].';';
                    }
                    $re = '/style="width:[0-9]+px;height:[0-9]+px;/m';
                    $renderer->doc .= preg_replace($re, $replacementSize, $data['svg']);
                }
                else
                {
                    $renderer->doc .= $data['svg'];
                }
            }
        } else {
            $renderer->doc .= "<object data='".$data['url']['svg']."' type='image/svg+xml'>";
            $renderer->doc .= "<span>".$data['markup']."</span>";
            $renderer->doc .= "</object>";
        }
        if($data['include_links'] == "1") {
            $renderer->doc .= "<div id=\"plantumlparse_link_section\">";
            $renderer->doc .= "<a target='_blank' href='".$data['url']['svg']."'>SVG</a> | ";
            $renderer->doc .= "<a target='_blank' href='".$data['url']['png']."'>PNG</a> | ";
            $renderer->doc .= "<a target='_blank' href='".$data['url']['txt']."'>TXT</a>";
            $renderer->doc .= "</div>";
        }
        $renderer->doc .= "</div>";

        return true;
    }
}
