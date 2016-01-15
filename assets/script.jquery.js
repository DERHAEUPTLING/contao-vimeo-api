;(function ($) {
    $(document).ready(function () {
        $('[data-vimeo-id]').each(function () {
            var video = $(this);
            var trigger = video.find('.trigger');

            var iframe = $('<iframe>', {
                'src': 'https://player.vimeo.com/video/' + video.data('vimeo-id') + '?autoplay=1',
                'width': '100%',
                'height': '100%',
                'frameborder': 0,
                'webkitallowfullscreen': true,
                'mozallowfullscreen': true,
                'allowfullscreen':  true
            });

            if (video.data('vimeo-lightbox')) {
                trigger.colorbox({
                    'className': 'vimeo-video-lightbox',
                    'current': 'video {current} of {total}',
                    'html': $($('<div></div>').html(iframe.clone())).html(),
                    'loop': false,
                    'maxWidth': '95%',
                    'maxHeight': '95%',
                    'rel': 'vimeo-video'
                });
            } else {
                var embed = $('<div>', {'class': 'embed'});

                trigger.on('click', function (e) {
                    e.preventDefault();
                    embed.append(iframe).appendTo(video);
                    video.addClass('active');
                });
            }
        });
    });
})(jQuery);