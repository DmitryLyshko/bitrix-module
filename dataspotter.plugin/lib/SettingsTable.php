<?php

namespace Dspotter\Plugin;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

/**
 * Class SettingsTable
 * @package Dspotter\Plugin
 */
class SettingsTable  extends Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'dspotter_settings';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('ID'),
            ),
            'PLUGIN_ENABLED' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('ENABLED'),
            ),
            'HASH' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('HASH'),
            ),
            'CREATED' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('CREATED'),
            ),
        );
    }
}