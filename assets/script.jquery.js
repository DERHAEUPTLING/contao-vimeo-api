;(function ($) {
    $(document).ready(function () {
        var players = [];
        var lightboxIframes = [];
        var resizeThreshold = 10; // in %

        // Pause all players
        function pausePlayers(excludeId) {
            for (var key in players) {
                if (excludeId && key == excludeId) {
                    continue;
                }

                players[key].api('pause');
            }
        }

        // Calculate the player size relative to window size
        function calculateSize(element) {
            var width = parseInt(element.data('vimeo-width'));
            var height = parseInt(element.data('vimeo-height'));
            var ratio = width / height;
            var windowHeight = $(window).height() * (1 - (resizeThreshold / 100));
            var windowWidth = $(window).width() * (1 - (resizeThreshold / 100));

            // Video width exceeds window width
            if (width >= windowWidth) {
                width = windowWidth;
                height = windowWidth / ratio;
            }

            // Video height exceeds window height
            if (height >= windowHeight) {
                height = windowHeight;
                width = windowHeight * ratio;
            }

            return {'width': Math.round(width), 'height': Math.round(height)};
        }

        // Create the iframe
        function createIframe(video) {
            var videoId = video.data('vimeo-video');
            var elementId = 'vimeo-video-' + videoId;

            return $('<iframe>', {
                'src': 'https://player.vimeo.com/video/' + videoId + '?api=1&amp;player_id=' + elementId,
                'id': elementId,
                'width': parseInt(video.data('vimeo-width')),
                'height': parseInt(video.data('vimeo-height')),
                'data-vimeo-width': parseInt(video.data('vimeo-width')),
                'data-vimeo-height': parseInt(video.data('vimeo-height')),
                'frameborder': 0,
                'webkitallowfullscreen': true,
                'mozallowfullscreen': true,
                'allowfullscreen': true
            });
        }

        // Resize the irfame and lightbox
        function resizeIframeAndLightbox(element) {
            var size = calculateSize(element);

            element.css('width', size.width);
            element.css('height', size.height);

            // Apply dimensions to the colorbox
            $.colorbox.resize({innerWidth: size.width, innerHeight: size.height});
        }

        $('[data-vimeo-video]').each(function () {
            var video = $(this);
            var embed = video.find('.embed');
            var trigger = video.find('.trigger');
            var iframe, player;

            // Open video in lightbox
            if (video.data('vimeo-lightbox') && !$('body').hasClass('mobile')) {
                trigger.colorbox({
                    'className': 'vimeo-video-lightbox',
                    'current': 'video {current} of {total}',
                    'href': '#' + video.attr('id') + ' .embed',
                    'inline': true,
                    'loop': false,
                    'rel': 'vimeo-video',
                    'onComplete': function () {
                        // If the iframe already exists there is no need to do anything with it
                        if (iframe) {
                            return;
                        }

                        iframe = createIframe(video).appendTo(embed).hide();

                        // Store the iframe in the lightbox iframes for future reference
                        lightboxIframes.push(iframe);

                        // Initialize the players when iframe is loaded
                        iframe.on('load', function () {
                            var videoId = video.data('vimeo-video');

                            // Create the player
                            if (!players[videoId]) {
                                player = $f(iframe[0]);
                                players[videoId] = player;

                                player.addEvent('ready', function () {
                                    // Play the video when opened in lightbox
                                    if (iframe.parents('#colorbox').length) {
                                        pausePlayers(videoId);
                                        player.api('play');

                                        // Autoplay the next video after this one finishes
                                        if (video.data('vimeo-lightbox-autoplay')) {
                                            player.addEvent('finish', function () {
                                                $.colorbox.next();
                                            });
                                        }
                                    }
                                });
                            }

                            // Resize the iframe only if it's in colorbox
                            if (iframe.parents('#colorbox').length) {
                                resizeIframeAndLightbox(iframe);
                                iframe.show();
                            }
                        });
                    }
                });
            } else {
                // Play the video in place
                trigger.on('click', function (e) {
                    e.preventDefault();

                    // Create the iframe if it does not exist
                    if (!iframe) {
                        iframe = createIframe(video).appendTo(embed);

                        // Initialize the player when iframe is loaded
                        iframe.on('load', function () {
                            player = $f(iframe[0]);
                            players.push(player);

                            // Play!
                            pausePlayers();
                            video.addClass('active');
                            player.api('play');
                        });
                    } else {
                        // Play!
                        pausePlayers();
                        video.addClass('active');
                        player.api('play');
                    }
                });
            }
        });

        // Resize the colorbox on window resize
        $(window).on('resize', function () {
            if (lightboxIframes.length) {
                for (var i = 0; i < lightboxIframes.length; i++) {
                    var iframe = lightboxIframes[i];

                    // Resize the colorbox for the currently displayed iframe in it
                    if (iframe.parents('#colorbox').length) {
                        resizeIframeAndLightbox(iframe);
                    }
                }
            }
        });
    });
})(jQuery);