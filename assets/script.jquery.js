;(function ($) {
    $(document).ready(function () {
        $('[data-vimeo-video]').each(function () {
            var video = $(this);
            var trigger = video.find('.trigger');
            var iframe = video.find('iframe');
            var player = $f(iframe[0]);

            if (video.data('vimeo-lightbox')) {
                player.addEvent('ready', function () {
                    if (iframe.parents('#colorbox').length) {
                        player.api('play');

                        if (video.data('vimeo-lightbox-autoplay')) {
                            player.addEvent('finish', function () {
                                $.colorbox.next();
                            });
                        }
                    }
                });

                trigger.colorbox({
                    'className': 'vimeo-video-lightbox',
                    'current': 'video {current} of {total}',
                    'inline': true,
                    'href': '#' + iframe.attr('id'),
                    'loop': false,
                    'rel': 'vimeo-video',
                    'innerHeight': video.data('vimeo-lightbox-height'),
                    'innerWidth': video.data('vimeo-lightbox-width')
                });
            } else {
                trigger.on('click', function (e) {
                    e.preventDefault();
                    video.addClass('active');
                    player.api('play');
                });
            }
        });
    });
})(jQuery);