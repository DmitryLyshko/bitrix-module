<?php

namespace Dspotter\Plugin;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

/**
 * Class DataTable
 * @package Dspotter\Plugin
 */
class OrderTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'dspotter_order_history';
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
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('ORDER_ID'),
			),
			'CID' => array(
				'data_type' => 'text',
				'required' => true,
				'title' => Loc::getMessage('CID'),
			),
			'CREATED' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('CREATED'),
			),
		);
	}
}