{{--
============================================================================
Composant : Vue Dashboard Livewire (Interface Utilisateur)
Technologie suggérée : Blade + Tailwind CSS + Livewire Wire:directives
Utilité pour mon projet : Interface graphique moderne du tableau de bord
avec cartes de solde, graphiques interactifs, et formulaire de transfert
Modal inclus pour les transferts Mobile Money
============================================================================
--}}

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    {{-- Container principal centré --}}
    <div class="max-w-7xl mx-auto">
        
        {{-- Header du Dashboard --}}
        <div class="mb-8">
            {{-- Titre principal avec icône --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        {{-- Logo MadaRevolut --}}
                        <svg class="w-10 h-10 mr-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                        MadaRevolut Dashboard
                    </h1>
                    {{-- Sous-titre avec nom d'utilisateur --}}
                    <p class="mt-2 text-sm text-gray-600">
                        Bienvenue, <span class="font-semibold">{{ Auth::user()->name }}</span> 
                        {{-- Badge du forfait --}}
                        <span class="ml-2 px-3 py-1 text-xs font-medium rounded-full 
                            {{ Auth::user()->subscription_plan === 'standard' ? 'bg-gray-200 text-gray-800' : 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white' }}">
                            {{ Auth::user()->subscriptionPlanName() }}
                        </span>
                    </p>
                </div>

                {{-- Bouton Actualiser --}}
                <button 
                    wire:click="refresh" 
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                    {{-- Icône actualiser (rotation au clic) --}}
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualiser
                </button>
            </div>
        </div>

        {{-- Messages de feedback (Succès/Erreur) --}}
        @if($successMessage)
            {{-- Message de succès (vert) --}}
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm animate-fade-in">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ $successMessage }}</p>
                </div>
            </div>
        @endif

        @if($errorMessage)
            {{-- Message d'erreur (rouge) --}}
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg shadow-sm animate-fade-in">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        {{-- Grid des cartes de solde --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            {{-- Carte 1: Solde Total --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium opacity-90">Solde Total</h3>
                    {{-- Icône portefeuille --}}
                    <svg class="w-8 h-8 opacity-80" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                {{-- Montant principal en grand --}}
                <p class="text-3xl font-bold">{{ number_format($totalBalance, 2, ',', ' ') }} <span class="text-lg">Ar</span></p>
                <p class="text-xs mt-2 opacity-75">Tous portefeuilles confondus</p>
            </div>

            {{-- Carte 2: Portefeuille Principal (MGA) --}}
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600">Ariary (MGA)</h3>
                    {{-- Icône billets --}}
                    <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($mainWalletBalance, 2, ',', ' ') }} <span class="text-lg text-gray-600">Ar</span></p>
                <p class="text-xs mt-2 text-gray-500">Portefeuille principal</p>
            </div>

            {{-- Carte 3: Portefeuille Crypto --}}
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600">Cryptomonnaies</h3>
                    {{-- Icône Bitcoin --}}
                    <svg class="w-8 h-8 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 5a1 1 0 011-1h1a1 1 0 010 2H9v1h1a1 1 0 010 2H9v1h1a1 1 0 010 2H8a1 1 0 01-1-1v-1H6a1 1 0 010-2h1V7H6a1 1 0 010-2h1V4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($cryptoWalletBalance, 8, ',', ' ') }} <span class="text-lg text-gray-600">₿</span></p>
                <p class="text-xs mt-2 text-gray-500">Bitcoin & Ethereum</p>
            </div>

            {{-- Carte 4: Portefeuille Or --}}
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600">Or Physique</h3>
                    {{-- Icône lingot d'or --}}
                    <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($goldWalletBalance, 2, ',', ' ') }} <span class="text-lg text-gray-600">g</span></p>
                <p class="text-xs mt-2 text-gray-500">Grammes d'or XAU</p>
            </div>

        </div>

        {{-- Section Actions Rapides --}}
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 7H7v6h6V7z"/>
                    <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"/>
                </svg>
                Actions Rapides
            </h2>
            
            {{-- Grid de boutons d'action --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                {{-- Bouton Transférer vers Mobile Money --}}
                <button 
                    wire:click="openTransferModal"
                    class="flex items-center justify-center px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg shadow-md hover:shadow-xl transform hover:scale-105 transition duration-300">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z"/>
                    </svg>
                    <span class="font-semibold">Transférer vers Mobile Money</span>
                </button>

                {{-- Bouton Recharger (désactivé pour le MVP) --}}
                <button 
                    disabled
                    class="flex items-center justify-center px-6 py-4 bg-gray-200 text-gray-500 rounded-lg shadow-md cursor-not-allowed opacity-60">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">Recharger (Bientôt)</span>
                </button>

                {{-- Bouton Investir (désactivé pour le MVP) --}}
                <button 
                    disabled
                    class="flex items-center justify-center px-6 py-4 bg-gray-200 text-gray-500 rounded-lg shadow-md cursor-not-allowed opacity-60">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">Investir (Bientôt)</span>
                </button>

            </div>
        </div>

        {{-- Section Transactions Récentes --}}
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                </svg>
                Transactions Récentes
            </h2>

            {{-- Tableau des transactions --}}
            @if(count($recentTransactions) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Détails</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentTransactions as $transaction)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    {{-- Date --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    {{-- Type --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $transaction->transactionTypeName() }}
                                    </td>
                                    {{-- Montant --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold 
                                        {{ $transaction->transaction_type === 'deposit' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->transaction_type === 'deposit' ? '+' : '-' }}
                                        {{ $transaction->formattedAmount() }}
                                    </td>
                                    {{-- Statut avec badge coloré --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php $badge = $transaction->statusBadge(); @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            bg-{{ $badge['color'] }}-100 text-{{ $badge['color'] }}-800">
                                            {{ $badge['text'] }}
                                        </span>
                                    </td>
                                    {{-- Description --}}
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        {{ $transaction->description ?: 'Aucune description' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Message si aucune transaction --}}
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-gray-500 text-lg">Aucune transaction récente</p>
                    <p class="text-gray-400 text-sm mt-2">Vos transactions apparaîtront ici</p>
                </div>
            @endif
        </div>

        {{-- Section Graphique de Dépenses (Placeholder pour ApexCharts) --}}
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                </svg>
                Analyse des Dépenses (30 derniers jours)
            </h2>

            {{-- Div pour le graphique ApexCharts (à implémenter avec JavaScript) --}}
            <div id="expenseChart" class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                <p class="text-gray-400">Graphique ApexCharts à intégrer ici</p>
                {{-- Note: Utiliser Alpine.js ou script externe pour initialiser ApexCharts avec $expenseChartData --}}
            </div>
        </div>

    </div>

    {{-- Modal de Transfert Mobile Money --}}
    @if($showTransferModal)
        {{-- Overlay sombre (fond semi-transparent) --}}
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 flex items-center justify-center p-4 animate-fade-in"
             wire:click="closeTransferModal">
            
            {{-- Contenu du modal (empêcher la fermeture au clic) --}}
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all animate-slide-up"
                 wire:click.stop>
                
                {{-- Header du modal --}}
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z"/>
                        </svg>
                        Transfert Mobile Money
                    </h3>
                    {{-- Bouton fermer --}}
                    <button wire:click="closeTransferModal" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                {{-- Formulaire de transfert --}}
                <form wire:submit.prevent="submitTransfer" class="space-y-5">
                    
                    {{-- Champ Montant --}}
                    <div>
                        <label for="transferAmount" class="block text-sm font-medium text-gray-700 mb-2">
                            Montant à transférer (Ar) *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Ar</span>
                            </div>
                            <input 
                                type="number" 
                                id="transferAmount"
                                wire:model="transferAmount"
                                class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                placeholder="Ex: 50000"
                                min="1000"
                                step="100"
                                required>
                        </div>
                        @error('transferAmount') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Montant minimum: 1 000 Ar • Frais: 5%</p>
                    </div>

                    {{-- Champ Numéro Mobile Money --}}
                    <div>
                        <label for="transferPhoneNumber" class="block text-sm font-medium text-gray-700 mb-2">
                            Numéro destinataire *
                        </label>
                        <input 
                            type="tel" 
                            id="transferPhoneNumber"
                            wire:model="transferPhoneNumber"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="Ex: 034 00 123 45"
                            required>
                        @error('transferPhoneNumber') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- Champ Opérateur Mobile Money --}}
                    <div>
                        <label for="transferProvider" class="block text-sm font-medium text-gray-700 mb-2">
                            Opérateur Mobile Money *
                        </label>
                        <select 
                            id="transferProvider"
                            wire:model="transferProvider"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="mvola">MVola (Telma)</option>
                            <option value="orange">Orange Money</option>
                            <option value="airtel">Airtel Money</option>
                        </select>
                        @error('transferProvider') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- Champ Description (optionnel) --}}
                    <div>
                        <label for="transferDescription" class="block text-sm font-medium text-gray-700 mb-2">
                            Description (optionnel)
                        </label>
                        <textarea 
                            id="transferDescription"
                            wire:model="transferDescription"
                            rows="2"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-none"
                            placeholder="Ex: Paiement loyer février"></textarea>
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="flex space-x-3 pt-4">
                        {{-- Bouton Annuler --}}
                        <button 
                            type="button"
                            wire:click="closeTransferModal"
                            class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-150">
                            Annuler
                        </button>
                        {{-- Bouton Confirmer --}}
                        <button 
                            type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:shadow-lg transform hover:scale-105 transition duration-300">
                            Confirmer le transfert
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

{{-- Styles CSS additionnels pour les animations --}}
<style>
    /* Animation fade-in pour les messages */
    @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-in-out;
    }

    /* Animation slide-up pour le modal */
    @keyframes slide-up {
        from { 
            opacity: 0;
            transform: translateY(20px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-slide-up {
        animation: slide-up 0.3s ease-out;
    }
</style>

{{-- Script Alpine.js / JavaScript pour ApexCharts (à ajouter dans un fichier séparé) --}}
{{-- 
<script>
    // Exemple d'initialisation ApexCharts avec les données Livewire
    const options = {
        chart: { type: 'donut', height: 350 },
        series: @json($expenseChartData['values']),
        labels: @json($expenseChartData['labels']),
        colors: ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
        legend: { position: 'bottom' }
    };
    const chart = new ApexCharts(document.querySelector("#expenseChart"), options);
    chart.render();
</script>
--}}
</div>
