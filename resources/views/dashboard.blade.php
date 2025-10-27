<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }} - {{ $currentYear }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    + Nieuwe Factuur
                </a>
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                    + Nieuwe Uitgave
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Year Statistics -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dit Jaar ({{ $currentYear }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Revenue -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Omzet</div>
                            <div class="mt-1 text-3xl font-semibold text-green-600">
                                €{{ number_format($ytdRevenue, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Expenses -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Kosten</div>
                            <div class="mt-1 text-3xl font-semibold text-red-600">
                                €{{ number_format($ytdExpenses, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Profit -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Winst</div>
                            <div class="mt-1 text-3xl font-semibold {{ $ytdProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                €{{ number_format($ytdProfit, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quarter Statistics -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dit Kwartaal (Q{{ now()->quarter }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Omzet</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            €{{ number_format($quarterRevenue, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Kosten</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            €{{ number_format($quarterExpenses, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Winst</div>
                        <div class="mt-1 text-2xl font-semibold {{ $quarterProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            €{{ number_format($quarterProfit, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if($unpaidInvoicesCount > 0 || $overdueInvoicesCount > 0)
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($unpaidInvoicesCount > 0)
                <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                <strong>{{ $unpaidInvoicesCount }}</strong> onbetaalde facturen (€{{ number_format($unpaidInvoicesTotal, 2, ',', '.') }})
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($overdueInvoicesCount > 0)
                <div class="bg-red-50 dark:bg-red-900 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 dark:text-red-200">
                                <strong>{{ $overdueInvoicesCount }}</strong> verlopen facturen (€{{ number_format($overdueInvoicesTotal, 2, ',', '.') }})
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Charts Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Revenue by Project -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Omzet per Project</h4>
                    @if($revenueByProject->count() > 0)
                        <div class="space-y-3">
                            @foreach($revenueByProject as $item)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item['project'] }}</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        €{{ number_format($item['total'], 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ ($item['total'] / $ytdRevenue * 100) }}%; background-color: {{ $item['color'] }}"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Nog geen omzet dit jaar</p>
                    @endif
                </div>

                <!-- Expenses by Category -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Kosten per Categorie</h4>
                    @if($expensesByCategory->count() > 0)
                        <div class="space-y-3">
                            @foreach($expensesByCategory as $item)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item['category'] }}</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        €{{ number_format($item['total'], 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ ($item['total'] / $ytdExpenses * 100) }}%; background-color: {{ $item['color'] }}"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Nog geen kosten dit jaar</p>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Recent Invoices -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recente Facturen</h4>
                            <a href="{{ route('invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Alle facturen →</a>
                        </div>
                        @if($recentInvoices->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentInvoices as $invoice)
                                <div class="flex justify-between items-start">
                                    <div>
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $invoice->customer ? $invoice->customer->name : 'Geen klant' }} • {{ $invoice->invoice_date->format('d-m-Y') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">€{{ number_format($invoice->total, 2, ',', '.') }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($invoice->status === 'paid') bg-green-100 text-green-800
                                            @elseif($invoice->status === 'sent') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Geen facturen gevonden</p>
                        @endif
                    </div>
                </div>

                <!-- Recent Expenses -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recente Uitgaven</h4>
                            <a href="{{ route('expenses.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Alle uitgaven →</a>
                        </div>
                        @if($recentExpenses->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentExpenses as $expense)
                                <div class="flex justify-between items-start">
                                    <div>
                                        <a href="{{ route('expenses.show', $expense) }}" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600">
                                            {{ $expense->invoice_number }}
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $expense->supplier ? $expense->supplier->name : 'Geen leverancier' }} • {{ $expense->invoice_date->format('d-m-Y') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">€{{ number_format($expense->total, 2, ',', '.') }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($expense->status === 'paid') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($expense->status) }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Geen uitgaven gevonden</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
