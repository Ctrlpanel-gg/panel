<div class="modal fade" id="totpViewRecoveryModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" x-data="totpViewRecovery()">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Recovery Codes') }}</h4>
                <button type="button" class="close" @click="closeModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <template x-if="!verified">
                    <div>
                        <p class="text-muted">{{ __('To view your recovery codes, please enter your current password and a 2FA code.') }}</p>

                        <div class="form-group">
                            <label>{{ __('Current Password') }}</label>
                            <input type="password" x-model="password" class="form-control" :class="{'is-invalid': errors.password}" placeholder="••••••">
                            <template x-if="errors.password">
                                <span class="text-danger small" x-text="errors.password"></span>
                            </template>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Authentication Code') }}</label>
                            <input type="text" x-model="code" class="form-control" :class="{'is-invalid': errors.code}" placeholder="000000">
                            <template x-if="errors.code">
                                <span class="text-danger small" x-text="errors.code"></span>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="verified">
                    <div class="text-center">
                        <div class="row mb-4">
                            <template x-for="code in recoveryCodes" :key="code">
                                <div class="col-6 py-2">
                                    <code x-text="code"></code>
                                </div>
                            </template>
                        </div>

                        <div class="flex-wrap d-flex justify-content-center">
                            <button class="mb-2 mr-2 btn btn-outline-info" @click="copyAllRecoveryCodes()">
                                <i class="mr-2 fas fa-copy"></i>{{ __('Copy All') }}
                            </button>
                            <button class="mb-2 mr-2 btn btn-outline-success" @click="downloadRecoveryCodes()">
                                <i class="mr-2 fas fa-download"></i>{{ __('Download .txt') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="modal-footer">
                <template x-if="!verified">
                    <div class="w-100 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" @click="verify()" :disabled="loading">
                            <span x-show="!loading">{{ __('View Codes') }}</span>
                            <span x-show="loading"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                </template>
                <template x-if="verified">
                    <button type="button" class="btn btn-primary btn-block" @click="closeModal()">{{ __('Close') }}</button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function totpViewRecovery() {
    return {
        verified: false,
        loading: false,
        password: '',
        code: '',
        recoveryCodes: [],
        errors: {},

        init() {
            $('#totpViewRecoveryModal').on('show.bs.modal', () => {
                this.reset();
            });
        },

        reset() {
            this.verified = false;
            this.loading = false;
            this.password = '';
            this.code = '';
            this.recoveryCodes = [];
            this.errors = {};
        },

        verify() {
            this.loading = true;
            this.errors = {};

            $.post("{{ route('profile.2fa.action', ['method' => 'totp', 'action' => 'showRecoveryCodes']) }}", {
                _token: "{{ csrf_token() }}",
                password: this.password,
                code: this.code
            }).done(response => {
                this.recoveryCodes = response.recovery_codes;
                this.verified = true;
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

        copyAllRecoveryCodes() {
            const text = this.recoveryCodes.join("\n");
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: "{{ __('Recovery codes copied to clipboard') }}",
                    position: 'top-end',
                    showConfirmButton: false,
                    background: '#343a40',
                    toast: true,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        },

        downloadRecoveryCodes() {
            const text = this.recoveryCodes.join("\n");
            const blob = new Blob([text], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = "{{ config('app.name', 'CtrlPanel.gg') }}-totp-backup-codes.txt";
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        },

        closeModal() {
            $('#totpViewRecoveryModal').modal('hide');
        }
    }
}
</script>
