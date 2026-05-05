@php($totp = Auth::user()->twoFactorMethods()->where('method', 'totp')->where('is_enabled', true)->first())
<div class="mb-3 card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-md-center">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center w-100">
                <!-- Mobile row -->
                <div class="d-flex align-items-center justify-content-between w-100 d-md-none">
                    <i class="{{ $method->getIcon() }} fa-2x text-muted"></i>

                    @if($totp)
                        <span class="badge badge-success">{{ __('Enabled') }}</span>
                    @else
                        <span class="badge badge-secondary">{{ __('Disabled') }}</span>
                    @endif
                </div>
                <!-- Desktop icon and text -->
                <i class="d-none d-md-inline-block mr-md-3 {{ $method->getIcon() }} fa-2x text-muted"></i>
                <div class="mt-2 mt-md-0">
                    <div class="font-weight-bold">{{ $method->getLabel() }}</div>
                    <div class="text-muted small">{{ $method->getDescription() }}</div>
                </div>
            </div>
            <!-- Desktop icon -->
            <div class="d-none d-md-block">
                @if($totp)
                    <span class="badge badge-success">{{ __('Enabled') }}</span>
                @else
                    <span class="badge badge-secondary">{{ __('Disabled') }}</span>
                @endif
            </div>
        </div>

        <hr>

        <div class="d-flex justify-content-between align-items-center">
            <span>{{ __('Enable') }}</span>
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="totpToggle" {{ $totp ? 'checked' : '' }}
                    @click.prevent="window.dispatchEvent(new CustomEvent('totp-toggle-click', { detail: { enabled: {{ $totp ? 'true' : 'false' }} } }))">
                <label class="custom-control-label" for="totpToggle"></label>
            </div>
        </div>

        @if($totp)
            <div class="mt-3">
                <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#totpViewRecoveryModal">
                    <i class="mr-2 fas fa-key"></i>{{ __('View Recovery Codes') }}
                </button>
            </div>
        @endif
    </div>
</div>

@push('modals')
    @include('twofactor_totp::modals.totp-setup-modal')
    @include('twofactor_totp::modals.totp-view-recovery-modal')
    @include('twofactor_totp::modals.totp-disable-modal')
@endpush

@push('scripts')
    <script>
        window.addEventListener('totp-toggle-click', (e) => {
            if (e.detail.enabled) {
                $('#totpDisableModal').modal('show');
            } else {
                $('#totpSetupModal').modal('show');
            }
        });
    </script>
@endpush
