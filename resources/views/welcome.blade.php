<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <title>CollabFlow - AI-Powered Project Management</title>
    </head>
    <body class="min-h-screen antialiased" style="background-color: var(--color-background-50);">
        <div class="min-h-screen" style="background-color: var(--color-background-50);">
            {{-- Navigation --}}
            <nav class="fixed top-0 w-full border-b z-50" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                <div class="max-w-7xl mx-auto px-8 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-cf-logo size="small" class="text-[var(--color-glaucous)]" />
                        <span class="font-semibold text-lg" style="font-family: Tahoma; color: var(--color-text-900);">CollabFlow</span>
                    </div>
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="transition" style="color: var(--color-text-700);" onmouseover="this.style.color='var(--color-text-900)'" onmouseout="this.style.color='var(--color-text-700)'">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="transition" style="color: var(--color-text-700);" onmouseover="this.style.color='var(--color-text-900)'" onmouseout="this.style.color='var(--color-text-700)'">
                                Log in
                            </a>
                            <a href="{{ route('register') }}" class="px-6 py-2 rounded-lg transition font-medium" style="background-color: var(--color-glaucous); color: white;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                Sign up
                            </a>
                        @endauth
                    </div>
                </div>
            </nav>

            {{-- Hero Section --}}
            <section class="pt-32 pb-20 px-8">
                <div class="max-w-4xl mx-auto text-center">
                    <div class="inline-block mb-6 px-4 py-2 rounded-full" style="background-color: rgba(92,128,188,0.1);">
                        <span class="font-medium text-sm" style="color: var(--color-glaucous);">AI-Powered Project Management</span>
                    </div>
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight" style="font-family: Tahoma; color: var(--color-text-900);">
                        Manage Projects with <span style="color: var(--color-glaucous);">AI Intelligence</span>
                    </h1>
                    <p class="text-xl mb-8 max-w-2xl mx-auto" style="color: var(--color-text-600);">
                        CollabFlow combines the power of artificial intelligence with human oversight to streamline your project
                        management workflow and boost team productivity.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="px-8 py-3 rounded-lg transition font-medium text-lg" style="background-color: var(--color-glaucous); color: white;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            Get Started Free
                        </a>
                        <a href="#features" class="px-8 py-3 rounded-lg border-2 transition font-medium text-lg" style="border-color: var(--color-background-300); color: var(--color-text-900); background-color: transparent;" onmouseover="this.style.backgroundColor='var(--color-background-100)'" onmouseout="this.style.backgroundColor='transparent'">
                            Learn More
                        </a>
                    </div>
                </div>
            </section>

            {{-- Features Section --}}
            <section id="features" class="py-20 px-8" style="background-color: var(--color-background-100);">
                <div class="max-w-6xl mx-auto">
                    <h2 class="text-4xl font-bold text-center mb-16" style="font-family: Tahoma; color: var(--color-text-900);">Why Choose CollabFlow?</h2>
                    <div class="grid md:grid-cols-3 gap-8">
                        {{-- Feature 1 --}}
                        <div class="p-8 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4" style="background-color: rgba(92,128,188,0.1);">
                                <svg class="w-6 h-6" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3" style="color: var(--color-text-900);">AI-Powered Task Generation</h3>
                            <p style="color: var(--color-text-600);">
                                Automatically generate and organize tasks using advanced AI, saving your team hours of planning time.
                            </p>
                        </div>

                        {{-- Feature 2 --}}
                        <div class="p-8 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4" style="background-color: rgba(196,214,176,0.2);">
                                <svg class="w-6 h-6" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3" style="color: var(--color-text-900);">Human-in-the-Loop Workflows</h3>
                            <p style="color: var(--color-text-600);">
                                Maintain full control with human oversight while leveraging AI suggestions for optimal results.
                            </p>
                        </div>

                        {{-- Feature 3 --}}
                        <div class="p-8 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4" style="background-color: rgba(255,159,28,0.15);">
                                <svg class="w-6 h-6" style="color: var(--color-orange-peel);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3" style="color: var(--color-text-900);">Real-Time Collaboration</h3>
                            <p style="color: var(--color-text-600);">
                                Work seamlessly with your team with real-time updates, notifications, and activity tracking.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Stats Section --}}
            <section class="py-20 px-8">
                <div class="max-w-6xl mx-auto">
                    <div class="grid md:grid-cols-4 gap-8 text-center">
                        <div>
                            <div class="text-4xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-glaucous);">10K+</div>
                            <p style="color: var(--color-text-600);">Active Users</p>
                        </div>
                        <div>
                            <div class="text-4xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-tea-green);">50K+</div>
                            <p style="color: var(--color-text-600);">Projects Managed</p>
                        </div>
                        <div>
                            <div class="text-4xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-orange-peel);">500K+</div>
                            <p style="color: var(--color-text-600);">Tasks Completed</p>
                        </div>
                        <div>
                            <div class="text-4xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-eggplant);">99.9%</div>
                            <p style="color: var(--color-text-600);">Uptime</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- CTA Section --}}
            <section class="py-20 px-8" style="background-color: var(--color-glaucous);">
                <div class="max-w-4xl mx-auto text-center">
                    <h2 class="text-4xl font-bold text-white mb-6" style="font-family: Tahoma;">Ready to Transform Your Project Management?</h2>
                    <p class="text-xl mb-8" style="color: rgba(255,255,255,0.9);">
                        Join thousands of teams already using CollabFlow to manage their projects more efficiently.
                    </p>
                    <a href="{{ route('register') }}" class="inline-block bg-white px-8 py-3 rounded-lg transition font-bold text-lg" style="color: var(--color-glaucous);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        Start Your Free Trial
                    </a>
                </div>
            </section>

            {{-- Footer --}}
            <footer class="border-t py-8 px-8" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
                <div class="max-w-6xl mx-auto text-center" style="color: var(--color-text-600);">
                    <p>&copy; 2025 CollabFlow. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
