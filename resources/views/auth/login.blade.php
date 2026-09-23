<x-layouts.app title="Sign in" :bare="true">
    <main class="relative flex min-h-dvh items-center justify-center overflow-hidden bg-surface-deep px-4 py-12">
        <div class="pointer-events-none absolute -top-40 -left-40 size-[28rem] rounded-full bg-primary/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-32 -bottom-40 size-[24rem] rounded-full bg-neon-cyan/10 blur-3xl"></div>

        <div class="relative w-full max-w-sm">
            <x-logo class="mb-10" />
            <h1 class="type-headline">Welcome back</h1>
            <p class="mt-2 text-text-muted">Sign in to stream from your home server.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 flex flex-col gap-4" hx-boost="false">
                @csrf
                <label class="flex flex-col gap-2">
                    <span class="type-label text-text-muted">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="type-label text-text-muted">Password</span>
                    <input type="password" name="password" required autocomplete="current-password" class="input">
                </label>
                @error('email')
                    <p class="rounded-md border border-crimson/40 bg-crimson/10 px-3 py-2 text-sm text-[#ffb4ab]" role="alert">{{ $message }}</p>
                @enderror
                <label class="flex items-center gap-2 text-sm text-text-muted">
                    <input type="checkbox" name="remember" value="1" class="size-4 accent-primary"> Keep me signed in
                </label>
                <button class="btn-primary mt-2 w-full">Sign in</button>
            </form>
        </div>
    </main>
</x-layouts.app>
