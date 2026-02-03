<?php
/**
 * ============================================================================
 * Composant : Migration Transactions (Transactions Table)
 * Technologie suggérée : Laravel 11 Migrations avec Polymorphisme
 * Utilité pour mon projet : Enregistre TOUTES les transactions financières
 * (transferts, recharges Mobile Money, achats crypto/or) avec traçabilité complète
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute la migration pour créer la table transactions
     * Historique complet et immuable de toutes les opérations financières
     */
    public function up(): void
    {
        // Création de la table 'transactions' pour l'historique complet
        Schema::create('transactions', function (Blueprint $table) {
            // Clé primaire auto-incrémentée
            $table->id();
            
            // UUID unique pour chaque transaction (sécurité et traçabilité)
            $table->uuid('transaction_uuid')->unique();
            
            // Portefeuille source (d'où vient l'argent)
            $table->foreignId('from_wallet_id')
                  ->nullable() // Nullable pour les recharges externes (Mobile Money)
                  ->constrained('wallets')
                  ->onDelete('restrict'); // Empêche la suppression si des transactions existent
            
            // Portefeuille destination (où va l'argent)
            $table->foreignId('to_wallet_id')
                  ->nullable() // Nullable pour les retraits externes
                  ->constrained('wallets')
                  ->onDelete('restrict');
            
            // Type de transaction pour catégoriser les opérations
            // 'transfer' = virement entre portefeuilles MadaRevolut
            // 'deposit' = recharge depuis Mobile Money
            // 'withdrawal' = retrait vers Mobile Money
            // 'investment' = achat crypto/or
            // 'divestment' = vente crypto/or
            // 'fee' = frais de transaction
            // 'subscription' = paiement forfait Premium/Metal/Ultra
            $table->enum('transaction_type', [
                'transfer', 
                'deposit', 
                'withdrawal', 
                'investment', 
                'divestment', 
                'fee', 
                'subscription'
            ]);
            
            // Montant de la transaction (toujours positif)
            $table->decimal('amount', 20, 8);
            
            // Devise de la transaction
            $table->string('currency', 10)->default('MGA');
            
            // Frais prélevés sur cette transaction (en Ariary)
            $table->decimal('fee', 20, 8)->default(0);
            
            // Statut de la transaction (workflow de validation)
            // 'pending' = en attente de validation
            // 'processing' = en cours de traitement
            // 'completed' = terminée avec succès
            // 'failed' = échouée (erreur)
            // 'cancelled' = annulée par l'utilisateur
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])
                  ->default('pending');
            
            // Numéro Mobile Money pour les dépôts/retraits (MVola, Orange, Airtel)
            $table->string('mobile_money_number')->nullable();
            
            // Opérateur Mobile Money concerné
            $table->enum('mobile_money_provider', ['mvola', 'orange', 'airtel'])->nullable();
            
            // Référence de transaction Mobile Money (fournie par l'opérateur)
            $table->string('mobile_money_reference')->nullable();
            
            // Description/motif de la transaction (ajoutée par l'utilisateur)
            $table->text('description')->nullable();
            
            // Métadonnées additionnelles (JSON pour flexibilité)
            // Ex: taux de change, prix du Bitcoin au moment de l'achat, etc.
            $table->json('metadata')->nullable();
            
            // Date/heure de finalisation de la transaction
            $table->timestamp('completed_at')->nullable();
            
            // Raison d'échec si la transaction a failed
            $table->text('failure_reason')->nullable();
            
            // IP de l'utilisateur qui a initié la transaction (sécurité)
            $table->string('ip_address', 45)->nullable();
            
            // User Agent du navigateur (pour détecter les fraudes)
            $table->string('user_agent')->nullable();
            
            // Horodatages automatiques
            $table->timestamps();
            
            // Index pour optimiser les recherches par portefeuille source
            $table->index('from_wallet_id');
            
            // Index pour optimiser les recherches par portefeuille destination
            $table->index('to_wallet_id');
            
            // Index pour optimiser les recherches par UUID
            $table->index('transaction_uuid');
            
            // Index pour optimiser les recherches par type et statut
            $table->index(['transaction_type', 'status']);
            
            // Index pour optimiser les recherches par date de création
            $table->index('created_at');
        });
    }

    /**
     * Annule la migration
     */
    public function down(): void
    {
        // Suppression de la table transactions
        Schema::dropIfExists('transactions');
    }
};
