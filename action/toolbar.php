<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_plantumlparser_toolbar extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
   
    }

    /**
     * Handles the "New PlantUML Graph" button.
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  ignored
     * @return void
     */

    public function insert_button(Doku_Event &$event, $param) {
        $event->data[] = array (
            'type' => 'format',
            'icon' => '../../plugins/plantumlparser/res/toolbar_icon.png',
            'title' => htmlspecialchars('New PlantUML Graph'),
            'open' => '<uml>',
            'close' => '</uml>',
            'sample' => '\n@startuml\nBob -> Alice : hello\n@enduml\n',
        );
    }

}

// vim:ts=4:sw=4:et:
