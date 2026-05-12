@include('admin.layouts.admin_partials.head')

<body class="bg-light" data-locale="{{ app()->getLocale() }}">
    {{-- Top header (Vue component is mounted inside the Inertia tree). --}}
    @include('admin.layouts.admin_partials.header')

    {{-- Left sidebar (Vue component is mounted inside the Inertia tree). --}}
    @include('admin.layouts.admin_partials.left_sidebar')

    {{-- Inertia mount point: Vue renders the full admin shell (header + sidebar + page). --}}
    @inertia

    @routes
    @flasher_render
    @include('admin.layouts.admin_partials.scripts')
</body>

</html>
