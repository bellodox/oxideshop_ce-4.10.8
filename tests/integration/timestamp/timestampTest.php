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
 * Integration test testing corect timestamp setting on update and insert in all tables
 * directly from sql query or with object save() call
 */
class Integration_Timestamp_TimestampTest extends OxidTestCase
{

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        $aTables = $this->objectNames();
        foreach ($aTables as $aTable) {
            $this->cleanUpTable($aTable[1]);
        }


        parent::tearDown();
    }

    /**
     * Data provider: object name, object db table, update field
     */
    public function objectNames()
    {
        $aNames = array(
            array('oxActions', 'oxactions', 'oxtitle'),
            array('oxAddress', 'oxaddress', 'oxcompany'),
            array('oxArticle', 'oxarticles', 'oxtitle'),
            array('oxAttribute', 'oxattribute', 'oxtitle'),
            array('oxCategory', 'oxcategories', 'oxtitle'),
            array('oxContent', 'oxcontents', 'oxtitle'),
            array('oxCountry', 'oxcountry', 'oxtitle'),
            array('oxDelivery', 'oxdelivery', 'oxtitle'),
            array('oxDeliverySet', 'oxdeliveryset', 'oxtitle'),
            array('oxDiscount', 'oxdiscount', 'oxtitle'),
            array('oxFile', 'oxfiles', 'oxfilename'),
            array('oxGbEntry', 'oxgbentries', 'oxcontent'),
            array('oxGroups', 'oxgroups', 'oxtitle'),
            array('oxLinks', 'oxlinks', 'oxurl'),
            array('oxManufacturer', 'oxmanufacturers', 'oxtitle'),
            array('oxMediaUrl', 'oxmediaurls', 'oxurl'),
            array('oxNews', 'oxnews', 'oxshortdesc'),
            array('oxOrder', 'oxorder', 'oxdelstreet'),
            array('oxOrderArticle', 'oxorderarticles', 'oxtitle'),
            array('oxOrderFile', 'oxorderfiles', 'oxfilename'),
            array('oxPayment', 'oxpayments', 'oxdesc'),
            array('oxPriceAlarm', 'oxpricealarm', 'oxemail'),
            array('oxRating', 'oxratings', 'oxobjectid'),
            array('oxRecommList', 'oxrecommlists', 'oxtitle'),
            array('oxRemark', 'oxremark', 'oxtext'),
            array('oxReview', 'oxreviews', 'oxtext'),
            array('oxSelectList', 'oxselectlist', 'oxtitle'),
            array('oxShop', 'oxshops', 'oxname'),
            array('oxState', 'oxstates', 'oxtitle'),
            array('oxUser', 'oxuser', 'oxusername'),
            array('oxUserBasketItem', 'oxuserbasketitems', 'oxsellist'),
            array('oxUserBasket', 'oxuserbaskets', 'oxtitle'),
            array('oxUserPayment', 'oxuserpayments', 'oxuserid'),
            array('oxVendor', 'oxvendor', 'oxtitle'),
            array('oxVoucher', 'oxvouchers', 'oxvouchernr'),
            array('oxVoucherSerie', 'oxvoucherseries', 'oxserienr'),
        );


        return $aNames;
    }

    /**
     * oxtimestamp field must have been setted with creation date on direct db insert
     *
     * @dataProvider objectNames
     */
    public function testOnInsertDb($objectName, $tableName)
    {
        $sInsertSql = "INSERT INTO `$tableName` SET `oxid` = '_testId'";
        $sSelectSql = "SELECT `oxtimestamp` FROM `$tableName` WHERE `oxid` = '_testId'";

        $oDb = oxDb::getDb();

        $oDb->Execute($sInsertSql);
        $sTimeStamp = $oDb->getOne($sSelectSql);

        $this->assertTrue($sTimeStamp != '0000-00-00 00:00:00');
    }

    /**
     * oxtimestamp field must have been setted with modification date on direct db update
     *
     * @dataProvider objectNames
     */
    public function testOnUpdateDb($objectName, $tableName, $modifyField)
    {
        $sInsertSql = "INSERT INTO `$tableName` SET `oxid` = '_testId', `oxtimestamp` = '0000-00-00 00:00:00' ";
        $sUpdateSql = "UPDATE `$tableName` SET `$modifyField` = '_testmodified' WHERE `oxid` = '_testId'";
        $sSelectSql = "SELECT `oxtimestamp` FROM `$tableName` WHERE `oxid` = '_testId'";

        $oDb = oxDb::getDb();

        $oDb->Execute($sInsertSql);
        $oDb->Execute($sUpdateSql);

        $sTimeStamp = $oDb->getOne($sSelectSql);

        $this->assertTrue($sTimeStamp != '0000-00-00 00:00:00');
    }

    /**
     * oxtimestamp field must have been setted creation date on object insert
     *
     * @dataProvider objectNames
     */
    public function testOnInsert($objectName, $tableName, $modifyField)
    {
        $attNameMod = $tableName . '__' . $modifyField;

        $oObject = oxNew($objectName);
        $oObject->setId('_testId');
        $oObject->$attNameMod = new oxField('test');
        $oObject->save();

        $oObject = oxNew($objectName);
        $oObject->load('_testId');

        $attName = $tableName . '__oxtimestamp';

        $this->assertTrue($oObject->$attName->value != '0000-00-00 00:00:00');
    }

    /**
     * oxtimestamp field must have been setted modification date on object update
     *
     * @dataProvider objectNames
     */
    public function testOnUpdate($objectName, $tableName, $modifyField)
    {
        $attName = $tableName . '__oxtimestamp';
        $attNameMod = $tableName . '__' . $modifyField;

        $oObject = oxNew($objectName);
        $oObject->setId('_testId');
        $oObject->$attName = new oxField('0000-00-00 00:00:00');
        $oObject->$attNameMod = new oxField('test');
        $oObject->save();

        $oObject = oxNew($objectName);
        $oObject->load('_testId');
        $oObject->$attNameMod = new oxField('testmodyfied');
        $oObject->save();

        $oObject = oxNew($objectName);
        $oObject->load('_testId');

        $this->assertTrue($oObject->$attName->value != '0000-00-00 00:00:00');
    }

    /**
     * Test to check if every DB table has oxtimestamp field
     *
     */
    public function testAllTablesHasOxTimestamp()
    {
        $oDb = oxDb::getDb();
        $sQ = "SHOW FULL tables WHERE Table_Type = 'BASE TABLE'";
        $aTableNames = $oDb->getArray($sQ);
        foreach ($aTableNames as $sKey => $aTable) {
            $sTableName = $aTable[0];
            $sSelectSql = "SHOW COLUMNS FROM `$sTableName` LIKE 'oxtimestamp'";
            $this->assertEquals("OXTIMESTAMP", $oDb->getOne($sSelectSql), "No OXTIMESTAMP field in TABLE: $sTableName");
        }
    }
}
