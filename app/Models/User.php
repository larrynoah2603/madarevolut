<?php
/**
 * ============================================================================
 * Composant : Modèle User (Utilisateur MadaRevolut)
 * Technologie suggérée : Laravel Eloquent ORM avec Relations
 * Utilité pour mon projet : Gère la logique métier des utilisateurs,
 * leurs relations avec portefeuilles/transactions/investissements,
 * et l'authentification 2FA avec Fortify
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    // Traits Laravel pour fonctionnalités avancées
    use HasFactory;           // Pour les factories de test
    use Notifiable;           // Pour les notifications email/SMS
    use SoftDeletes;          // Pour la suppression douce (soft delete)
    use TwoFactorAuthenticatable; // Pour l'authentification 2FA (Fortify)

    /**
     * Attributs assignables en masse (mass assignment)
     * Ces champs peuvent être remplis via User::create() ou $user->fill()
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',                    // Nom complet de l'utilisateur
        'email',                   // Email unique pour connexion
        'password',                // Mot de passe hashé
        'phone_number',            // Numéro de téléphone malgache
        'subscription_plan',       // Type de forfait (standard, plus, premium, metal, ultra)
        'subscription_expires_at', // Date d'expiration du forfait payant
        'mobile_money_number',     // Numéro Mobile Money par défaut
        'mobile_money_provider',   // Opérateur (mvola, orange, airtel)
    ];

    /**
     * Attributs cachés lors de la sérialisation (API, JSON)
     * Ces champs sensibles ne seront jamais exposés dans les réponses JSON
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',                     // Mot de passe hashé (sécurité)
        'remember_token',               // Token "Se souvenir de moi"
        'two_factor_secret',            // Secret 2FA Google Authenticator
        'two_factor_recovery_codes',    // Codes de récupération 2FA
    ];

    /**
     * Attributs à caster automatiquement vers un type spécifique
     * Laravel convertit automatiquement ces champs lors de l'accès
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',        // Cast vers objet Carbon (dates)
        'subscription_expires_at' => 'datetime',  // Cast vers objet Carbon
        'two_factor_enabled' => 'boolean',        // Cast vers booléen PHP
        'two_factor_confirmed_at' => 'datetime',  // Cast vers objet Carbon
        'password' => 'hashed',                   // Automatiquement hashé avec bcrypt
    ];

    /**
     * Relation One-to-Many : Un utilisateur possède plusieurs portefeuilles
     * Permet d'accéder à tous les portefeuilles via: $user->wallets
     *
     * @return HasMany
     */
    public function wallets(): HasMany
    {
        // Retourne tous les portefeuilles de cet utilisateur
        // Utilise la clé étrangère 'user_id' dans la table 'wallets'
        return $this->hasMany(Wallet::class);
    }

    /**
     * Relation One-to-Many : Un utilisateur a plusieurs investissements
     * Permet d'accéder au portfolio via: $user->investments
     *
     * @return HasMany
     */
    public function investments(): HasMany
    {
        // Retourne tous les investissements (crypto/or) de cet utilisateur
        return $this->hasMany(Investment::class);
    }

    /**
     * Méthode helper : Récupère le portefeuille principal en Ariary (MGA)
     * Utilisé pour les opérations courantes (recharge, transfert)
     *
     * @return Wallet|null
     */
    public function mainWallet()
    {
        // Cherche le premier portefeuille de type 'main' avec devise 'MGA'
        return $this->wallets()
                    ->where('wallet_type', 'main')
                    ->where('currency', 'MGA')
                    ->first();
    }

    /**
     * Méthode helper : Récupère le portefeuille crypto (Bitcoin/Ethereum)
     *
     * @return Wallet|null
     */
    public function cryptoWallet()
    {
        // Cherche le portefeuille de type 'crypto'
        return $this->wallets()
                    ->where('wallet_type', 'crypto')
                    ->first();
    }

    /**
     * Méthode helper : Récupère le portefeuille or
     *
     * @return Wallet|null
     */
    public function goldWallet()
    {
        // Cherche le portefeuille de type 'gold'
        return $this->wallets()
                    ->where('wallet_type', 'gold')
                    ->first();
    }

    /**
     * Vérifie si l'utilisateur a un forfait Premium actif
     * Retourne true si le forfait est payant ET non expiré
     *
     * @return bool
     */
    public function hasPremiumSubscription(): bool
    {
        // Vérifie que le plan n'est pas 'standard' (gratuit)
        if ($this->subscription_plan === 'standard') {
            return false;
        }

        // Si pas de date d'expiration, considéré comme expiré
        if (!$this->subscription_expires_at) {
            return false;
        }

        // Vérifie que la date d'expiration est dans le futur
        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Calcule le solde total de tous les portefeuilles en Ariary
     * Convertit les cryptos et or en MGA pour affichage du patrimoine total
     *
     * @return float
     */
    public function totalBalanceMGA(): float
    {
        // Initialisation du solde total
        $total = 0;

        // Boucle sur tous les portefeuilles de l'utilisateur
        foreach ($this->wallets as $wallet) {
            // Si le portefeuille est en Ariary, ajouter directement
            if ($wallet->currency === 'MGA') {
                $total += $wallet->balance;
            } else {
                // Sinon, convertir (à implémenter avec API de taux de change)
                // Pour l'instant, on ajoute la valeur brute (à améliorer)
                $total += $wallet->balance;
            }
        }

        // Retourne le solde total arrondi à 2 décimales
        return round($total, 2);
    }

    /**
     * Formate le numéro de téléphone au format international malgache
     * Ex: 0340001122 → +261 34 00 01 122
     *
     * @return string
     */
    public function formattedPhoneNumber(): string
    {
        // Retrait des espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9]/', '', $this->phone_number);

        // Si le numéro commence par 0, le remplacer par +261
        if (substr($phone, 0, 1) === '0') {
            $phone = '+261' . substr($phone, 1);
        }

        // Retourne le numéro formaté
        return $phone;
    }

    /**
     * Retourne le nom du forfait en français pour affichage
     *
     * @return string
     */
    public function subscriptionPlanName(): string
    {
        // Tableau de traduction des noms de forfaits
        $plans = [
            'standard' => 'Standard (Gratuit)',
            'plus' => 'Plus (20 000 Ar/mois)',
            'premium' => 'Premium (50 000 Ar/mois)',
            'metal' => 'Metal (100 000 Ar/mois)',
            'ultra' => 'Ultra (250 000 Ar/mois)',
        ];

        // Retourne le nom traduit ou le code brut si non trouvé
        return $plans[$this->subscription_plan] ?? $this->subscription_plan;
    }
}
