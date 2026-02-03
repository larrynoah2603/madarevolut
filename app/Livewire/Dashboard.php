<?php
/**
 * ============================================================================
 * Composant : Dashboard Livewire (Tableau de Bord Principal)
 * Technologie suggérée : Laravel Livewire 3 (Composant Full-Stack)
 * Utilité pour mon projet : Dashboard financier en temps réel sans
 * rechargement de page - affiche soldes, graphiques de dépenses, et
 * permet les transferts Mobile Money instantanés
 * ============================================================================
 */

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\Wallet;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    /**
     * Propriétés publiques Livewire (réactives - mises à jour automatiques)
     * Ces variables sont synchronisées entre le serveur et le navigateur
     */
    
    // Solde total de l'utilisateur (tous portefeuilles confondus)
    public $totalBalance = 0;

    // Solde du portefeuille principal (MGA)
    public $mainWalletBalance = 0;

    // Solde du portefeuille crypto
    public $cryptoWalletBalance = 0;

    // Solde du portefeuille or
    public $goldWalletBalance = 0;

    // Transactions récentes (5 dernières)
    public $recentTransactions = [];

    // Données pour le graphique de dépenses (Chart.js/ApexCharts)
    public $expenseChartData = [];

    // État du modal de transfert (ouvert/fermé)
    public $showTransferModal = false;

    // Formulaire de transfert Mobile Money
    public $transferAmount = '';
    public $transferPhoneNumber = '';
    public $transferProvider = 'mvola'; // Par défaut MVola
    public $transferDescription = '';

    // Messages de feedback utilisateur
    public $successMessage = '';
    public $errorMessage = '';

    /**
     * Règles de validation pour le formulaire de transfert
     * Laravel valide automatiquement ces champs avant soumission
     *
     * @var array
     */
    protected $rules = [
        'transferAmount' => 'required|numeric|min:1000', // Minimum 1000 Ar
        'transferPhoneNumber' => 'required|regex:#^(0|\\+261)[0-9]{9}$#', // Format malgache
        'transferProvider' => 'required|in:mvola,orange,airtel', // Opérateurs valides
        'transferDescription' => 'nullable|string|max:255', // Description optionnelle
    ];

    /**
     * Messages de validation personnalisés en français
     *
     * @var array
     */
    protected $messages = [
        'transferAmount.required' => 'Le montant est obligatoire.',
        'transferAmount.numeric' => 'Le montant doit être un nombre.',
        'transferAmount.min' => 'Le montant minimum est de 1 000 Ar.',
        'transferPhoneNumber.required' => 'Le numéro de téléphone est obligatoire.',
        'transferPhoneNumber.regex' => 'Format invalide. Ex: 034 00 123 45 ou +261 34 00 123 45',
        'transferProvider.required' => 'Veuillez sélectionner un opérateur.',
        'transferProvider.in' => 'Opérateur invalide.',
    ];

    /**
     * Hook Livewire : Méthode exécutée lors du chargement initial du composant
     * Charge toutes les données financières de l'utilisateur
     */
    public function mount()
    {
        // Chargement des données au montage du composant
        $this->loadDashboardData();
    }

    /**
     * Charge toutes les données du dashboard (soldes, transactions, graphiques)
     * Méthode centrale qui récupère les infos depuis la base de données
     */
    public function loadDashboardData()
    {
        // Récupération de l'utilisateur connecté (via Laravel Auth)
        $user = Auth::user();

        // Chargement eager loading des portefeuilles (optimisation requête)
        // Évite le problème N+1 queries
        $user->load('wallets', 'investments');

        // === CALCUL DES SOLDES ===
        
        // Portefeuille principal (Ariary)
        $mainWallet = $user->mainWallet();
        $this->mainWalletBalance = $mainWallet ? $mainWallet->balance : 0;

        // Portefeuille crypto (Bitcoin/Ethereum)
        $cryptoWallet = $user->cryptoWallet();
        $this->cryptoWalletBalance = $cryptoWallet ? $cryptoWallet->balance : 0;

        // Portefeuille or
        $goldWallet = $user->goldWallet();
        $this->goldWalletBalance = $goldWallet ? $goldWallet->balance : 0;

        // Solde total (tous portefeuilles)
        // Note: Dans une vraie app, il faudrait convertir crypto et or en MGA
        $this->totalBalance = $this->mainWalletBalance + $this->cryptoWalletBalance + $this->goldWalletBalance;

        // === CHARGEMENT DES TRANSACTIONS RÉCENTES ===
        
        // Récupère les 5 dernières transactions de l'utilisateur
        // Inclut les transactions entrantes ET sortantes
        $walletIds = $user->wallets->pluck('id')->toArray(); // IDs de tous les portefeuilles

        $this->recentTransactions = Transaction::where(function ($query) use ($walletIds) {
            // Transactions où l'utilisateur est source OU destination
            $query->whereIn('from_wallet_id', $walletIds)
                  ->orWhereIn('to_wallet_id', $walletIds);
        })
        ->with(['fromWallet', 'toWallet']) // Eager loading pour éviter N+1
        ->latest() // Tri par date décroissante (plus récentes en premier)
        ->limit(5) // Limite à 5 résultats
        ->get();

        // === CHARGEMENT DES DONNÉES GRAPHIQUE ===
        
        // Statistiques de dépenses par catégorie (30 derniers jours)
        $this->loadExpenseChartData();
    }

    /**
     * Charge les données pour le graphique de dépenses mensuel
     * Groupé par type de transaction avec montants totaux
     */
    private function loadExpenseChartData()
    {
        // Récupération de l'utilisateur
        $user = Auth::user();
        $walletIds = $user->wallets->pluck('id')->toArray();

        // Requête SQL groupée par type de transaction
        // Somme des montants par catégorie sur les 30 derniers jours
        $expenses = Transaction::whereIn('from_wallet_id', $walletIds) // Transactions sortantes
            ->where('status', 'completed') // Uniquement les transactions finalisées
            ->where('created_at', '>=', now()->subDays(30)) // 30 derniers jours
            ->select(
                'transaction_type',
                DB::raw('SUM(amount) as total_amount') // Somme des montants
            )
            ->groupBy('transaction_type') // Groupement par type
            ->get();

        // Transformation des données pour ApexCharts
        // Format: { labels: [...], values: [...] }
        $this->expenseChartData = [
            'labels' => $expenses->pluck('transaction_type')->map(function ($type) {
                // Traduction des types en français
                $translations = [
                    'transfer' => 'Virements',
                    'withdrawal' => 'Retraits',
                    'investment' => 'Investissements',
                    'fee' => 'Frais',
                    'subscription' => 'Abonnements',
                ];
                return $translations[$type] ?? $type;
            })->toArray(),
            'values' => $expenses->pluck('total_amount')->toArray(), // Montants
        ];
    }

    /**
     * Ouvre le modal de transfert Mobile Money
     * Appelé lors du clic sur le bouton "Transférer"
     */
    public function openTransferModal()
    {
        // Active l'affichage du modal
        $this->showTransferModal = true;

        // Réinitialise les messages
        $this->resetMessages();
    }

    /**
     * Ferme le modal de transfert
     * Appelé lors du clic sur "Annuler" ou en dehors du modal
     */
    public function closeTransferModal()
    {
        // Désactive l'affichage du modal
        $this->showTransferModal = false;

        // Réinitialise le formulaire
        $this->resetTransferForm();
    }

    /**
     * Traite le transfert vers Mobile Money
     * Valide les données, crée la transaction, et met à jour les soldes
     */
    public function submitTransfer()
    {
        // Validation des données du formulaire
        // Laravel Livewire valide automatiquement avec $rules
        $this->validate();

        try {
            // Début de transaction SQL (rollback automatique en cas d'erreur)
            DB::beginTransaction();

            // Récupération de l'utilisateur et son portefeuille principal
            $user = Auth::user();
            $wallet = $user->mainWallet();

            // Vérification : portefeuille existe ?
            if (!$wallet) {
                throw new \Exception('Portefeuille principal introuvable.');
            }

            // Montant + frais (5% pour les retraits Mobile Money)
            $amount = floatval($this->transferAmount);
            $fee = $amount * 0.05; // 5% de frais
            $totalDebit = $amount + $fee;

            // Vérification : solde suffisant ?
            if (!$wallet->hasSufficientBalance($totalDebit)) {
                throw new \Exception('Solde insuffisant. Disponible: ' . number_format($wallet->availableBalance(), 2, ',', ' ') . ' Ar');
            }

            // Nettoyage du numéro de téléphone
            $phoneNumber = preg_replace('/[^0-9]/', '', $this->transferPhoneNumber);

            // Création de la transaction de retrait
            $transaction = Transaction::create([
                'from_wallet_id' => $wallet->id, // Portefeuille source
                'to_wallet_id' => null, // Pas de portefeuille destination (externe)
                'transaction_type' => 'withdrawal', // Type: retrait
                'amount' => $amount, // Montant du transfert
                'currency' => 'MGA', // Devise Ariary
                'fee' => $fee, // Frais calculés
                'status' => 'processing', // Statut initial: en cours
                'mobile_money_number' => $phoneNumber, // Numéro destinataire
                'mobile_money_provider' => $this->transferProvider, // Opérateur
                'description' => $this->transferDescription ?: 'Retrait Mobile Money', // Description
            ]);

            // Débit du portefeuille (montant + frais)
            $wallet->debit($totalDebit);

            // Simulation : Dans une vraie app, on appellerait ici l'API Mobile Money
            // Pour le MVP, on finalise directement la transaction
            $transaction->markAsCompleted();

            // Génération d'une référence fictive Mobile Money
            $transaction->mobile_money_reference = 'MM' . strtoupper(substr($this->transferProvider, 0, 3)) . rand(100000000, 999999999);
            $transaction->save();

            // Validation de la transaction SQL
            DB::commit();

            // Message de succès
            $this->successMessage = 'Transfert de ' . number_format($amount, 2, ',', ' ') . ' Ar effectué avec succès vers ' . $phoneNumber . ' (' . strtoupper($this->transferProvider) . ')';

            // Rechargement des données du dashboard
            $this->loadDashboardData();

            // Fermeture du modal après 2 secondes (via JavaScript)
            $this->dispatch('transfer-success'); // Événement Livewire custom

            // Réinitialisation du formulaire
            $this->resetTransferForm();

        } catch (\Exception $e) {
            // Annulation de la transaction SQL en cas d'erreur
            DB::rollBack();

            // Message d'erreur
            $this->errorMessage = 'Erreur : ' . $e->getMessage();
        }
    }

    /**
     * Rafraîchit les données du dashboard (appelé manuellement)
     * Utilisé pour le bouton "Actualiser"
     */
    public function refresh()
    {
        // Rechargement complet des données
        $this->loadDashboardData();

        // Message de confirmation
        $this->successMessage = 'Données actualisées avec succès.';
    }

    /**
     * Réinitialise le formulaire de transfert
     */
    private function resetTransferForm()
    {
        // Remise à zéro de tous les champs
        $this->transferAmount = '';
        $this->transferPhoneNumber = '';
        $this->transferProvider = 'mvola';
        $this->transferDescription = '';

        // Réinitialise les messages
        $this->resetMessages();
    }

    /**
     * Réinitialise les messages de feedback
     */
    private function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    /**
     * Méthode render() : retourne la vue Blade du composant
     * Appelée automatiquement par Livewire à chaque mise à jour
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Retourne la vue Blade avec toutes les données
        return view('livewire.dashboard')
            ->layout('layouts.app');
    }
}
