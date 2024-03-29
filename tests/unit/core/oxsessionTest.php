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

class fake_basket extends oxBasket
{

    public function iAmFake()
    {
        return true;
    }
}

class Unit_oxsessionTest_oxUtilsServer extends oxUtilsServer
{

    /**
     * $_COOKIE alternative for testing
     *
     * @var array
     */
    protected $_aCookieVars = array();

    public function setOxCookie($sVar, $sVal = "", $iExpire = 0, $sPath = '/', $sDomain = null, $blToSession = true, $blSecure = false)
    {
        //unsetting cookie
        if (!isset($sVar) && !isset($sVal)) {
            $this->_aCookieVars = null;

            return;
        }

        $this->_aCookieVars[$sVar] = $sVal;
    }

    public function getOxCookie($sVar = null)
    {
        if (!$sVar) {
            return $this->_aCookieVars;
        }

        if ($this->_aCookieVars[$sVar]) {
            return $this->_aCookieVars[$sVar];
        }

        return null;
    }

    public function delOxCookie()
    {
        $this->_aCookieVars = null;
    }
}

class Unit_oxsessionTest_oxUtilsObject extends oxUtilsObject
{

    /**
     * Overriding original oxUtilsObject::generateUID()
     *
     * @return string
     */
    public function generateUid()
    {
        return "testsession";
    }
}

/**
 * oxSession child for testing
 */
class testSession extends oxSession
{

    /**
     * Keeps test session vars
     *
     * @var array
     */
    static protected $_aSessionVars = array();

    /**
     * Set session var for testing
     *
     * @param string $sVar
     * @param string $sVal
     */
    static public function setVar($sVar, $sVal)
    {
        self::$_aSessionVars[$sVar] = $sVal;
        //parent::setVar($sVar, $sVal);
    }

    /**
     * Gets session var for testing
     *
     * @param string $sVar
     *
     * @return string
     */
    static public function getVar($sVar)
    {
        if (isset(self::$_aSessionVars[$sVar])) {
            return self::$_aSessionVars[$sVar];
        }

        return oxRegistry::getSession()->getVariable($sVar);
    }

    /**
     * Deletes test var $sVar
     *
     * @param string $sVar
     */
    static public function deleteVar($sVar)
    {
        unset(self::$_aSessionVars[$sVar]);
    }

    /**
     * Checks protected $this->_blNewSession var
     *
     * @return bool
     */
    public function isNewSession()
    {
        return $this->_blNewSession;
    }

    /**
     * Initialize session data (calls php::session_start())
     *
     * @return null
     */
    protected function _sessionStart()
    {
        //return @session_start();
    }

    /**
     * Ends the current session and store session data.
     *
     * @return null
     */
    public function freeze()
    {
    }
}


/**
 * Testing oxsession class
 */
class Unit_Core_oxsessionTest extends OxidTestCase
{

    /**
     * Set session save path value if session.save_path value in php.ini is empty
     */
    public $sDefaultSessSavePath = '';

    /**
     * Internal oxSession instance
     *
     */
    public $oSession = null;

    /**
     * Original oxConfig instance
     *
     * @var object
     */
    protected $_oOriginalConfig = null;

    /**
     * Initialize the fixture.
     *
     * @return null
     */
    protected function setUp()
    {
        parent::setUp();
        //creating new instance
        $this->oSession = new testSession();
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        $oDB = oxDb::getDb();
        $sDelete = "DROP TABLE IF EXISTS oxsessions";
        $oDB->Execute($sDelete);

        //removing oxUtils module
        oxRemClassModule('testUtils');
        oxRemClassModule('Unit_oxsessionTest_oxUtilsServer');
        oxRemClassModule('Unit_oxsessionTest_oxUtilsObject');

        $this->oSession->freeze();

        parent::tearDown();
    }

    /**
     * Test case for oxSession::regenerateSessionId()
     *
     * @return null
     */
    public function testRegenerateSessionId()
    {
        $myConfig = oxRegistry::getConfig();

        $oSession = $this->getMock('testSession', array("_getNewSessionId"));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));

        $oSession->setVar('someVar1', true);
        $oSession->setVar('someVar2', 15);
        $oSession->setVar('actshop', 5);
        $oSession->setVar('lang', 3);
        $oSession->setVar('currency', 3);
        $oSession->setVar('language', 12);
        $oSession->setVar('tpllanguage', 12);

        $sOldSid = $oSession->getId();

        $oSession->regenerateSessionId();

        //most sense is to perform this check
        //if session id was changed
        $this->assertNotEquals($sOldSid, $oSession->getId());

        //checking if new id is correct (md5($newid))
        $this->assertEquals("newSessionId", $oSession->getId());

        $this->assertEquals($oSession->getVar('someVar1'), true);
        $this->assertEquals($oSession->getVar('someVar2'), 15);
        $this->assertEquals($oSession->getVar('actshop'), 5);
        $this->assertEquals($oSession->getVar('lang'), 3);
        $this->assertEquals($oSession->getVar('currency'), 3);
        $this->assertEquals($oSession->getVar('language'), 12);
        $this->assertEquals($oSession->getVar('tpllanguage'), 12);

        $oSession->setVar('someVar1', null);
        $oSession->setVar('someVar2', null);
        $oSession->setVar('actshop', null);
        $oSession->setVar('lang', null);
        $oSession->setVar('currency', null);
        $oSession->setVar('language', $myConfig->sDefaultLang);
        $oSession->setVar('tpllanguage', null);
    }

    /**
     * Test for oxSession::start()
     *
     * @return null
     */
    public function testStartWhenDebugisOnAndErrorMessageExpected()
    {
        modConfig::getInstance()->setConfigParam('iDebug', 1);
        modConfig::setRequestParameter("sid", "testSid");

        modSession::getInstance()->setVar('sessionagent', 'oldone');

        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "isTrustedClientIp", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "getServerVar", "{return 'none';}");

        $oSession = $this->getMock("oxSession", array("_allowSessionStart", "initNewSession", "_setSessionId", "_sessionStart"));
        $oSession->expects($this->any())->method('_allowSessionStart')->will($this->returnValue(true));
        $oSession->expects($this->any())->method('initNewSession');
        $oSession->expects($this->any())->method('_setSessionId');
        $oSession->expects($this->any())->method('_sessionStart');
        $oSession->start();

        $aErrors = $this->getSession()->getVariable('Errors');

        $this->assertTrue(is_array($aErrors));
        $this->assertEquals(1, count($aErrors));

        $oExcp = unserialize(current($aErrors['default']));
        $this->assertNotNull($oExcp);
        $this->assertTrue($oExcp instanceof oxExceptionToDisplay);
        $this->assertEquals("Different browser (oldone, none), creating new SID...<br>", $oExcp->getOxMessage());
    }

    public function testIsSidNeededPassingCustomUrl()
    {
        $sUrl = "someurl";

        $oSession = $this->getMock("oxSession", array("getConfig", '_getSessionUseCookies', 'isSessionStarted'));
        $oSession->expects($this->once())->method('_getSessionUseCookies')->will($this->returnValue(false));
        $oSession->expects($this->any())->method('isSessionStarted')->will($this->returnValue(true));
        $this->assertTrue($oSession->isSidNeeded($sUrl));
    }

    public function testIsSidNeededPassingCustomUrlChangeSsl()
    {
        $sUrl = "https://someurl";

        $oConfig = $this->getMock("oxconfig", array("isCurrentProtocol"));
        $oConfig->expects($this->once())->method('isCurrentProtocol')->will($this->returnValue(false));

        $oSession = $this->getMock("oxSession", array("getConfig", '_getSessionUseCookies', '_getCookieSid', 'isSessionStarted'));
        $oSession->expects($this->once())->method('_getSessionUseCookies')->will($this->returnValue(true));
        $oSession->expects($this->once())->method('_getCookieSid')->will($this->returnValue(true));
        $oSession->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));
        $oSession->expects($this->any())->method('isSessionStarted')->will($this->returnValue(true));
        $this->assertTrue($oSession->isSidNeeded($sUrl));
    }

    public function testAllowSessionStartWhenSearchEngine()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return true;}");
        modConfig::setRequestParameter('skipSession', 0);

        $oSession = new oxSession();
        $this->assertFalse($oSession->UNITallowSessionStart());
    }

    public function testAllowSessionStartIsAdmin()
    {
        $oSession = $this->getMock('oxSession', array('isAdmin'));
        $oSession->expects($this->atLeastOnce())->method('isAdmin')->will($this->returnValue(true));
        $this->assertTrue($oSession->UNITallowSessionStart());
    }

    public function testAllowSessionStartWhenSkipSessionParam()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        modConfig::setRequestParameter('skipSession', 1);

        $oSession = new oxSession();
        $this->assertFalse($oSession->UNITallowSessionStart());
    }

    public function testAllowSessionStartSessionRequiredAction()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        modConfig::setRequestParameter('skipSession', 0);

        $oSession = $this->getMock('oxSession', array('_isSessionRequiredAction'));
        $oSession->expects($this->atLeastOnce())->method('_isSessionRequiredAction')->will($this->returnValue(true));
        $this->assertTrue($oSession->UNITallowSessionStart());
    }

    public function testAllowSessionStartCookieIsFoundMustStart()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return true;}");
        modConfig::setRequestParameter('skipSession', 0);

        $oSession = $this->getMock('oxSession', array('_isSessionRequiredAction'));
        $oSession->expects($this->any())->method('_isSessionRequiredAction')->will($this->returnValue(false));
        $this->assertTrue($oSession->UNITallowSessionStart());
    }

    public function testAllowSessionStartRequestContainsSidParameter()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return true;}");
        modConfig::setRequestParameter('skipSession', 0);
        modConfig::setRequestParameter('sid', 'xxx');

        $oSession = $this->getMock('oxSession', array('_isSessionRequiredAction'));
        $oSession->expects($this->any())->method('_isSessionRequiredAction')->will($this->returnValue(false));
        $this->assertTrue($oSession->UNITallowSessionStart());
    }

    public function testProcessUrlSidIsNotNeeded()
    {
        $oSession = $this->getMock('oxsession', array('isSidNeeded'));
        $oSession->expects($this->once())->method('isSidNeeded')->will($this->returnValue(false));
        $this->assertEquals('sameurl', $oSession->processUrl('sameurl'));
    }

    public function testProcessUrlSidNeeded()
    {
        $oSession = $this->getMock('oxsession', array('isSidNeeded', 'sid'));
        $oSession->expects($this->once())->method('isSidNeeded')->will($this->returnValue(true));
        $oSession->expects($this->once())->method('sid')->will($this->returnValue('sid=xxx'));
        $this->assertEquals('sameurl?sid=xxx&amp;', $oSession->processUrl('sameurl'));
    }

    public function testProcessUrlSidNeededButNotEgzisting()
    {
        $oSession = $this->getMock('oxsession', array('isSidNeeded', 'sid'));
        $oSession->expects($this->once())->method('isSidNeeded')->will($this->returnValue(true));
        $oSession->expects($this->once())->method('sid')->will($this->returnValue(''));
        $this->assertEquals('sameurl', $oSession->processUrl('sameurl'));
    }

    public function testIsSidNeededForceSessionStart()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return false;}");

        $oSession = $this->getMock('oxsession', array('_forceSessionStart', 'isSessionStarted'));
        $oSession->expects($this->once())->method('_forceSessionStart')->will($this->returnValue(true));
        $oSession->expects($this->any())->method('isSessionStarted')->will($this->returnValue(true));
        $this->assertTrue($oSession->isSidNeeded());
    }

    public function testForceSessionStart_notSearchEngine()
    {
        $oSession = $this->getProxyClass('oxSession');

        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");

        modConfig::getInstance()->setConfigParam('blForceSessionStart', false);
        $this->assertFalse($oSession->UNITforceSessionStart());

        modConfig::getInstance()->setConfigParam('blForceSessionStart', true);
        $this->assertTrue($oSession->UNITforceSessionStart());

        modConfig::getInstance()->setConfigParam('blForceSessionStart', false);
        modConfig::setRequestParameter('su', '123456');
        $this->assertTrue($oSession->UNITforceSessionStart());

        modConfig::getInstance()->setConfigParam('blForceSessionStart', false);
        modConfig::setRequestParameter('su', '');
        $oSession->setNonPublicVar("_blForceNewSession", true);
        $this->assertTrue($oSession->UNITforceSessionStart());
    }

    public function testForceSessionStart_isSearchEngine()
    {
        $oSession = $this->getProxyClass('oxSession');

        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return true;}");
        modConfig::getInstance()->setConfigParam('blForceSessionStart', true);
        modConfig::setRequestParameter('su', '123456');
        $oSession->setNonPublicVar("_blForceNewSession", true);

        $this->assertFalse($oSession->UNITforceSessionStart());
    }

    public function testIsSidNeededWhenSearchEngine()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return true;}");

        $oSession = new oxsession();
        $this->assertFalse($oSession->isSidNeeded());
    }

    public function testIsSidNeededCookieFound()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return true;}");

        $oSession = new oxsession();
        $this->assertFalse($oSession->isSidNeeded());
    }

    public function testIsSidNeededWithSessionMarker()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return false;}");

        modSession::getInstance()->setVar('blSidNeeded', true);

        $oSession = $this->getMock('oxSession', array('isSessionStarted'));
        $oSession->expects($this->any())->method('isSessionStarted')->will($this->returnValue(true));
        $this->assertTrue($oSession->isSidNeeded());
    }

    public function testIsSidNeededSpecialAction()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return false;}");

        modSession::getInstance()->setVar('blSidNeeded', false);
        modConfig::setRequestParameter('fnc', 'tobasket');

        $oSession = $this->getMock('oxSession', array('isSessionStarted'));
        $oSession->expects($this->any())->method('isSessionStarted')->will($this->returnValue(true));
        $this->assertTrue($oSession->isSidNeeded());
        $this->assertTrue($oSession->getVariable('blSidNeeded'));
    }

    public function testIsSidNeededRegularPageViewNoSessionNeeded()
    {
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return false;}");

        modSession::getInstance()->setVar('blSidNeeded', false);

        $oSession = new oxsession();
        $this->assertFalse($oSession->isSidNeeded());
    }

    public function testIsSessionRequiredActionNoSpecialAction()
    {
        modConfig::setRequestParameter('fnc', 'nothingspecial');

        $oSession = new oxsession();
        $this->assertFalse($oSession->UNITisSessionRequiredAction());
    }

    public function testIsSessionRequiredActionRequired()
    {
        modConfig::setRequestParameter('fnc', 'tobasket');

        $oSession = new oxsession();
        $this->assertTrue($oSession->UNITisSessionRequiredAction());
    }

    /**
     * oxSession::start() test for admin login
     *
     */
    public function testStartAdmin()
    {
        $oSession = $this->getMock('testSession', array('isAdmin', "_getNewSessionId"));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(true));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));
        $this->assertNull($oSession->getId());
        $this->assertEquals($oSession->getName(), 'sid');
        $oSession->start();
        $this->assertNotNull($oSession->getId());
        $this->assertEquals($oSession->getName(), 'admin_sid');
        //oxRegistry::getConfig()->blAdmin = false;
    }

    /**
     * oxSession::start() test for non admin login
     *
     */
    public function testStartNonAdmin()
    {
        $oSession = $this->getMock('testSession', array('isAdmin', '_allowSessionStart', "_getNewSessionId"));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        $oSession->expects($this->any())->method('_allowSessionStart')->will($this->returnValue(true));
        $this->assertNull($oSession->getId());
        $this->assertEquals($oSession->getName(), 'sid');
        $oSession->start();
        $this->assertNotNull($oSession->getId());
        $this->assertEquals($oSession->getName(), 'sid');
    }

    /**
     * oxSession::start() test for non admin login
     *
     */
    public function testStartDoesNotGenerateSidIfNotNeeded()
    {
        $this->oSession = $this->getMock('testSession', array('_allowSessionStart'));
        $this->oSession->expects($this->any())->method('_allowSessionStart')->will($this->returnValue(false));
        $this->assertNull($this->oSession->getId());
        $this->assertEquals($this->oSession->getName(), 'sid');
        $this->oSession->start();
        $this->assertNull($this->oSession->getId());
        $this->assertEquals($this->oSession->getName(), 'sid');
    }

    /**
     * oxSession::start() test forcing sid
     *
     */
    public function testStartSetsSidPriority()
    {
        oxAddClassModule('Unit_oxsessionTest_oxUtilsServer', 'oxUtilsServer');
        $this->oSession = $this->getMock('testSession', array('isAdmin'));
        $this->oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        //set parameter
        modConfig::setRequestParameter('sid', 'testSid1');
        $this->oSession->start();
        $this->assertEquals($this->oSession->getId(), 'testSid1');

        //set cookie
        oxRegistry::get("oxUtilsServer")->setOxCookie('sid', 'testSid2');
        oxRegistry::getConfig()->setConfigParam('blSessionUseCookies', true);
        $this->oSession->start();
        $this->assertEquals($this->oSession->getId(), 'testSid2');

        //forcing sid (ususally for SSL<->nonSSL transitions)
        modConfig::setRequestParameter('force_sid', 'testSid3');
        $this->oSession->start();
        $this->assertEquals($this->oSession->getId(), 'testSid3');

        //reset params
        modConfig::setRequestParameter('sid', null);
        oxRegistry::get("oxUtilsServer")->setOxCookie('sid', null);
        $this->oSession->setVar('force_sid', null);
    }

    /**
     * oxSession::start() test forcing sid
     *
     */
    public function testStartSetsNewSid()
    {
        oxAddClassModule('Unit_oxsessionTest_oxUtilsServer', 'oxUtilsServer');
        $this->oSession = $this->getMock('testSession', array('isAdmin', 'initNewSession'));
        $this->oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(true));
        $this->oSession->expects($this->any())->method('initNewSession');
        $this->oSession->setId('xxxx');
        $this->assertEquals('xxxx', $this->oSession->getId());
        $this->oSession->start();
        $this->assertNotEquals('xxxx', $this->oSession->getId());
    }

    /**
     * oxSession::start() cookies not available
     *
     */
    public function testStartCookiesNotAvailable()
    {
        oxAddClassModule('Unit_oxsessionTest_oxUtilsServer', 'oxUtilsServer');
        $oSession = $this->getMock('testSession', array('isAdmin', '_getCookieSid', '_isSwappedClient', '_allowSessionStart', "_getNewSessionId"));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        $oSession->expects($this->any())->method('_getCookieSid')->will($this->returnValue(false));
        $oSession->expects($this->any())->method('_isSwappedClient')->will($this->returnValue(true));
        $oSession->expects($this->any())->method('_allowSessionStart')->will($this->returnValue(true));
        modConfig::setRequestParameter('force_sid', 'testSid3');
        $oSession->start();
        $this->assertNotEquals($oSession->getId(), 'testSid3');
    }

    /**
     * oxsession::allowSessionStart() test for normal case
     */
    function testAllowSessionStartNormal()
    {
        $this->assertFalse($this->oSession->UNITallowSessionStart());
    }

    function testAllowSessionStartNormalForced()
    {
        modConfig::getInstance()->setConfigParam('blForceSessionStart', 1);
        $this->assertTrue($this->oSession->UNITallowSessionStart());
    }

    /**
     * oxsession::allowSessionStart() test for search engines
     */
    function testAllowSessionStartForSearchEngines()
    {
        oxRegistry::getUtils()->setSearchEngine(true);
        $this->assertFalse($this->oSession->UNITallowSessionStart());
        oxRegistry::getUtils()->setSearchEngine(false);
    }

    /**
     * oxsession::allowSessionStart() test forcing skip
     */
    function testAllowSessionStartForceSkip()
    {
        modConfig::setRequestParameter('skipSession', true);
        $this->assertFalse($this->oSession->UNITallowSessionStart());
        modConfig::setRequestParameter('skipSession', false);
    }

    /**
     * oxsession::isSwappedClient() normal calse
     */
    function testIsSwappedClientNormal()
    {
        $this->assertFalse($this->oSession->UNITisSwappedClient());
    }

    /**
     * oxsession::isSwappedClient() for search engines
     */
    function testIsSwappedClientForSearchEngines()
    {
        oxRegistry::getUtils()->setSearchEngine(true);
        $this->assertFalse($this->oSession->UNITisSwappedClient());
        oxRegistry::getUtils()->setSearchEngine(false);
    }

    /**
     * oxsession::isSwappedClient() as for differnet clients
     */
    function testIsSwappedClientAsDifferentUserAgent()
    {
        $oSubj = $this->getMock("oxSession", array('_checkUserAgent'));
        $oSubj->expects($this->any())->method('_checkUserAgent')->will($this->returnValue(true));
        $this->assertTrue($oSubj->UNITisSwappedClient());
    }

    /**
     * oxsession::isSwappedClient() as for differnet clients with correct token
     */
    function testIsSwappedClientAsDifferentUserAgentCorrectToken()
    {
        modConfig::getInstance()->setRequestParameter('rtoken', 'test1');

        $oSubj = $this->getMock("oxSession", array('_checkUserAgent'));
        $oSubj->expects($this->any())->method('_checkUserAgent')->will($this->returnValue(true));
        $oSubj->setVariable('_rtoken', 'test1');
        $this->assertFalse($oSubj->UNITisSwappedClient());
    }

    /**
     * oxsession::isSwappedClient() as for differnet clients
     */
    function testIsSwappedClientAsDifferentClientIfRemoteAccess()
    {
        $this->assertTrue($this->oSession->UNITcheckUserAgent('browser1', 'browser2'));
    }

    /**
     * oxsession::isSwappedClient() as timeout for normal user
     */
    /*
   function testIsSwappedClientAsTimeoutForUser()
   {
       $this->oSession = $this->getMock( 'testSession', array( 'isAdmin' ) );
       $this->oSession->expects( $this->any() )->method( 'isAdmin')->will( $this->returnValue( false ) );
       oxAddClassModule( 'modOxUtilsDate', 'oxUtilsDate' );
       oxRegistry::get("oxUtilsDate")->UNITSetTime( 100 );
       modConfig::setConfigParam('iSessionTimeout', 10);
       $this->oSession->setVar( "sessiontimestamp", 10);
       $this->assertFalse($this->oSession->UNITcheckByTimeOut());

       //large delay
       oxRegistry::get("oxUtilsDate")->UNITSetTime( 1000 );
       $this->assertTrue($this->oSession->UNITcheckByTimeOut());
       modConfig::setConfigParam('iSessionTimeout', null);
   }*/

    /**
     * oxsession::isSwappedClient() as timeout for normal user
     * if session timeout is not set
     */
    /*
   function testIsSwappedClientAsTimeoutForUserIfSessTimeoutNotSet()
   {
       $this->oSession = $this->getMock( 'testSession', array( 'isAdmin' ) );
       $this->oSession->expects( $this->any() )->method( 'isAdmin')->will( $this->returnValue( false ) );
       oxAddClassModule( 'modOxUtilsDate', 'oxUtilsDate' );
       oxRegistry::get("oxUtilsDate")->UNITSetTime( 60 );
       $this->oSession->setVar( "sessiontimestamp", 10);
       $this->assertFalse($this->oSession->UNITcheckByTimeOut());

       //large delay
       oxRegistry::get("oxUtilsDate")->UNITSetTime( 4000 );
       $this->assertTrue($this->oSession->UNITcheckByTimeOut());
   }*/

    /**
     * oxsession::isSwappedClient() as timeout for admin
     */
    /*
   function testIsSwappedClientAsTimeoutForAdmin()
   {
       $this->oSession = $this->getMock( 'testSession', array( 'isAdmin' ) );
       $this->oSession->expects( $this->any() )->method( 'isAdmin')->will( $this->returnValue( true ) );
       oxAddClassModule( 'modOxUtilsDate', 'oxUtilsDate' );
       oxRegistry::get("oxUtilsDate")->UNITSetTime( 60 );
       $this->oSession->setVar( "sessiontimestamp", 10);
       modConfig::setConfigParam('iSessionTimeoutAdmin', 10);
       $this->assertFalse($this->oSession->UNITcheckByTimeOut());

       //large delay
       oxRegistry::get("oxUtilsDate")->UNITSetTime( 4000 );
       $this->assertTrue($this->oSession->UNITcheckByTimeOut());
   }*/

    /**
     * oxsession::isSwappedClient() cookie check test is performed
     */
    function testIsSwappedClientSidCheck()
    {
        $oDB = oxDb::getDb();
        $sInsert = "CREATE TABLE `oxsessions` (
                `ID` int( 11 ) NOT NULL AUTO_INCREMENT ,
                `SessionID` varchar( 64 ) default NULL ,
                `session_data` text,
                `expiry` int( 11 ) default NULL ,
                `expireref` varchar( 64 ) default NULL ,
                PRIMARY KEY ( `ID` ) ,
                KEY `SessionID` ( `SessionID` ) ,
                KEY `expiry` ( `expiry` )
                )";
        $oDB->Execute($sInsert);
        $this->assertTrue($this->oSession->UNITcheckSid());

        $sInsert = "INSERT INTO `oxsessions` ( `SessionID` ) VALUES ( 'sessiontest' )";
        $oDB->Execute($sInsert);

        $oSession = $this->getMock('testSession', array('getId'));
        $oSession->expects($this->any())->method('getId')->will($this->returnValue('sessiontest'));
        $this->assertFalse($oSession->UNITcheckSid());
    }

    /**
     * oxsession::isSwappedClient() cookie check test is performed
     */
    function testIsSwappedClientCookieCheck()
    {
        $myConfig = oxRegistry::getConfig();
        oxAddClassModule('Unit_oxsessionTest_oxUtilsServer', 'oxUtilsServer');
        $this->assertFalse($this->oSession->UNITcheckCookies(null, null));
        $this->assertEquals("oxid", oxRegistry::get("oxUtilsServer")->getOxCookie('sid_key'));
        $this->assertFalse($this->oSession->UNITcheckCookies("oxid", null));
        $aSessCookSet = $this->oSession->getVar("sessioncookieisset");
        $this->assertEquals("ox_true", $aSessCookSet[$myConfig->getCurrentShopURL()]);
        $this->assertFalse($this->oSession->UNITcheckCookies("oxid", $aSessCookSet));
        $this->assertTrue($this->oSession->UNITcheckCookies(null, $aSessCookSet));
        $this->assertEquals("oxid", oxRegistry::get("oxUtilsServer")->getOxCookie('sid_key'));

        modConfig::getInstance()->setConfigParam('blSessionUseCookies', 1);
        $oSession = $this->getMock('testSession', array('_checkCookies'));
        $oSession->expects($this->once())->method('_checkCookies');
        $oSession->UNITisSwappedClient();

        modConfig::getInstance()->setConfigParam('blSessionUseCookies', 0);
        $oSession = $this->getMock('testSession', array('_checkCookies'));
        $oSession->expects($this->never())->method('_checkCookies');
        $oSession->UNITisSwappedClient();
    }

    /**
     * oxSession::_checkCookies() test case
     *
     * @return null
     */
    public function testCheckCookiesSsl()
    {
        $oConfig = $this->getMock("oxconfig", array("isSsl", "getSslShopUrl", "getShopUrl", "getConfigParam"));
        $oConfig->expects($this->once())->method('isSsl')->will($this->returnValue(true));
        $oConfig->expects($this->once())->method('getSslShopUrl')->will($this->returnValue("testsslurl"));
        $oConfig->expects($this->never())->method('getShopUrl');
        $oConfig->expects($this->never())->method('getConfigParam');

        $oSession = $this->getMock("oxsession", array("getConfig"));
        $oSession->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));
        $this->assertFalse($oSession->UNITcheckCookies(false, array()));
    }

    /**
     * oxSession::_checkCookies() test case
     *
     * @return null
     */
    public function testCheckCookiesNoSsl()
    {
        $oConfig = $this->getMock("oxconfig", array("isSsl", "getSslShopUrl", "getShopUrl", "getConfigParam"));
        $oConfig->expects($this->once())->method('isSsl')->will($this->returnValue(false));
        $oConfig->expects($this->never())->method('getSslShopUrl');
        $oConfig->expects($this->once())->method('getShopUrl')->will($this->returnValue("testurl"));
        $oConfig->expects($this->once())->method('getConfigParam')->with($this->equalTo('iDebug'))->will($this->returnValue(true));

        $oSession = $this->getMock("oxsession", array("getConfig"));
        $oSession->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));
        $this->assertTrue($oSession->UNITcheckCookies(false, array("testurl" => "ox_true")));
    }

    /**
     * oxsession::intiNewSesssion() test
     */
    function testInitNewSession()
    {
        $myConfig = oxRegistry::getConfig();

        $oSession = $this->getMock('testSession', array("_getNewSessionId"));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));

        $oSession->setVar('someVar1', true);
        $oSession->setVar('someVar2', 15);
        $oSession->setVar('actshop', 5);
        $oSession->setVar('lang', 3);
        $oSession->setVar('currency', 3);
        $oSession->setVar('language', 12);
        $oSession->setVar('tpllanguage', 12);

        $sOldSid = $oSession->getId();

        $oSession->initNewSession();

        //most sense is to perform this check
        //if session id was changed
        $this->assertNotEquals($sOldSid, $oSession->getId());

        //checking if new id is correct (md5($newid))
        $this->assertEquals("newSessionId", $oSession->getId());

        //$this->assertNotEquals($this->oSession->getVar('someVar1'), true);
        //$this->assertNotEquals($this->oSession->getVar('someVar2'), 15);
        $this->assertEquals($oSession->getVar('actshop'), 5);
        $this->assertEquals($oSession->getVar('lang'), 3);
        $this->assertEquals($oSession->getVar('currency'), 3);
        $this->assertEquals($oSession->getVar('language'), 12);
        $this->assertEquals($oSession->getVar('tpllanguage'), 12);

        $oSession->setVar('someVar1', null);
        $oSession->setVar('someVar2', null);
        $oSession->setVar('actshop', null);
        $oSession->setVar('lang', null);
        $oSession->setVar('currency', null);
        $oSession->setVar('language', $myConfig->sDefaultLang);
        $oSession->setVar('tpllanguage', null);
    }

    /**
     * oxsession::intiNewSesssion() test
     */
    function testInitNewSessionWithPersParams()
    {
        $myConfig = oxRegistry::getConfig();

        $oSession = $this->getMock('testSession', array("_getNewSessionId"));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));

        $oSession->setVar('someVar1', true);
        $oSession->setVar('someVar2', 15);
        $oSession->setVar('actshop', 5);
        $oSession->setVar('lang', 3);
        $oSession->setVar('currency', 3);
        $oSession->setVar('language', 12);
        $oSession->setVar('tpllanguage', 12);

        $sOldSid = $oSession->getId();

        $oSession->initNewSession();

        //most sense is to perform this check
        //if session id was changed
        $this->assertNotEquals($sOldSid, $oSession->getId());

        //checking if new id is correct (md5($newid))
        $this->assertEquals("newSessionId", $oSession->getId());

        $this->assertEquals($oSession->getVar('actshop'), 5);
        $this->assertEquals($oSession->getVar('lang'), 3);
        $this->assertEquals($oSession->getVar('currency'), 3);
        $this->assertEquals($oSession->getVar('language'), 12);
        $this->assertEquals($oSession->getVar('tpllanguage'), 12);

        $oSession->setVar('someVar1', null);
        $oSession->setVar('someVar2', null);
        $oSession->setVar('actshop', null);
        $oSession->setVar('lang', null);
        $oSession->setVar('currency', null);
        $oSession->setVar('language', $myConfig->sDefaultLang);
        $oSession->setVar('tpllanguage', null);
    }

    /**
     * oxsession::setSessionId() test. Normal case
     */
    function testSetSessionIdNormal()
    {
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return true;}");
        modConfig::getInstance()->setConfigParam('blForceSessionStart', 0);

        $oSession = $this->getMock('testSession', array("_getNewSessionId"));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));

        $this->assertFalse($oSession->isNewSession());

        $oSession->start();
        $this->assertEquals($oSession->getName(), 'sid');
        $oSession->UNITsetSessionId('testSid');

        $this->assertEquals($oSession->getId(), 'testSid');
        $this->assertTrue($oSession->isNewSession());

        //reset session
        $oSession->initNewSession();
        $this->assertNotEquals('testSid', $oSession->getId());
    }

    function testSetSessionIdSkipCookies()
    {
        oxTestModules::addFunction('oxUtilsServer', 'setOxCookie', '{throw new Exception("may not! (set cookies while they are turned off)");}');

        modConfig::getInstance()->setConfigParam('blSessionUseCookies', 0);
        $oSession = $this->getMock('testSession', array('_allowSessionStart'));
        $oSession->expects($this->once())->method('_allowSessionStart')->will($this->returnValue(false));
        $oSession->UNITsetSessionId('test');

        $oSession = $this->getMock('testSession', array('_allowSessionStart'));
        $oSession->expects($this->once())->method('_allowSessionStart')->will($this->returnValue(true));
        $oSession->UNITsetSessionId('test');
    }

    function testSetSessionIdForced()
    {
        oxAddClassModule('Unit_oxsessionTest_oxUtilsServer', 'oxUtilsServer');
        modConfig::getInstance()->setConfigParam('blForceSessionStart', 1);

        $oSession = $this->getMock('testSession', array("_getNewSessionId"));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));

        $this->assertFalse($oSession->isNewSession());

        $oSession->start();
        $this->assertEquals($oSession->getName(), 'sid');
        $oSession->UNITsetSessionId('testSid');

        $this->assertEquals($oSession->getId(), 'testSid');
        $this->assertTrue($oSession->isNewSession());
        $this->assertEquals(oxRegistry::get("oxUtilsServer")->getOxCookie($oSession->getName()), 'testSid');

        //reset session
        $oSession->InitNewSession();
        $this->assertNotEquals('testSid', $oSession->getId());
    }

    /**
     * oxsession::setSessionId() test. Admin
     */
    function testSetSessionIdAdmin()
    {
        oxTestModules::addFunction("oxUtilsServer", "getOxCookie", "{return 'testSid';}");

        $oSession = $this->getMock('testSession', array('isAdmin', '_sessionStart', "_getNewSessionId"));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));

        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(true));
        $oSession->expects($this->any())->method('_sessionStart')->will($this->returnValue(true));
        $this->assertFalse($oSession->isNewSession());

        $oSession->start();
        //session name is different..
        $this->assertEquals($oSession->getName(), 'admin_sid');
        $oSession->UNITsetSessionId('testSid');

        //..but still eveything is set
        $this->assertEquals($oSession->getId(), 'testSid');
        $this->assertTrue($oSession->isNewSession());
        $this->assertEquals(oxRegistry::get("oxUtilsServer")->getOxCookie($this->oSession->getName()), 'testSid');

        //reset session
        $oSession->InitNewSession();
        $this->assertNotEquals($oSession->getId(), 'testSid');
    }

    /**
     * oxsession::setSessionId() test. For search engines.
     */
    function testSetSessionIdSearchEngines()
    {
        oxRegistry::getUtils()->setSearchEngine(true);

        $oSession = $this->getMock("oxsession", array("_getNewSessionId", "_allowSessionStart"));
        $oSession->expects($this->any())->method('_getNewSessionId');
        $oSession->expects($this->any())->method('_allowSessionStart')->will($this->returnValue(true));

        $this->assertFalse($oSession->isNewSession());

        $oSession->start();

        $this->assertEquals($oSession->getName(), 'sid');
        $oSession->UNITsetSessionId('testSid');

        $this->assertEquals($oSession->getId(), 'testSid');
        $this->assertTrue($oSession->isNewSession());

        //have no cookie as search engine
        $this->assertEquals(oxRegistry::get("oxUtilsServer")->getOxCookie($oSession->getName()), null);

        //reset session
        $oSession->initNewSession();
        $this->assertNotEquals($oSession->getId(), 'testSid');

        //teardown
        oxRegistry::getUtils()->setSearchEngine(false);
    }

    /**
     * oxsession::checkMandatoryCookieSupport() test. Normal case. not critical action.
     */
    /*function testCheckMandatoryCookieSupportNormal()
    {
        oxRegistry::getConfig()->setConfigParam( 'blSessionEnforceCookies', false );
        $this->assertTrue($this->oSession->UNITcheckMandatoryCookieSupport( "account", "tobasket"));
    }*/

    /**
     * oxsession::checkMandatoryCookieSupport() test in critical action when cookies are supported
     */
    /*function testCheckMandatoryCookieSupportCookiesSupported()
    {
        oxRegistry::getConfig()->setConfigParam( 'blSessionEnforceCookies', true );
        $this->assertFalse($this->oSession->UNITcheckMandatoryCookieSupport( 'register', '' ));
        $this->assertFalse($this->oSession->UNITcheckMandatoryCookieSupport( 'account', ''));
        $this->assertFalse($this->oSession->UNITcheckMandatoryCookieSupport( 'alist', 'tobasket'));
        $this->assertFalse($this->oSession->UNITcheckMandatoryCookieSupport( 'details', 'login_noredirect'));
    }*/

    /**
     * oxsession::freeze() test
     */
    function testFreeze()
    {
        //noting to test here as oxSession::freeze() includes only PHP session functionality
        //testing at least if this method exists by just calling it
        session_id("testSessId");
        $testSession = new oxSession();
        $testSession->freeze();
    }

    /**
     * oxRegistry::getSession()->setVariable() test
     */
    function testSetHasGetVar()
    {
        //taking real session object
        $testSession = new oxSession();
        $testSession->setVariable('testVar', 'testVal');
        $this->assertTrue($testSession->hasVariable('testVar'));
        $this->assertEquals('testVal', $testSession->getVariable('testVar'));
    }

    /**
     * oxsession::sid() test normal case
     */
    function testSidNormal()
    {
        oxRegistry::getConfig()->setConfigParam('blSessionUseCookies', false);
        $oSession = $this->getMock('testSession', array('_getCookieSid', 'isAdmin'));
        $oSession->expects($this->any())->method('_getCookieSid')->will($this->returnValue('admin_sid'));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        $oSession->UNITsetSessionId('testSid');
        $this->assertEquals('sid=testSid', $oSession->sid());

        oxRegistry::getConfig()->setConfigParam('blSessionUseCookies', true);
        $oSession->UNITsetSessionId('testSid');
        $this->assertEquals('', $oSession->sid());
    }

    /**
     * oxsession::sid() test normal case
     */
    function testSidInAdmin()
    {
        $oSession = $this->getMock('testSession', array('_getCookieSid', 'isAdmin', 'getSessionChallengeToken'));
        $oSession->expects($this->any())->method('getSessionChallengeToken')->will($this->returnValue('stok'));
        $oSession->expects($this->any())->method('_getCookieSid')->will($this->returnValue('admin_sid'));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(true));
        $oSession->UNITsetSessionId('testSid');

        $sRet = 'stoken=stok';

        $this->assertEquals($sRet, $oSession->sid());
    }

    /**
     * oxsession::sid() test normal case
     */
    function testSidIfIdNotSetButSearchEngine()
    {
        $oConfig = $this->getMock('oxConfig', array('getShopId'));
        $oConfig->expects($this->any())->method('getShopId')->will($this->returnValue('2'));
        $oConfig->expects($this->any())->method('getShopId')->will($this->returnValue('2'));
        $oConfig->setConfigParam('blSessionUseCookies', false);
        $oConfig->setConfigParam('aCacheViews', array());
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return true;}");
        $oSession = $this->getMock('testSession', array('_getCookieSid', 'isAdmin', 'getConfig'));
        $oSession->expects($this->any())->method('_getCookieSid')->will($this->returnValue('admin_sid'));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        $oSession->expects($this->any())->method('getConfig')->will($this->returnValue($oConfig));
        $oSession->UNITsetSessionId(null);
        $sSid = $oSession->sid();

        // update: shp adding functionality is also in oxUtilsUrl, where it belongs
        $this->assertEquals('', $sSid);
    }

    /**
     * oxsession::sid() test in amdin
     */
    function testSidIsSearchEngine()
    {
        $oConfig = $this->getMock('oxConfig', array('getShopId'));
        $oConfig->expects($this->any())->method('getShopId')->will($this->returnValue('2'));
        $oConfig->expects($this->any())->method('getShopId')->will($this->returnValue('2'));
        $oConfig->setConfigParam('blSessionUseCookies', false);
        $oConfig->setConfigParam('aCacheViews', array());
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return true;}");
        $oSession = $this->getMock('testSession', array('_getCookieSid', 'isAdmin', 'getConfig'));
        $oSession->expects($this->any())->method('_getCookieSid')->will($this->returnValue('admin_sid'));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        $oSession->expects($this->any())->method('getConfig')->will($this->returnValue($oConfig));
        $oSession->UNITsetSessionId('testSid');
        $sSid = $oSession->sid();

        // update: shp adding functionality is also in oxUtilsUrl, where it belongs
        $this->assertEquals('', $sSid);
    }

    /**
     * oxsession::hiddenSid() test
     */
    function testHiddenSidIsAdmin()
    {
        $oSession = $this->getMock('testSession', array('isAdmin', 'getSessionChallengeToken', 'isSidNeeded'));
        $oSession->expects($this->any())->method('getSessionChallengeToken')->will($this->returnValue('stok'));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(true));
        $oSession->expects($this->any())->method('isSidNeeded')->will($this->returnValue(true));
        $oSession->UNITsetSessionId('testSid');
        $sSid = $oSession->hiddenSid();
        $this->assertEquals('<input type="hidden" name="stoken" value="stok" /><input type="hidden" name="force_sid" value="testSid" />', $sSid);
    }

    /**
     * oxsession::hiddenSid() test
     */
    function testHiddenSidIsAdminWithCookies()
    {
        $oSession = $this->getMock('testSession', array('isAdmin', 'getSessionChallengeToken', 'isSidNeeded'));
        $oSession->expects($this->any())->method('getSessionChallengeToken')->will($this->returnValue('stok'));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(true));
        $oSession->expects($this->any())->method('isSidNeeded')->will($this->returnValue(false));
        $oSession->UNITsetSessionId('testSid');
        $sSid = $oSession->hiddenSid();
        $this->assertEquals('<input type="hidden" name="stoken" value="stok" />', $sSid);
    }

    /**
     * oxsession::hiddenSid() test
     */
    function testHiddenSidNotAdmin()
    {
        $oSession = $this->getMock('testSession', array('isAdmin', 'getSessionChallengeToken', 'isSidNeeded'));
        $oSession->expects($this->any())->method('getSessionChallengeToken')->will($this->returnValue('stok'));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        $oSession->expects($this->any())->method('isSidNeeded')->will($this->returnValue(true));
        $oSession->UNITsetSessionId('testSid');
        $sSid = $oSession->hiddenSid();
        $this->assertEquals('<input type="hidden" name="stoken" value="stok" /><input type="hidden" name="force_sid" value="testSid" />', $sSid);
    }

    /**
     * oxsession::hiddenSid() test
     */
    function testHiddenSidNotAdminWithCookies()
    {
        $oSession = $this->getMock('testSession', array('isAdmin', 'getSessionChallengeToken', 'isSidNeeded'));
        $oSession->expects($this->any())->method('getSessionChallengeToken')->will($this->returnValue('stok'));
        $oSession->expects($this->any())->method('isAdmin')->will($this->returnValue(false));
        $oSession->expects($this->any())->method('isSidNeeded')->will($this->returnValue(false));
        $oSession->UNITsetSessionId('testSid');
        $sSid = $oSession->hiddenSid();
        $this->assertEquals('<input type="hidden" name="stoken" value="stok" />', $sSid);
    }

    /**
     * oxsession::getBasketName() test
     */
    function testGetBasketNameblMallSharedBasket()
    {
        $this->assertEquals($this->oSession->UNITgetBasketName(), oxRegistry::getConfig()->getShopId() . '_basket');
    }

    /**
     * oxsession::getBasketName() test
     */
    function testGetBasketName()
    {
        oxRegistry::getConfig()->setConfigParam('blMallSharedBasket', 1);
        $this->assertEquals('basket', $this->oSession->UNITgetBasketName());
    }

    /**
     *  oxsession::getBasket() not basket instance
     */
    function testGetBasket_notBasketInstance()
    {
        $oClass = oxNew('__PHP_Incomplete_Class');
        $oSession = $this->getMock('oxsession', array('_getBasketName'));
        $oSession->expects($this->once())->method('_getBasketName')->will($this->returnValue(serialize($oClass)));

        $oSessionBasket = $oSession->getBasket();
        $this->assertTrue($oSessionBasket instanceof oxbasket, "oSessionBasket is instance of oxbasket (found " . get_class($oSessionBasket) . ")");
    }

    /**
     *  oxsession::getBasket() wrong basket instance
     */
    function testGetBasket_notWrongBasketInstance()
    {
        $oFakeBasket = oxNew('fake_basket');
        $oSession = $this->getMock('oxsession', array('_getBasketName'));
        $oSession->expects($this->once())->method('_getBasketName')->will($this->returnValue(serialize($oFakeBasket)));

        $oSessionBasket = $oSession->getBasket();
        $this->assertTrue($oSessionBasket instanceof oxbasket, "oSessionBasket is instance of oxbasket (found " . get_class($oSessionBasket) . ")");
    }

    /**
     *  oxsession::setBasket() test
     */
    function testSetBasket_getBasket()
    {
        $oBasket = oxNew('oxbasket');
        $this->assertNotNull($oBasket);
        $this->oSession->setBasket($oBasket);

        $oSessionBasket = $this->oSession->getBasket();
        $this->assertEquals($oBasket, $oSessionBasket);
    }

    /**
     * oxsession::delBasket() test
     */
    function testDelBasket()
    {
        $oSession = $this->getMock('oxsession', array('_getBasketName'));
        $oSession->expects($this->once())->method('_getBasketName')->will($this->returnValue('xxx'));
        $oSession->delBasket();
    }

    /**
     * Test for bug #853
     */
    function testDbSessionHandlerExists()
    {
        $this->assertTrue(file_exists(_DB_SESSION_HANDLER), _DB_SESSION_HANDLER . " does not exist");
    }

    function testGetRequestChallengeToken()
    {
        $oSession = oxNew('oxsession');
        modConfig::setRequestParameter('stoken', 'asd');
        $this->assertEquals('asd', $oSession->getRequestChallengeToken());
        modConfig::setRequestParameter('stoken', 'asd#asd$$');
        $this->assertEquals('asdasd', $oSession->getRequestChallengeToken());
    }

    public function testGetSessionChallengeToken()
    {
        modSession::getInstance()->setVar('sess_stoken', '');
        $oSession = $this->getMock('oxSession', array('_initNewSessionChallenge'));
        $oSession->expects($this->once())->method('_initNewSessionChallenge')->will($this->evalFunction('{modSession::getInstance()->setVar("sess_stoken", "newtok");}'));
        $this->assertEquals('newtok', $oSession->getSessionChallengeToken());
        modSession::getInstance()->setVar('sess_stoken', 'asd541)$#sdf');
        $this->assertEquals('asd541sdf', $oSession->getSessionChallengeToken());
    }

    public function testCheckSessionChallenge()
    {
        $oSession = $this->getMock('oxsession', array('getSessionChallengeToken', 'getRequestChallengeToken'));
        $oSession->expects($this->once())->method('getSessionChallengeToken')->will($this->returnValue(''));
        $oSession->expects($this->never())->method('getRequestChallengeToken')->will($this->returnValue(''));
        $this->assertEquals(false, $oSession->checkSessionChallenge());

        $oSession = $this->getMock('oxsession', array('getSessionChallengeToken', 'getRequestChallengeToken'));
        $oSession->expects($this->once())->method('getSessionChallengeToken')->will($this->returnValue('aa'));
        $oSession->expects($this->once())->method('getRequestChallengeToken')->will($this->returnValue('aad'));
        $this->assertEquals(false, $oSession->checkSessionChallenge());

        $oSession = $this->getMock('oxsession', array('getSessionChallengeToken', 'getRequestChallengeToken'));
        $oSession->expects($this->once())->method('getSessionChallengeToken')->will($this->returnValue('aa'));
        $oSession->expects($this->once())->method('getRequestChallengeToken')->will($this->returnValue('aa'));
        $this->assertEquals(true, $oSession->checkSessionChallenge());
    }

    public function testInitNewSessionChallenge()
    {
        modSession::getInstance()->setVar('sess_stoken', '');
        $oSession = new oxSession();
        $this->assertEquals('', modSession::getInstance()->getVar('sess_stoken'));
        $this->assertEquals('', $oSession->getRequestChallengeToken());

        $oSession->UNITinitNewSessionChallenge();
        $s1 = modSession::getInstance()->getVar('sess_stoken');
        $this->assertNotEquals('', $s1);

        $oSession->UNITinitNewSessionChallenge();
        $s2 = modSession::getInstance()->getVar('sess_stoken');
        $this->assertNotEquals('', $s2);
        $this->assertNotEquals($s1, $s2);
    }

    public function testInitNewSessionRecreatesChallengeToken()
    {
        $oSession = $this->getMock('oxsession', array('_initNewSessionChallenge', "_getNewSessionId", '_sessionStart'));
        $oSession->expects($this->any())->method('_sessionStart')->will($this->returnValue(null));
        $oSession->expects($this->any())->method('_getNewSessionId')->will($this->returnValue("newSessionId"));
        $oSession->expects($this->once())->method('_initNewSessionChallenge');
        $oSession->initNewSession();
    }

    /**
     * test _getRequireSessionWithParams if no config val exists
     */
    function testGetRequireSessionWithParamsNoConf()
    {
        $oCfg = $this->getMock('oxConfig', array('getConfigParam'));
        $oCfg->expects($this->once())->method('getConfigParam')
            ->with($this->equalTo('aRequireSessionWithParams'))
            ->will($this->returnValue(null));
        $oSess = $this->getMock('oxSession', array('getConfig'));
        $oSess->expects($this->once())->method('getConfig')
            ->will($this->returnValue($oCfg));
        $this->assertEquals(
            array(
                 'cl'          =>
                     array(
                         'register' => true,
                         'account'  => true,
                     ),
                 'fnc'         =>
                     array(
                         'tobasket'         => true,
                         'login_noredirect' => true,
                         'tocomparelist'    => true,
                     ),
                 '_artperpage' => true,
                 'ldtype'      => true,
                 'listorderby' => true,
            )
            , $oSess->UNITgetRequireSessionWithParams()
        );
    }

    /**
     * test _getRequireSessionWithParams if config val exists
     */
    function testGetRequireSessionWithParamsWithConf()
    {
        $oCfg = $this->getMock('oxConfig', array('getConfigParam'));
        $oCfg->expects($this->once())->method('getConfigParam')
            ->with($this->equalTo('aRequireSessionWithParams'))
            ->will(
                $this->returnValue(
                    array(
                         'cl'     => array('xxx' => 1), // add new value inside param
                         'fnc'    => 1, // override param to allow all values
                         '_param' => true, // add new params
                         '_ddd'   => array('yyy' => 1),
                    )
                )
            );
        $oSess = $this->getMock('oxSession', array('getConfig'));
        $oSess->expects($this->once())->method('getConfig')
            ->will($this->returnValue($oCfg));
        $this->assertEquals(
            array(
                 'cl'          =>
                     array(
                         'xxx'      => 1,
                         'register' => true,
                         'account'  => true,
                     ),
                 'fnc'         => 1,
                 '_param'      => true,
                 '_ddd'        => array('yyy' => 1),
                 '_artperpage' => true,
                 'ldtype'      => true,
                 'listorderby' => true,
            )
            , $oSess->UNITgetRequireSessionWithParams()
        );
    }

    /**
     * check config array handling
     */
    function testIsSessionRequiredAction()
    {
        $oSess = $this->getMock('oxSession', array('_getRequireSessionWithParams'));
        $oSess->expects($this->exactly(7))->method('_getRequireSessionWithParams')
            ->will(
                $this->returnValue(
                    array(
                         'clx'  => true,
                         'fncx' => array('a1' => true, 's3' => 1),
                    )
                )
            );
        $this->assertEquals(false, $oSess->UNITisSessionRequiredAction());
        modConfig::setRequestParameter('clx', '0');
        $this->assertEquals(true, $oSess->UNITisSessionRequiredAction());
        modConfig::setRequestParameter('fncx', '0');
        $this->assertEquals(true, $oSess->UNITisSessionRequiredAction());
        modConfig::setRequestParameter('clx', null);
        $this->assertEquals(false, $oSess->UNITisSessionRequiredAction());
        modConfig::setRequestParameter('fncx', 'a1');
        $this->assertEquals(true, $oSess->UNITisSessionRequiredAction());
        modConfig::setRequestParameter('fncx', 'a3');
        $this->assertEquals(false, $oSess->UNITisSessionRequiredAction());
        modConfig::setRequestParameter('fncx', 's3');
        $this->assertEquals(true, $oSess->UNITisSessionRequiredAction());
    }

    /**
     * check if forces session on POST request
     */
    function testIsSessionRequiredActionOnPost()
    {
        $oSess = $this->getMock('oxSession', array('_getRequireSessionWithParams'));
        $oSess->expects($this->exactly(2))->method('_getRequireSessionWithParams')
            ->will(
                $this->returnValue(
                    array()
                )
            );

        $sInitial = $_SERVER['REQUEST_METHOD'];
        try {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $this->assertEquals(false, $oSess->UNITisSessionRequiredAction());
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $this->assertEquals(true, $oSess->UNITisSessionRequiredAction());
        } catch (Exception $e) {
        }
        $_SERVER['REQUEST_METHOD'] = $sInitial;
        if ($e) {
            throw $e;
        };
    }

    public function testGetRemoteAccessToken()
    {
        $oSubj = new oxSession();
        $sTestToken = $oSubj->getRemoteAccessToken();
        $this->assertEquals(8, strlen($sTestToken));
    }

    public function testGetRemoteAccessTokenNotGenerated()
    {
        $oSubj = new oxSession();
        $oSubj->deleteVariable('_rtoken');
        $sTestToken = $oSubj->getRemoteAccessToken(false);

        $this->assertNull($sTestToken);
        //generating one
        $oSubj->getRemoteAccessToken();
        $sTestToken = $oSubj->getRemoteAccessToken(false);
        //expecting real tokent
        $this->assertEquals(8, strlen($sTestToken));
    }

    public function testGetRemoteAccessTokenTwice()
    {
        $oSubj = new oxSession();
        $oSubj->deleteVariable('_rtoken');
        $sToken1 = $oSubj->getRemoteAccessToken();
        $sToken2 = $oSubj->getRemoteAccessToken();

        $this->assertEquals($sToken1, $sToken2);
        $this->assertEquals(8, strlen($sToken2));
    }

    public function testIsRemoteAccessTokenValid()
    {
        modConfig::setRequestParameter('rtoken', 'test1');

        $oSubj = $this->getProxyClass('oxSession');
        $oSubj->setVariable('_rtoken', 'test1');
        $this->assertTrue($oSubj->UNITisValidRemoteAccessToken());
    }

    /**
     * Test handling of supplying an array instead of a string for rtoken.
     */
    public function testIsRemoteAccessTokenValidArrayRequestParameter()
    {
        //Suppress all error reporting on purpose for this test
        error_reporting(0);

        $this->setRequestParam('rtoken', array(1) );

        $oSubj = $this->getProxyClass('oxSession');
        $oSubj->setVariable('_rtoken', 'test1');
        $this->assertFalse($oSubj->_isValidRemoteAccessToken());
    }

    public function testIsTokenValidNot()
    {
        modConfig::setRequestParameter('stoken', 'test1');

        $oSubj = $this->getProxyClass('oxSession');
        $oSubj->setVariable('_stoken', 'test2');
        $this->assertFalse($oSubj->UNITisValidRemoteAccessToken());
    }

    public function testGetBasketReservations()
    {
        $this->assertTrue(oxRegistry::getSession()->getBasketReservations() instanceof oxBasketReservation);
        // test cache
        $this->assertSame(oxRegistry::getSession()->getBasketReservations(), oxRegistry::getSession()->getBasketReservations());
    }

    public function testSetForceNewSession()
    {
        $oSubj = $this->getProxyClass('oxSession');
        $this->assertFalse($oSubj->getNonPublicVar("_blForceNewSession"));

        $oSubj->setForceNewSession();
        $this->assertTrue($oSubj->getNonPublicVar("_blForceNewSession"));
    }

    public function testIsSessionStarted()
    {
        $oSession = $this->getProxyClass("oxSession");
        $this->assertFalse($oSession->isSessionStarted());

        // thats only way to test, cant wrap native "session_start()" function
        $oSession->setNonPublicVar("_blStarted", true);
        $this->assertTrue($oSession->isSessionStarted());
    }

    public function testIsActualSidInCookiePossitive()
    {
        $sOriginalVal = $_COOKIE["sid"];
        $_COOKIE["sid"] = "testIdDifferent";
        $oSession = $this->getMock('oxSession', array('getId'));
        $oSession->expects($this->any())->method('getId')->will($this->returnValue('testId'));

        $blRes = $oSession->isActualSidInCookie();

        $_COOKIE["sid"] = $sOriginalVal;
        $this->assertFalse($blRes);
    }

    public function testIsActualSidInCookieNegative()
    {
        $sOriginalVal = $_COOKIE["sid"];
        $_COOKIE["sid"] = "testId";
        $oSession = $this->getMock('oxSession', array('getId'));
        $oSession->expects($this->any())->method('getId')->will($this->returnValue('testId'));

        $blRes = $oSession->isActualSidInCookie();

        $_COOKIE["sid"] = $sOriginalVal;

        $this->assertTrue($blRes);
    }

    public function testIsActualSidInCookieNotSet()
    {
        $sOriginalVal = $_COOKIE["sid"];
        unset($_COOKIE["sid"]);
        $oSession = $this->getMock('oxSession', array('getId'));
        $oSession->expects($this->any())->method('getId')->will($this->returnValue('testId'));

        $blRes = $oSession->isActualSidInCookie();

        $_COOKIE["sid"] = $sOriginalVal;

        $this->assertFalse($blRes);
    }

}
