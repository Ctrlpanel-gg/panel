<table id="datatable" class="table table-striped">
    <thead>
        <tr>
            <th width="20"></th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('User') }}</th>
            <th>{{ __('Server id') }}</th>
            <th>{{ __('Product') }}</th>
            <th>{{ __('Suspended at') }}</th>
            <th>{{ __('Created at') }}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const table = $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ config('SETTINGS::LOCALE:DATATABLES') }}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{ route('admin.servers.datatable') }}{{ $filter ?? '' }}",
            order: [
                [6, "desc"]
            ],
            columns: [{
                    data: 'status',
                    name: 'servers.suspended',
                    sortable: false
                },
                {
                    data: 'name'
                },
                {
                    data: 'user',
                    name: 'user.name',
                },
                {
                    data: 'identifier'
                },
                {
                    data: 'resources',
                    name: 'product.name',
                    sortable: false
                },
                {
                    data: 'suspended'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'actions',
                    sortable: false
                },
            ],
            fnDrawCallback: function(oSettings) {
                $('[data-toggle="popover"]').popover();
            }
        });

        $(document).on('click', '.delete-server-btn', function() {
            const serverId = $(this).data('server-id');
            const action = $(this).data('action');

            let swalOptions = {
                title: "{{ __('Are you sure?') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: "{{ __('Yes, delete it!') }}",
                cancelButtonText: "{{ __('Cancel') }}",
                reverseButtons: true,
                html: `
                    <p>{{ __('Do you want to delete this server?') }}</p>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="refund-credits">
                        <label class="custom-control-label" for="refund-credits">
                            {{ __('Refund credits?') }}
                            <i data-toggle="popover" data-trigger="hover" data-placement="top" data-content="{{ __('Only the price for one billing period will be refunded.') }}" class="fas fa-info-circle text-white"></i>
                        </label>
                    </div>
                `,
                didOpen: () => {
                    $('[data-toggle="popover"]').popover();
                }
            };

            Swal.fire(swalOptions).then((result) => {
                if (result.isConfirmed) {
                    const refund = document.getElementById('refund-credits')?.checked;
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = action;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    if (refund) {
                        const refundInput = document.createElement('input');
                        refundInput.type = 'hidden';
                        refundInput.name = 'refund';
                        refundInput.value = '1';
                        form.appendChild(refundInput);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
</script>
