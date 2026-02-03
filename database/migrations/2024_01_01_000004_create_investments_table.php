<?php
/**
 * ============================================================================
 * Composant : Migration Investissements (Investments Table)
 * Technologie suggérée : Laravel 11 Migrations avec Tracking de Performance
 * Utilité pour mon projet : Enregistre tous les investissements en crypto 
 * (Bitcoin, Ethereum) et or physique avec calcul automatique des gains/pertes
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute la migration pour créer la table investments
     * Portfolio d'investissements avec tracking de performance en temps réel
     */
    public function up(): void
    {
        // Création de la table 'investments' pour le suivi des actifs
        Schema::create('investments', function (Blueprint $table) {
            // Clé primaire auto-incrémentée
            $table->id();
            
            // Référence à l'utilisateur investisseur (clé étrangère)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Référence au portefeuille utilisé pour l'investissement
            $table->foreignId('wallet_id')
                  ->constrained('wallets')
                  ->onDelete('restrict'); // Ne pas supprimer si investissement actif
            
            // Type d'actif investi
            // 'bitcoin' = BTC, 'ethereum' = ETH, 'gold' = Or physique (XAU)
            $table->enum('asset_type', ['bitcoin', 'ethereum', 'gold']);
            
            // Code de la cryptomonnaie ou matière première (BTC, ETH, XAU)
            $table->string('asset_symbol', 10);
            
            // Quantité achetée (ex: 0.005 BTC, 10 grammes d'or)
            // 20 chiffres dont 8 décimales pour précision Bitcoin (satoshis)
            $table->decimal('quantity', 20, 8);
            
            // Prix d'achat unitaire en Ariary (MGA) au moment de l'achat
            $table->decimal('purchase_price_mga', 20, 2);
            
            // Prix d'achat unitaire en USD (pour référence internationale)
            $table->decimal('purchase_price_usd', 20, 2);
            
            // Montant total investi en Ariary (quantity × purchase_price_mga)
            $table->decimal('total_invested_mga', 20, 2);
            
            // Montant total investi en USD (pour suivi performance globale)
            $table->decimal('total_invested_usd', 20, 2);
            
            // Prix actuel de l'actif en Ariary (mis à jour via API externe)
            $table->decimal('current_price_mga', 20, 2)->nullable();
            
            // Prix actuel de l'actif en USD (mis à jour via API externe)
            $table->decimal('current_price_usd', 20, 2)->nullable();
            
            // Valeur actuelle totale en Ariary (quantity × current_price_mga)
            $table->decimal('current_value_mga', 20, 2)->nullable();
            
            // Valeur actuelle totale en USD
            $table->decimal('current_value_usd', 20, 2)->nullable();
            
            // Gain ou perte non réalisé(e) en Ariary (current_value - total_invested)
            $table->decimal('unrealized_pnl_mga', 20, 2)->default(0);
            
            // Gain ou perte non réalisé(e) en USD
            $table->decimal('unrealized_pnl_usd', 20, 2)->default(0);
            
            // Pourcentage de gain/perte (ex: +15.5% ou -8.2%)
            $table->decimal('pnl_percentage', 10, 2)->default(0);
            
            // Statut de l'investissement
            // 'active' = position ouverte (actif détenu)
            // 'sold' = position fermée (actif vendu)
            // 'partial' = partiellement vendu
            $table->enum('status', ['active', 'sold', 'partial'])->default('active');
            
            // Date de vente (si vendu)
            $table->timestamp('sold_at')->nullable();
            
            // Prix de vente unitaire en Ariary (si vendu)
            $table->decimal('sell_price_mga', 20, 2)->nullable();
            
            // Prix de vente unitaire en USD (si vendu)
            $table->decimal('sell_price_usd', 20, 2)->nullable();
            
            // Gain/perte réalisé(e) après vente en Ariary
            $table->decimal('realized_pnl_mga', 20, 2)->nullable();
            
            // Gain/perte réalisé(e) après vente en USD
            $table->decimal('realized_pnl_usd', 20, 2)->nullable();
            
            // Référence à la transaction d'achat (lien avec transactions table)
            $table->foreignId('purchase_transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->onDelete('set null');
            
            // Référence à la transaction de vente (si vendu)
            $table->foreignId('sell_transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->onDelete('set null');
            
            // Notes personnelles de l'utilisateur sur cet investissement
            $table->text('notes')->nullable();
            
            // Dernière mise à jour des prix (pour savoir si les données sont fraîches)
            $table->timestamp('price_updated_at')->nullable();
            
            // Horodatages automatiques
            $table->timestamps();
            
            // Soft delete (pour garder l'historique)
            $table->softDeletes();
            
            // Index pour optimiser les recherches par utilisateur
            $table->index('user_id');
            
            // Index pour optimiser les recherches par type d'actif
            $table->index('asset_type');
            
            // Index pour optimiser les recherches par statut
            $table->index('status');
            
            // Index composé pour portfolio par utilisateur et type d'actif
            $table->index(['user_id', 'asset_type', 'status']);
        });
    }

    /**
     * Annule la migration
     */
    public function down(): void
    {
        // Suppression de la table investments
        Schema::dropIfExists('investments');
    }
};
