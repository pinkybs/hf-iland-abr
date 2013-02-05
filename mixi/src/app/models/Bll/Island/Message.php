<?php

class Bll_Island_Message
{
    /*protected static $template = array
        (
            'INVITE' => '{*actor*}在【{*app_link*}】中邀请您去Ta的岛上做客，费用全包哦~赶快动身吧！{*app_link2*}',
            'GIFT'   => '{*actor*}送了您一份来自【{*app_link*}】的礼物，赶快打开看看吧！{*app_link2*}'
        );*/

    protected static $template = array
        (
            'INVITE' => '{*actor*}はあなたを{*actor*}の島に招待しました。今すぐ行ってみよう！{*app_link2*}',
            'GIFT'   => '{*actor*}からプレゼントをもらいました。すぐに確認しよう！{*app_link2*}'
        );
        
    public static function send($type, $actor, $target, $data = null)
    {
        if(SEND_MESSAGE && isset(self::$template[$type])) {
            $appUrl = 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234';

            $st = floor(microtime(true)*1000);

            $actor_info = Bll_User::getPerson($actor);

            if ($data) {
                $data['actor'] = $actor_info['name'];
            } else {
                $data = array('actor' => $actor_info['name']);
            }

            if ($type == 'INVITE') {
                $invite_param= 'hf_invite=true&hf_inviter=' . $actor . '&hf_st=' . $st;
                $sg = md5($invite_param . APP_KEY . APP_SECRET);
                $appUrl .= '&' . $invite_param . '&hf_sg=' . $sg;

                Bll_Island_Log::addInvite($actor, $target, $st, $sg);
                $app_link2 = '<a href="' . $appUrl . '">加入游戏</a>';
                $data['app_link2'] = $app_link2;
            } else if ($type == 'GIFT') {
                $gift_param = 'hf_gift=true&hf_sender=' . $actor . '&hf_gift_id=' . $data['gift_id'] . '&hf_st=' . $st;
                $sg = md5($gift_param . APP_KEY . APP_SECRET);
                $appUrl .= '&' . $gift_param . '&hf_sg=' . $sg;

                Bll_Island_Log::addSendGift($actor, $target, $data['gift_id'], $st, $sg);
                $app_link2 = '<a href="' . $appUrl . '">接受礼物</a>';
                $data['app_link2'] = $app_link2;
            }

            $app_link = '<a href="' . $appUrl . '">ドリームアイランド</a>';
            
            $data['app_link'] = $app_link;

            $tpl = self::$template[$type];

            $body = self::buildTemplate($tpl, $data);

            $taobao = Taobao_Rest::getInstance();
            $taobao->setUser($actor, $_SESSION['session']);

            try {
                $taobao->jianghu->msg_publish($target, $body, 1);
            }catch (Exception $e) {
                err_log($e->getMessage());
            }
        }
    }
    
    public static function sendGiftToAppUser($actor, $target)
    {
        if(SEND_MESSAGE) {
            $appUrl = 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234';

            $actor_info = Bll_User::getPerson($actor);

            $data = array('actor' => $actor_info['name']);

            $app_link2 = '<a href="' . $appUrl . '">接受礼物</a>';
            $data['app_link2'] = $app_link2;

            $app_link = '<a href="' . $appUrl . '">ドリームアイランド</a>';
            $data['app_link'] = $app_link;

            $tpl = self::$template['GIFT'];

            $body = self::buildTemplate($tpl, $data);

            $taobao = Taobao_Rest::getInstance();
            $taobao->setUser($actor, $_SESSION['session']);

            try {
                $taobao->jianghu->msg_publish($target, $body, 1);
            }catch (Exception $e) {
                err_log($e->getMessage());
            }
        }        
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