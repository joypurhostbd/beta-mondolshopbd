/**
 * Admin CRUD Shared Utility Library
 * MondolShopBD Modular Monolith
 */

(function ($) {
    'use strict';

    window.AdminCrud = {
        /**
         * Initialize standard DataTable
         */
        initDataTable: function (selector, options) {
            if ($.fn.DataTable && $(selector).length) {
                return $(selector).DataTable($.extend({
                    responsive: true,
                    pageLength: 25,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search records..."
                    }
                }, options || {}));
            }
        },

        /**
         * Confirm delete with SweetAlert2
         */
        confirmDelete: function (formSelector, title, text) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title || 'Are you sure?',
                    text: text || "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $(formSelector).submit();
                    }
                });
            } else if (confirm(title || 'Are you sure you want to delete this?')) {
                $(formSelector).submit();
            }
        },

        /**
         * AJAX status toggle handler
         */
        toggleStatus: function (url, id, status, callback) {
            var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
            return $.ajax({
                url: url,
                type: 'POST',
                data: {
                    hidden_id: id,
                    status: status,
                    _token: token
                },
                success: function (response) {
                    if (typeof callback === 'function') {
                        callback(response);
                    } else {
                        location.reload();
                    }
                },
                error: function (xhr) {
                    console.error('Status toggle failed', xhr);
                }
            });
        },

        /**
         * Initialize Select2 dropdown
         */
        initSelect2: function (selector, options) {
            if ($.fn.select2 && $(selector).length) {
                return $(selector).select2($.extend({
                    width: '100%'
                }, options || {}));
            }
        }
    };
})(jQuery);