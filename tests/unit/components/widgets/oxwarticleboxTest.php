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
 * Tests for oxwArticleBox class
 */
class Unit_Components_Widgets_oxwArticleBoxTest extends OxidTestCase
{

    /**
     * Template view parameters data provider
     */
    public function _dpTemplateViewParams()
    {
        return array(
            array("product", "listitem_grid", "widget/product/listitem_grid.tpl"),
            array("product", "listitem_infogrid", "widget/product/listitem_infogrid.tpl"),
            array("product", "listitem_line", "widget/product/listitem_line.tpl"),
            array("product", "boxproduct", "widget/product/boxproduct.tpl"),
            array("product", "bargainitem", "widget/product/bargainitem.tpl"),
        );
    }

    /**
     * Test for rendering default template
     */
    public function testRender()
    {
        $oArticleBox = new oxwArticleBox();

        $this->assertEquals("widget/product/boxproduct.tpl", $oArticleBox->render(), "Default template should be loaded");
    }

    /**
     * Test for getting different templates
     *
     * @dataProvider _dpTemplateViewParams
     */
    public function testRenderDifferentTemplates($sWidgetType, $sListType, $sExpected)
    {
        $oArticleBox = new oxwArticleBox();

        $aViewParams = array(
            "sWidgetType" => $sWidgetType,
            "sListType"   => $sListType,
        );
        $oArticleBox->setViewParameters($aViewParams);

        $this->assertEquals($sExpected, $oArticleBox->render(), "Correct template should be loaded");
    }

    /**
     * Test for rendering forced template
     */
    public function testRenderForcedTemplate()
    {
        $oArticleBox = new oxwArticleBox();

        $sForcedTemplate = "page/compare/inc/compareitem.tpl";

        $aViewParams = array(
            "oxwtemplate" => $sForcedTemplate,
        );
        $oArticleBox->setViewParameters($aViewParams);

        $this->assertEquals($sForcedTemplate, $oArticleBox->render(), "Correct template should be loaded");
    }

    /**
     * Test for getting product by id set in view parameters
     */
    public function testGetProduct()
    {
        $oArticleBox = new oxwArticleBox();

        $sId = "1126";
        $iLinkType = 4;
        $aViewParams = array(
            "anid"      => $sId,
            "iLinkType" => $iLinkType,
        );
        $oArticleBox->setViewParameters($aViewParams);

        $this->assertEquals($sId, $oArticleBox->getProduct()->getId(), "Correct product should be loaded");
        $this->assertEquals($iLinkType, $oArticleBox->getProduct()->getLinkType(), "Correct link type should be set");
    }

    /**
     * Checking if additional parameters are being recieved and added properly
     */
    public function testGetProductWithSearch()
    {
        $oArticleBox = new oxwArticleBox();
        $this->setLanguage(1);

        $sId = "1126";
        $iLinkType = 4;
        $aViewParams = array(
            "anid"      => $sId,
            "iLinkType" => $iLinkType,
        );
        $oArticleBox->setViewParameters($aViewParams);
        $sLinkUrl = $oArticleBox->getProduct()->getMainLink();

        $oArticleBox->setParent("search");
        $oConfig = $this->getMock("oxConfig", array('getTopActiveView'));
        $oSearch = new Search();
        $oConfig->expects($this->any())->method('getTopActiveView')->will($this->returnValue($oSearch));

        $oArticleBox->setConfig($oConfig);
        $sLinkUrl .= "?listtype=search&amp;searchparam=1126";


        $this->setRequestParam("searchparam", "1126");
        // removing cached object
        $oArticleBox->setProduct(null);
        $this->assertEquals($sLinkUrl, $oArticleBox->getProduct()->getMainLink(), "Correct product link with additional search parameters should be loaded");
    }

    /**
     * Test for getting link
     */
    public function testGetLink()
    {
        $oView = new aList();
        $oView->setClassName("alist");

        oxRegistry::getConfig()->setActiveView($oView);

        $oArticleBox = new oxwArticleBox();
        $aViewParams = array(
            "_parent" => $oView->getClassName()
        );
        $oArticleBox->setViewParameters($aViewParams);

        $this->assertNotEquals(false, strpos($oArticleBox->getLink(), "cl=alist"));
    }

    /**
     * Test case for getting RSS links
     */
    public function testGetRSSLinks()
    {
        $oArticleBox = new oxwArticleBox();

        $aRSS = array(
            'title' => 'OXID eShop 5/Bargain',
            'link'  => 'http://trunk:8080/en/rss/OXID-eShop-5/Bargain/',
        );
        $aViewParams = array(
            "rsslinks" => $aRSS,
        );
        $oArticleBox->setViewParameters($aViewParams);

        $this->assertEquals($aRSS, $oArticleBox->getRSSLinks(), "Should get RSS links' array");
    }

    /**
     * Test case for getting RSS links in correct type
     */
    public function testGetRSSLinksReturnsCorrectType()
    {
        $oArticleBox = new oxwArticleBox();

        $aViewParams = array(
            "rsslinks" => array(),
        );
        $oArticleBox->setViewParameters($aViewParams);

        $this->assertEquals(array(), $oArticleBox->getRSSLinks(), "Should get array");

        $aViewParams = array(
            "rsslinks" => "rsslink",
        );
        $oArticleBox->setViewParameters($aViewParams);

        $this->assertEquals(null, $oArticleBox->getRSSLinks(), "Should get null");
    }

    /**
     * View parameters test case data provider
     *
     * @return array
     */
    public function _dpViewParameters()
    {
        return array(
            array("recommid", "Z8oRXLEnInxn", "getRecommId", "Recommendation list id"),
            array("iIteration", "7", "getIteration", "Iteration number"),
            array("iIndex", "3", "getIndex", "Test id"),
            array("owishid", "7g7eZ6hxUsad", "getWishId", "Wishlist id"),
            array("showMainLink", false, "getShowMainLink", "Condition if main link is showed"),
            array("blDisableToCart", true, "getDisableToCart", "Condition if to cart button is showed"),
            array("toBasketFunction", "tobasket", "getToBasketFunction", "toBasket function"),
            array("removeFunction", "remove", "getRemoveFunction", "Remove function"),
            array("altproduct", false, "getAltProduct", "Condition if alternate product exists"),
        );
    }

    /**
     * Test case for checking correct parameters return from view
     *
     * @dataProvider _dpViewParameters
     */
    public function testGetViewParameterValue($sKey, $mxValue, $sFunction, $sMessage)
    {
        $oArticleBox = new oxwArticleBox();

        $aViewParams = array($sKey => $mxValue);
        $oArticleBox->setViewParameters($aViewParams);

        $this->assertEquals($mxValue, $oArticleBox->$sFunction(), $sMessage);
    }

    /**
     * Check if category is being properly retrieved when it's set in parent controller
     *
     */
    public function testGetActiveCategory_ParentControllerActiveCategoryIsSet_ReturnCategory()
    {
        $oCategory = new oxCategory();
        $oCategory->load('943a9ba3050e78b443c16e043ae60ef3');

        $oList = new aList();
        $oList->setActiveCategory($oCategory);

        $oConfig = $this->getMock('oxConfig', array('getTopActiveView'));
        $oConfig->expects($this->any())->method('getTopActiveView')->will($this->returnValue($oList));

        $oArticleBox = $this->getMock('oxwArticleBox', array('getConfig'));
        $oArticleBox->expects($this->any())->method('getConfig')->will($this->returnValue($oConfig));

        $this->assertTrue($oArticleBox->getActiveCategory() instanceof oxCategory);
        $this->assertEquals('943a9ba3050e78b443c16e043ae60ef3', $oArticleBox->getActiveCategory()->getId());
        $this->assertEquals('Eco-Fashion', $oArticleBox->getActiveCategory()->getTitle());
    }

    /**
     * Check if category is being properly retrieved when it's not set in parent controller
     *
     */
    public function testGetActiveCategory_ParentControllerActiveCategoryIsNotSet_ReturnNull()
    {
        $oCategory = new oxCategory();

        $oList = new aList();
        $oList->setActiveCategory($oCategory);

        $oConfig = $this->getMock('oxConfig', array('getTopActiveView'));
        $oConfig->expects($this->any())->method('getTopActiveView')->will($this->returnValue($oList));

        $oArticleBox = $this->getMock('oxwArticleBox', array('getConfig'));
        $oArticleBox->expects($this->any())->method('getConfig')->will($this->returnValue($oConfig));

        $this->assertTrue($oArticleBox->getActiveCategory() instanceof oxCategory);
        $this->assertEquals(null, $oArticleBox->getActiveCategory()->getId());
        $this->assertEquals(null, $oArticleBox->getActiveCategory()->getTitle());
    }
}