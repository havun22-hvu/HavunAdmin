<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Sync Details') }} - {{ ucfirst($sync->service) }}
            </h2>
            <a href="{{ route('sync.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Terug
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Sync Overview -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Overzicht</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($sync->service) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $sync->type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd class="mt-1">
                                        @if($sync->status === 'success')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                                                Success
                                            </span>
                                        @elseif($sync->status === 'failed')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">
                                                Failed
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-200">
                                                {{ ucfirst($sync->status) }}
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Gestart op</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $sync->started_at->format('d-m-Y H:i:s') }}
                                    </dd>
                                </div>
                                @if($sync->completed_at)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Voltooid op</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $sync->completed_at->format('d-m-Y H:i:s') }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duur</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $sync->started_at->diffInSeconds($sync->completed_at) }} seconden
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            @if($sync->items_found !== null)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Statistieken</h3>

                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $sync->items_found }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Gevonden</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    {{ $sync->items_processed }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Verwerkt</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ $sync->items_created }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Aangemaakt</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-yellow-600">
                                    {{ $sync->items_updated }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Bijgewerkt</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">
                                    {{ $sync->items_failed }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Mislukt</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Message -->
            @if($sync->error_message)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">Foutmelding</h3>
                        <pre class="text-sm text-red-700 dark:text-red-300 whitespace-pre-wrap font-mono">{{ $sync->error_message }}</pre>
                    </div>
                </div>
            @endif

            <!-- Metadata -->
            @if($sync->metadata)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Details</h3>

                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ json_encode($sync->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
