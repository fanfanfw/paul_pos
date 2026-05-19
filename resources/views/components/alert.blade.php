@if (session('success') || session('error') || session('warning'))
    @if (session('success'))
        <span class="hidden" data-swal-toast data-type="success" data-message="{{ session('success') }}"></span>
    @endif

    @if (session('error'))
        <span class="hidden" data-swal-toast data-type="error" data-message="{{ session('error') }}"></span>
    @endif

    @if (session('warning'))
        <span class="hidden" data-swal-toast data-type="warning" data-message="{{ session('warning') }}"></span>
    @endif
@endif
