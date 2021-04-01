window.addEventListener("load", function() {
    new ClipboardJS("#copy-button");

    toastr.options = {
        closeButton: false,
        debug: false,
        newestOnTop: false,
        progressBar: true,
        positionClass: "toast-top-right",
        preventDuplicates: false,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "3000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    };

    $("textarea[maxlength]").maxlength({
        alwaysShow: true,
        threshold: 10,
        placement: "centered-right",
        warningClass: "badge badge-success",
        limitReachedClass: "badge badge-danger"
    });

    $("input[maxlength]").maxlength({
        alwaysShow: true,
        threshold: 10,
        placement: "centered-right",
        warningClass: "badge badge-success",
        limitReachedClass: "badge badge-danger"
    });
});
