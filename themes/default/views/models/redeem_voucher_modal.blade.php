<!-- The Modal -->
<div class="modal fade" id="redeemVoucherModal">
    <div class="modal-dialog">
        <div class="modal-content bg-zinc-900 border border-zinc-800">
            <!-- Modal Header -->
            <div class="modal-header border-b border-zinc-800">
                <h4 class="modal-title text-white font-medium">{{ __('Redeem voucher code') }}</h4>
                <button type="button" class="close text-zinc-400 hover:text-white transition-colors" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body p-6">
                <form id="redeemVoucherForm" onsubmit="return false" method="post" action="{{ route('voucher.redeem') }}">
                    <div>
                        <label for="redeemVoucherCode" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Code') }}</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-zinc-400">
                                <i class="fas fa-money-check-alt"></i>
                            </span>
                            <input id="redeemVoucherCode" 
                                   name="code" 
                                   placeholder="YOURMOM_ON_TOP" 
                                   type="text"
                                   class="input pl-10">
                        </div>
                        <span id="redeemVoucherCodeError" class="mt-2 text-sm text-red-400"></span>
                        <span id="redeemVoucherCodeSuccess" class="mt-2 text-sm text-emerald-400"></span>
                    </div>
                </form>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer border-t border-zinc-800">
                <button type="button" 
                        class="btn bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200" 
                        data-dismiss="modal">
                    {{ __('Close') }}
                </button>
                <button name="submit" 
                        id="redeemVoucherSubmit" 
                        onclick="redeemVoucherCode()" 
                        type="button"
                        class="btn btn-primary">
                    {{ __('Redeem') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function redeemVoucherCode() {
        let form = document.getElementById('redeemVoucherForm')
        let button = document.getElementById('redeemVoucherSubmit')
        let input = document.getElementById('redeemVoucherCode')

        console.log(form.method, form.action)
        button.disabled = true

        $.ajax({
            method: form.method,
            url: form.action,
            dataType: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
                code: input.value
            },
            success: function (response) {
                resetForm()
                redeemVoucherSetSuccess(response)
                setTimeout(() => {
                    $('#redeemVoucherModal').modal('toggle');
                } , 1500)
            },
            error: function (jqXHR, textStatus, errorThrown) {
                resetForm()
                redeemVoucherSetError(jqXHR)
                console.error(jqXHR.responseJSON)
            },

        })
    }

    function resetForm() {
        let button = document.getElementById('redeemVoucherSubmit')
        let input = document.getElementById('redeemVoucherCode')
        let successLabel = document.getElementById('redeemVoucherCodeSuccess')
        let errorLabel = document.getElementById('redeemVoucherCodeError')

        input.classList.remove('is-invalid')
        input.classList.remove('is-valid')
        successLabel.innerHTML = ''
        errorLabel.innerHTML = ''
        button.disabled = false
    }

    function redeemVoucherSetError(error) {
        let input = document.getElementById('redeemVoucherCode')
        let errorLabel = document.getElementById('redeemVoucherCodeError')

        input.classList.add("is-invalid")

        errorLabel.innerHTML = error.status === 422 ? error.responseJSON.errors.code[0] : error.responseJSON.message
    }

    function redeemVoucherSetSuccess(response) {
        let input = document.getElementById('redeemVoucherCode')
        let successLabel = document.getElementById('redeemVoucherCodeSuccess')

        successLabel.innerHTML = response.success
        input.classList.remove('is-invalid')
        input.classList.add('is-valid')
    }
</script>
