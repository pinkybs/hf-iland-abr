<?php

class Hapyfish2_Island_Bll_Message
{
    protected static $template = array
        (
            'INVITE' => '{*actor*} mời bạn chơi cùng trong [{*app_link*}], chơi thôi! {*app_link2*}',
            'GIFT'   => '{*actor*} vừa tặng bạn quà trong [{*app_link*}], xem ngay nhé! {*app_link2*}',
            'REMIND_1'   => '[{*app_link*}] của {*actor*} đang chờ bạn thu tiền! {*app_link2*}',
            'REMIND_2'   => '[{*app_link*}] của {*actor*} đang chờ bạn đón khách giúp! {*app_link2*}',
            'REMIND_3'   => '{*actor*} nhắc bạn mau thu tiền trên đảo! {*app_link2*}',
            'REMIND_4'   => '{*actor*} nhắc bạn mau thu tiền trên đảo! {*app_link2*}',
            'moochPlant'   => '{*actor*} trộm không ít tiền trên đảo của bạn, mau đi thu tiền kẻo hết! {*app_link2*}'
        );

    public static function send($type, $actor, $target, $data = null)
    {
        if(SEND_MESSAGE && isset(self::$template[$type])) {
            $appUrl = 'http://soap.soha.vn/apps/dtd';

            $st = floor(microtime(true)*1000);

            $rowUser = Hapyfish2_Platform_Bll_UidMap::getUser($actor);
            $actor_info = Hapyfish2_Platform_Bll_User::getUser($rowUser['uid']);

            if ($data) {
                $data['actor'] = $actor_info['name'];
            } else {
                $data = array('actor' => $actor_info['name']);
            }

            if ($type == 'INVITE') {
                $invite_param= 'hf_invite=true&hf_inviter=' . $actor . '&hf_st=' . $st;
                $sg = md5($invite_param . APP_KEY . APP_SECRET);
                $appUrl .= '&' . $invite_param . '&hf_sg=' . $sg;

                Hapyfish2_Island_Bll_InviteLog::addInvite($actor, $target, $st, $sg);
                $app_link2 = '<a href="' . $appUrl . '">Chơi</a>';
                $data['app_link2'] = $app_link2;
            } else if ($type == 'GIFT') {
                $gift_param = 'hf_gift=true&hf_sender=' . $actor . '&hf_gift_id=' . $data['gift_id'] . '&hf_st=' . $st;
                $sg = md5($gift_param . APP_KEY . APP_SECRET);
                $appUrl .= '&' . $gift_param . '&hf_sg=' . $sg;

                Hapyfish2_Island_Bll_InviteLog::addSendGift($actor, $target, $data['gift_id'], $st, $sg);
                $app_link2 = '<a href="' . $appUrl . '">Nhận quà</a>';
                $data['app_link2'] = $app_link2;
            } else if ($type == 'REMIND_1') {
                $app_link2 = '<a href="' . $appUrl . '">Thu tiền</a>';
                $data['app_link2'] = $app_link2;
            } else if ($type == 'REMIND_2') {
                $app_link2 = '<a href="' . $appUrl . '">Đón khách</a>';
                $data['app_link2'] = $app_link2;
            } else if ($type == 'moochPlant') {
	            //if ( Bll_Cache_Activity::isSendMessage($target) ) {
	            //    return;
	            //}
                //Bll_Cache_Activity::setSendMessage($target);
                $app_link2 = '<a href="http://soap.soha.vn/apps/dtd">Đảo Thiên Đường</a>';
                $data['app_link2'] = $app_link2;
            }

            $app_link = '<a href="' . $appUrl . '">Đảo Thiên Đường</a>';
            $data['app_link'] = $app_link;
            $tpl = self::$template[$type];
            $body = self::buildTemplate($tpl, $data);

            $context = Hapyfish2_Util_Context::getDefaultInstance();
    		$session_key = $context->get('session_key');
            $taobao = Taobao_Rest::getInstance();
            $taobao->setUser($actor, $session_key);

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
            $appUrl = 'http://i.taobao.com/apps/show.htm?appkey=12029234';

            $rowUser = Hapyfish2_Platform_Bll_UidMap::getUser($actor);
            $actor_info = Hapyfish2_Platform_Bll_User::getUser($rowUser['uid']);

            $data = array('actor' => $actor_info['name']);

            $app_link2 = '<a href="' . $appUrl . '">Nhận quà</a>';
            $data['app_link2'] = $app_link2;

            $app_link = '<a href="' . $appUrl . '">Đảo Thiên Đường</a>';
            $data['app_link'] = $app_link;

            $tpl = self::$template['GIFT'];
            $body = self::buildTemplate($tpl, $data);

            $context = Hapyfish2_Util_Context::getDefaultInstance();
    		$session_key = $context->get('session_key');
            $taobao = Taobao_Rest::getInstance();
            $taobao->setUser($actor, $session_key);

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