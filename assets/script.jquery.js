;(function ($) {
    $(document).ready(function () {
        var players = [];

        function pausePlayers() {
            for (var i = 0; i < players.length; i++) {
                players[i].api('pause');
            }
        }

        $('[data-vimeo-video]').each(function () {
            var video = $(this);
            var trigger = video.find('.trigger');
            var iframe = video.find('iframe');
            var player = $f(iframe[0]);

            players.push(player);

            if (video.data('vimeo-lightbox')) {
                player.addEvent('ready', function () {
                    pausePlayers();

                    // Play the video when opened in lightbox
                    if (iframe.parents('#colorbox').length) {
                        player.api('play');

                        // Autoplay the next video after this one finishes
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
                    pausePlayers();
                    video.addClass('active');
                    player.api('play');
                });
            }
        });
    });
})(jQuery);