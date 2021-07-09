<!-- Button to Open the Modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#redeemVoucherModal">
    Open modal
</button>

<!-- The Modal -->
<div class="modal fade" id="redeemVoucherModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Redeem voucher code</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="code">Code</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-money-check-alt"></i>
                                </div>
                            </div>
                            <input id="code" name="code" placeholder="SUMMER" type="text" class="form-control">
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button name="submit" type="button" class="btn btn-primary">Redeem</button>
            </div>

        </div>
    </div>
</div>


<script>
    function validateCode(){
        $.ajax({
            
        })
    }
</script>
