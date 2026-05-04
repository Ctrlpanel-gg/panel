<div class="modal fade" id="totpDisableModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" x-data="totpDisable()">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Disable Two-Factor Authentication') }}</h4>
                <button type="button" class="close" @click="closeModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="text-muted">{{ __('To disable two-factor authentication, please enter your current password and a 2FA code.') }}</p>
                
                <div class="form-group">
                    <label>{{ __('Current Password') }}</label>
                    <input type="password" x-model="password" class="form-control" :class="{'is-invalid': errors.password}" placeholder="••••••">
                    <template x-if="errors.password">
                        <span class="text-danger small" x-text="errors.password"></span>
                    </template>
                </div>

                <div class="form-group">
                    <label>{{ __('Authentication Code') }}</label>
                    <input type="text" x-model="code" class="form-control" :class="{'is-invalid': errors.code}" placeholder="000000" inputmode="numeric">
                    <template x-if="errors.code">
                        <span class="text-danger small" x-text="errors.code"></span>
                    </template>
                </div>
            </div>

            <div class="modal-footer">
                <div class="w-100 d-flex justify-content-end">
                    <button type="button" class="btn btn-danger" @click="disable()" :disabled="loading">
                        <span x-show="!loading">{{ __('Disable 2FA') }}</span>
                        <span x-show="loading"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function totpDisable() {
    return {
        loading: false,
        password: '',
        code: '',
        errors: {},

        init() {
            $('#totpDisableModal').on('show.bs.modal', () => {
                this.reset();
            });
        },

        reset() {
            this.loading = false;
            this.password = '';
            this.code = '';
            this.errors = {};
        },

        disable() {
            this.loading = true;
            this.errors = {};

            $.post("{{ route('profile.2fa.totp.disable') }}", {
                _token: "{{ csrf_token() }}",
                password: this.password,
                code: this.code
            }).done(response => {
                this.closeModal();
                Swal.fire({
                    icon: 'success',
                    title: '{{ __('Success') }}',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }).fail(xhr => {
                if (xhr.status === 422) {
                    this.errors = xhr.responseJSON.errors;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong'
                    });
                }
            }).always(() => {
                this.loading = false;
            });
        },

        closeModal() {
            $('#totpDisableModal').modal('hide');
        }
    }
}
</script>
