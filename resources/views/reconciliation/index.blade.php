<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Reconciliatie - Duplicaten Koppelen
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Duplicate Groups -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Gekoppelde Duplicaten ({{ count($duplicateGroups) }} groepen)
                    </h3>

                    @if(count($duplicateGroups) > 0)
                        <div class="space-y-6">
                            @foreach($duplicateGroups as $group)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                                    <div class="mb-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            Memorial Ref: {{ $group['memorial_reference'] }}
                                        </span>
                                        <span class="ml-2 text-sm text-gray-500">{{ $group['count'] }} transacties</span>
                                    </div>

                                    <div class="space-y-2">
                                        <!-- Master Invoice -->
                                        @if($group['master'])
                                            <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded p-3">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-600 text-white mr-2">
                                                            MASTER
                                                        </span>
                                                        <span class="font-semibold text-gray-900 dark:text-gray-100">
                                                            {{ $group['master']->invoice_number }}
                                                        </span>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">
                                                            {{ $group['master']->source }}
                                                        </span>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">
                                                            €{{ number_format($group['master']->total, 2, ',', '.') }}
                                                        </span>
                                                    </div>
                                                    <a href="{{ route($group['master']->type === 'income' ? 'invoices.show' : 'expenses.show', $group['master']) }}"
                                                       class="text-sm text-blue-600 hover:text-blue-800">
                                                        Bekijken →
                                                    </a>
                                                </div>
                                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $group['master']->description }}
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Duplicate Invoices -->
                                        @foreach($group['duplicates'] as $duplicate)
                                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-3 ml-8">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 mr-2">
                                                            DUPLICATE
                                                        </span>
                                                        <span class="font-semibold text-gray-900 dark:text-gray-100">
                                                            {{ $duplicate->invoice_number }}
                                                        </span>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">
                                                            {{ $duplicate->source }}
                                                        </span>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">
                                                            €{{ number_format($duplicate->total, 2, ',', '.') }}
                                                        </span>
                                                        @if($duplicate->match_confidence)
                                                            <span class="text-xs text-gray-500 ml-2">
                                                                ({{ $duplicate->match_confidence }}% match)
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <a href="{{ route($duplicate->type === 'income' ? 'invoices.show' : 'expenses.show', $duplicate) }}"
                                                           class="text-sm text-blue-600 hover:text-blue-800">
                                                            Bekijken
                                                        </a>
                                                        <form action="{{ route('reconciliation.unlink', $duplicate) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="text-sm text-red-600 hover:text-red-800"
                                                                    onclick="return confirm('Weet je zeker dat je deze koppeling wilt verwijderen?')">
                                                                Ontkoppelen
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                @if($duplicate->match_notes)
                                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $duplicate->match_notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Geen gekoppelde duplicaten gevonden.</p>
                    @endif
                </div>
            </div>

            <!-- Unmatched Invoices -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Ongekoppelde Transacties ({{ $unmatchedInvoices->count() }})
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Deze geïmporteerde transacties hebben geen memorial reference en moeten handmatig worden beoordeeld.
                    </p>

                    @if($unmatchedInvoices->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nummer</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bron</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Datum</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Beschrijving</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bedrag</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acties</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($unmatchedInvoices as $invoice)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $invoice->invoice_number }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                {{ ucfirst($invoice->source) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                {{ $invoice->invoice_date->format('d-m-Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ Str::limit($invoice->description, 50) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                €{{ number_format($invoice->total, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <a href="{{ route($invoice->type === 'income' ? 'invoices.show' : 'expenses.show', $invoice) }}"
                                                   class="text-blue-600 hover:text-blue-800">
                                                    Bekijken →
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Alle geïmporteerde transacties hebben een memorial reference.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
