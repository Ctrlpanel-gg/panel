<div class="modal fade" id="totpSetupModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" x-data="totpSetup()">
    <div class="modal-dialog" :class="step === 'setup' ? 'modal-lg' : ''">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" x-text="step === 'setup' ? '{{ __('Setup Two-Factor Authentication') }}' : '{{ __('Recovery Codes') }}'"></h4>
                <button type="button" class="close" @click="closeModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Step 1: Scan QR & Verify -->
                <template x-if="step === 'setup'">
                    <div>
                        <div class="row align-items-center">
                            <div class="mb-4 mb-md-0 text-center col-md-5 d-flex flex-column align-items-center justify-content-center">
                                <div class="p-2 bg-white d-inline-block rounded shadow-sm">
                                    <div x-html="qrSvg"></div>
                                </div>
                                <div class="mt-3 text-muted small px-2">
                                    {{ __('Scan this QR code with your authenticator app (e.g. Google Authenticator, Authy, Bitwarden).') }}
                                </div>
                            </div>
                            <div class="col-md-7">
                                <h5>{{ __('Manual Entry') }}</h5>
                                <p class="text-muted small">
                                    {{ __('If you cannot scan the QR code, enter this secret key into your app:') }}
                                </p>
                                <div class="form-group">
                                    <code x-text="secret"></code>
                                    <button class="btn btn-link text-secondary p-1 ml-2" type="button" @click="copySecret()" title="{{ __('Copy to clipboard') }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>

                                <hr>

                                <div class="form-group">
                                    <label>{{ __('Authentication Code') }}</label>
                                    <input type="text" x-model="code" class="form-control" :class="{'is-invalid': errors.code}" placeholder="000000" inputmode="numeric">
                                    <template x-if="errors.code">
                                        <span class="text-danger small" x-text="errors.code"></span>
                                    </template>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Current Password') }}</label>
                                    <input type="password" x-model="password" class="form-control" :class="{'is-invalid': errors.password}" placeholder="••••••">
                                    <template x-if="errors.password">
                                        <span class="text-danger small" x-text="errors.password"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Step 2: Recovery Codes -->
                <template x-if="step === 'recovery'">
                    <div class="text-center">
                        <div class="mb-3 alert alert-warning small text-left">
                            <i class="mr-2 fas fa-exclamation-triangle"></i>
                            {{ __('Recovery codes are used to access your account if you lose your authenticator device. Store them safely!') }}
                        </div>

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
                <template x-if="step === 'setup'">
                    <div class="w-100 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" @click="enableTotp()" :disabled="loading">
                            <span x-show="!loading">{{ __('Activate') }}</span>
                            <span x-show="loading"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                </template>
                <template x-if="step === 'recovery'">
                    <button type="button" class="btn btn-primary btn-block" @click="done()">{{ __('Done') }}</button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function totpSetup() {
    return {
        step: 'setup',
        qrSvg: '',
        secret: '',
        code: '',
        password: '',
        recoveryCodes: [],
        loading: false,
        errors: {},

        init() {
            $('#totpSetupModal').on('show.bs.modal', () => {
                this.reset();
                this.fetchSetupData();
            });
        },

        reset() {
            this.step = 'setup';
            this.qrSvg = '';
            this.secret = '';
            this.code = '';
            this.password = '';
            this.recoveryCodes = [];
            this.loading = false;
            this.errors = {};
        },

        fetchSetupData() {
            $.post("{{ route('profile.2fa.totp.setup') }}", {
                _token: "{{ csrf_token() }}"
            }).done(response => {
                this.qrSvg = response.qr_svg;
                this.secret = response.secret;
            }).fail(xhr => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Something went wrong'
                });
                this.closeModal();
            });
        },

        enableTotp() {
            this.loading = true;
            this.errors = {};

            $.post("{{ route('profile.2fa.totp.enable') }}", {
                _token: "{{ csrf_token() }}",
                code: this.code,
                password: this.password
            }).done(response => {
                this.recoveryCodes = response.recovery_codes;
                this.step = 'recovery';
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

        copySecret() {
            const rawSecret = this.secret.replace(/\s/g, '');
            navigator.clipboard.writeText(rawSecret).then(() => {
                toastr.success("{{ __('Secret copied to clipboard') }}");
            });
        },

        copyAllRecoveryCodes() {
            const text = this.recoveryCodes.join("\n");
            navigator.clipboard.writeText(text).then(() => {
                toastr.success("{{ __('Recovery codes copied to clipboard') }}");
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
            $('#totpSetupModal').modal('hide');
        },

        done() {
            this.closeModal();
            location.reload(); // Refresh to update the UI (toggle, etc.)
        }
    }
}
</script>
