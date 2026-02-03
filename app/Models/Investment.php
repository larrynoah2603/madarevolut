<?php
/**
 * ============================================================================
 * Composant : Modèle Investment (Portfolio Crypto & Or)
 * Technologie suggérée : Laravel Eloquent ORM avec Accessors/Mutators
 * Utilité pour mon projet : Gère les investissements Bitcoin/Ethereum/Or
 * avec calcul automatique des profits/pertes (PnL) en temps réel
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
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
        'user_id',                  // ID de l'utilisateur investisseur
        'wallet_id',                // ID du portefeuille utilisé
        'asset_type',               // Type: bitcoin, ethereum, gold
        'asset_symbol',             // Symbole: BTC, ETH, XAU
        'quantity',                 // Quantité achetée (ex: 0.005 BTC)
        'purchase_price_mga',       // Prix d'achat unitaire en Ariary
        'purchase_price_usd',       // Prix d'achat unitaire en USD
        'total_invested_mga',       // Montant total investi en MGA
        'total_invested_usd',       // Montant total investi en USD
        'current_price_mga',        // Prix actuel en MGA (mis à jour)
        'current_price_usd',        // Prix actuel en USD (mis à jour)
        'current_value_mga',        // Valeur actuelle en MGA
        'current_value_usd',        // Valeur actuelle en USD
        'unrealized_pnl_mga',       // Gain/perte non réalisé(e) MGA
        'unrealized_pnl_usd',       // Gain/perte non réalisé(e) USD
        'pnl_percentage',           // Pourcentage de gain/perte
        'status',                   // Statut: active, sold, partial
        'sold_at',                  // Date de vente
        'sell_price_mga',           // Prix de vente unitaire MGA
        'sell_price_usd',           // Prix de vente unitaire USD
        'realized_pnl_mga',         // Gain/perte réalisé(e) après vente MGA
        'realized_pnl_usd',         // Gain/perte réalisé(e) après vente USD
        'purchase_transaction_id',  // Transaction d'achat
        'sell_transaction_id',      // Transaction de vente
        'notes',                    // Notes personnelles
        'price_updated_at',         // Dernière mise à jour des prix
    ];

    /**
     * Attributs cachés lors de la sérialisation
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Aucun champ sensible à cacher
    ];

    /**
     * Attributs à caster automatiquement
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:8',              // 8 décimales pour Bitcoin (satoshis)
        'purchase_price_mga' => 'decimal:2',    // 2 décimales pour Ariary
        'purchase_price_usd' => 'decimal:2',    // 2 décimales pour USD
        'total_invested_mga' => 'decimal:2',    // 2 décimales
        'total_invested_usd' => 'decimal:2',    // 2 décimales
        'current_price_mga' => 'decimal:2',     // 2 décimales
        'current_price_usd' => 'decimal:2',     // 2 décimales
        'current_value_mga' => 'decimal:2',     // 2 décimales
        'current_value_usd' => 'decimal:2',     // 2 décimales
        'unrealized_pnl_mga' => 'decimal:2',    // 2 décimales
        'unrealized_pnl_usd' => 'decimal:2',    // 2 décimales
        'pnl_percentage' => 'decimal:2',        // 2 décimales (ex: +15.50%)
        'sell_price_mga' => 'decimal:2',        // 2 décimales
        'sell_price_usd' => 'decimal:2',        // 2 décimales
        'realized_pnl_mga' => 'decimal:2',      // 2 décimales
        'realized_pnl_usd' => 'decimal:2',      // 2 décimales
        'sold_at' => 'datetime',                // Cast vers Carbon
        'price_updated_at' => 'datetime',       // Cast vers Carbon
    ];

    /**
     * Relation Many-to-One : Appartient à un utilisateur
     * Permet d'accéder au propriétaire via: $investment->user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        // Retourne l'utilisateur propriétaire de cet investissement
        return $this->belongsTo(User::class);
    }

    /**
     * Relation Many-to-One : Appartient à un portefeuille
     * Permet d'accéder au portefeuille via: $investment->wallet
     *
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        // Retourne le portefeuille utilisé pour cet investissement
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relation Many-to-One : Transaction d'achat
     *
     * @return BelongsTo
     */
    public function purchaseTransaction(): BelongsTo
    {
        // Retourne la transaction d'achat originale
        return $this->belongsTo(Transaction::class, 'purchase_transaction_id');
    }

    /**
     * Relation Many-to-One : Transaction de vente
     *
     * @return BelongsTo
     */
    public function sellTransaction(): BelongsTo
    {
        // Retourne la transaction de vente (si vendu)
        return $this->belongsTo(Transaction::class, 'sell_transaction_id');
    }

    /**
     * Met à jour les prix actuels et recalcule les PnL
     * Appelé régulièrement via un job planifié (Scheduler)
     *
     * @param float $newPriceMGA Nouveau prix en Ariary
     * @param float $newPriceUSD Nouveau prix en USD
     * @return bool
     */
    public function updatePrices(float $newPriceMGA, float $newPriceUSD): bool
    {
        // Met à jour les prix actuels
        $this->current_price_mga = $newPriceMGA;
        $this->current_price_usd = $newPriceUSD;

        // Recalcule la valeur actuelle totale (quantité × prix actuel)
        $this->current_value_mga = $this->quantity * $newPriceMGA;
        $this->current_value_usd = $this->quantity * $newPriceUSD;

        // Recalcule le PnL non réalisé (valeur actuelle - investissement)
        $this->unrealized_pnl_mga = $this->current_value_mga - $this->total_invested_mga;
        $this->unrealized_pnl_usd = $this->current_value_usd - $this->total_invested_usd;

        // Recalcule le pourcentage de gain/perte
        // Formule: ((valeur actuelle - investissement) / investissement) × 100
        if ($this->total_invested_mga > 0) {
            $this->pnl_percentage = (($this->current_value_mga - $this->total_invested_mga) / $this->total_invested_mga) * 100;
        }

        // Met à jour le timestamp de dernière mise à jour
        $this->price_updated_at = now();

        // Sauvegarde et retourne le résultat
        return $this->save();
    }

    /**
     * Marque l'investissement comme vendu
     * Calcule le PnL réalisé et finalise la position
     *
     * @param float $sellPriceMGA Prix de vente unitaire en MGA
     * @param float $sellPriceUSD Prix de vente unitaire en USD
     * @param int $sellTransactionId ID de la transaction de vente
     * @return bool
     */
    public function markAsSold(float $sellPriceMGA, float $sellPriceUSD, int $sellTransactionId): bool
    {
        // Change le statut à 'sold'
        $this->status = 'sold';

        // Enregistre les prix de vente
        $this->sell_price_mga = $sellPriceMGA;
        $this->sell_price_usd = $sellPriceUSD;

        // Calcule le PnL réalisé
        // Formule: (quantité × prix vente) - montant investi
        $this->realized_pnl_mga = ($this->quantity * $sellPriceMGA) - $this->total_invested_mga;
        $this->realized_pnl_usd = ($this->quantity * $sellPriceUSD) - $this->total_invested_usd;

        // Enregistre la transaction de vente
        $this->sell_transaction_id = $sellTransactionId;

        // Enregistre la date de vente
        $this->sold_at = now();

        // Sauvegarde et retourne le résultat
        return $this->save();
    }

    /**
     * Vérifie si l'investissement est actif (position ouverte)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        // Retourne true si le statut est 'active'
        return $this->status === 'active';
    }

    /**
     * Vérifie si l'investissement a été vendu
     *
     * @return bool
     */
    public function isSold(): bool
    {
        // Retourne true si le statut est 'sold'
        return $this->status === 'sold';
    }

    /**
     * Vérifie si l'investissement est actuellement en profit
     *
     * @return bool
     */
    public function isInProfit(): bool
    {
        // Retourne true si le PnL non réalisé est positif
        return $this->unrealized_pnl_mga > 0;
    }

    /**
     * Vérifie si l'investissement est actuellement en perte
     *
     * @return bool
     */
    public function isInLoss(): bool
    {
        // Retourne true si le PnL non réalisé est négatif
        return $this->unrealized_pnl_mga < 0;
    }

    /**
     * Retourne le nom de l'actif en français
     *
     * @return string
     */
    public function assetName(): string
    {
        // Tableau de traduction
        $assets = [
            'bitcoin' => 'Bitcoin (BTC)',
            'ethereum' => 'Ethereum (ETH)',
            'gold' => 'Or Physique (XAU)',
        ];

        // Retourne le nom traduit
        return $assets[$this->asset_type] ?? $this->asset_type;
    }

    /**
     * Formate la quantité pour affichage avec symbole
     * Ex: 0.005 BTC → "0.00500000 ₿"
     *
     * @return string
     */
    public function formattedQuantity(): string
    {
        // Symboles d'actifs
        $symbols = [
            'BTC' => '₿',
            'ETH' => 'Ξ',
            'XAU' => 'g',
        ];

        // Récupère le symbole
        $symbol = $symbols[$this->asset_symbol] ?? $this->asset_symbol;

        // Formate avec 8 décimales pour Bitcoin/Ethereum
        $decimals = ($this->asset_symbol === 'XAU') ? 2 : 8;
        $formatted = number_format($this->quantity, $decimals, ',', ' ');

        // Retourne quantité formatée + symbole
        return $formatted . ' ' . $symbol;
    }

    /**
     * Formate le PnL avec couleur pour affichage
     * Retourne un tableau avec montant, pourcentage et couleur
     *
     * @return array ['amount' => string, 'percentage' => string, 'color' => string]
     */
    public function formattedPnL(): array
    {
        // Détermine la couleur selon le signe
        $color = $this->unrealized_pnl_mga > 0 ? 'green' : ($this->unrealized_pnl_mga < 0 ? 'red' : 'gray');

        // Préfixe + ou - selon le signe
        $prefix = $this->unrealized_pnl_mga > 0 ? '+' : '';

        // Formate le montant
        $amount = $prefix . number_format($this->unrealized_pnl_mga, 2, ',', ' ') . ' Ar';

        // Formate le pourcentage
        $percentage = $prefix . number_format($this->pnl_percentage, 2, ',', '') . '%';

        // Retourne le tableau complet
        return [
            'amount' => $amount,
            'percentage' => $percentage,
            'color' => $color,
        ];
    }

    /**
     * Scope : Investissements actifs uniquement
     * Utilisation: Investment::active()->get()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        // Filtre les investissements avec statut 'active'
        return $query->where('status', 'active');
    }

    /**
     * Scope : Investissements par type d'actif
     * Utilisation: Investment::ofAsset('bitcoin')->get()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $assetType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfAsset($query, string $assetType)
    {
        // Filtre les investissements par type d'actif
        return $query->where('asset_type', $assetType);
    }
}
