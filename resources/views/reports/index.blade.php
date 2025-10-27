<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rapportages & Export') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success/Error Messages --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

                {{-- Quarterly Report Card --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-800">Kwartaaloverzicht</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Voor omzetbelasting aangifte. Exporteert alle inkomsten per kwartaal met project opsplitsing.
                        </p>
                        <form action="{{ route('reports.quarterly') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jaar</label>
                                <select name="year" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @for ($y = date('Y') + 1; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kwartaal</label>
                                <select name="quarter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" {{ $currentQuarter == 1 ? 'selected' : '' }}>Q1 (Jan-Mrt)</option>
                                    <option value="2" {{ $currentQuarter == 2 ? 'selected' : '' }}>Q2 (Apr-Jun)</option>
                                    <option value="3" {{ $currentQuarter == 3 ? 'selected' : '' }}>Q3 (Jul-Sep)</option>
                                    <option value="4" {{ $currentQuarter == 4 ? 'selected' : '' }}>Q4 (Okt-Dec)</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150 ease-in-out">
                                Exporteer Kwartaal
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Yearly Report Card --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-800">Jaaroverzicht</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Voor inkomstenbelasting aangifte. Exporteert alle inkomsten en uitgaven van het hele jaar met winst berekening.
                        </p>
                        <form action="{{ route('reports.yearly') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jaar</label>
                                <select name="year" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @for ($y = date('Y') + 1; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150 ease-in-out">
                                Exporteer Jaaroverzicht
                            </button>
                        </form>
                    </div>
                </div>

                {{-- BTW Report Card --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-800">BTW Aangifte</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Voor BTW aangifte (wanneer BTW-plichtig). Berekent verschuldigde BTW minus voorbelasting.
                        </p>
                        <form action="{{ route('reports.btw') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jaar</label>
                                <select name="year" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @for ($y = date('Y') + 1; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kwartaal</label>
                                <select name="quarter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" {{ $currentQuarter == 1 ? 'selected' : '' }}>Q1 (Jan-Mrt)</option>
                                    <option value="2" {{ $currentQuarter == 2 ? 'selected' : '' }}>Q2 (Apr-Jun)</option>
                                    <option value="3" {{ $currentQuarter == 3 ? 'selected' : '' }}>Q3 (Jul-Sep)</option>
                                    <option value="4" {{ $currentQuarter == 4 ? 'selected' : '' }}>Q4 (Okt-Dec)</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-md transition duration-150 ease-in-out">
                                Exporteer BTW Aangifte
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            {{-- Previously Generated Exports --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Eerder Gegenereerde Exports</h3>

                    @if (count($exports) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bestandsnaam</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grootte</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aangemaakt</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($exports as $export)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $export['name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($export['size'] / 1024, 2) }} KB
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ date('d-m-Y H:i', $export['modified']) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('reports.download', $export['name']) }}" class="text-blue-600 hover:text-blue-900 mr-3">Download</a>
                                                <form action="{{ route('reports.delete', $export['name']) }}" method="POST" class="inline" onsubmit="return confirm('Weet je zeker dat je dit bestand wilt verwijderen?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Verwijderen</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Nog geen exports gegenereerd.</p>
                    @endif
                </div>
            </div>

            {{-- Information Box --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="text-md font-semibold text-blue-800 mb-2">Informatie over exports</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li><strong>Kwartaaloverzicht:</strong> Gebruik dit voor je kwartaal omzetbelasting aangifte bij de Belastingdienst.</li>
                    <li><strong>Jaaroverzicht:</strong> Gebruik dit voor je jaarlijkse inkomstenbelasting aangifte.</li>
                    <li><strong>BTW Aangifte:</strong> Alleen nodig wanneer je BTW-plichtig bent (omzet > €20.000/jaar).</li>
                    <li><strong>Bestandsformaat:</strong> Alle exports zijn CSV bestanden die je kunt openen in Excel.</li>
                    <li><strong>Bewaarplicht:</strong> Bewaar deze exports minimaal 7 jaar voor de Belastingdienst.</li>
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>
