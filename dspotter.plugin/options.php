<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$module_id = 'dspotter.plugin';

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight($module_id)<"S") {
    $APPLICATION->AuthForm('Доступ запрещен');
}

\Bitrix\Main\Loader::includeModule($module_id);


$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

$results = $DB->Query("SELECT * from dspotter_settings where id = 1");
$settings = $results->fetch();
if ($settings === false) {
    $settings = [
        'PLUGIN_ENABLED' => false,
        'HASH' => '',
        'SITE_NAME' => ''
    ];
}

\Bitrix\Main\Loader::IncludeModule("sale");
$statusResult = \Bitrix\Sale\Internals\StatusTable::getList([
    'order' => array('SORT'=>'ASC'),
    'filter' => array('TYPE'=>'O'),

]);
$stats = [];
while($status = $statusResult->fetch()) {
    $stats[$status['ID']] = CSaleStatus::GetByID($status['ID'])['NAME'];
}

$aTabs = [
    [
        'DIV' => 'edit1',
        'TAB' => 'Настройки dspotter',
        'OPTIONS' => [
            ['field_active', 'Модуль включен',
                (bool) $settings['PLUGIN_ENABLED'],
                ['checkbox']
            ],
            ['field_hash', 'Код активации модуля',
                $settings['HASH'],
                ['text', 30]
            ],
            ['field_site_name', 'Укажите название сайта, который будет отправлен в аналитику',
                $settings['SITE_NAME'],
                ['text', 30]
            ],
            ['field_order_cancel', 'Статус отмены заказа на вашем сайте',
                $settings['ORDER_CANCEL'],
                ['selectbox', $stats]
            ],
        ]
    ],
];

if ($request->isPost() && $request['Update'] && check_bitrix_sessid()) {
    $results = $DB->Query("SELECT * from dspotter_settings where id = 1");
    $res = $results->fetch();
    $enabled = 0;
    if ($request->get('field_active') === 'Y') {
        $enabled = 1;
    }

    $hash = $DB->ForSql($request->get('field_hash'));
    $site_name = $DB->ForSql($request->get('field_site_name'));
    $order_cancel = $DB->ForSql($request->get('field_order_cancel'));

    if ($res === false) {
        $result = $DB->Query("INSERT INTO dspotter_settings(`PLUGIN_ENABLED`, `HASH`, `ORDER_CANCEL`, `SITE_NAME`) VALUES ({$enabled}, '{$hash}', '{$order_cancel}', '{$site_name}')");
    } else {
        $result = $DB->Query("UPDATE dspotter_settings SET `PLUGIN_ENABLED` = {$enabled}, `HASH` = '{$hash}', `SITE_NAME` = '{$site_name}', `ORDER_CANCEL` = '{$order_cancel}' where id = 1");
    }

    $result->fetch();
    header('Location: ' . $_SERVER['HTTP_REFERER']);die;
}

$tabControl = new CAdminTabControl('tabControl', $aTabs);

?>
<? $tabControl->Begin(); ?>
<form method='post' action='<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>' name='dspotter'>

    <? foreach ($aTabs as $aTab):
            if($aTab['OPTIONS']):?>
        <? $tabControl->BeginNextTab(); ?>
        <? __AdmSettingsDrawList($module_id, $aTab['OPTIONS']); ?>

    <?      endif;
        endforeach; ?>

    <?
    $tabControl->BeginNextTab();



    $tabControl->Buttons(); ?>

    <input type="submit" name="Update" value="<?echo GetMessage('MAIN_SAVE')?>">
    <input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET')?>">
    <?=bitrix_sessid_post();?>
</form>
<? $tabControl->End(); ?>
