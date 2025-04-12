<div class="glass-panel">
    <div class="p-6 border-b border-zinc-800/50">
        <div class="flex justify-between items-center">
            <h5 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-server mr-2 text-zinc-400"></i>
                {{ __('Server List') }}
            </h5>
        </div>
    </div>
    <div class="p-6">
        <table id="datatable" class="w-full">
            <thead>
                <tr class="text-left text-zinc-400">
                    <th class="px-2 py-3" width="20"></th>
                    <th class="px-2 py-3">{{ __('Name') }}</th>
                    <th class="px-2 py-3">{{ __('User') }}</th>
                    <th class="px-2 py-3">{{ __('Server id') }}</th>
                    <th class="px-2 py-3">{{ __('Product') }}</th>
                    <th class="px-2 py-3">{{ __('Suspended at') }}</th>
                    <th class="px-2 py-3">{{ __('Created at') }}</th>
                    <th class="px-2 py-3"></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    function submitResult() {
        return confirm("{{ __('Are you sure you wish to delete?') }}") !== false;
    }

    document.addEventListener("DOMContentLoaded", function() {
        $('#datatable').DataTable({
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
            columns: [
                {
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
    });
</script>
