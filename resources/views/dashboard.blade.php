<?php
use App\Models\Publication;
use Illuminate\Support\Facades\DB;

\$stats = [
    'total' => Publication::where('_del_mark', 0)->count(),
    'deleted' => Publication::where('_del_mark', 1)->count(),
    'this_year' => Publication::where('_del_mark', 0)
        ->whereYear('upload_date', date('Y'))
        ->count(),
    'by_type' => Publication::where('_del_mark', 0)
        ->select('issue_types.issue_type', DB::raw('count(*) as count'))
        ->leftJoin('issue_types', 'publications.id_issue_type', '=', 'issue_types.id_issue_type')
        ->groupBy('issue_types.issue_type')
        ->orderByDesc('count')
        ->limit(5)
        ->get(),
];
?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Here's an overview of your literature database.</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Total Publications -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Total Publications
                                    </dt>
                                    <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ \$stats['total'] }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Year -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Added This Year
                                    </dt>
                                    <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ \$stats['this_year'] }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deleted -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Deleted Items
                                    </dt>
                                    <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ \$stats['deleted'] }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Publications by Type -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Publications by Type</h3>
                    <div class="space-y-4">
                        @forelse(\$stats['by_type'] as \$type)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center flex-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 min-w-[120px]">
                                        {{ \$type->issue_type ?? 'Unknown' }}
                                    </span>
                                    <div class="flex-1 mx-4">
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ (\$type->count / \$stats['total']) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ \$type->count }}
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No publication data available.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('publications.index') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition">
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">View All Publications</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Browse and manage your literature database</p>
                        </div>
                        <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>

                <a href="#" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition">
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add New Publication</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Create a new entry in your database</p>
                        </div>
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
