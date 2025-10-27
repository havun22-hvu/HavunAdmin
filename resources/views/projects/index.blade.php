<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Projecten
            </h2>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                + Nieuw Project
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($projects as $project)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $project->color ?? '#95A5A6' }}"></div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">
                                        {{ $project->name }}
                                    </a>
                                </h3>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($project->status === 'active') bg-green-100 text-green-800
                                @elseif($project->status === 'development') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>

                        @if($project->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ Str::limit($project->description, 100) }}
                        </p>
                        @endif

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Omzet</p>
                                    <p class="text-lg font-semibold text-green-600">
                                        €{{ number_format($project->total_revenue, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Kosten</p>
                                    <p class="text-lg font-semibold text-red-600">
                                        €{{ number_format($project->total_expenses, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 text-center border-t border-gray-200 dark:border-gray-700 pt-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Winst</p>
                                <p class="text-xl font-bold {{ $project->profit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                    €{{ number_format($project->profit, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $project->invoices_count }} facturen</span>
                            @if($project->start_date)
                            <span>Start: {{ $project->start_date->format('M Y') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($projects->isEmpty())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-12">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Geen projecten</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start met het aanmaken van je eerste project.</p>
                    <div class="mt-6">
                        <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            + Nieuw Project
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
