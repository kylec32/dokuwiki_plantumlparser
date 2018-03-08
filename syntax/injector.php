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
    public function handle($match, $state, $pos, Doku_Handler $handler){
        $markup = str_replace('</'.$this->TAG.'>','',str_replace('<'.$this->TAG.'>','',$match));
        return $markup;
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
        $diagramObject = new PlantUmlDiagram($data);
        $renderer->doc .= "<span id='plant-uml-diagram-".md5($diagramObject->getSVGDiagramUrl())."'>";
        $renderer->doc .= "<object data='".$diagramObject->getSVGDiagramUrl()."' type='image/svg+xml'>";
        $renderer->doc .= "<span>".$diagramObject->getMarkup()."</span>";
        $renderer->doc .= "</object>";
        $renderer->doc .= "<div>";
        $renderer->doc .= "<a target='_blank' href='".$diagramObject->getSVGDiagramUrl()."'>SVG</a> | ";
        $renderer->doc .= "<a target='_blank' href='".$diagramObject->getPNGDiagramUrl()."'>PNG</a> | ";
        $renderer->doc .= "<a target='_blank' href='".$diagramObject->getTXTDiagramUrl()."'>TXT</a>";
        $renderer->doc .= "</div>";
        $renderer->doc .= "</span>";

        return true;
    }
}
