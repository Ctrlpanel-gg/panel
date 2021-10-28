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
    
      let form = document.getElementById("deleteServerForm")
      form.addEventListener("submit", function(e) {
          e.preventDefault();
          Swal.fire({
              title: "Are you sure?",
              html: 'Are you sure you wish to delete this server?<br>This is an irreversible action, all files will be removed.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d9534f',
              showCancelButton: true,
              confirmButtonText: 'Yes, delete it!',
              cancelButtonText: 'No, cancel!',
              reverseButtons: true
          }).then((result) => {
              if (result.isConfirmed) {
                  return form.submit()
              } else {
                  return Swal.fire('Canceled ...', 'Server deletion has been canceled.', 'info')
              }
          });
      });
    
</script>
