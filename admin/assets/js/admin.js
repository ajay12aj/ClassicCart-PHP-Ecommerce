/* =====================================================================
   ClassicCart Admin Panel - JavaScript
===================================================================== */
$(function () {

    /* ---------------- Mobile sidebar toggle ---------------- */
    $('#sidebarToggle').on('click', function () {
        $('#sidebar').toggleClass('open');
        if ($('#sidebar').hasClass('open')) {
            $('#sidebarOverlay').show();
        } else {
            $('#sidebarOverlay').hide();
        }
    });
    $('.sidebar-overlay').on('click', function () {
        $('#sidebar').removeClass('open');
        $('#sidebarOverlay').hide();
    });

    $('.sidebar-nav a').on('click', function () {
        if ($(window).width() <= 768) {
            $('#sidebar').removeClass('open');
            $('#sidebarOverlay').hide();
        }
    });

    /* ---------------- Flash auto-dismiss ---------------- */
    $('.flash').each(function () {
        var $el = $(this);
        setTimeout(function () { $el.fadeOut(300); }, 5000);
    });
    $('.flash-close').on('click', function () {
        $(this).closest('.flash').fadeOut(200);
    });

    /* ---------------- Confirmation helpers ---------------- */
    $(document).on('click', '[data-confirm]', function (e) {
        var msg = $(this).data('confirm') || 'Are you sure you want to continue?';
        if (!window.confirm(msg)) {
            e.preventDefault();
        }
    });
    $(document).on('submit', '[data-confirm-form]', function (e) {
        var msg = $(this).data('confirm-form') || 'Are you sure you want to continue?';
        if (!window.confirm(msg)) {
            e.preventDefault();
        }
    });

});