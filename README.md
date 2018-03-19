# PlantUML Parser Plugin

---

[![Build Status](https://travis-ci.org/kylec32/dokuwiki_plantumlparser.svg?branch=master)](https://travis-ci.org/kylec32/dokuwiki_plantumlparser)

This plugin integrates [PlantUML](http://plantuml.sourceforge.net) into the [DokuWiki](http://www.dokuwiki.org) wiki engine.
It allows to generate UML graph images from simple description text block.

# Features
* Create any UML graph supported by PlantUML.
* Generated images are SVGs.
* Toolbar button.
* No internet access required for wiki server.

# Sample
This block describes a sequence diagram:

    <uml>
    Alice -> Bob: Authentication Request
    Bob --> Alice: Authentication Response

    Alice -> Bob: Another authentication Request
    Alice <-- Bob: another authentication Response
    </uml>

and results in:

![Sample](http://plantuml.sourceforge.net/img/sequence_img001.png)

# Contributors
* [Kyle Carter](https://github.com/kylec32)
* [Antoine Aflalo](https://github.com/Belphemur)

