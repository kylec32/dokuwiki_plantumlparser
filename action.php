<?php
if (!defined('DOKU_INC')) die();

class action_plugin_plantumlparser extends DokuWiki_Action_Plugin
{
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'componentUMLContent', array());
    }
    
    /**
    * Fix UML search result in the content
    * When searching for a string that is inside a ``uml`` tag with SVG rendering, 
    * the DokuWiki built-in syntax highlighter breaks SVG content when applying highlight into it. 
    * This function removes the search highlight inside SVG to avoid breaking SVG rendering.
    */
    function componentUMLContent(Doku_Event &$event, $param)
    {
        $dom = new DomDocument();
        @$dom->loadHTML(mb_convert_encoding($event->data, 'HTML-ENTITIES', "UTF-8"));
        $xpath = new DOMXPath($dom);
        
        foreach ($xpath->query("//div[starts-with(@id,'plant-uml-diagram')]//span[contains(@class,'search_hit')]") as $node) {
            // $text->nodeValue = "TESTE";
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($node->nodeValue);
    
            $parent = $node->parentNode;
            $parent->insertBefore($fragment,$node);
            $parent->removeChild($node);
        }
        
        $event->data = $dom->saveHTML($dom->documentElement);
    }
}
