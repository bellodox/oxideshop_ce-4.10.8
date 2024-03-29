<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 * @version   OXID eShop CE
 */

/**
 * Testing oxXml class.
 */
class Unit_Core_oxSimpleXmlTest extends OxidTestCase
{

    public function testObjectToXml()
    {
        $oXml = new oxSimpleXml();

        $oTestObject = new oxStdClass();
        $oTestObject->title = "TestTitle";
        $oTestObject->keys = new oxStdClass();
        $oTestObject->keys->key = array("testKey1", "testKey2");

        $sTestResult = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $sTestResult .= "<testXml>";
        $sTestResult .= "<title>TestTitle</title>";
        $sTestResult .= "<keys><key>testKey1</key><key>testKey2</key></keys>";
        $sTestResult .= "</testXml>\n";

        $this->assertEquals($sTestResult, $oXml->objectToXml($oTestObject, "testXml"));
    }

    public function testObjectToXmlWithObjectsInArray()
    {
        $oXml = new oxSimpleXml();

        $oModule1 = new stdClass();
        $oModule1->id = "id1";
        $oModule1->active = true;

        $oModule2 = new stdClass();
        $oModule2->id = "id2";
        $oModule2->active = false;

        $oTestObject = new oxStdClass();
        $oTestObject->title = "TestTitle";
        $oTestObject->modules = new oxStdClass();
        $oTestObject->modules->module = array($oModule1, $oModule2);

        $oExpectedXml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><testXml/>");
        $oExpectedXml->addChild("title", "TestTitle");
        $modules = $oExpectedXml->addChild("modules");

        $module = $modules->addChild("module");
        $module->addChild('id', 'id1');
        $module->addChild('active', '1');

        $module = $modules->addChild("module");
        $module->addChild('id', 'id2');
        $module->addChild('active', '');

        $this->assertEquals($oExpectedXml->asXML(), $oXml->objectToXml($oTestObject, "testXml"));
    }

    public function testXmlToObject()
    {
        $oXml = new oxSimpleXml();

        $sTestXml = '<?xml version="1.0"?>';
        $sTestXml .= '<testXml>';
        $sTestXml .= '<title>TestTitle</title>';
        $sTestXml .= '<keys><key>testKey1</key><key>testKey2</key></keys>';
        $sTestXml .= '</testXml>';

        $oRes = $oXml->xmlToObject($sTestXml);

        $this->assertEquals((string) $oRes->title, "TestTitle");
        $this->assertEquals((string) $oRes->keys->key[0], "testKey1");
        $this->assertEquals((string) $oRes->keys->key[1], "testKey2");
    }

    public function testObjectToXmlWithElementsAndAttributes()
    {
        $oXml = new oxSimpleXml();

        $oElement1 = new stdClass();
        $oElement1->id = 'id1';
        $oElement1->active = true;

        $oElement2 = new stdClass();
        $oElement2->id = array('attributes' => array('attr1' => 'value1', 'attr2' => 'value2'), 'value' => 'id2');
        $oElement2->active = true;

        $oTestObject = new stdClass();
        $oTestObject->elements = new stdClass();
        $oTestObject->elements->element = array(array('attributes' => array('attr3' => 'value3'), 'value' => $oElement1), $oElement2);

        $sTestResult = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $sTestResult .= '<testXml>';
        $sTestResult .= '<elements>';
        $sTestResult .= '<element attr3="value3"><id>id1</id><active>1</active></element>';
        $sTestResult .= '<element><id attr1="value1" attr2="value2">id2</id><active>1</active></element>';
        $sTestResult .= '</elements>';
        $sTestResult .= '</testXml>' . "\n";

        $this->assertEquals($sTestResult, $oXml->objectToXml($oTestObject, "testXml"));
    }

    public function testObjectToXmlWithElementsWithAttributesKey()
    {
        $oXml = new oxSimpleXml();

        $oTestObject = new stdClass();
        $oTestObject->attributes = new stdClass();
        $oTestObject->attributes->attribute = array('attrValue1', 'attrValue2');

        $sTestResult = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $sTestResult .= '<testXml>';
        $sTestResult .= '<attributes>';
        $sTestResult .= '<attribute>attrValue1</attribute>';
        $sTestResult .= '<attribute>attrValue2</attribute>';
        $sTestResult .= '</attributes>';
        $sTestResult .= '</testXml>' . "\n";

        $this->assertEquals($sTestResult, $oXml->objectToXml($oTestObject, "testXml"));
    }

    public function testObjectToXmlWithAssocArrayKeys()
    {
        $oXml = new oxSimpleXml();

        $oTestObject = new oxStdClass();
        $oTestObject->elements = array('element' => array(
            array('key1' => 'value1', 'key2' => 'value2'),
            array('key1' => 'value1', 'key2' => 'value2')
        ));

        $sTestResult = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $sTestResult .= "<testXml>";
        $sTestResult .= "<elements>";
        $sTestResult .= "<element><key1>value1</key1><key2>value2</key2></element>";
        $sTestResult .= "<element><key1>value1</key1><key2>value2</key2></element>";
        $sTestResult .= "</elements>";
        $sTestResult .= "</testXml>\n";

        $this->assertEquals($sTestResult, $oXml->objectToXml($oTestObject, "testXml"));
    }
}