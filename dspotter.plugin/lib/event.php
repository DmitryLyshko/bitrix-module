<?php

namespace Dspotter\Plugin;

require_once("SettingsTable.php");
require_once("OrderTable.php");

class Event
{
    CONST API_URL = 'https://dspotter.dinrem.com/api/birix/event';

    static public function OnSave(\Bitrix\Main\Event $event)
    {
        $settings = SettingsTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 1]);
        $settings = $settings->fetch();
        if ($settings === false && (int) $settings['PLUGIN_ENABLED'] === 0) {
            return false;
        }

        if (!$event || !$event->getParameter("IS_NEW")) {
            return false;
        }

        if (\Bitrix\Main\Loader::includeModule('sale') === false) {
            return false;
        }

        $order = $event->getParameter("ENTITY");
        $params = self::getOrderParams($order, $settings);

        $cid = $_COOKIE['_ga'] !== null ? $_COOKIE['_ga'] : 'empty';
        OrderTable::add([
            'ORDER_ID' => (int) $order->getField('ID'),
            'CID' => $cid
        ]);
 
        $sendingParams = [
            'hash' => $settings['HASH'],
            'cid' => $cid,
            'order_id' => $order->getField('ID'),
            'event_name' => 'create_order',
            'event_fields' => $params,
        ];

        self::sendRequest($sendingParams);

        return true;
    }

    public function OnPurchase($event)
    {
        if (!$event) {
            return false;
        }

        $settings = SettingsTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 1]);
        $settings = $settings->fetch();
        if ($settings === false && (int) $settings['PLUGIN_ENABLED'] === 0) {
            return false;
        }

        if (\Bitrix\Main\Loader::includeModule('sale') === false) {
            return false;
        }

        $payment = $event->getParameter("ENTITY");
        if (!$payment->isPaid()) {
            return false;
        }

        $params = self::getOrderParams($payment, $settings);
        $order = json_decode($params, true);

        $arFilter = ['ORDER_ID' => (int) $order['transaction_id']];
        $res = OrderTable::GetList([], $arFilter, false, ['nPageSize' => 50], ['CID']);
        $order_history = $res->fetch();

        $sendingParams = [
            'hash' => $settings['HASH'],
            'cid' => $order_history['CID'],
            'order_id' => $order['transaction_id'],
            'event_name' => 'purchase',
            'event_fields' => $params,
        ];

        self::sendRequest($sendingParams);

        return true;
    }

    public function OnRefuseEvent($event)
    {
        if (!$event) {
            return false;
        }

        $settings = SettingsTable::getList(['order' => ['ID' => 'DESC'], 'limit' => 1]);
        $settings = $settings->fetch();
        if ($settings === false && (int) $settings['PLUGIN_ENABLED'] === 0) {
            return false;
        }

        if (\Bitrix\Main\Loader::includeModule('sale') === false) {
            return false;
        }

        $order = $event->getParameter("ENTITY");
        //TODO playtoday
        if ($order->getField('STATUS_ID') !== 'G') {
            return false;
        }

        $arFilter = ['ORDER_ID' => (int) $order->getField('ID')];
        $res = OrderTable::GetList([], $arFilter, false, ['nPageSize' => 50], ['CID']);
        $order_history = $res->fetch();

        $sendingParams = [
            'hash' => $settings['HASH'],
            'cid' => $order_history['CID'],
            'order_id' => $order->getField('ID'),
            'event_name' => 'refuse',
            'event_fields' => json_encode(['transaction_id' => $order->getField('ID')]),
        ];

        self::sendRequest($sendingParams);

        return true;
    }

    public static function getOrderParams($order, $settings)
    {
        $basketItems = $order->getBasket();
        $items = [];
        foreach($basketItems as $item) {
            $items = [
                'id' => $item->getId(),
                'name' => $item->getField('NAME'),
                'price' => $item->getField('PRICE'),
                'brand' => '',
                'quantity' => (int) $item->getField('QUANTITY')
            ];
        }

        $params = json_encode([
            'transaction_id' => $order->getField('ID'),
            'affiliation' => $settings['SITE_NAME'],
            'value' => $order->getPrice(),
            'currency' => $order->getCurrency(),
            'tax' => $order->getVatSum(),
            'shipping' => $order->getDeliveryPrice(),
            'items' => $items
        ], JSON_UNESCAPED_UNICODE);

        return $params;
    }

    public static function sendRequest($data)
    {
        $url = self::API_URL . '?' . http_build_query($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return true;
    }
}