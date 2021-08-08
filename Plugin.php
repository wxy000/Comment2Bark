<?php
/**
 * 推送评论通知
 * 
 * @package Comment2Bark
 * @author wxy
 * @version 1.0.3
 * @link http://118.178.241.13
 */
class Comment2Bark_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
    
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('Comment2Bark_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array('Comment2Bark_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('Comment2Bark_Plugin', 'sc_send');
        
        return _t('请配置此插件的ip或域名地址, 以使您的bark推送生效');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $bark = new Typecho_Widget_Helper_Form_Element_Text('bark', NULL, NULL, _t('Bark服务地址'), _t('请在此处填入bark的服务地址,例如https://api.day.app/2432432432ewqeqeqw   注意地址后面的/不需要填'));
        $form->addInput($bark->addRule('required', _t('您必须填写一个正确的bark服务地址')));
        $title = new Typecho_Widget_Helper_Form_Element_Text('title', NULL, '博客', _t('推送信息的标题'), _t('在此输入推送信息的标题'));
        $form->addInput($title);
        $groupname = new Typecho_Widget_Helper_Form_Element_Text('groupname', NULL, '博客', _t('分组名称'), _t('输入分组名称,即在app的历史消息中按分组保存信息'));
        $form->addInput($groupname);
        $sound = new Typecho_Widget_Helper_Form_Element_Text('sound', NULL, NULL, _t('推送铃声'), _t('具体有哪些铃声可到app中查看'));
        $form->addInput($sound);
        $is_archive = new Typecho_Widget_Helper_Form_Element_Radio('is_archive', ['0' => _t('否'), '1' => _t('是')], '1', _t('是否自动保存通知消息'), _t('这里的设置高于app中的设置,若选择保存,则app端会将推送消息保存'));
        $form->addInput($is_archive);
        $author = new Typecho_Widget_Helper_Form_Element_Text('author', NULL, NULL, _t('作者名称'), _t('评论推送排除作者,若不填则全部都会推送'));
        $form->addInput($author);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 微信推送
     * 
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @return void
     */
    public static function sc_send($comment, $post)
    {
        $options = Typecho_Widget::widget('Widget_Options');

        $bark = $options->plugin('Comment2Bark')->bark;
        $title = $options->plugin('Comment2Bark')->title;
        $groupname = $options->plugin('Comment2Bark')->groupname;
        $sound = $options->plugin('Comment2Bark')->sound;
        $is_archive = $options->plugin('Comment2Bark')->is_archive;
        $author = $options->plugin('Comment2Bark')->author;
        
        if($comment['author']==trim($author)){
            return $comment;
        }

        $desp = "**".$comment['author']."** 在「".$post->title."」中说到: > ".$comment['text'];
        $desp = urlencode($desp);
        $desp = $desp."?url=".$post->permalink;
        
        $url = '';
        if(trim($title)!=''){
            $url = $bark.'/'.$title.'/'.$desp;
        }else {
            $url = $bark.'/'.$desp;
        }
        
        #分组
        if(trim($groupname)!=''){
            $url = $url.'&group='.$groupname;
        }
        #铃声
        if(trim($sound)!=''){
            $url = $url.'&sound='.$sound;
        }
        #是否自动保存通知消息
        $url = $url.'&isArchive='.$is_archive;

        /*$postdata = http_build_query(
            array(
                'text' => $text,
                'desp' => $desp
                )
            );

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
                )
            );
        $context  = stream_context_create($opts);
        $result = file_get_contents('http://sc.ftqq.com/'.$sckey.'.send', false, $context);*/
        $result = file_get_contents($url);
        return  $comment;
    }
}
