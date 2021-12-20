<!-- The Modal -->
<div class="modal fade" id="editConfigurationModel">
    <div class="modal-dialog">
        <div class="modal-content ">

            <form method="post" action="{{route('admin.configurations.updatevalue')}}">
                @csrf
                    @method('PATCH')
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">{{__('Edit Configuration')}}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <div class="form-group">
                        <label id="keyLabel" for="value">{{__('Text Field')}}</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fa fa-cog"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <input hidden id="key" name="key" type="text" class="form-control" required="required">
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.configuration = {
        parse(key, value, type) {
            $('#keyLabel').html(key)
            $('#key').val(key)
            $('#value').remove();
            if (type === 'integer') {
                $('.input-group').append('<input id="value" name="value" type="number" class="form-control" required="required">')
            } else if (type === 'boolean') {
                $('.input-group').append('<select id="value" name="value" class="form-control" required=required>' +
                    '<option value="true">true</option>' +
                    '<option value="false">false</option>' +
                    '</select>')
            } else if (type === 'string') {
                $('.input-group').append('<input id="value" name="value" type="text" class="form-control" required="required">')
            }
            $('#value').val(value)
            $('#editConfigurationModel').modal('show')
        }
    }
</script>
