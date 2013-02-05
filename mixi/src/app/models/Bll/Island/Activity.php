<?php

class Bll_Island_Activity
{
    /*protected static $template = array
        (
            'USER_LEVEL_UP'     => array('body' => '${actor}在【{*app_link*}】中升到了{*level*}级，赶快去看看吧~', 'image' => array('yonghushengji.jpg')),
            'ISLAND_LEVEL_UP'   => array('body' => '${actor}在【{*app_link*}】中的岛屿升到了{*level*}级，赶快去踩踩吧~','image' => array('daoyukuozhan.jpg')),
            'BUILDING_LEVEL_UP' => array('body' => '${actor}【{*app_link*}】中的设施升级了，赶快去凑凑热闹吧~','image' => array('sheshishengji.jpg')),
            'BOAT_LEVEL_UP'     => array('body' => '${actor}【{*app_link*}】中的船只升级了，赶快去瞧瞧吧~','image' => array('chuanshengji.jpg')),
            'DOCK_EXPANSION'    => array('body' => '${actor}【{*app_link*}】中的船位增加了，又有机会拉人了~','image' => array('chuanweishengji.jpg')),
            'MISSION_COMPLETE'  => array('body' => '${actor}【{*app_link*}】中完成了任务，赶快去他的岛恭喜一下吧~','image' => array('jianshe.jpg')),
            'USER_OBTAIN_TITLE' => array('body' => '${actor}居然获得了{*title*}的称号，不要羡慕了，加入【{*app_link*}】一起努力加油吧~','image' => array('huodechenghao.jpg')),
            'BUILDING_DAMAGE'   => array('body' => '${actor}在【{*app_link*}】中破坏了${participator}的{*building*}，赶快去帮助TA！','image' => array('pohuai.jpg')),
            'APP_JOIN'          => array('body' => '${actor}加入了【{*app_link*}】，拥有了自己的小岛！','image' => array('kaitong.jpg'))
        );*/
    
    protected static $template = array
    (
        'USER_LEVEL_UP'     => array('body' => 'LV{*level*}になりました。さっそく見に来てね！', 'image' => array('yonghushengji.jpg')),
        'ISLAND_LEVEL_UP'   => array('body' => 'LV{*level*}になりました。さっそく見に来てね！','image' => array('daoyukuozhan.jpg')),        
        //'BUILDING_LEVEL_UP' => array('body' => '{*building*}をLV{*level*}にレベルアップしました！','image' => array('sheshishengji.jpg')),
        'BUILDING_LEVEL_UP' => array('body' => '{*building*}ををレベルアップしたよ。遊びに来てね！','image' => array('sheshishengji.jpg')),
        //'BOAT_LEVEL_UP'     => array('body' => '船をレベルアップしました。さっそく見に来てね！','image' => array('chuanshengji.jpg')),
        'BOAT_LEVEL_UP'     => array('body' => '{*boat*}が使えるようになりました！これでお客さんが増えるぞ！','image' => array('chuanshengji.jpg')),
        //'DOCK_EXPANSION'    => array('body' => '港を拡大しました。さっそく島を見に来てね！','image' => array('chuanweishengji.jpg')),
        'DOCK_EXPANSION'    => array('body' => '港を{*expanOld*}隻から{*expanNew*}隻に拡大しました。マイミクに感謝！','image' => array('chuanweishengji.jpg')),
        //'MISSION_COMPLETE'  => array('body' => 'ミッションをクリアーしました。ちょっと寄っていってね！','image' => array('jianshe.jpg')),
        'USER_OBTAIN_TITLE' => array('body' => '{*title*}の称号を取得しました。祝いしに島に来てね！','image' => array('huodechenghao.jpg')),
        'BUILDING_DAMAGE'   => array('body' => '{*actor*}は{*target*}の{*building*}を壊しました。すぐ助けに行こう！','image' => array('pohuai.jpg')),
        'APP_JOIN'          => array('body' => '{*actor*}はドリームアイランドのコミュニティに参加しました。','image' => array('kaitong.jpg')),
        'BUY_FIFA'          => array('body' => '{*actor*}が{*plant*}代表を応援するためサッカー場を建設したよ！')
    );

    public static function send($type, $actor, $data = null, $target = null)
    {
        if(SEND_ACTIVITY && isset(self::$template[$type])) {
            $imgUrl = Zend_Registry::get('static') . '/apps/island/images/feed/';
            $app_link = 'ドリームアイランド';

            $tpl = self::$template[$type];

            $userActor = Bll_User::getPerson($actor);
            if ($target) {
                $userTarget = Bll_User::getPerson($target);
            }
            
            if ($data) {
                $data['app_link'] = $app_link;
                $data['actor'] = $userActor['name'];
                $data['target'] = $userTarget['name'];
            } else {
                $data = array('app_link' => $app_link, 'actor' => $userActor['name'], 'target' => $userTarget['name']);
            }
            
            if ($target) {
                $data['target'] = $target;
            }
            
            $title = self::buildTemplate($tpl['body'], $data);
            
            if ($type == 'BUY_FIFA') {
                $picture = $data['imgUrl'];
            }
            else if ($type == 'BUILDING_LEVEL_UP' || $type == 'BOAT_LEVEL_UP' || $type == 'DOCK_EXPANSION'){
                //$picture = $imgUrl . $tpl['image'][0];
                $picture = Zend_Registry::get('static') . '/apps/island/images/activity/' . $data['img'] . '.jpg';
            }
            else {
                $picture = '';
            }

            $feedSettings = array(
                'title' => $title,
                'picurl' => $picture,
                'mimeType' => 'image/jpeg'
            );

            return Zend_Json::encode($feedSettings);
        }
        
        return null;
    }

    protected static function buildTemplate($tpl, $json_array)
    {
        foreach ($json_array as $k => $v) {
            $keys[] = '{*' . $k . '*}';
            $values[] = $v;
        }

        return str_replace($keys, $values, $tpl);
    }
}