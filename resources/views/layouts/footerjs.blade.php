<!-- Required Js -->
<script src="{{ URL::asset('build/js/plugins/popper.min.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins/i18next.min.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins/i18nextHttpBackend.min.js') }}"></script>
<script src="{{ URL::asset('build/js/icon/custom-font.js') }}"></script>
<script src="{{ URL::asset('build/js/script.js') }}"></script>
<script src="{{ URL::asset('build/js/theme.js') }}?v=3"></script>
<script src="{{ URL::asset('build/js/multi-lang.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins/feather.min.js') }}"></script>

{{-- Tema: dark por padrão; escolha do usuário (topbar) persiste; light opcional --}}
<script>
    (function () {
        var saved = null;
        try { saved = localStorage.getItem('vbt-theme'); } catch (e) {}
        var theme = (saved === 'light') ? 'light' : 'dark';
        layout_change(theme);
        layout_sidebar_change(theme);
    })();
</script>

@if (env('APP_BOX_CONTAINER') == false)
    <script>
        change_box_container('true');
    </script>
@endif

@if (env('APP_BOX_CONTAINER') == false)
    <script>
        change_box_container('false');
    </script>
@endif

@if (env('APP_CAPTION_SHOW') == 'true')
    <script>
        layout_caption_change('true');
    </script>
@endif

@if (env('APP_CAPTION_SHOW') == false)
    <script>
        layout_caption_change('false');
    </script>
@endif

@if (env('APP_RTL_LAYOUT') == 'true')
    <script>
        layout_rtl_change('true');
    </script>
@endif

@if (env('APP_RTL_LAYOUT') == false)
    <script>
        layout_rtl_change('false');
    </script>
@endif

@if (env('APP_PRESET_THEME') != '')
    <script>
        preset_change("{{env('APP_PRESET_THEME')}}");
    </script>
@endif
