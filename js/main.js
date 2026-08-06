/*  ---------------------------------------------------
    Theme Name: Anime
    Description: Anime video tamplate
    Author: Colorib
    Author URI: https://colorib.com/
    Version: 1.0
    Created: Colorib
---------------------------------------------------------  */

'use strict';

function showMaintenancePopup() {
    if (!window.localStorage.getItem("nbhzvn_domain_maintenance")) {
        var modal = new Modal();
        modal.body = `
            <p>Trang web Nobihaza Vietnam Collection sẽ bảo trì trong ngày 8/8/2026 để chuyển đổi tên miền sang <b>nbhzvn.com</b>.</p>
            <p>Các liên kết thuộc tên miền cũ sẽ được chuyển hướng sang tên miền mới cho đến khi tên miền hết hạn vào ngày 16/8 (hoặc có thể sẽ thêm vài ngày nữa từ nhà cung cấp).</p>
            <p>Nhấn vào nút <b>Tìm hiểu thêm</b> để biết thêm chi tiết.</p>
        `;
        modal.footer = `
            <a href="/bulletin"><button type="button" class="btn btn-primary">Tìm hiểu thêm</button></a>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
        `;
        window.localStorage.setItem("nbhzvn_domain_maintenance", "1");
        modal.show();
    }
}

(function ($) {

    /*------------------
        Preloader
    --------------------*/
    $(window).on('load', function () {
        $(".loader").fadeOut();
        $("#preloder").delay(200).fadeOut("slow");

        /*------------------
            FIlter
        --------------------*/
        $('.filter__controls li').on('click', function () {
            $('.filter__controls li').removeClass('active');
            $(this).addClass('active');
        });
        if ($('.filter__gallery').length > 0) {
            var containerEl = document.querySelector('.filter__gallery');
            var mixer = mixitup(containerEl);
        }
    });

    /*------------------
        Background Set
    --------------------*/
    $('.set-bg').each(function () {
        var bg = $(this).data('setbg');
        $(this).css('background-image', 'url(' + bg + ')');
    });

    /*------------------
		Navigation
	--------------------*/
    $(".mobile-menu").slicknav({
        prependTo: '#mobile-menu-wrap',
        allowParentLinks: true
    });

    var hero_s = $(".hero__slider");
    var autoplayTimeout;
    hero_s.owlCarousel({
        loop: true,
        margin: 0,
        items: 1,
        dots: true,
        nav: true,
        navText: ["<span class='arrow_carrot-left'></span>", "<span class='arrow_carrot-right'></span>"],
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true,
        autoplayTimeout: 10000,
        autoplayHoverPause: false,
        mouseDrag: false
    });

    hero_s.on('click', '.owl-prev, .owl-next', function () {
        hero_s.trigger('stop.owl.autoplay');
        clearTimeout(autoplayTimeout);
        autoplayTimeout = setTimeout(function () {
            hero_s.trigger('play.owl.autoplay', [10000]);
        }, 2000);
    });

    /*------------------
        Scroll To Top
    --------------------*/
    $("#scrollToTopButton").click(function() {
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
     });

    function equalizeCarouselHeights() {
        let maxHeight = 0;
        $(".owl-item").css("height", "auto");
        $(".owl-item").each(function() {
            let thisHeight = $(this).outerHeight();
            if (thisHeight > maxHeight) maxHeight = thisHeight;
        });
        $(".owl-item").css("height", maxHeight + "px");

        showMaintenancePopup();
    }
    $(document).ready(equalizeCarouselHeights);
    $(window).resize(equalizeCarouselHeights);

})(jQuery);