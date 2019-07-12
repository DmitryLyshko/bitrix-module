<?php

IncludeModuleLangFile(__FILE__);
use \Bitrix\Main\ModuleManager;

class dataspotter_plugin extends CModule
{
    var $MODULE_ID = "dataspotter.plugin";
    var $MODULE_VERSION = "1.0";
    var $MODULE_VERSION_DATE = "2019-07-11 18:00:00";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $errors;

    function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . "/version.php";
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        
        $this->MODULE_ID = "dataspotter.plugin";
        $this->MODULE_NAME = "Dspotter plugin";
        $this->MODULE_DESCRIPTION = "Плагин Dspotter";
        $this->MODULE_GROUP_RIGHTS = "N";
        $this->PARTNER_NAME = "dspotter";
        $this->PARTNER_URI = "https://dspotter.dinrem.com/";
    }

    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallEvents();
        return true;
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        \Bitrix\Main\ModuleManager::UnRegisterModule($this->MODULE_ID);
        return true;
    }

    function InstallDB()
    {
        global $DB;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/dataspotter.plugin/install/db/install.sql");
        if (!$this->errors) {

            return true;
        } else
            return $this->errors;
    }

    function UnInstallDB()
    {
        global $DB;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/dataspotter.plugin/install/db/uninstall.sql");
        if (!$this->errors) {
            return true;
        } else
            return $this->errors;
    }

    function InstallEvents()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', $this->MODULE_ID, '\Dspotter\Plugin\Event', 'OnSave');
        $eventManager->registerEventHandler('sale', 'OnSaleOrderPaid', $this->MODULE_ID, '\Dspotter\Plugin\Event', 'OnPurchase');
        $eventManager->registerEventHandler('sale', 'OnSaleStatusOrderChange', $this->MODULE_ID, '\Dspotter\Plugin\Event', 'OnRefuseEvent');
        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler('sale', 'OnSaleOrderSaved', $this->MODULE_ID, '\Dspotter\Plugin\Event', 'OnSave');
        $eventManager->unRegisterEventHandler('sale', 'OnSaleOrderPaid', $this->MODULE_ID, '\Dspotter\Plugin\Event', 'OnPurchase');
        $eventManager->unRegisterEventHandler('sale', 'OnSaleStatusOrderChange', $this->MODULE_ID, '\Dspotter\Plugin\Event', 'OnRefuseEvent');
        return true;
    }
}
