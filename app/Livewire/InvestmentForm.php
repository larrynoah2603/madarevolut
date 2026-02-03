<?php
/**
 * ============================================================================
 * Composant : Investment Livewire (Achat Crypto & Or)
 * Technologie suggérée : Laravel Livewire 3 + API Prix en Temps Réel
 * Utilité pour mon projet : Permet aux utilisateurs d'acheter fictivement
 * du Bitcoin, Ethereum ou de l'Or avec leurs Ariary (MGA) et enregistre
 * l'investissement dans leur portfolio avec calcul automatique des PnL
 * ============================================================================
 */

namespace App\Livewire;

use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Wallet;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestmentForm extends Component
{
    /**
     * Propriétés publiques Livewire (réactives)
     */
    
    // Type d'actif sélectionné ('bitcoin', 'ethereum', 'gold')
    public $assetType = 'bitcoin';

    // Montant en Ariary à investir
    public $investmentAmount = '';

    // Prix actuel de l'actif (mis à jour en temps réel)
    public $currentPriceMGA = 0;
    public $currentPriceUSD = 0;

    // Quantité d'actif que l'utilisateur recevra
    public $estimatedQuantity = 0;

    // Taux de change USD/MGA fictif (pour le MVP)
    // Dans une vraie app, récupérer via API (ex: exchangerate-api.com)
    public $usdToMgaRate = 4500; // 1 USD = 4500 Ar (approximatif)

    // Messages de feedback
    public $successMessage = '';
    public $errorMessage = '';

    // Modal de confirmation d'achat
    public $showConfirmModal = false;

    /**
     * Règles de validation
     *
     * @var array
     */
    protected $rules = [
        'assetType' => 'required|in:bitcoin,ethereum,gold',
        'investmentAmount' => 'required|numeric|min:10000', // Minimum 10 000 Ar
    ];

    /**
     * Messages de validation personnalisés
     *
     * @var array
     */
    protected $messages = [
        'assetType.required' => 'Veuillez sélectionner un actif.',
        'assetType.in' => 'Type d\'actif invalide.',
        'investmentAmount.required' => 'Le montant est obligatoire.',
        'investmentAmount.numeric' => 'Le montant doit être un nombre.',
        'investmentAmount.min' => 'Le montant minimum est de 10 000 Ar.',
    ];

    /**
     * Hook Livewire : Chargement initial
     */
    public function mount()
    {
        // Charge les prix actuels au montage
        $this->loadCurrentPrices();
    }

    /**
     * Charge les prix actuels des actifs depuis une API externe
     * Pour le MVP, utilise des valeurs fictives
     * Dans une vraie app, appeler CoinGecko API ou similaire
     */
    public function loadCurrentPrices()
    {
        // === PRIX FICTIFS POUR LE MVP ===
        // Dans une vraie app, faire:
        // $response = Http::get('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum&vs_currencies=usd');
        // $bitcoinUSD = $response['bitcoin']['usd'];

        // Prix fictifs en USD (approximatifs février 2026)
        $pricesUSD = [
            'bitcoin' => 95000,   // 1 BTC ≈ 95 000 USD
            'ethereum' => 3200,   // 1 ETH ≈ 3 200 USD
            'gold' => 85,         // 1 gramme d'or ≈ 85 USD
        ];

        // Prix en USD de l'actif sélectionné
        $this->currentPriceUSD = $pricesUSD[$this->assetType] ?? 0;

        // Conversion en Ariary (USD × taux de change)
        $this->currentPriceMGA = $this->currentPriceUSD * $this->usdToMgaRate;

        // Calcul de la quantité estimée
        $this->calculateEstimatedQuantity();
    }

    /**
     * Calcule la quantité d'actif que l'utilisateur recevra
     * Appelé automatiquement quand $investmentAmount change
     */
    public function calculateEstimatedQuantity()
    {
        // Si pas de montant ou prix invalide, quantité = 0
        if (!$this->investmentAmount || $this->currentPriceMGA <= 0) {
            $this->estimatedQuantity = 0;
            return;
        }

        // Calcul: Quantité = Montant investi ÷ Prix unitaire
        // Ex: 427 500 Ar ÷ 427 500 000 Ar/BTC = 0.001 BTC
        $this->estimatedQuantity = floatval($this->investmentAmount) / $this->currentPriceMGA;
    }

    /**
     * Listener Livewire : Mise à jour automatique lors du changement d'actif
     */
    public function updatedAssetType()
    {
        // Recharge les prix quand l'utilisateur change d'actif
        $this->loadCurrentPrices();
    }

    /**
     * Listener Livewire : Mise à jour automatique lors du changement de montant
     */
    public function updatedInvestmentAmount()
    {
        // Recalcule la quantité estimée
        $this->calculateEstimatedQuantity();
    }

    /**
     * Ouvre le modal de confirmation d'achat
     */
    public function openConfirmModal()
    {
        // Validation du formulaire avant d'ouvrir le modal
        $this->validate();

        // Ouvre le modal de confirmation
        $this->showConfirmModal = true;

        // Reset des messages
        $this->resetMessages();
    }

    /**
     * Ferme le modal de confirmation
     */
    public function closeConfirmModal()
    {
        // Ferme le modal
        $this->showConfirmModal = false;
    }

    /**
     * Traite l'achat d'investissement
     * Débite le portefeuille et crée l'enregistrement Investment
     */
    public function confirmPurchase()
    {
        // Re-validation par sécurité
        $this->validate();

        try {
            // Début de transaction SQL
            DB::beginTransaction();

            // Récupération de l'utilisateur et son portefeuille principal
            $user = Auth::user();
            $wallet = $user->mainWallet();

            // Vérification : portefeuille existe ?
            if (!$wallet) {
                throw new \Exception('Portefeuille principal introuvable.');
            }

            // Montant de l'investissement
            $amount = floatval($this->investmentAmount);

            // Frais de transaction (2% pour les investissements)
            $fee = $amount * 0.02; // 2% de frais
            $totalDebit = $amount + $fee;

            // Vérification : solde suffisant ?
            if (!$wallet->hasSufficientBalance($totalDebit)) {
                throw new \Exception('Solde insuffisant. Disponible: ' . number_format($wallet->availableBalance(), 2, ',', ' ') . ' Ar');
            }

            // Symbole de l'actif
            $assetSymbols = [
                'bitcoin' => 'BTC',
                'ethereum' => 'ETH',
                'gold' => 'XAU',
            ];
            $assetSymbol = $assetSymbols[$this->assetType];

            // Création de la transaction d'investissement
            $transaction = Transaction::create([
                'from_wallet_id' => $wallet->id,
                'to_wallet_id' => null, // Investissement externe
                'transaction_type' => 'investment',
                'amount' => $amount,
                'currency' => 'MGA',
                'fee' => $fee,
                'status' => 'completed', // Complété immédiatement pour le MVP
                'description' => 'Achat de ' . $assetSymbol . ' - ' . number_format($this->estimatedQuantity, 8, ',', '') . ' ' . $assetSymbol,
            ]);

            // Débit du portefeuille
            $wallet->debit($totalDebit);

            // Récupération ou création du portefeuille d'investissement
            // (crypto pour BTC/ETH, gold pour XAU)
            $investmentWalletType = ($this->assetType === 'gold') ? 'gold' : 'crypto';
            $investmentWallet = $user->wallets()
                ->where('wallet_type', $investmentWalletType)
                ->where('currency', $assetSymbol)
                ->first();

            // Si le portefeuille n'existe pas, le créer
            if (!$investmentWallet) {
                $walletNames = [
                    'BTC' => 'Portefeuille Bitcoin',
                    'ETH' => 'Portefeuille Ethereum',
                    'XAU' => 'Portefeuille Or',
                ];

                $investmentWallet = Wallet::create([
                    'user_id' => $user->id,
                    'wallet_type' => $investmentWalletType,
                    'wallet_name' => $walletNames[$assetSymbol] ?? 'Investissement',
                    'currency' => $assetSymbol,
                    'balance' => 0,
                    'status' => 'active',
                ]);
            }

            // Crédit du portefeuille d'investissement avec la quantité achetée
            $investmentWallet->credit($this->estimatedQuantity);

            // Création de l'enregistrement Investment
            Investment::create([
                'user_id' => $user->id,
                'wallet_id' => $investmentWallet->id,
                'asset_type' => $this->assetType,
                'asset_symbol' => $assetSymbol,
                'quantity' => $this->estimatedQuantity,
                'purchase_price_mga' => $this->currentPriceMGA,
                'purchase_price_usd' => $this->currentPriceUSD,
                'total_invested_mga' => $amount,
                'total_invested_usd' => $amount / $this->usdToMgaRate,
                'current_price_mga' => $this->currentPriceMGA,
                'current_price_usd' => $this->currentPriceUSD,
                'current_value_mga' => $amount, // Valeur initiale = montant investi
                'current_value_usd' => $amount / $this->usdToMgaRate,
                'unrealized_pnl_mga' => 0, // Pas encore de profit/perte
                'unrealized_pnl_usd' => 0,
                'pnl_percentage' => 0,
                'status' => 'active',
                'purchase_transaction_id' => $transaction->id,
                'price_updated_at' => now(),
            ]);

            // Validation de la transaction SQL
            DB::commit();

            // Message de succès
            $this->successMessage = 'Investissement réussi ! Vous avez acheté ' . 
                number_format($this->estimatedQuantity, 8, ',', '') . ' ' . $assetSymbol . 
                ' pour ' . number_format($amount, 2, ',', ' ') . ' Ar.';

            // Fermeture du modal
            $this->closeConfirmModal();

            // Réinitialisation du formulaire
            $this->resetForm();

            // Rechargement des prix
            $this->loadCurrentPrices();

        } catch (\Exception $e) {
            // Annulation de la transaction SQL
            DB::rollBack();

            // Message d'erreur
            $this->errorMessage = 'Erreur : ' . $e->getMessage();

            // Fermeture du modal
            $this->closeConfirmModal();
        }
    }

    /**
     * Réinitialise le formulaire
     */
    private function resetForm()
    {
        $this->investmentAmount = '';
        $this->estimatedQuantity = 0;
        $this->assetType = 'bitcoin';
    }

    /**
     * Réinitialise les messages
     */
    private function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    /**
     * Retourne le nom de l'actif en français
     *
     * @return string
     */
    public function getAssetNameProperty()
    {
        $names = [
            'bitcoin' => 'Bitcoin (BTC)',
            'ethereum' => 'Ethereum (ETH)',
            'gold' => 'Or Physique (XAU)',
        ];

        return $names[$this->assetType] ?? $this->assetType;
    }

    /**
     * Méthode render() : retourne la vue Blade
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.investment-form')
            ->layout('layouts.app');
    }
}
