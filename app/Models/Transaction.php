<?php
/**
 * ============================================================================
 * Composant : Modèle Transaction (Historique Financier Complet)
 * Technologie suggérée : Laravel Eloquent ORM avec UUID et Observers
 * Utilité pour mon projet : Enregistre toutes les opérations financières
 * avec traçabilité complète et gestion des statuts (pending → completed)
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    // Traits pour fonctionnalités avancées
    use HasFactory; // Pour les factories de test

    /**
     * Attributs assignables en masse
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_uuid',           // UUID unique pour chaque transaction
        'from_wallet_id',             // Portefeuille source (débit)
        'to_wallet_id',               // Portefeuille destination (crédit)
        'transaction_type',           // Type: transfer, deposit, withdrawal, investment, etc.
        'amount',                     // Montant de la transaction
        'currency',                   // Devise utilisée
        'fee',                        // Frais prélevés
        'status',                     // Statut: pending, processing, completed, failed, cancelled
        'mobile_money_number',        // Numéro Mobile Money (pour deposit/withdrawal)
        'mobile_money_provider',      // Opérateur: mvola, orange, airtel
        'mobile_money_reference',     // Référence fournie par l'opérateur
        'description',                // Description/motif de la transaction
        'metadata',                   // Données additionnelles (JSON)
        'completed_at',               // Date de finalisation
        'failure_reason',             // Raison d'échec (si failed)
        'ip_address',                 // IP de l'utilisateur (sécurité)
        'user_agent',                 // Navigateur utilisé (détection fraude)
    ];

    /**
     * Attributs cachés lors de la sérialisation
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'ip_address',   // Données sensibles RGPD
        'user_agent',   // Données sensibles RGPD
    ];

    /**
     * Attributs à caster automatiquement
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',        // Arrondi à 2 décimales
        'fee' => 'decimal:2',           // Arrondi à 2 décimales
        'metadata' => 'array',          // Cast JSON vers tableau PHP
        'completed_at' => 'datetime',   // Cast vers Carbon
    ];

    /**
     * Événement de création : génère automatiquement l'UUID
     * Ce code s'exécute AVANT l'insertion en base de données
     */
    protected static function boot()
    {
        parent::boot();

        // Événement déclenché lors de la création d'une nouvelle transaction
        static::creating(function ($transaction) {
            // Génération d'un UUID v4 unique pour traçabilité
            // Format: 550e8400-e29b-41d4-a716-446655440000
            $transaction->transaction_uuid = (string) Str::uuid();

            // Capture automatique de l'IP si disponible dans la requête
            if (request()->ip()) {
                $transaction->ip_address = request()->ip();
            }

            // Capture automatique du User-Agent si disponible
            if (request()->header('User-Agent')) {
                $transaction->user_agent = request()->header('User-Agent');
            }
        });
    }

    /**
     * Relation Many-to-One : Portefeuille source (FROM)
     * Permet d'accéder au portefeuille d'origine via: $transaction->fromWallet
     *
     * @return BelongsTo
     */
    public function fromWallet(): BelongsTo
    {
        // Retourne le portefeuille source de cette transaction
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    /**
     * Relation Many-to-One : Portefeuille destination (TO)
     * Permet d'accéder au portefeuille de destination via: $transaction->toWallet
     *
     * @return BelongsTo
     */
    public function toWallet(): BelongsTo
    {
        // Retourne le portefeuille destination de cette transaction
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }

    /**
     * Scope : Transactions complétées uniquement
     * Utilisation: Transaction::completed()->get()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        // Filtre les transactions avec statut 'completed'
        return $query->where('status', 'completed');
    }

    /**
     * Scope : Transactions en attente
     * Utilisation: Transaction::pending()->get()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        // Filtre les transactions avec statut 'pending'
        return $query->where('status', 'pending');
    }

    /**
     * Scope : Transactions par type
     * Utilisation: Transaction::ofType('deposit')->get()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        // Filtre les transactions par type spécifié
        return $query->where('transaction_type', $type);
    }

    /**
     * Marque la transaction comme complétée
     * Finalise la transaction et met à jour le timestamp
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        // Change le statut à 'completed'
        $this->status = 'completed';
        
        // Enregistre la date/heure de complétion (now)
        $this->completed_at = now();
        
        // Sauvegarde et retourne le résultat
        return $this->save();
    }

    /**
     * Marque la transaction comme échouée
     * Enregistre la raison de l'échec
     *
     * @param string $reason Raison de l'échec
     * @return bool
     */
    public function markAsFailed(string $reason): bool
    {
        // Change le statut à 'failed'
        $this->status = 'failed';
        
        // Enregistre la raison de l'échec
        $this->failure_reason = $reason;
        
        // Sauvegarde et retourne le résultat
        return $this->save();
    }

    /**
     * Annule la transaction
     * Utilisé si l'utilisateur annule avant finalisation
     *
     * @return bool
     */
    public function cancel(): bool
    {
        // Vérification : ne peut annuler que si pending ou processing
        if (!in_array($this->status, ['pending', 'processing'])) {
            return false; // Transaction déjà finalisée, impossible d'annuler
        }

        // Change le statut à 'cancelled'
        $this->status = 'cancelled';
        
        // Sauvegarde et retourne le résultat
        return $this->save();
    }

    /**
     * Vérifie si la transaction est complétée
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        // Retourne true si le statut est 'completed'
        return $this->status === 'completed';
    }

    /**
     * Vérifie si la transaction est en attente
     *
     * @return bool
     */
    public function isPending(): bool
    {
        // Retourne true si le statut est 'pending'
        return $this->status === 'pending';
    }

    /**
     * Vérifie si la transaction a échoué
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        // Retourne true si le statut est 'failed'
        return $this->status === 'failed';
    }

    /**
     * Calcule le montant total débité (montant + frais)
     * Montant réel prélevé du portefeuille source
     *
     * @return float
     */
    public function totalDebited(): float
    {
        // Montant total = Montant de la transaction + Frais
        return $this->amount + $this->fee;
    }

    /**
     * Formate le montant pour affichage avec devise
     * Ex: 50000 MGA → "50 000 Ar"
     *
     * @return string
     */
    public function formattedAmount(): string
    {
        // Symboles de devises
        $symbols = [
            'MGA' => 'Ar',
            'BTC' => '₿',
            'ETH' => 'Ξ',
            'XAU' => 'g',
        ];

        // Récupère le symbole
        $symbol = $symbols[$this->currency] ?? $this->currency;

        // Formate avec espaces comme séparateurs
        $formatted = number_format($this->amount, 2, ',', ' ');

        // Retourne montant formaté + symbole
        return $formatted . ' ' . $symbol;
    }

    /**
     * Retourne le nom de l'opérateur Mobile Money en français
     *
     * @return string|null
     */
    public function mobileMoneyProviderName(): ?string
    {
        // Si pas de provider, retourner null
        if (!$this->mobile_money_provider) {
            return null;
        }

        // Tableau de traduction
        $providers = [
            'mvola' => 'MVola (Telma)',
            'orange' => 'Orange Money',
            'airtel' => 'Airtel Money',
        ];

        // Retourne le nom traduit
        return $providers[$this->mobile_money_provider] ?? $this->mobile_money_provider;
    }

    /**
     * Retourne le type de transaction en français
     *
     * @return string
     */
    public function transactionTypeName(): string
    {
        // Tableau de traduction
        $types = [
            'transfer' => 'Virement',
            'deposit' => 'Recharge',
            'withdrawal' => 'Retrait',
            'investment' => 'Investissement',
            'divestment' => 'Vente d\'actif',
            'fee' => 'Frais',
            'subscription' => 'Abonnement',
        ];

        // Retourne le type traduit
        return $types[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * Retourne le statut de transaction en français avec badge couleur
     *
     * @return array ['text' => string, 'color' => string]
     */
    public function statusBadge(): array
    {
        // Configuration des badges (texte + couleur Tailwind)
        $badges = [
            'pending' => ['text' => 'En attente', 'color' => 'yellow'],
            'processing' => ['text' => 'En cours', 'color' => 'blue'],
            'completed' => ['text' => 'Complétée', 'color' => 'green'],
            'failed' => ['text' => 'Échouée', 'color' => 'red'],
            'cancelled' => ['text' => 'Annulée', 'color' => 'gray'],
        ];

        // Retourne le badge correspondant au statut
        return $badges[$this->status] ?? ['text' => $this->status, 'color' => 'gray'];
    }
}
