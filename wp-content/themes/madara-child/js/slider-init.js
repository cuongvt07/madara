jQuery(document).ready(function ($) {
    const widgetSource = $('.mad-widget-source');
    const swiperWrapper = $('.mad-vertical-carousel .swiper-wrapper');

    if (widgetSource.length && swiperWrapper.length) {
        const images = widgetSource.find('.slider__thumb_item img');
        const links = widgetSource.find('.slider__thumb_item a');

        console.log('Found ' + images.length + ' images in widget');

        if (images.length > 0) {
            // ============================================
            // AUTO-DUPLICATE SLIDES CHO LOOP MODE
            // ============================================
            const SLIDES_PER_VIEW = 5;
            const MIN_SLIDES_FOR_LOOP = SLIDES_PER_VIEW * 3;

            let slidesData = [];

            images.each(function (index) {
                const img = $(this);
                const link = links.eq(index);
                slidesData.push({
                    src: img.attr('src'),
                    href: link.attr('href')
                });
            });

            const originalCount = slidesData.length;
            if (originalCount < MIN_SLIDES_FOR_LOOP) {
                const duplications = Math.ceil(MIN_SLIDES_FOR_LOOP / originalCount);
                const duplicatedData = [];
                for (let i = 0; i < duplications; i++) {
                    duplicatedData.push(...slidesData);
                }
                slidesData = duplicatedData;
                console.log('🔄 Duplicated: ' + originalCount + ' → ' + slidesData.length);
            }

            // ============================================
            // TẠO SLIDES
            // ============================================
            slidesData.forEach(function (slideData) {
                const slide = $('<div class="swiper-slide"></div>');
                const anchor = $('<a href="' + slideData.href + '" class="mad-slide-link"></a>');
                const newImg = $('<img src="' + slideData.src + '" alt="" draggable="false">');

                anchor.append(newImg);
                slide.append(anchor);
                swiperWrapper.append(slide);
            });

            // ============================================
            // PREVENT LINK BEHAVIORS
            // ============================================
            $(document).on('mousedown', '.mad-vertical-carousel .mad-slide-link', function (e) {
                if (e.which === 1) $(this).data('mousedown-time', Date.now());
            });

            $(document).on('contextmenu dragstart', '.mad-vertical-carousel .mad-slide-link, .mad-vertical-carousel img', function (e) {
                e.preventDefault();
                return false;
            });

            const totalSlides = slidesData.length;

            // ============================================
            // KHỞI TẠO SWIPER - 5 SLIDES CÂN ĐỐI
            // ============================================
            const verticalSwiper = new Swiper('.mad-vertical-carousel', {
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: 'auto',  // AUTO - dựa vào CSS width

                // Khoảng cách giữa slides
                spaceBetween: 15,

                // Infinite loop
                loop: true,
                loopedSlides: 5,  // Chỉ cần 5 slides cho loop

                // Coverflow effect - depth thấp để không bị lệch
                coverflowEffect: {
                    rotate: 0,
                    stretch: 0,
                    depth: 80,      // Giảm depth
                    modifier: 1,    // Giảm modifier
                    slideShadows: false
                },

                // BREAKPOINTS - Chỉ mobile mới overlap
                breakpoints: {
                    // Mobile (< 768px): slides chồng lên nhau + buffer lớn
                    0: {
                        spaceBetween: -60,  // Âm = chồng lên nhau
                        loopedSlides: 10,   // Buffer lớn để không bị trắng
                        loopAdditionalSlides: 10,
                        speed: 300,         // Nhanh hơn
                        coverflowEffect: {
                            rotate: 0,
                            stretch: 0,
                            depth: 100,
                            modifier: 1,
                            slideShadows: false
                        }
                    },

                    // PC (>= 768px): giữ nguyên spacing
                    768: {
                        spaceBetween: 15,   // Dương = thoáng
                        coverflowEffect: {
                            rotate: 0,
                            stretch: 0,
                            depth: 80,
                            modifier: 1,
                            slideShadows: false
                        }
                    }
                },

                navigation: {
                    nextEl: '.mad-vertical-carousel .swiper-button-next',
                    prevEl: '.mad-vertical-carousel .swiper-button-prev',
                },

                // ============================================
                // DRAG-TO-SCROLL
                // ============================================
                simulateTouch: true,
                allowTouchMove: true,
                touchRatio: 1,
                touchAngle: 45,
                threshold: 5,

                // Ngăn tooltip
                touchStartPreventDefault: true,
                touchStartForcePreventDefault: true,
                touchMoveStopPropagation: false,

                // Ngăn click khi drag
                preventClicks: true,
                preventClicksPropagation: true,
                slideToClickedSlide: false,

                resistance: true,
                resistanceRatio: 0.85,
                shortSwipes: true,
                longSwipes: true,
                longSwipesRatio: 0.5,
                longSwipesMs: 300,
                followFinger: true,
                freeMode: false,
                centeredSlidesBounds: true,

                speed: 500,
                roundLengths: true,

                keyboard: {
                    enabled: true,
                    onlyInViewport: true,
                },

                mousewheel: {
                    forceToAxis: true,
                    sensitivity: 1,
                    releaseOnEdges: false,
                },

                watchOverflow: false,
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                observer: true,
                observeParents: true,
                observeSlideChildren: true,
                autoHeight: false,
                cssMode: false,
                initialSlide: 0,  // Bắt đầu từ 0, centeredSlides sẽ căn giữa

                runCallbacksOnInit: true,
                slideToClickedSlide: true,  // Click vào slide = active

                // ============================================
                // EVENT HANDLERS
                // ============================================
                on: {
                    init: function () {
                        console.log('✅ Swiper initialized');
                        console.log('  • Total slides:', this.slides.length);
                        console.log('  • Active index:', this.activeIndex);
                        console.log('  • Real index:', this.realIndex);

                        // Force update classes sau 100ms
                        var swiper = this;
                        setTimeout(function () {
                            swiper.update();
                            swiper.updateSlides();
                            swiper.updateSlidesClasses();
                        }, 100);
                    },


                    slideChange: function () {
                        console.log('📍 Slide:', this.activeIndex);
                    },

                    touchStart: function (swiper, event) {
                        swiper.isDragging = false;
                        swiper.touchStartTime = Date.now();

                        if (event.target.tagName === 'A' || $(event.target).closest('a').length) {
                            event.preventDefault();
                        }
                    },

                    touchMove: function (swiper, event) {
                        swiper.isDragging = true;
                        event.preventDefault();
                    },

                    touchEnd: function (swiper, event) {
                        if (swiper.isDragging) {
                            event.preventDefault();
                            event.stopPropagation();
                            event.stopImmediatePropagation();
                        }

                        setTimeout(function () {
                            if (swiper.snapIndex !== undefined) {
                                swiper.slideTo(swiper.snapIndex, swiper.params.speed);
                            }
                        }, 50);

                        setTimeout(function () {
                            swiper.isDragging = false;
                        }, 150);
                    },

                    click: function (swiper, event) {
                        const touchDuration = Date.now() - swiper.touchStartTime;

                        if (touchDuration > 200 || swiper.isDragging) {
                            event.preventDefault();
                            event.stopPropagation();
                            event.stopImmediatePropagation();
                            return false;
                        }
                    },

                    transitionEnd: function () {
                        this.update();
                    }
                }
            });

            console.log('✅ Overlap carousel ready - slides overlap by 80px');

            // Prevent link clicks during drag
            $(document).on('click', '.mad-vertical-carousel .mad-slide-link', function (e) {
                const $link = $(this);
                const mousedownTime = $link.data('mousedown-time');
                const duration = Date.now() - (mousedownTime || 0);

                if (verticalSwiper.isDragging || duration > 200) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            });

            // Remove title
            setTimeout(function () {
                $('.mad-vertical-carousel .mad-slide-link').removeAttr('title');
            }, 100);

            // Final update
            setTimeout(function () {
                verticalSwiper.update();
                verticalSwiper.updateSlides();
                verticalSwiper.updateProgress();
                verticalSwiper.updateSlidesClasses();
                console.log('✅ All updates completed');
            }, 100);
        }
    }
});