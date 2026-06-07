(function (window, $) {
    'use strict';

    if (!$ || !$.fn || !$.fn.DataTable) {
        window.initAdminDataTable = function () {
            console.warn('DataTables library is not loaded.');
            return null;
        };
        return;
    }

    window.initAdminDataTable = function (selector, options) {
        var tableSelector = selector || '#ordersTable';
        var userOptions = options || {};

        var defaultOptions = {
            paging: true,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [10, 25, 50, 100],
            responsive: true,
            scrollY: '320px',
            scrollCollapse: false,
            info: true,
            ordering: true,
            language: {
                search: '',
                searchPlaceholder: 'Search...',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        };

        var finalOptions = $.extend(true, {}, defaultOptions, userOptions);

        if (!$.fn.dataTable.isDataTable(tableSelector)) {
            return $(tableSelector).DataTable(finalOptions);
        }

        return $(tableSelector).DataTable();
    };
})(window, window.jQuery);
