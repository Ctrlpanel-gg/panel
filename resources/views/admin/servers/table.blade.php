<table id="datatable" class="table table-striped">
    <thead>
    <tr>
        <th width="20"></th>
        <th>Name</th>
        <th>User</th>
        <th>Config</th>
        <th>Suspended At</th>
        <th>Created At</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script>
    function submitResult() {
        return confirm("Are you sure you wish to delete?") !== false;
    }

    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{route('admin.servers.datatable')}}{{$filter ?? ''}}",
            order: [[ 5, "desc" ]],
            columns: [
                {data: 'status' , name : 'servers.suspended'},
                {data: 'name'},
                {data: 'user' , name : 'user.name'},
                {data: 'resources' , name : 'product.name'},
                {data: 'suspended'},
                {data: 'created_at'},
                {data: 'actions' , sortable : false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
