$(function () {
    'use strict';

    // Select all tables with the class "custom-datatable"
    var dt_tables = $('.custom-datatable');

    if (dt_tables.length) {
        dt_tables.each(function () {
            var dt_instance = $(this).DataTable({
                responsive: true, // Enables responsiveness for all tables
                paging: true, // Enables pagination
                ordering: true, // Enables sorting
                info: true, // Displays table info
                autoWidth: false, // Ensures proper width handling
                dom:
                    '<"row mx-6 d-flex justify-content-between"' +
                    '<"col-md-6 d-flex align-items-center"lB>' +
                    '<"col-md-6 d-flex justify-content-end"f>' +
                    '>t' +
                    '<"row mx-4"' +
                    '<"col-sm-12 col-md-6 text-start"i>' +
                    '<"col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center"p>' +
                    '>',
                language: {
                    sLengthMenu: 'Show _MENU_',
                    search: '',
                    searchPlaceholder: 'Search...',
                    paginate: {
                        next: '<i class="ti ti-chevron-right ti-sm"></i>',
                        previous: '<i class="ti ti-chevron-left ti-sm"></i>'
                    }
                },
                buttons: [
                    {
                        extend: 'collection',
                        className: 'btn btn-label-secondary dropdown-toggle',
                        text: '<i class="ti ti-upload ti-xs me-2"></i>Export',
                        buttons: [
                            {
                                extend: 'pdf',
                                text: '<i class="ti ti-file-description me-2"></i>PDF',
                                className: 'dropdown-item'
                            },
                            {
                                extend: 'excel',
                                text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel',
                                className: 'dropdown-item'
                            }
                        ]
                    }
                ],
                // Responsive modal for smaller screens
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function (row) {
                                return 'Details';
                            }
                        }),
                        type: 'column',
                        renderer: function (api, rowIdx, columns) {
                            var data = $.map(columns, function (col, i) {
                                return col.title !== ''
                                    ? '<tr data-dt-row="' +
                                    col.rowIndex +
                                    '" data-dt-column="' +
                                    col.columnIndex +
                                    '">' +
                                    '<td>' +
                                    col.title +
                                    ':' +
                                    '</td> ' +
                                    '<td>' +
                                    col.data +
                                    '</td>' +
                                    '</tr>'
                                    : '';
                            }).join('');

                            return data ? $('<table class="table"/><tbody />').append(data) : false;
                        }
                    }
                }
            });

            // Add table title if needed
            // $(this).closest('.card').find('.table-head-label').html('<h5 class="card-title mb-0">Data Table</h5>');

            // On each DataTable draw, initialize tooltips
            $(this).on('draw.dt', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        boundary: document.body
                    });
                });
            });

            // Delete Record Functionality
            $(this).find('tbody').on('click', '.delete-record', function () {
                dt_instance.row($(this).parents('tr')).remove().draw();
            });
        });
    }

    // Adjust form styles for DataTables filters
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
});
