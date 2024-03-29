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
class Unit_Components_Widgets_oxwArticleDetailsTest extends OxidTestCase
{

    /**
     * Test get active zoom picture.
     *
     * @return null
     */
    public function testGetActZoomPic()
    {
        $oDetails = new oxwArticleDetails();
        $this->assertEquals(1, $oDetails->getActZoomPic());
    }

    /**
     * Test getDefaultSorting when default sorting is not set
     *
     * @return null
     */
    public function testGetDefaultSortingUndefinedSorting()
    {
        $oController = new oxwArticleDetails();

        $oCategory = $this->getMock('oxCategory', array('getDefaultSorting'));
        $oCategory->expects($this->any())->method('getDefaultSorting')->will($this->returnValue(''));
        $oController->setActiveCategory($oCategory);

        $this->assertEquals(null, $oController->getDefaultSorting());
    }

    /**
     * Test getDefaultSorting when default sorting is set
     *
     * @return null
     */
    public function testGetDefaultSortingDefinedSorting()
    {
        $oController = new oxwArticleDetails();

        $oCategory = $this->getMock('oxCategory', array('getDefaultSorting'));
        $oCategory->expects($this->any())->method('getDefaultSorting')->will($this->returnValue('testsort'));
        $oController->setActiveCategory($oCategory);

        $this->assertEquals(array('sortby' => 'testsort', 'sortdir' => "asc"), $oController->getDefaultSorting());
    }

    /**
     * Test getDefaultSorting when sorting mode is undefined
     *
     * @return null
     */
    public function testDefaultSortingWhenSortingModeIsUndefined()
    {
        $oController = new oxwArticleDetails();

        $oCategory = $this->getMock('oxCategory', array('getDefaultSorting', 'getDefaultSortingMode'));
        $oCategory->expects($this->any())->method('getDefaultSorting')->will($this->returnValue('testsort'));
        $oCategory->expects($this->any())->method('getDefaultSortingMode')->will($this->returnValue(null));
        $oController->setActiveCategory($oCategory);

        $this->assertEquals(array('sortby' => 'testsort', 'sortdir' => "asc"), $oController->getDefaultSorting());
    }

    /**
     * Test getDefaultSorting when sorting mode is set to 'asc'
     * This might be a little too much, but it's a case
     *
     * @return null
     */
    public function testDefaultSortingWhenSortingModeIsAsc()
    {
        $oController = new oxwArticleDetails();

        $oCategory = $this->getMock('oxCategory', array('getDefaultSorting', 'getDefaultSortingMode'));
        $oCategory->expects($this->any())->method('getDefaultSorting')->will($this->returnValue('testsort'));
        $oCategory->expects($this->any())->method('getDefaultSortingMode')->will($this->returnValue(false));

        $oController->setActiveCategory($oCategory);

        $this->assertEquals(array('sortby' => 'testsort', 'sortdir' => "asc"), $oController->getDefaultSorting());
    }

    /**
     * Test getDefaultSorting when sorting mode is set to 'desc'
     *
     * @return null
     */
    public function testDefaultSortingWhenSortingModeIsDesc()
    {
        $oController = new oxwArticleDetails();

        $oCategory = $this->getMock('oxCategory', array('getDefaultSorting', 'getDefaultSortingMode'));
        $oCategory->expects($this->any())->method('getDefaultSorting')->will($this->returnValue('testsort'));
        $oCategory->expects($this->any())->method('getDefaultSortingMode')->will($this->returnValue(true));

        $oController->setActiveCategory($oCategory);

        $this->assertEquals(array('sortby' => 'testsort', 'sortdir' => "desc"), $oController->getDefaultSorting());
    }


    /**
     * Test get parent product.
     *
     * @return null
     */
    public function testGetParentProduct()
    {
        $oProduct = $this->getMock("oxarticle", array("isBuyable"));
        $oProduct->expects($this->any())->method('isBuyable')->will($this->returnValue(true));

        $oDetailsView = $this->getMock("oxwArticleDetails", array("getProduct"));
        $oDetailsView->expects($this->any())->method('getProduct')->will($this->returnValue($oProduct));

        $oProduct = $oDetailsView->UNITgetParentProduct('1126');
        $this->assertTrue($oProduct instanceof oxarticle);
        $this->assertEquals('1126', $oProduct->getId());
    }

    /**
     * Test if ratings are activated.
     *
     * @return null
     */
    public function testRatingIsActive()
    {
        $this->setConfigParam('bl_perfLoadReviews', true);
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $this->assertTrue($oDetails->ratingIsActive());
    }

    public function testCanRate()
    {
        $oArt = new oxarticle();
        $oArt->load('2000');

        $oUser = new oxUser();
        $oUser->load('oxdefaultadmin');

        $this->setConfigParam('bl_perfLoadReviews', true);

        $oDetails = $this->getMock('oxwArticleDetails', array('getProduct', 'getUser'));
        $oDetails->expects($this->any())->method('getProduct')->will($this->returnValue($oArt));
        $oDetails->expects($this->any())->method('getUser')->will($this->returnValue($oUser));

        $this->assertTrue($oDetails->canRate());
    }

    public function testCanChangeTags_nouser()
    {
        $oView = $this->getMock('Details', array('getUser'));
        $oView->expects($this->once())->method('getUser');

        $this->assertFalse($oView->canChangeTags());
    }

    public function testCanChangeTags_withuser()
    {
        $oView = $this->getMock('Details', array('getUser'));
        $oView->expects($this->once())->method('getUser')->will($this->returnValue(true));

        $this->assertTrue($oView->canChangeTags());
    }

    /**
     * Test get attributes.
     *
     * @return null
     */
    public function testGetAttributes()
    {
        $oArticle = new oxarticle();
        $oArticle->load('1672');
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_oProduct", $oArticle);
        $sSelect = "select oxattrid from oxobject2attribute where oxobjectid = '$sArtID'";
        $sID = oxDb::getDB()->getOne($sSelect);
        $sSelect = "select oxvalue from oxobject2attribute where oxattrid = '$sID' and oxobjectid = '$sArtID'";
        $sExpectedValue = oxDb::getDB()->getOne($sSelect);
        $aAttrList = $oDetails->getAttributes();
        $sAttribValue = $aAttrList[$sID]->oxobject2attribute__oxvalue->value;
        $this->assertEquals($sExpectedValue, $sAttribValue);
    }

    /**
     * Test get tag cloud after adding new tags.
     *
     * @return null
     */
    public function testGetTagCloudManagerAfterAddTags()
    {
        oxTestModules::addFunction('oxSeoEncoderTag', '_saveToDb', '{return null;}');
        oxTestModules::addFunction("oxutilsserver", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . oxRegistry::getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        oxTestModules::addFunction("oxutils", "seoIsActive", "{return true;}");
        $this->setRequestParam('newTags', "newTag");
        $oArt = new oxarticle();
        $oArt->load('2000');
        $oArt->setId('_testArt');
        $oArt->save();

        $oArticle = new oxArticle();
        $oArticle->load('_testArt');

        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_oProduct", $oArticle);
        $this->assertTrue($oDetails->getTagCloudManager() instanceof oxTagCloud);
    }

    public function testIsEditableTags()
    {
        $oView = $this->getMock($this->getProxyClassName('oxwArticleDetails'), array('getProduct', 'getUser'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue(true));
        $oView->expects($this->once())->method('getUser')->will($this->returnValue(true));

        $this->assertTrue($oView->isEditableTags());
        $this->assertTrue($oView->getNonPublicVar('_blCanEditTags'));
    }

    /**
     * Test get link type.
     *
     * @return null
     */
    public function testGetLinkType()
    {
        $this->setRequestParam('listtype', 'vendor');
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_VENDOR, $oDetailsView->getLinkType());

        $this->setRequestParam('listtype', 'manufacturer');
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_MANUFACTURER, $oDetailsView->getLinkType());

        $this->setRequestParam('listtype', 'tag');
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_TAG, $oDetailsView->getLinkType());

        $this->setRequestParam('listtype', null);
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->once())->method('getActiveCategory')->will($this->returnValue(null));
        $this->assertEquals(OXARTICLE_LINKTYPE_CATEGORY, $oDetailsView->getLinkType());

        $oCategory = $this->getMock("oxcategory", array('isPriceCategory'));
        $oCategory->expects($this->once())->method('isPriceCategory')->will($this->returnValue(true));

        $this->setRequestParam('listtype', "recommlist");
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory')->will($this->returnValue($oCategory));
        $this->assertEquals(OXARTICLE_LINKTYPE_RECOMM, $oDetailsView->getLinkType());

        $this->setRequestParam('listtype', null);
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->once())->method('getActiveCategory')->will($this->returnValue($oCategory));
        $this->assertEquals(OXARTICLE_LINKTYPE_PRICECATEGORY, $oDetailsView->getLinkType());
    }

    /**
     * Test get variant list.
     *
     * @return null
     */
    public function testGetVariantListExceptCurrent()
    {
        $oProd = new oxBase();
        $oProd->setId('asdasd');

        $oKeep1 = new stdClass();
        $oKeep1->asd = 'asd';

        $oKeep2 = new stdClass();
        $oKeep2->asdd = 'asasdd';

        $oList = new oxlist();
        $oList->assign(array('asdasd' => $oKeep1, 'asd' => $oKeep2));

        $oDetails = $this->getMock('oxwArticleDetails', array('getVariantList', 'getProduct'));
        $oDetails->expects($this->once())->method('getVariantList')->will($this->returnValue($oList));
        $oDetails->expects($this->once())->method('getProduct')->will($this->returnValue($oProd));

        $aRet = $oDetails->getVariantListExceptCurrent();

        $this->assertEquals(1, count($aRet));

        $oExpect = new oxlist();
        $oExpect->assign(array('asd' => $oKeep2));
        $this->assertEquals($oExpect->getArray(), $aRet->getArray());

        // do not reload nor clone articles
        $this->assertSame($oKeep2, $aRet['asd']);

        // original unchanged
        $this->assertEquals(2, count($oList));
    }

    /**
     * Test load variant information.
     *
     * @return null
     */
    public function testLoadVariantInformation()
    {
        $this->setConfigParam('blVariantParentBuyable', true);

        $this->getProxyClass('oxarticle');
        $oProductParent = $this->getMock('oxarticlePROXY', array('getSelectLists', 'getId'));
        $oProductParent->expects($this->once())->method('getSelectLists');
        $oProductParent->expects($this->atLeastOnce())->method('getId')->will($this->returnValue('123'));
        $oProductParent->oxarticles__oxvarcount = new oxField(10);

        $oProduct = $this->getMock('oxarticle', array('getParentArticle', 'getVariants', 'getId'));
        $oProduct->expects($this->never())->method('getVariants');
        $oProduct->expects($this->atLeastOnce())->method('getId')->will($this->returnValue('testArtId'));
        $oProduct->oxarticles__oxvarcount = new oxField(10);

        $oVar1 = new oxarticle();
        $oVar1->setId('var1');

        $oVar2 = new oxarticle();
        $oVar2->setId('var2');

        $oVar3 = new oxarticle();
        $oVar3->setId('var3');

        $oVar4 = new oxarticle();
        $oVar4->setId('var4');

        $oVarList = new oxlist();
        $oVarList->offsetSet($oProduct->getId(), $oProduct);
        $oVarList->offsetSet($oVar1->getId(), $oVar1);
        $oVarList->offsetSet($oVar2->getId(), $oVar2);
        $oVarList->offsetSet($oVar3->getId(), $oVar3);
        $oVarList->offsetSet($oVar4->getId(), $oVar4);

        $oProductParent->setNonPublicVar('_aVariantsWithNotOrderables', array("full" => $oVarList));
        $oProductParent->setNonPublicVar('_blNotBuyableParent', true);

        $oProduct->expects($this->any())->method('getParentArticle')->will($this->returnValue($oProductParent));

        $oDetailsView = $this->getMock('oxwArticleDetails', array('getProduct', 'getLinkType'));
        $oDetailsView->expects($this->once())->method('getProduct')->will($this->returnValue($oProduct));
        $oDetailsView->expects($this->exactly(6))->method('getLinkType');
        $oDetailsView->loadVariantInformation();
    }

    /**
     * Test get variant list.
     *
     * @return null
     */
    public function testGetVariantList()
    {
        $this->setRequestParam('anid', '2077');

        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $this->assertEquals(3, $oDetails->getVariantList()->count());
    }

    /**
     * Test getting object of handling CAPTCHA image
     *
     * @return null
     */
    public function testGetCaptcha()
    {
        $oObj = $this->getProxyClass('Contact');
        $this->assertEquals(oxNew('oxCaptcha'), $oObj->getCaptcha());
    }

    /**
     * Test get media files.
     *
     * @return null
     */
    public function testGetMediaFiles()
    {
        $sQ = "insert into oxmediaurls (oxid, oxobjectid, oxurl, oxdesc) values ('_test2', '2000', 'http://www.youtube.com/watch?v=ZN239G6aJZo', 'test2')";
        oxDb::getDb()->execute($sQ);

        $oArt = new oxArticle();
        $oArt->load('2000');
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_oProduct", $oArt);
        $oMediaUrls = $oDetails->getMediaFiles();

        $this->assertEquals(1, count($oMediaUrls));
        $this->assertTrue(isset($oMediaUrls['_test2']));
        $this->assertEquals('test2', $oMediaUrls['_test2']->oxmediaurls__oxdesc->value);
    }

    /**
     * Test get last seen product list.
     *
     * @return null
     */
    public function testGetLastProducts()
    {
        $this->setSessionParam('aHistoryArticles', array('1771'));

        $this->setRequestParam('anid', '1771');
        $oDetails = new oxwArticleDetails();
        $oDetails->init();
        $oDetails->render();
        $oDetails->getLastProducts();

        $this->setRequestParam('anid', '2000');
        $oDetails = new oxwArticleDetails();
        $oDetails->init();

        $this->assertEquals('1771', $oDetails->getLastProducts()->current()->getId());
    }

    /**
     * Test get manufacturer.
     *
     * @return null
     */
    public function testGetManufacturer()
    {
        $sManId = '68342e2955d7401e6.18967838';
        $oArticle = $this->getMock('oxarticle', array('getManufacturerId'));
        $oArticle->expects($this->any())->method('getManufacturerId')->will($this->returnValue(false));

        $oDetails = $this->getMock('oxwArticleDetails', array('getProduct'));
        $oDetails->expects($this->any())->method('getProduct')->will($this->returnValue($oArticle));

        $oExpVendor = new oxvendor();
        $oExpVendor->load($sManId);

        $oVendor = $oDetails->getManufacturer();
        $this->assertEquals($oExpVendor->oxvendors__oxtitle->value, $oVendor->oxvendors__oxtitle->value);
    }

    /**
     * Test get vendor.
     *
     * @return null
     */
    public function testGetVendor()
    {
        $sVendId = '68342e2955d7401e6.18967838';

        $oArticle = $this->getMock('oxarticle', array('getVendorId'));
        $oArticle->expects($this->any())->method('getVendorId')->will($this->returnValue(false));

        $oDetails = $this->getMock('oxwArticleDetails', array('getProduct'));
        $oDetails->expects($this->once())->method('getProduct')->will($this->returnValue($oArticle));

        $oExpVendor = new oxvendor();
        $oExpVendor->load($sVendId);
        $oVendor = $oDetails->getVendor();
        $this->assertEquals($oExpVendor->oxvendors__oxtitle->value, $oVendor->oxvendors__oxtitle->value);
    }

    /**
     * Test get category.
     *
     * @return null
     */
    public function testGetCategory()
    {
        $oArticle = new oxarticle();
        $oArticle->load('1126');
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_oProduct", $oArticle);
        $oCategory = $oDetails->getCategory();

        $sCatId = "8a142c3e49b5a80c1.23676990";

        $this->assertNotNull($oCategory);
        $this->assertEquals($sCatId, $oCategory->getId());
    }

    /**
     * Test get picture gallery.
     *
     * @return null
     */
    public function testGetPictureGallery()
    {
        $sArtID = "096a1b0849d5ffa4dd48cd388902420b";

        $oArticle = new oxarticle();
        $oArticle->load($sArtID);
        $sActPic = oxRegistry::getConfig()->getPictureUrl(null) . "generated/product/1/380_340_75/" . basename($oArticle->oxarticles__oxpic1->value);

        $oDetails = $this->getMock('oxwArticleDetails', array("getPicturesProduct"));
        $oDetails->expects($this->once())->method('getPicturesProduct')->will($this->returnValue($oArticle));
        $aPicGallery = $oDetails->getPictureGallery();

        $this->assertEquals($sActPic, $aPicGallery['ActPic']);
    }

    /**
     * Test get active picture.
     *
     * @return null
     */
    public function testGetActPicture()
    {
        $aPicGallery = array('ActPic' => 'aaa');
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_aPicGallery", $aPicGallery);
        $this->assertEquals('aaa', $oDetails->getActPicture());
    }

    /**
     * Test get more pictures.
     *
     * @return null
     */
    public function testMorePics()
    {
        $aPicGallery = array('MorePics' => true);
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_aPicGallery", $aPicGallery);
        $this->assertTrue($oDetails->morePics());
    }

    /**
     * Test get icons.
     *
     * @return null
     */
    public function testGetIcons()
    {
        $aPicGallery = array('Icons' => 'aaa');
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_aPicGallery", $aPicGallery);
        $this->assertEquals('aaa', $oDetails->getIcons());
    }

    /**
     * Test show zoom pictures.
     *
     * @return null
     */
    public function testShowZoomPics()
    {
        $aPicGallery = array('ZoomPic' => true);
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_aPicGallery", $aPicGallery);
        $this->assertTrue($oDetails->showZoomPics());
    }

    /**
     * Test get zoom pictures.
     *
     * @return null
     */
    public function testGetZoomPics()
    {
        $aPicGallery = array('ZoomPics' => 'aaa');
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_aPicGallery", $aPicGallery);
        $this->assertEquals('aaa', $oDetails->getZoomPics());
    }

    /**
     * Test get reviews.
     *
     * @return null
     */
    public function testGetReviews()
    {
        $oArticle = $this->getMock('oxarticle', array('getReviews'));
        $oArticle->expects($this->any())->method('getReviews')->will($this->returnValue("aaa"));

        $oDetails = $this->getMock('oxwArticleDetails', array('getProduct'));
        $oDetails->expects($this->once())->method('getProduct')->will($this->returnValue($oArticle));

        $this->assertEquals('aaa', $oDetails->getReviews());
    }

    /**
     * Test get crossselling.
     *
     * @return null
     */
    public function testGetCrossSelling()
    {
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oArticle = oxNew("oxarticle");
        $oArticle->load("1849");
        $oDetails->setNonPublicVar("_oProduct", $oArticle);
        $oList = $oDetails->getCrossSelling();
        $this->assertTrue($oList instanceof oxarticlelist);

        $iCount = 3;
        $iCount = 2;
        $this->assertEquals($iCount, $oList->count());
    }

    /**
     * Testing Account_Noticelist::getSimilarProducts()
     *
     * @return null
     */
    public function testGetSimilarProductsEmptyProductList()
    {
        $oView = $this->getMock("Account_Noticelist", array("getNoticeProductList"));
        $oView->expects($this->any())->method('getNoticeProductList')->will($this->returnValue(array()));
        $this->assertNull($oView->getSimilarProducts());
    }

    /**
     * Testing Account_Noticelist::getSimilarProducts()
     *
     * @return null
     */
    public function testGetSimilarProducts()
    {
        $oProduct = $this->getMock("oxArticleList", array("getSimilarProducts"));
        $oProduct->expects($this->any())->method('getSimilarProducts')->will($this->returnValue("testSimilarProducts"));

        $oView = $this->getMock("Account_Noticelist", array("getNoticeProductList"));
        $oView->expects($this->any())->method('getNoticeProductList')->will($this->returnValue(array($oProduct)));
        $this->assertEquals("testSimilarProducts", $oView->getSimilarProducts());
    }

    /**
     * Test get ids for similar recomendation list.
     *
     * @return null
     */

    public function testGetSimilarRecommListIds()
    {
        $articleId = "articleId";
        $aArrayKeys = array($articleId);
        $oProduct = $this->getMock("oxarticle", array("getId"));
        $oProduct->expects($this->once())->method("getId")->will($this->returnValue($articleId));

        $oDetails = $this->getMock("oxwArticleDetails", array("getProduct"));
        $oDetails->expects($this->once())->method("getProduct")->will($this->returnValue($oProduct));
        $this->assertEquals($aArrayKeys, $oDetails->getSimilarRecommListIds(), "getSimilarRecommListIds() should return array of key from result of getProduct()");
    }

    /**
     * Test get accessories.
     *
     * @return null
     */
    public function testGetAccessoires()
    {
        $oArticle = $this->getMock('oxarticle', array('getAccessoires'));
        $oArticle->expects($this->any())->method('getAccessoires')->will($this->returnValue("aaa"));

        $oDetails = $this->getMock('oxwArticleDetails', array('getProduct'));
        $oDetails->expects($this->once())->method('getProduct')->will($this->returnValue($oArticle));

        $this->assertEquals("aaa", $oDetails->getAccessoires());
    }

    /**
     * Test get also bought these products.
     *
     * @return null
     */
    public function testGetAlsoBoughtTheseProducts()
    {
        $oArticle = $this->getMock('oxarticle', array('getCustomerAlsoBoughtThisProducts'));
        $oArticle->expects($this->any())->method('getCustomerAlsoBoughtThisProducts')->will($this->returnValue("aaa"));

        $oDetails = $this->getMock('oxwArticleDetails', array('getProduct'));
        $oDetails->expects($this->once())->method('getProduct')->will($this->returnValue($oArticle));

        $this->assertEquals("aaa", $oDetails->getAlsoBoughtTheseProducts());
    }

    /**
     * Test is product added to price allarm.
     *
     * @return null
     */
    public function testIsPriceAlarm()
    {
        $oArticle = new oxArticle();
        $oArticle->oxarticles__oxblfixedprice = new oxField(1, oxField::T_RAW);

        $oView = $this->getMock('oxwArticleDetails', array('getProduct'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue($oArticle));

        $this->assertEquals(0, $oView->isPriceAlarm());
    }

    /**
     * Test is product added to price allarm - true test.
     *
     * @return null
     */
    public function testIsPriceAlarm_true()
    {
        $oArticle = new oxArticle();
        $oArticle->oxarticles__oxblfixedprice = new oxField(0, oxField::T_RAW);

        $oView = $this->getMock('oxwArticleDetails', array('getProduct'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue($oArticle));

        $this->assertEquals(1, $oView->isPriceAlarm());
    }

    /**
     * Test search title setter/getter.
     *
     * @return null
     */
    public function testSetGetSearchTitle()
    {
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setSearchTitle("tetsTitle");

        $this->assertEquals("tetsTitle", $oDetails->getSearchTitle());
    }

    /**
     * Test category path setter/getter.
     *
     * @return null
     */
    public function testSetGetCatTreePath()
    {
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setCatTreePath("tetsPath");

        $this->assertEquals("tetsPath", $oDetails->getCatTreePath());
    }

    /**
     * Test is persistent parameter.
     *
     * @return null
     */
    public function testIsPersParam()
    {
        $oArt = new oxArticle();
        $oArt->oxarticles__oxisconfigurable = new oxField(true);
        $oSubj = $this->getProxyClass("oxwArticleDetails");
        $oSubj->setNonPublicVar("_oProduct", $oArt);
        $oSubj->setNonPublicVar("_blIsInitialized", true);
        $this->assertTrue($oSubj->isPersParam());
    }

    /**
     * Test is persistent parameter navigative.
     *
     * @return null
     */
    public function testIsPersParamNegative()
    {
        $oArt = new oxArticle();
        $oArt->oxarticles__oxisconfigurable = new oxField(false);
        $oSubj = $this->getProxyClass("oxwArticleDetails");
        $oSubj->setNonPublicVar("_oProduct", $oArt);
        $oSubj->setNonPublicVar("_blIsInitialized", true);
        $this->assertFalse($oSubj->isPersParam());
    }

    public function testGetRatingValue_active()
    {
        $oConfig = $this->getMock('oxConfig', array('getConfigParam'));
        $oConfig->expects($this->once())->method('getConfigParam')->with($this->equalTo('blShowVariantReviews'))->will($this->returnValue(true));

        $oProduct = $this->getMock('oxArticle', array('getArticleRatingAverage'));
        $oProduct->expects($this->once())->method('getArticleRatingAverage')->will($this->returnValue(123.855));

        $oView = $this->getMock($this->getProxyClassName('oxwArticleDetails'), array('getConfig', 'isReviewActive', 'getProduct'));
        $oView->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));
        $oView->expects($this->once())->method('isReviewActive')->will($this->returnValue(true));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue($oProduct));

        $this->assertSame(123.9, $oView->getRatingValue());
        $this->assertSame(123.9, $oView->getNonPublicVar('_dRatingValue'));
    }

    public function testGetRatingValue_inactive()
    {
        $oView = $this->getMock($this->getProxyClassName('oxwArticleDetails'), array('getConfig', 'isReviewActive', 'getProduct'));
        $oView->expects($this->never())->method('getConfig');
        $oView->expects($this->once())->method('isReviewActive')->will($this->returnValue(false));
        $oView->expects($this->never())->method('getProduct');

        $this->assertSame(0.0, $oView->getRatingValue());
        $this->assertSame(0.0, $oView->getNonPublicVar('_dRatingValue'));
    }

    public function testIsReviewActive()
    {
        $oConfig = $this->getMock('oxConfig', array('getConfigParam'));
        $oConfig->expects($this->once())->method('getConfigParam')->with($this->equalTo('bl_perfLoadReviews'))->will($this->returnValue('test_isactive'));

        $oView = $this->getMock('oxwArticleDetails', array('getConfig'));
        $oView->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));

        $this->assertSame('test_isactive', $oView->isReviewActive());
    }

    public function testGetRatingCount_active()
    {
        $oConfig = $this->getMock('oxConfig', array('getConfigParam'));
        $oConfig->expects($this->once())->method('getConfigParam')->with($this->equalTo('blShowVariantReviews'))->will($this->returnValue(true));

        $oProduct = $this->getMock('oxArticle', array('getArticleRatingCount'));
        $oProduct->expects($this->once())->method('getArticleRatingCount')->will($this->returnValue(123));

        $oView = $this->getMock($this->getProxyClassName('oxwArticleDetails'), array('getConfig', 'isReviewActive', 'getProduct'));
        $oView->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));
        $oView->expects($this->once())->method('isReviewActive')->will($this->returnValue(true));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue($oProduct));

        $this->assertSame(123, $oView->getRatingCount());
        $this->assertSame(123, $oView->getNonPublicVar('_iRatingCnt'));
    }

    public function testGetRatingCount_inactive()
    {
        $oView = $this->getMock($this->getProxyClassName('oxwArticleDetails'), array('getConfig', 'isReviewActive', 'getProduct'));
        //$oView->expects( $this->never() )->method( 'getConfig' );
        $oView->expects($this->once())->method('isReviewActive')->will($this->returnValue(false));
        $oView->expects($this->never())->method('getProduct');

        $this->assertSame(false, $oView->getRatingCount());
        $this->assertSame(false, $oView->getNonPublicVar('_iRatingCnt'));
    }

    public function testGetPriceAlarmStatus()
    {
        $this->setRequestParam('iPriceAlarmStatus', 514);
        $oDetails = new oxwArticleDetails();

        $this->assertSame(514, $oDetails->getPriceAlarmStatus());
    }

    public function testGetBidPrice()
    {
        $aParams = array();
        $aParams['price'] = '123.45';
        $this->setRequestParam('pa', $aParams);

        $oView = $this->getProxyClass('oxwArticleDetails');

        $this->assertSame('123,45', $oView->getBidPrice());
        $this->assertSame('123,45', $oView->getNonPublicVar('_sBidPrice'));
    }

    /**
     * details::getVariantSelections() test case
     *
     * @return null
     */
    public function testGetVariantSelections()
    {
        $oProduct = $this->getMock("oxarticle", array("getVariantSelections"));
        $oProduct->expects($this->once())->method("getVariantSelections")->will($this->returnValue("varselections"));
        //$oProduct->expects( $this->never() )->method( "getId" );

        // no parent
        $oView = $this->getMock("oxwArticleDetails", array("getProduct", "_getParentProduct"));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue($oProduct));
        $oView->expects($this->once())->method('_getParentProduct')->will($this->returnValue(false));

        $this->assertEquals("varselections", $oView->getVariantSelections());

        $oProduct = $this->getMock("oxarticle", array("getVariantSelections"));
        $oProduct->expects($this->never())->method('getVariantSelections')->will($this->returnValue("varselections"));
        //$oProduct->expects( $this->once() )->method( 'getId');

        $oParent = $this->getMock("oxarticle", array("getVariantSelections"));
        $oParent->expects($this->once())->method('getVariantSelections')->will($this->returnValue("parentselections"));

        // has parent
        $oView = $this->getMock("oxwArticleDetails", array("getProduct", "_getParentProduct"));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue($oProduct));
        $oView->expects($this->once())->method('_getParentProduct')->will($this->returnValue($oParent));

        $this->assertEquals("parentselections", $oView->getVariantSelections());
    }

    /**
     * details::getPicturesProduct() test case
     *
     * @return null
     */
    public function testGetPicturesProductNoVariantInfo()
    {
        $oProduct = $this->getMock("stdclass", array("getId"));
        $oProduct->expects($this->never())->method('getId');

        // no picture product id
        $oView = $this->getMock("oxwArticleDetails", array("getProduct", 'getVariantSelections'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue($oProduct));
        $oView->expects($this->once())->method('getVariantSelections')->will($this->returnValue(false));
        $this->assertSame($oProduct, $oView->getPicturesProduct());
    }

    public function testGetPicturesProductWithNoPerfectFitVariant()
    {
        $oProduct = $this->getMock("stdclass", array("getId"));
        $oProduct->expects($this->never())->method('getId');

        $aInfo = array(
            'oActiveVariant' => $oProduct,
            'blPerfectFit'   => false
        );
        // no picture product id
        $oView = $this->getMock("oxwArticleDetails", array("getProduct", 'getVariantSelections'));
        $oView->expects($this->never())->method('getProduct');
        $oView->expects($this->once())->method('getVariantSelections')->will($this->returnValue($aInfo));
        $this->assertSame($oProduct, $oView->getPicturesProduct());
    }

    public function testGetPicturesProductWithPerfectFitVariant()
    {
        $oProduct = $this->getMock("stdclass", array("getId"));
        $oProduct->expects($this->never())->method('getId');

        $aInfo = array(
            'oActiveVariant' => $oProduct,
            'blPerfectFit'   => true
        );
        // no picture product id
        $oView = $this->getMock("oxwArticleDetails", array("getProduct", 'getVariantSelections'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue('prod'));
        $oView->expects($this->once())->method('getVariantSelections')->will($this->returnValue($aInfo));
        $this->assertEquals('prod', $oView->getPicturesProduct());
    }

    /**
     * Test get invisible product.
     *
     * @return null
     */
    public function testGetProductInvisibleProduct()
    {
        $oProduct = $this->getMock('oxarticle', array('isVisible'));
        $oProduct->expects($this->once())->method('isVisible')->will($this->returnValue(false));

        $this->setRequestParam('anid', 'notexistingproductid');
        oxTestModules::addFunction("oxUtils", "redirect", "{ throw new Exception( \$aA[0] ); }");

        try {
            $oDetailsView = $this->getProxyClass('oxwArticleDetails');
            $oDetailsView->setNonPublicVar('_oProduct', $oProduct);
            $oDetailsView->getProduct();
        } catch (Exception $oExcp) {
            $this->assertEquals(oxRegistry::getConfig()->getShopHomeURL(), $oExcp->getMessage(), 'result does not match');

            return;
        }
        $this->fail('product should not be returned');
    }

    /**
     * Test is multidimensionall variants enabled.
     *
     * @return null
     */
    public function testIsMdVariantView()
    {
        $this->setConfigParam('blUseMultidimensionVariants', true);
        $oMdVariant = $this->getMock('oxMdVariant', array('getMaxDepth'));
        $oMdVariant->expects($this->any())->method('getMaxDepth')->will($this->returnValue(2));
        $oProduct = $this->getMock('oxarticle', array('getMdVariants'));
        $oProduct->expects($this->any())->method('getMdVariants')->will($this->returnValue($oMdVariant));
        $oDetails = $this->getMock('oxwArticleDetails', array('getProduct'));
        $oDetails->expects($this->any())->method('getProduct')->will($this->returnValue($oProduct));

        $this->assertTrue($oDetails->isMdVariantView());
    }

    /**
     * Test is multidimensionall variants disabled.
     *
     * @return null
     */
    public function testIsMdVariantViewNotActive()
    {
        $this->setConfigParam('blUseMultidimensionVariants', false);
        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $this->assertFalse($oDetails->isMdVariantView());
    }

    public function testGetTagSeparator()
    {
        $oConfig = $this->getMock('oxConfig', array('getConfigParam'));
        $oConfig->expects($this->once())->method('getConfigParam')->with($this->equalTo('sTagSeparator'))->will($this->returnValue('test_separator'));

        $oView = $this->getMock('oxwArticleDetails', array('getConfig'));
        $oView->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));

        $this->assertSame('test_separator', $oView->getTagSeparator());
    }

    /**
     * Test getDefaultSorting when sorting mode is set to 'desc'
     *
     * @return null
     */
    public function testDefaultSorting_SortingDefinedCameFromSearch_doNotSort()
    {
        $this->getConfig()->setRequestParameter('listtype', 'search');
        $oController = new oxwArticleDetails();

        $oCategory = $this->getMock('oxCategory', array('getDefaultSorting', 'getDefaultSortingMode'));
        $oCategory->expects($this->any())->method('getDefaultSorting')->will($this->returnValue('testsort'));
        $oCategory->expects($this->any())->method('getDefaultSortingMode')->will($this->returnValue(true));

        $oController->setActiveCategory($oCategory);

        $this->assertNull($oController->getDefaultSorting());
    }

}