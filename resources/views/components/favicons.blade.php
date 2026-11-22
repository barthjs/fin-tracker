{{-- Icons, theme colors and the web app manifest in the head of every panel page --}}
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#fafafa" />
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#09090b" />
<link rel="icon" sizes="32x32" href="/favicon.ico" />
<link rel="icon" type="image/svg+xml" href="/icons/icon.svg" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
<link rel="manifest" href="{{ route('pwa.manifest') }}" />
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
