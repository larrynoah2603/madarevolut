{{--
============================================================================
Composant : Vue Investment Form (Formulaire d'Investissement)
Technologie suggérée : Blade + Tailwind CSS + Livewire
Utilité pour mon projet : Interface d'achat de Bitcoin/Ethereum/Or
avec calcul en temps réel de la quantité, prix actuels, et modal
de confirmation avant finalisation de l'investissement
============================================================================
--}}

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    {{-- Container principal --}}
    <div class="max-w-4xl mx-auto">
        
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                {{-- Icône investissement --}}
                <svg class="w-10 h-10 mr-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                </svg>
                Investir en Crypto & Or
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                Achetez du Bitcoin, Ethereum ou de l'Or avec vos Ariary (MGA)
            </p>
        </div>

        {{-- Messages de feedback --}}
        @if($successMessage)
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
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg shadow-sm animate-fade-in">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        {{-- Formulaire principal --}}
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
            
            {{-- Section 1: Sélection de l'actif --}}
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    1. Choisissez votre actif
                </h2>

                {{-- Grid de cartes d'actifs --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    {{-- Carte Bitcoin --}}
                    <div 
                        wire:click="$set('assetType', 'bitcoin')"
                        class="cursor-pointer p-6 rounded-xl border-2 transition duration-300 transform hover:scale-105
                            {{ $assetType === 'bitcoin' ? 'border-orange-500 bg-orange-50 shadow-lg' : 'border-gray-200 hover:border-orange-300' }}">
                        <div class="flex flex-col items-center">
                            {{-- Icône Bitcoin --}}
                            <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mb-3">
                                <svg class="w-10 h-10 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 5a1 1 0 011-1h1a1 1 0 010 2H9v1h1a1 1 0 010 2H9v1h1a1 1 0 010 2H8a1 1 0 01-1-1v-1H6a1 1 0 010-2h1V7H6a1 1 0 010-2h1V4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Bitcoin</h3>
                            <p class="text-sm text-gray-500 mt-1">BTC</p>
                            @if($assetType === 'bitcoin')
                                <span class="mt-2 px-3 py-1 bg-orange-500 text-white text-xs font-semibold rounded-full">
                                    Sélectionné ✓
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Carte Ethereum --}}
                    <div 
                        wire:click="$set('assetType', 'ethereum')"
                        class="cursor-pointer p-6 rounded-xl border-2 transition duration-300 transform hover:scale-105
                            {{ $assetType === 'ethereum' ? 'border-blue-500 bg-blue-50 shadow-lg' : 'border-gray-200 hover:border-blue-300' }}">
                        <div class="flex flex-col items-center">
                            {{-- Icône Ethereum --}}
                            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                                <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 3.5L6 10.5l4 2.25 4-2.25L10 3.5z"/>
                                    <path d="M6 11.5l4 5.5 4-5.5-4 2.25-4-2.25z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Ethereum</h3>
                            <p class="text-sm text-gray-500 mt-1">ETH</p>
                            @if($assetType === 'ethereum')
                                <span class="mt-2 px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-full">
                                    Sélectionné ✓
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Carte Or --}}
                    <div 
                        wire:click="$set('assetType', 'gold')"
                        class="cursor-pointer p-6 rounded-xl border-2 transition duration-300 transform hover:scale-105
                            {{ $assetType === 'gold' ? 'border-yellow-500 bg-yellow-50 shadow-lg' : 'border-gray-200 hover:border-yellow-300' }}">
                        <div class="flex flex-col items-center">
                            {{-- Icône Or --}}
                            <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mb-3">
                                <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Or Physique</h3>
                            <p class="text-sm text-gray-500 mt-1">XAU</p>
                            @if($assetType === 'gold')
                                <span class="mt-2 px-3 py-1 bg-yellow-500 text-white text-xs font-semibold rounded-full">
                                    Sélectionné ✓
                                </span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- Section 2: Prix actuel --}}
            <div class="mb-8 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">
                    2. Prix actuel du marché
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Prix en USD --}}
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <p class="text-sm text-gray-600 mb-1">Prix en USD</p>
                        <p class="text-2xl font-bold text-gray-900">
                            ${{ number_format($currentPriceUSD, 2, '.', ',') }}
                        </p>
                    </div>
                    {{-- Prix en Ariary --}}
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <p class="text-sm text-gray-600 mb-1">Prix en Ariary</p>
                        <p class="text-2xl font-bold text-indigo-600">
                            {{ number_format($currentPriceMGA, 2, ',', ' ') }} Ar
                        </p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    ⓘ Prix mis à jour automatiquement • Taux USD/MGA: {{ number_format($usdToMgaRate, 0, ',', ' ') }}
                </p>
            </div>

            {{-- Section 3: Montant à investir --}}
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    3. Montant à investir
                </h2>
                
                <div>
                    <label for="investmentAmount" class="block text-sm font-medium text-gray-700 mb-2">
                        Montant en Ariary (Ar) *
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-lg font-semibold">Ar</span>
                        </div>
                        <input 
                            type="number" 
                            id="investmentAmount"
                            wire:model.live="investmentAmount"
                            class="block w-full pl-12 pr-12 py-4 text-lg border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="Ex: 100000"
                            min="10000"
                            step="1000"
                            required>
                    </div>
                    @error('investmentAmount') 
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">
                        Montant minimum: 10 000 Ar • Frais de transaction: 2%
                    </p>
                </div>

                {{-- Boutons de montant rapide --}}
                <div class="mt-4 flex flex-wrap gap-2">
                    <button 
                        wire:click="$set('investmentAmount', 50000)"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                        50 000 Ar
                    </button>
                    <button 
                        wire:click="$set('investmentAmount', 100000)"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                        100 000 Ar
                    </button>
                    <button 
                        wire:click="$set('investmentAmount', 250000)"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                        250 000 Ar
                    </button>
                    <button 
                        wire:click="$set('investmentAmount', 500000)"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                        500 000 Ar
                    </button>
                </div>
            </div>

            {{-- Section 4: Estimation de quantité --}}
            @if($investmentAmount && $estimatedQuantity > 0)
                <div class="mb-8 p-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-2 border-green-300">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Estimation de votre achat
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Quantité estimée --}}
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-600 mb-1">Vous recevrez</p>
                            <p class="text-3xl font-bold text-green-600">
                                {{ number_format($estimatedQuantity, 8, ',', '') }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">{{ $this->assetName }}</p>
                        </div>
                        
                        {{-- Frais --}}
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-600 mb-1">Frais de transaction (2%)</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($investmentAmount * 0.02, 2, ',', ' ') }} Ar
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Total débité: {{ number_format($investmentAmount * 1.02, 2, ',', ' ') }} Ar
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Bouton d'achat --}}
            <div class="flex justify-center">
                <button 
                    wire:click="openConfirmModal"
                    class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-lg font-bold rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition duration-300 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                    </svg>
                    Acheter maintenant
                </button>
            </div>

        </div>

    </div>

    {{-- Modal de confirmation --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4 animate-fade-in"
             wire:click="closeConfirmModal">
            
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8 transform transition-all animate-slide-up"
                 wire:click.stop>
                
                {{-- Header du modal --}}
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Confirmer l'achat
                    </h3>
                    <button wire:click="closeConfirmModal" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                {{-- Récapitulatif de l'achat --}}
                <div class="bg-gray-50 rounded-xl p-6 mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-4">Récapitulatif de votre achat</h4>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Actif sélectionné:</span>
                            <span class="font-bold text-gray-900">{{ $this->assetName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Montant investi:</span>
                            <span class="font-bold text-gray-900">{{ number_format($investmentAmount, 2, ',', ' ') }} Ar</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Frais (2%):</span>
                            <span class="font-bold text-gray-900">{{ number_format($investmentAmount * 0.02, 2, ',', ' ') }} Ar</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="text-gray-700 font-semibold">Total débité:</span>
                            <span class="font-bold text-indigo-600 text-lg">{{ number_format($investmentAmount * 1.02, 2, ',', ' ') }} Ar</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between bg-green-50 -mx-6 px-6 py-3 rounded-lg">
                            <span class="text-green-700 font-semibold">Vous recevrez:</span>
                            <span class="font-bold text-green-600 text-lg">{{ number_format($estimatedQuantity, 8, ',', '') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Avertissement --}}
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg">
                    <p class="text-sm text-yellow-800">
                        <strong>⚠️ Attention:</strong> Les investissements comportent des risques. La valeur de votre investissement peut fluctuer.
                    </p>
                </div>

                {{-- Boutons d'action --}}
                <div class="flex space-x-3">
                    <button 
                        wire:click="closeConfirmModal"
                        class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button 
                        wire:click="confirmPurchase"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:shadow-lg transform hover:scale-105 transition">
                        Confirmer l'achat
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>

{{-- Styles CSS pour animations --}}
<style>
    @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-in-out;
    }

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
