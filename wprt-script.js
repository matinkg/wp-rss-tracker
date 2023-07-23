(function ($) {
    $(document).ready(function () {
        if ($('.wprt-post').length > 0) {
            $('.wprt-post a').attr({
                target: '_blank',
                rel: 'nofollow'
            });
        }
    });
})(jQuery);