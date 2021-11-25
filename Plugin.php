<?php

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 添加一个短代码，可以添加一个“点击复制”的按钮
 *
 * @package CopyShortCode
 * @author Echo
 * @version 0.9.0
 * @link https://github.com/Uncle-Antonio/CopyShortCode
 */
class CopyShortCode_Plugin implements Typecho_Plugin_Interface
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
        Typecho_Plugin::factory('Widget_Archive')->footer = array('CopyShortCode_Plugin', 'footer');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('CopyShortCode_Plugin','editor');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('CopyShortCode_Plugin','parselable');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('CopyShortCode_Plugin','parselable');
    }
    
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function editor()
    {
?>
<script type="text/javascript">
    $(window).load(function (event){
        $('#wmd-button-row').append('<li class="wmd-button" id="wmd-copy-button" style="" title="插入复制按钮"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAwUExURQAAAAAAAExpcQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADpSnb0AAAAQdFJOUx/AACWE3oBdCxaRQdOvMp7x9sr4AAAAdklEQVQY03XPyQ7DIAxFUT8zeAgh//+3NQ6t2PTsfGVZQMxsd0tjxkAx97tQcDitIGidl1rFlckgXjMIplelAupvsAdj0BFY6ULZQddVY/oFQXiU/m/MHuTYmDWcYdtB3qcHga1gjjQGJJ9O5Uromp9rX7fFmQ8f0QP+0FkGnAAAAABJRU5ErkJggg=="</li>')
    })
    function inserContentToTextArea(myField,textContent,modelId) {
    $(modelId).remove();
    if (document.selection) {//IE浏览器
        myField.focus();
        var sel = document.selection.createRange();
        sel.text = textContent;
        myField.focus();
    } else if (myField.selectionStart || myField.selectionStart == '0') {
        //FireFox、Chrome
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        var cursorPos = startPos;
        myField.value = myField.value.substring(0, startPos)
            + textContent
            + myField.value.substring(endPos, myField.value.length);
        cursorPos += textContent.length;

        myField.selectionStart = cursorPos;
        myField.selectionEnd = cursorPos;
        myField.focus();
    }
    else{//其他环境
        myField.value += textContent;
        myField.focus();
    }

    //开启粘贴上传图片

}
    $(document).on('click', '#wmd-copy-button', function() {//复制按钮
        textContent = '[cp s="显示文本" t="复制文本"]';
        myField = document.getElementById('text');
        inserContentToTextArea(myField,textContent);
    });
</script>
<?php
    }
    

    /**
     * 内容标签替换
     * 
     * @param string $content
     * @return string
     */
    public static function parselable($content, $widget, $lastResult)
    {
        $content = empty($lastResult) ? $content : $lastResult;
        if ($widget instanceof Widget_Archive) {
            // 没有标签直接返回
            if ( false === strpos( $content, '[' ) ) {
                return $content;
            }
            $tags = self::parseTag($content, 'cp');
            foreach($tags as $tag) {
                //$content .= $tag[0];
                $content = self::parseAndReplaceTag($content, $tag[0]);
            }
        }
        return $content;
    }
    
    private static function parseAndReplaceTag ($content, $tag) {
        // (\w+)=(?:['"]([^\["\']+?)['"]|(\w+))
        $regex = "/(\w+)=(?:['\"]([^\[\"\']+?)['\"]|(\w+))/";
        $match = array();
        $atts = array();
        //$content .= $tag;
        preg_match_all($regex, $tag, $match, PREG_SET_ORDER);
        foreach($match as $item) {
          if(true === isset($item[3])) {
            $atts[$item[1]] = $item[3];
          }
          else {
            $atts[$item[1]] = $item[2];
          }
        }
        if(isset($atts['skip'])) {
            return $content;
        }
        $show = $atts['s'];
        $copy = $atts['t'];
        $regexTag = sprintf('/%s/', preg_quote($tag, '/'));
        $replace_with = '';
        $replace_with .= '<button class="copy-btn" data-clipboard-text="'
        .$copy.'">'.$show.'</button>';
        $content = preg_replace($regexTag, $replace_with, $content, 1);
        return $content;
    }
    
    private static function parseTag($content, $tagnames = null ) {
        $regex = "/\[{$tagnames}[^\]]*?\]/";
        $match = array();
        preg_match_all($regex, $content, $match, PREG_SET_ORDER);
        return $match;
    }
    
    public static function footerjs(){
        $prefix = Typecho_Common::url('CopyShortCode', Helper::options()->pluginUrl);
        echo '<script src="' . $prefix . '/jquery.githubRepoWidget.min.js"></script>';
    }

    public static function footer(){
    $prefix = Typecho_Common::url('CopyShortCode', Helper::options()->pluginUrl);
    echo '<link rel="stylesheet" href="' . $prefix . '/copy.min.css">';
    echo '<script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>';
    echo '<script src="https://cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>';
?>
<script type="text/javascript">
$.extend({
        message: function (a) {
            var b = {
                title: "",
                message: " 操作成功",
                time: "3000",
                type: "success",
                showClose: !0,
                autoClose: !0,
                onClose: function () {}
            };
            "string" == typeof a && (b.message = a), "object" == typeof a && (b = $.extend({}, b, a));
            var c, d, e, f = b.showClose ? '<div class="c-message--close">×</div>' : "",
                g = "" !== b.title ? '<h2 class="c-message__title">' + b.title + "</h2>" : "",
                h = '<div class="c-message animated animated-lento slideInRight"><i class=" c-message--icon c-message--' + b.type + '"></i><div class="el-notification__group">' + g + '<div class="el-notification__content">' + b.message + "</div>" + f + "</div></div>",
                i = $("body"),
                j = $(h);
            d = function () {
                j.addClass("slideOutRight"), j.one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function () {
                    e()
                })
            }, e = function () {
                j.remove(), b.onClose(b), clearTimeout(c)
            }, $(".c-message").remove(), i.append(j), j.one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function () {
                j.removeClass("messageFadeInDown")
            }), i.on("click", ".c-message--close", function (a) {
                d()
            }), b.autoClose && (c = setTimeout(function () {
                d()
            }, b.time))
        }
    });
var clipboard = new ClipboardJS('.copy-btn');
$(document).on('click','.copy-btn',function(){
    $.message({
        title: "复制成功",
        type: "warning",
        autoHide: !1,
        time: "2000"
    })
})
</script>
<?php
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
        /** 样式表 */
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
}
