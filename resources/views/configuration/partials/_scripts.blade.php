{{-- Configuration Page Scripts --}}
@vite(['resources/js/common/form-utils.js', 'resources/js/common/toast-utils.js'])

<script>
window.configRoutes = {
    store:            "{{ route('configuration.plans.store') }}",
    update:           "{{ url('configuration/plans') }}",
    destroy:          "{{ url('configuration/plans') }}",
    toggleStatus:     "{{ url('configuration/plans/toggle-status') }}",
    reorder:          "{{ route('configuration.plans.reorder') }}",
    index:            "{{ route('configuration.index') }}",
    categoryUpdate:   "{{ url('configuration/categories') }}",
    categoryDestroy:  "{{ url('configuration/categories') }}"
};
window.csrfToken = "{{ csrf_token() }}";
</script>

@vite(['resources/js/pages/configuration.js'])
