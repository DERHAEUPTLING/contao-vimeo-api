/*! modernizr 3.3.1 (Custom Build) | MIT *
 * http://modernizr.com/download/?-touchevents !*/
!function(e,n,t){function o(e,n){return typeof e===n}function s(){var e,n,t,s,i,a,r;for(var l in f)if(f.hasOwnProperty(l)){if(e=[],n=f[l],n.name&&(e.push(n.name.toLowerCase()),n.options&&n.options.aliases&&n.options.aliases.length))for(t=0;t<n.options.aliases.length;t++)e.push(n.options.aliases[t].toLowerCase());for(s=o(n.fn,"function")?n.fn():n.fn,i=0;i<e.length;i++)a=e[i],r=a.split("."),1===r.length?Modernizr[r[0]]=s:(!Modernizr[r[0]]||Modernizr[r[0]]instanceof Boolean||(Modernizr[r[0]]=new Boolean(Modernizr[r[0]])),Modernizr[r[0]][r[1]]=s),d.push((s?"":"no-")+r.join("-"))}}function i(){return"function"!=typeof n.createElement?n.createElement(arguments[0]):p?n.createElementNS.call(n,"http://www.w3.org/2000/svg",arguments[0]):n.createElement.apply(n,arguments)}function a(){var e=n.body;return e||(e=i(p?"svg":"body"),e.fake=!0),e}function r(e,t,o,s){var r,f,l,d,u="modernizr",p=i("div"),h=a();if(parseInt(o,10))for(;o--;)l=i("div"),l.id=s?s[o]:u+(o+1),p.appendChild(l);return r=i("style"),r.type="text/css",r.id="s"+u,(h.fake?h:p).appendChild(r),h.appendChild(p),r.styleSheet?r.styleSheet.cssText=e:r.appendChild(n.createTextNode(e)),p.id=u,h.fake&&(h.style.background="",h.style.overflow="hidden",d=c.style.overflow,c.style.overflow="hidden",c.appendChild(h)),f=t(p,e),h.fake?(h.parentNode.removeChild(h),c.style.overflow=d,c.offsetHeight):p.parentNode.removeChild(p),!!f}var f=[],l={_version:"3.3.1",_config:{classPrefix:"",enableClasses:!0,enableJSClass:!0,usePrefixes:!0},_q:[],on:function(e,n){var t=this;setTimeout(function(){n(t[e])},0)},addTest:function(e,n,t){f.push({name:e,fn:n,options:t})},addAsyncTest:function(e){f.push({name:null,fn:e})}},Modernizr=function(){};Modernizr.prototype=l,Modernizr=new Modernizr;var d=[],u=l._config.usePrefixes?" -webkit- -moz- -o- -ms- ".split(" "):[];l._prefixes=u;var c=n.documentElement,p="svg"===c.nodeName.toLowerCase(),h=l.testStyles=r;Modernizr.addTest("touchevents",function(){var t;if("ontouchstart"in e||e.DocumentTouch&&n instanceof DocumentTouch)t=!0;else{var o=["@media (",u.join("touch-enabled),("),"heartz",")","{#modernizr{top:9px;position:absolute}}"].join("");h(o,function(e){t=9===e.offsetTop})}return t}),s(),delete l.addTest,delete l.addAsyncTest;for(var m=0;m<Modernizr._q.length;m++)Modernizr._q[m]();e.Modernizr=Modernizr}(window,document);
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
            if (video.data('vimeo-lightbox') && !Modernizr.touchevents) {
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

                            // Pause the players and activate the video
                            pausePlayers();
                            video.addClass('active');

                            // Autoplay video on non-mobile devices
                            if (!Modernizr.touchevents) {
                                player.api('play');
                            }
                        });
                    } else {
                        // Pause the players and activate the video
                        pausePlayers();
                        video.addClass('active');

                        // Autoplay video on non-mobile devices
                        if (!Modernizr.touchevents) {
                            player.api('play');
                        }
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