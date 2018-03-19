<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once 'syntax/PlantUmlDiagram.php';

final class PlantUMLDiagramTest extends TestCase
{

    public function testSvgUrlGeneratedCorrectly()
    {
        $diagramObject = new PlantUmlDiagram("@startuml\nalice -> bob: yo what up\nbob->alice: not much\n@enduml");

        $this->assertEquals(
            'https://www.planttext.com/plantuml/svg/SoWkIImgAStDuKhCoKnELT2rKqZAJx9IgCnNACz8B54eBU02ydNjmB9M2ddv9GgvfSaPN0wfUIb0NG00',
            $diagramObject->getSVGDiagramUrl()
        );
    }

    public function testPngUrlGeneratedCorrectly()
    {
        $diagramObject = new PlantUmlDiagram("@startuml\nalice -> bob: yo what up\nbob->alice: not much\n@enduml");

        $this->assertEquals(
            'https://www.planttext.com/plantuml/png/SoWkIImgAStDuKhCoKnELT2rKqZAJx9IgCnNACz8B54eBU02ydNjmB9M2ddv9GgvfSaPN0wfUIb0NG00',
            $diagramObject->getPNGDiagramUrl()
        );
    }

    public function testTxtUrlGeneratedCorrectly()
    {
        $diagramObject = new PlantUmlDiagram("@startuml\nalice -> bob: yo what up\nbob->alice: not much\n@enduml");

        $this->assertEquals(
            'https://www.planttext.com/plantuml/txt/SoWkIImgAStDuKhCoKnELT2rKqZAJx9IgCnNACz8B54eBU02ydNjmB9M2ddv9GgvfSaPN0wfUIb0NG00',
            $diagramObject->getTXTDiagramUrl()
        );
    }

    public function testGetMarkup()
    {
        $diagramObject = new PlantUmlDiagram("@startuml\nalice -> bob: yo what up\nbob->alice: not much\n@enduml");

        $this->assertEquals(
            '@startuml<br />
alice -> bob: yo what up<br />
bob->alice: not much<br />
@enduml',
            $diagramObject->getMarkup()
        );
    }
}
