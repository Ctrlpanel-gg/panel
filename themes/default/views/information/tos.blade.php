@extends('layouts.app')

@section('content')
<body class="dark-mode">
    <!-- Typography Demo Section -->
    <div class="container mx-auto px-6 py-12">
        <div class="mb-12">
            <div class="caption">Typography System</div>
            <h1 class="h1 mb-6 text-p4">Modern SaaS Design</h1>
            <p class="body-1 max-w-2xl text-p5">
                Showcasing our new typography system inspired by Xora's modern design patterns.
                This system provides consistent, scalable typography for the entire application.
            </p>
        </div>

        <!-- Typography Scale Demo -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="glass-panel p-8">
                <h2 class="h3 mb-6 text-p4">Heading Styles</h2>
                <div class="space-y-4">
                    <div>
                        <span class="small-2 text-p3 uppercase tracking-wider">H1 - Display</span>
                        <h1 class="h1 text-p4">Amazingly simple</h1>
                    </div>
                    <div>
                        <span class="small-2 text-p3 uppercase tracking-wider">H2 - Large</span>
                        <h2 class="h2 text-p4">Powerful features</h2>
                    </div>
                    <div>
                        <span class="small-2 text-p3 uppercase tracking-wider">H3 - Medium</span>
                        <h3 class="h3 text-p4">Built for developers</h3>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-8">
                <h2 class="h3 mb-6 text-p4">Body Text Styles</h2>
                <div class="space-y-4">
                    <div>
                        <span class="small-2 text-p3 uppercase tracking-wider">Body 1 - Large</span>
                        <p class="body-1 text-p5">The quick brown fox jumps over the lazy dog. This is body-1 text for larger content.</p>
                    </div>
                    <div>
                        <span class="small-2 text-p3 uppercase tracking-wider">Body 2 - Medium</span>
                        <p class="body-2 text-p5">The quick brown fox jumps over the lazy dog. This is body-2 text for medium content.</p>
                    </div>
                    <div>
                        <span class="small-2 text-p3 uppercase tracking-wider">Base - Regular</span>
                        <p class="base text-p5">The quick brown fox jumps over the lazy dog. This is base text for regular content.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button System Demo -->
        <div class="glass-panel p-8 mb-12">
            <h2 class="h3 mb-6 text-p4">Button System</h2>
            <div class="flex flex-wrap gap-4 mb-6">
                <button class="btn btn-primary">Primary Button</button>
                <button class="btn btn-info">Info Button</button>
                <button class="btn btn-success">Success Button</button>
                <button class="btn btn-warning">Warning Button</button>
                <button class="btn btn-danger">Danger Button</button>
            </div>
            <div class="btn-glow group">
                <div class="btn-inner">
                    <span class="btn-glow-before"></span>
                    <span class="base-bold text-p1 uppercase z-2 relative">Advanced Glow Button</span>
                    <span class="btn-glow-after"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms of Service Content -->
    <div class="container mx-auto px-6 py-8">
        <div class="flex justify-center">
            <div class="w-full max-w-4xl">
                <div class="card">
                    <div class="card-header">
                        <h1 class="h4 text-p4 mb-0">{{ __('Terms of Service') }}</h1>
                    </div>
                    <div class="card-body prose prose-invert max-w-none">
                        @include('information.tos-content')
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@endsection
