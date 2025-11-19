<!-- The Modal -->
<div x-data="{ open: false }" @open-redeem-modal.window="open = true" x-show="open" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background overlay -->
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay, show/hide based on modal state -->
        <div x-show="open" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="open = false"
            class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="open" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-gray-800 rounded-lg border border-gray-700 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <!-- Modal Header -->
            <div class="bg-gray-800 px-6 py-4 border-b border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-white flex items-center" id="modal-title">
                        <i class="fas fa-money-check-alt mr-2 text-accent-400"></i>
                        {{ __('Redeem voucher code') }}
                    </h3>
                    <button @click="open = false" type="button"
                        class="text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal body -->
            <div class="bg-gray-800 px-6 py-4">
                <form id="redeemVoucherForm" onsubmit="return false" method="post"
                    action="{{ route('voucher.redeem') }}">
                    <div class="space-y-2">
                        <label for="redeemVoucherCode"
                            class="block text-sm font-medium text-gray-300">{{ __('Code') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-money-check-alt text-gray-400"></i>
                            </div>
                            <input id="redeemVoucherCode" name="code" placeholder="SUMMER" type="text"
                                class="block w-full pl-10 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500 transition-colors">
                        </div>
                        <span id="redeemVoucherCodeError" class="text-red-400 text-sm block"></span>
                        <span id="redeemVoucherCodeSuccess" class="text-green-400 text-sm block"></span>
                    </div>
                </form>
            </div>

            <!-- Modal footer -->
            <div class="bg-gray-800 px-6 py-4 border-t border-gray-700 flex items-center justify-end gap-3">
                <button @click="open = false" type="button"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition-all duration-200">
                    {{ __('Close') }}
                </button>
                <button name="submit" id="redeemVoucherSubmit" onclick="redeemVoucherCode()" type="button"
                    class="px-6 py-2 bg-gradient-to-r from-accent-600 to-accent-500 hover:from-accent-500 hover:to-accent-600 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-accent-500/50 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
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

        button.disabled = true

        $.ajax({
            method: form.method,
            url: form.action,
            dataType: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
                code: input.value
            },
            success: function(response) {
                resetForm()
                redeemVoucherSetSuccess(response)
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('close-redeem-modal'));
                    location.reload();
                }, 1500)
            },
            error: function(jqXHR, textStatus, errorThrown) {
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

        input.classList.remove('border-gray-600', 'focus:ring-accent-500', 'focus:border-accent-500')
        input.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500')

        errorLabel.innerHTML = error.status === 422 ? error.responseJSON.errors.code[0] : error.responseJSON.message
    }

    function redeemVoucherSetSuccess(response) {
        let input = document.getElementById('redeemVoucherCode')
        let successLabel = document.getElementById('redeemVoucherCodeSuccess')

        successLabel.innerHTML = response.success
        input.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500')
        input.classList.add('border-green-500', 'focus:ring-green-500', 'focus:border-green-500')
    }
</script>
