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

require_once realpath(dirname(__FILE__)) . '/basemoduleTestCase.php';

class Integration_Modules_OnlineModuleNotifierTest extends BaseModuleTestCase
{

    /**
     * Tests if module was activated.
     */
    public function testVersionNotify()
    {
        $oEnvironment = new Environment();
        $oEnvironment->prepare(array('extending_1_class', 'extending_1_class_3_extensions', 'with_everything'));

        $oCaller = $this->getMock('oxOnlineModuleVersionNotifierCaller', array('doRequest'), array(), '', false);
        $oCaller->expects($this->any())->method('doRequest')->with($this->equalTo($this->_getExpectedRequest()));

        $oModuleList = new oxModuleList();
        $sModuleDir = realpath(dirname(__FILE__)) . '/testData/modules';
        $oModuleList->getModulesFromDir($sModuleDir);

        $oNotifier = new oxOnlineModuleVersionNotifier($oCaller, $oModuleList);
        $oNotifier->versionNotify();
    }

    protected function _getExpectedRequest()
    {
        $oRequest = new oxOnlineModulesNotifierRequest();

        $sShopUrl = $this->getConfig()->getShopUrl();
        $oRequest->edition = $this->getConfig()->getEdition();
        $oRequest->version = $this->getConfig()->getVersion();
        $oRequest->shopUrl = $sShopUrl;
        $oRequest->pVersion = '1.1';
        $oRequest->productId = 'eShop';

        $modules = new stdClass();
        $modules->module = array();

        $aModulesInfo = array();
        $aModulesInfo[] = array('id' => 'extending_1_class', 'version' => '1.0', 'activeInShop' => array($sShopUrl));
        $aModulesInfo[] = array('id' => 'extending_1_class_3_extensions', 'version' => '1.0', 'activeInShop' => array($sShopUrl));
        $aModulesInfo[] = array('id' => 'extending_3_blocks', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'extending_3_classes', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'extending_3_classes_with_1_extension', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'no_extending', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'with_1_extension', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'with_2_files', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'with_2_settings', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'with_2_templates', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'with_events', 'version' => '1.0', 'activeInShop' => array());
        $aModulesInfo[] = array('id' => 'with_everything', 'version' => '1.0', 'activeInShop' => array($sShopUrl));

        foreach ($aModulesInfo as $aModuleInfo) {
            $module = new stdClass();
            $module->id = $aModuleInfo['id'];
            $module->version = $aModuleInfo['version'];
            $module->activeInShops = new stdClass();
            $module->activeInShops->activeInShop = $aModuleInfo['activeInShop'];
            $modules->module[] = $module;
        }

        $oRequest->modules = $modules;

        return $oRequest;
    }
}
