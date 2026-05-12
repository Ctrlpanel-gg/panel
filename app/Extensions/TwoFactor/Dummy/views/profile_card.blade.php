@php($dummy = Auth::user()->twoFactorMethods->where('method', 'dummy')->where('is_enabled', true)->first())
<div class="mb-3 card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-md-center">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center w-100">
                <!-- Mobile row -->
                <div class="d-flex align-items-center justify-content-between w-100 d-md-none">
                    <i class="{{ $method->getIcon() }} fa-2x text-muted"></i>

                    @if($dummy)
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
            <!-- Desktop badge -->
            <div class="d-none d-md-block">
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
                <input type="checkbox"
                       class="custom-control-input"
                       id="dummyToggle"
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
