<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'he' ? 'rtl' : 'ltr' }}"
      x-data x-init="$store.darkMode.init()">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Notification Container -->
            <div x-data="{
                notifications: $store.notifications,
                init() {
                    // Check for session flash messages
                    @php
                        $flashNotify = session('notify');
                        @endphp
                        @if($flashNotify && is_array($flashNotify) && isset($flashNotify['message']))
                            const flashNotify = {{ json_encode($flashNotify) }};
                            if (flashNotify.message) {
                                $store.notifications.add(flashNotify.message, flashNotify.type || 'info');
                            }
                        @endif
                }
            }" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full">
                <template x-for="notification in notifications.items" :key="notification.id">
                    <div
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-full"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-full"
                        class="p-4 rounded-lg shadow-lg flex items-start gap-3"
                        :class="{
                            'bg-red-100 dark:bg-red-900 border border-red-200 dark:border-red-700': notification.type === 'error',
                            'bg-green-100 dark:bg-green-900 border border-green-200 dark:border-green-700': notification.type === 'success',
                            'bg-blue-100 dark:bg-blue-900 border border-blue-200 dark:border-blue-700': notification.type === 'info',
                            'bg-yellow-100 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700': notification.type === 'warning'
                        }"
                    >
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <template x-if="notification.type === 'error'">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </template>
                            <template x-if="notification.type === 'success'">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </template>
                            <template x-if="notification.type === 'info'">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </template>
                            <template x-if="notification.type === 'warning'">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </template>
                        </div>

                        <!-- Message -->
                        <div class="flex-1 min-w-0">
                            <p
                                class="text-sm font-medium"
                                :class="{
                                    'text-red-800 dark:text-red-200': notification.type === 'error',
                                    'text-green-800 dark:text-green-200': notification.type === 'success',
                                    'text-blue-800 dark:text-blue-200': notification.type === 'info',
                                    'text-yellow-800 dark:text-yellow-200': notification.type === 'warning'
                                }"
                                x-text="notification.message"
                            ></p>
                        </div>

                        <!-- Close Button -->
                        <button
                            @click="$store.notifications.remove(notification.id)"
                            class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </body>
</html>
