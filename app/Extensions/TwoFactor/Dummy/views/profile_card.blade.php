@php($dummy = Auth::user()->twoFactorMethods()->where('method', 'dummy')->where('is_enabled', true)->first())
<div class="mb-3 card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="mr-2 fas fa-flask fa-2x text-muted"></i>
                <div class="d-inline-block align-middle">
                    <div class="font-weight-bold">{{ __('Dummy 2FA (Test)') }}</div>
                    <div class="text-muted small">{{ __('Just enter 123456 to test') }}</div>
                </div>
            </div>
            <div>
                @if($dummy)
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
                <input type="checkbox" class="custom-control-input" id="dummyToggle"
                    {{ $dummy ? 'checked' : '' }}
                    @click.prevent="toggleDummy()">
                <label class="custom-control-label" for="dummyToggle"></label>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleDummy() {
        const enabled = {{ $dummy ? 'true' : 'false' }};
        if (enabled) {
            $.post("{{ route('profile.2fa.disable', ['method' => 'dummy']) }}", { _token: "{{ csrf_token() }}" })
             .done(() => location.reload());
        } else {
            const code = prompt("Enter 123456 to enable Dummy 2FA");
            if (code) {
                $.post("{{ route('profile.2fa.enable', ['method' => 'dummy']) }}", { _token: "{{ csrf_token() }}", code: code })
                 .done(() => location.reload())
                 .fail(xhr => alert(xhr.responseJSON.message || "Failed"));
            }
        }
    }
</script>
@endpush
