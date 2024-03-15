/*!
 * LOADPROMPT jQuery Plugin
 *
 * @version: 0.1
 * @file: jquery-loadprompt.js
 * @author: Jack Wang
 * @email: 826138363@qq.com
 * @license: MIT License
 */
(function ($) {
    /**
     *
     * @param text 加载中提示窗内提示内容
     * @param callback 打开加载提示窗时执行方法
     */
    $.showLoading = function(text, callback) {
        //创建遮罩层
        $('body').append(`<div class="load-backdrop"></div>`);
        //创建加载中效果
        var load_html =
            `
            <div class="load-panel">
                <div class="load-spinner"></div>
                <div class="load-content color-load">${text}</div>
            </div>
            `;

        $('.mui-content').append(load_html);

        callback();
    }

    /**
     *
     * @param text 加载成功提示窗内提示内容
     * @param callback 加载成功后回调方法
     */
    $.loadSuccess = function(text, callback) {
        //创建加载成功效果
        var done_html =
            `
            <svg class="done" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="circle" cx="26" cy="26" r="25" fill="none" />
                <path class="check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
            </svg>
            <div class="load-content color-done">${text}</div>
            `;

        $('.load-panel').html(done_html);

        //延长2秒等待动画结束
        setTimeout(function () {
            //关闭遮罩遮罩层
            $('.load-backdrop').remove();
            //关闭加载窗
            $('.load-panel').remove();

            //执行回调方法
            callback();
        }, 2000);
    }

    /**
     *
     * @param text 加载失败提示窗内提示内容
     * @param callback 加载失败后回调方法
     */
    $.loadError = function(text, callback) {
        //创建加载成功效果
        var error_html =
            `
            <svg class="error" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="circle" cx="26" cy="26" r="25" fill="none" />
                    <path class="line" fill="none" d="M17.36,34.736l17.368-17.472" />
                    <path class="line" fill="none" d="M34.78,34.684L17.309,17.316" />
                </svg>
            <div class="load-content color-error">${text}</div>
            `;

        $('.load-panel').html(error_html);

        //延长2秒等待动画结束
        setTimeout(function () {
            //关闭遮罩遮罩层
            $('.load-backdrop').remove();
            //关闭加载窗
            $('.load-panel').remove();

            //执行回调方法
            callback();
        }, 2000);
    }
}(jQuery));