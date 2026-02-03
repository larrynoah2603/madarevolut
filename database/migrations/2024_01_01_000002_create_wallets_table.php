<?php
/**
 * ============================================================================
 * Composant : Migration Portefeuilles (Wallets Table)
 * Technologie suggérée : Laravel 11 Migrations avec Foreign Keys
 * Utilité pour mon projet : Permet à chaque utilisateur d'avoir plusieurs
 * portefeuilles (MGA principal, crypto, or, etc.) avec soldes séparés
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute la migration pour créer la table wallets
     * Un utilisateur peut avoir plusieurs portefeuilles (multi-devise)
     */
    public function up(): void
    {
        // Création de la table 'wallets' pour gérer les portefeuilles multiples
        Schema::create('wallets', function (Blueprint $table) {
            // Clé primaire auto-incrémentée
            $table->id();
            
            // Référence à l'utilisateur propriétaire du portefeuille (clé étrangère)
            // unsignedBigInteger pour correspondre au type id() de users
            $table->foreignId('user_id')
                  ->constrained('users') // Contrainte sur la table users
                  ->onDelete('cascade'); // Si l'utilisateur est supprimé, ses portefeuilles aussi
            
            // Type de portefeuille pour différencier les actifs
            // 'main' = Ariary principal, 'crypto' = cryptomonnaies, 'gold' = or physique
            $table->enum('wallet_type', ['main', 'crypto', 'gold'])->default('main');
            
            // Nom personnalisé du portefeuille (ex: "Économies", "Trading Bitcoin")
            $table->string('wallet_name')->default('Portefeuille Principal');
            
            // Devise du portefeuille ('MGA' pour Ariary, 'BTC' pour Bitcoin, 'XAU' pour Or)
            $table->string('currency', 10)->default('MGA');
            
            // Solde actuel du portefeuille (decimal pour la précision financière)
            // 15 chiffres au total, 2 après la virgule pour les Ariary
            // Pour le Bitcoin: 15,8 (8 décimales pour les satoshis)
            $table->decimal('balance', 20, 8)->default(0);
            
            // Solde bloqué (fonds en attente de transaction, non disponibles)
            $table->decimal('locked_balance', 20, 8)->default(0);
            
            // Statut du portefeuille (actif, gelé, fermé)
            $table->enum('status', ['active', 'frozen', 'closed'])->default('active');
            
            // Numéro de compte unique pour les virements (généré automatiquement)
            $table->string('account_number')->unique();
            
            // IBAN fictif pour les transferts internationaux (format Madagascar)
            $table->string('iban')->nullable()->unique();
            
            // Activation de l'intérêt sur épargne (pour les comptes épargne)
            $table->boolean('interest_enabled')->default(false);
            
            // Taux d'intérêt annuel (en pourcentage, ex: 5.5 pour 5,5%)
            $table->decimal('interest_rate', 5, 2)->default(0);
            
            // Horodatages automatiques
            $table->timestamps();
            
            // Soft delete pour historique
            $table->softDeletes();
            
            // Index composé pour optimiser les recherches par utilisateur et type
            $table->index(['user_id', 'wallet_type']);
            
            // Index sur le numéro de compte pour les recherches rapides
            $table->index('account_number');
        });
    }

    /**
     * Annule la migration
     */
    public function down(): void
    {
        // Suppression de la table wallets
        Schema::dropIfExists('wallets');
    }
};
