<?php
/**
 * ============================================================================
 * Composant : Modèle Wallet (Portefeuille Multi-Devises)
 * Technologie suggérée : Laravel Eloquent ORM avec Mutators
 * Utilité pour mon projet : Gère les portefeuilles MGA/Crypto/Or,
 * les soldes disponibles/bloqués, et les transactions associées
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Wallet extends Model
{
    // Traits pour fonctionnalités avancées
    use HasFactory;    // Pour les factories de test
    use SoftDeletes;   // Pour la suppression douce

    /**
     * Attributs assignables en masse
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',           // ID de l'utilisateur propriétaire
        'wallet_type',       // Type: main, crypto, gold
        'wallet_name',       // Nom personnalisé du portefeuille
        'currency',          // Devise: MGA, BTC, ETH, XAU
        'balance',           // Solde disponible
        'locked_balance',    // Solde bloqué (transactions en attente)
        'status',            // Statut: active, frozen, closed
        'account_number',    // Numéro de compte unique
        'iban',              // IBAN fictif pour virements
        'interest_enabled',  // Activation des intérêts
        'interest_rate',     // Taux d'intérêt annuel
    ];

    /**
     * Attributs cachés lors de la sérialisation
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Aucun champ sensible à cacher pour les portefeuilles
    ];

    /**
     * Attributs à caster automatiquement
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2',           // Arrondi à 2 décimales pour MGA
        'locked_balance' => 'decimal:2',    // Arrondi à 2 décimales
        'interest_enabled' => 'boolean',    // Cast vers booléen
        'interest_rate' => 'decimal:2',     // Taux en pourcentage (ex: 5.50)
    ];

    /**
     * Événement de création : génère automatiquement le numéro de compte
     * Ce code s'exécute AVANT l'insertion en base de données
     */
    protected static function boot()
    {
        parent::boot();

        // Événement déclenché lors de la création d'un nouveau portefeuille
        static::creating(function ($wallet) {
            // Génération d'un numéro de compte unique (format: MR-XXXXXXXXXXXX)
            // MR = MadaRevolut, suivi de 12 chiffres aléatoires
            $wallet->account_number = 'MR-' . strtoupper(Str::random(12));

            // Génération d'un IBAN fictif (format Madagascar: MG + 27 caractères)
            // Format réel IBAN Madagascar: MG00 XXXX XXXX XXXX XXXX XXXX XXX
            $wallet->iban = 'MG' . rand(10, 99) . strtoupper(Str::random(25));
        });
    }

    /**
     * Relation Many-to-One : Un portefeuille appartient à un utilisateur
     * Permet d'accéder au propriétaire via: $wallet->user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        // Retourne l'utilisateur propriétaire de ce portefeuille
        return $this->belongsTo(User::class);
    }

    /**
     * Relation One-to-Many : Transactions DEPUIS ce portefeuille (débit)
     * Permet d'accéder aux transactions sortantes via: $wallet->outgoingTransactions
     *
     * @return HasMany
     */
    public function outgoingTransactions(): HasMany
    {
        // Retourne toutes les transactions où ce portefeuille est la source
        return $this->hasMany(Transaction::class, 'from_wallet_id');
    }

    /**
     * Relation One-to-Many : Transactions VERS ce portefeuille (crédit)
     * Permet d'accéder aux transactions entrantes via: $wallet->incomingTransactions
     *
     * @return HasMany
     */
    public function incomingTransactions(): HasMany
    {
        // Retourne toutes les transactions où ce portefeuille est la destination
        return $this->hasMany(Transaction::class, 'to_wallet_id');
    }

    /**
     * Relation One-to-Many : Investissements liés à ce portefeuille
     *
     * @return HasMany
     */
    public function investments(): HasMany
    {
        // Retourne tous les investissements financés par ce portefeuille
        return $this->hasMany(Investment::class);
    }

    /**
     * Calcule le solde disponible (balance - locked_balance)
     * Ce montant peut être utilisé pour de nouvelles transactions
     *
     * @return float
     */
    public function availableBalance(): float
    {
        // Solde disponible = Solde total - Solde bloqué
        return $this->balance - $this->locked_balance;
    }

    /**
     * Vérifie si le portefeuille a suffisamment de fonds disponibles
     * Utilisé avant d'initier une transaction
     *
     * @param float $amount Montant à vérifier
     * @return bool
     */
    public function hasSufficientBalance(float $amount): bool
    {
        // Retourne true si le solde disponible est >= au montant demandé
        return $this->availableBalance() >= $amount;
    }

    /**
     * Crédite le portefeuille (ajoute de l'argent)
     * Utilisé pour les dépôts et virements reçus
     *
     * @param float $amount Montant à ajouter
     * @return bool
     */
    public function credit(float $amount): bool
    {
        // Augmente le solde du montant spécifié
        $this->balance += $amount;
        
        // Sauvegarde en base de données et retourne le résultat
        return $this->save();
    }

    /**
     * Débite le portefeuille (retire de l'argent)
     * Utilisé pour les retraits et virements envoyés
     *
     * @param float $amount Montant à retirer
     * @return bool
     */
    public function debit(float $amount): bool
    {
        // Vérification de sécurité : solde suffisant ?
        if (!$this->hasSufficientBalance($amount)) {
            // Si fonds insuffisants, retourner false sans modifier
            return false;
        }

        // Diminue le solde du montant spécifié
        $this->balance -= $amount;
        
        // Sauvegarde en base de données et retourne le résultat
        return $this->save();
    }

    /**
     * Bloque temporairement un montant (pour transaction en attente)
     * Utilisé lors de l'initiation d'une transaction qui nécessite validation
     *
     * @param float $amount Montant à bloquer
     * @return bool
     */
    public function lock(float $amount): bool
    {
        // Vérification : assez de fonds disponibles ?
        if (!$this->hasSufficientBalance($amount)) {
            return false;
        }

        // Augmente le solde bloqué
        $this->locked_balance += $amount;
        
        // Sauvegarde et retourne le résultat
        return $this->save();
    }

    /**
     * Débloque un montant précédemment bloqué
     * Utilisé lors de l'annulation d'une transaction ou après finalisation
     *
     * @param float $amount Montant à débloquer
     * @return bool
     */
    public function unlock(float $amount): bool
    {
        // Diminue le solde bloqué (ne peut pas être négatif)
        $this->locked_balance = max(0, $this->locked_balance - $amount);
        
        // Sauvegarde et retourne le résultat
        return $this->save();
    }

    /**
     * Formate le solde pour affichage avec symbole de devise
     * Ex: 150000.00 → "150 000 Ar" pour MGA
     *
     * @return string
     */
    public function formattedBalance(): string
    {
        // Symboles de devises
        $symbols = [
            'MGA' => 'Ar',        // Ariary malgache
            'BTC' => '₿',         // Bitcoin
            'ETH' => 'Ξ',         // Ethereum
            'XAU' => 'g',         // Or (grammes)
        ];

        // Récupère le symbole ou utilise le code devise
        $symbol = $symbols[$this->currency] ?? $this->currency;

        // Formate le nombre avec espaces comme séparateur de milliers
        $formatted = number_format($this->balance, 2, ',', ' ');

        // Retourne le montant formaté avec symbole
        return $formatted . ' ' . $symbol;
    }

    /**
     * Vérifie si le portefeuille est actif (peut effectuer des transactions)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        // Retourne true uniquement si le statut est 'active'
        return $this->status === 'active';
    }

    /**
     * Gèle le portefeuille (bloque toutes les opérations)
     * Utilisé en cas de fraude suspectée ou demande utilisateur
     *
     * @return bool
     */
    public function freeze(): bool
    {
        // Change le statut à 'frozen'
        $this->status = 'frozen';
        
        // Sauvegarde et retourne le résultat
        return $this->save();
    }

    /**
     * Dégèle le portefeuille (réactive les opérations)
     *
     * @return bool
     */
    public function unfreeze(): bool
    {
        // Change le statut à 'active'
        $this->status = 'active';
        
        // Sauvegarde et retourne le résultat
        return $this->save();
    }
}
