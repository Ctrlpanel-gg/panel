

<div class="modal fade" id="suspendReasonModal" tabindex="-1" aria-labelledby="suspendReasonModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="suspendForm" method="POST" action="">
      @csrf
      @method('POST')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="suspendReasonModalLabel">Reason for Suspension</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="server_id" id="server_id">
          <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <textarea class="form-control" name="reason" id="reason" rows="3" required
                      oninput="this.value = this.value.replace(/[<>]/g, '')"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  $(document).ready(function () {
    $(document).on('click', '.suspend-btn', function () {
      const serverId = $(this).data('server-id');
      const actionUrl = $(this).data('action');

      $('#server_id').val(serverId);
      $('#suspendForm').attr('action', actionUrl);

      // jQuery Modal öffnen statt Bootstrap
      $('#suspendReasonModal').modal('show');
    });
  });
</script>


