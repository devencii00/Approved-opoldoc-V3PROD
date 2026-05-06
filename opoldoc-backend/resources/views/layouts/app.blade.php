<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Opol Doctors Clinic</title>
    @vite('resources/css/app.css')
      <link rel="stylesheet" href="{{ asset('assets/fonts/css/stylefont.css') }}">
    <link rel="icon" type="image/x-icon" href="/images/opoldoc-weblog.ico">
    <style>
        .scrollbar-hidden {
            scrollbar-width: none;
        }

        .scrollbar-hidden::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
    </style>
</head>
<body class="font-sans min-h-screen bg-slate-100 text-slate-800 [background-image:radial-gradient(ellipse_at_80%_0%,rgba(6,182,212,0.06)_0%,transparent_55%)]">

    @yield('body')

    <script>
        window.apiFetch = function (path, options) {
            var token = null
            try {
                if (window.localStorage) {
                    token = window.localStorage.getItem('api_token')
                }
            } catch (e) {
                token = null
            }

            var baseOptions = options || {}
            var headers = baseOptions.headers ? Object.assign({}, baseOptions.headers) : {}

            if (token) {
                headers['Authorization'] = 'Bearer ' + token
            }

            if (!headers['Accept']) {
                headers['Accept'] = 'application/json'
            }

            if (!headers['X-Requested-With']) {
                headers['X-Requested-With'] = 'XMLHttpRequest'
            }

            var resolvedPath = path
            try {
                var u = new URL(String(path || ''), window.location.origin)
                if (u.origin !== window.location.origin) {
                    resolvedPath = u.pathname + u.search + u.hash
                } else {
                    resolvedPath = u.toString()
                }
            } catch (e) {
                resolvedPath = path
            }

            if (typeof AbortController !== 'function') {
                return fetch(resolvedPath, Object.assign({}, baseOptions, { headers: headers, credentials: 'same-origin' }))
            }

            var controller = new AbortController()
            var timeoutId = setTimeout(function () {
                try { controller.abort() } catch (_) {}
            }, 30000)

            var merged = Object.assign({}, baseOptions, { headers: headers, credentials: 'same-origin', signal: controller.signal })
            return fetch(resolvedPath, merged).finally(function () {
                clearTimeout(timeoutId)
            })
        }
    </script>

</body>
</html>
