<?php
/**
 * ============================================================================
 * Composant : Migration Utilisateurs (Users Table)
 * Technologie suggérée : Laravel 11 Migrations (Schema Builder)
 * Utilité pour mon projet : Stocke les informations des utilisateurs 
 * MadaRevolut avec authentification 2FA, type de forfait, et numéro Mobile Money
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute la migration pour créer la table users
     * Cette table stocke tous les utilisateurs de l'application MadaRevolut
     */
    public function up(): void
    {
        // Création de la table 'users' dans la base de données MySQL
        Schema::create('users', function (Blueprint $table) {
            // Clé primaire auto-incrémentée
            $table->id();
            
            // Nom complet de l'utilisateur (obligatoire)
            $table->string('name');
            
            // Email unique pour l'authentification (obligatoire et unique)
            $table->string('email')->unique();
            
            // Timestamp de vérification de l'email (nullable pour les comptes non vérifiés)
            $table->timestamp('email_verified_at')->nullable();
            
            // Mot de passe hashé (bcrypt/argon2) pour la sécurité
            $table->string('password');
            
            // Numéro de téléphone malgache (format: +261 XX XX XXX XX) - unique et obligatoire
            $table->string('phone_number')->unique();
            
            // Type de forfait MadaRevolut (par défaut: Standard gratuit)
            // Valeurs possibles: 'standard', 'plus', 'premium', 'metal', 'ultra'
            $table->enum('subscription_plan', ['standard', 'plus', 'premium', 'metal', 'ultra'])
                  ->default('standard');
            
            // Date d'expiration du forfait (nullable pour le plan standard gratuit)
            $table->timestamp('subscription_expires_at')->nullable();
            
            // Activation de l'authentification à deux facteurs (2FA)
            $table->boolean('two_factor_enabled')->default(false);
            
            // Secret 2FA pour Google Authenticator (stocké chiffré)
            $table->text('two_factor_secret')->nullable();
            
            // Codes de récupération 2FA (stockés chiffrés, JSON array)
            $table->text('two_factor_recovery_codes')->nullable();
            
            // Date de confirmation 2FA (quand l'utilisateur a activé 2FA)
            $table->timestamp('two_factor_confirmed_at')->nullable();
            
            // Numéro Mobile Money principal (MVola/Orange/Airtel) pour les transferts
            $table->string('mobile_money_number')->nullable();
            
            // Opérateur Mobile Money ('mvola', 'orange', 'airtel')
            $table->enum('mobile_money_provider', ['mvola', 'orange', 'airtel'])->nullable();
            
            // Token de mémorisation pour "Se souvenir de moi"
            $table->rememberToken();
            
            // Horodatages automatiques (created_at, updated_at)
            $table->timestamps();
            
            // Soft delete (permet de "supprimer" sans effacer définitivement)
            $table->softDeletes();
            
            // Index pour optimiser les recherches par email et téléphone
            $table->index('email');
            $table->index('phone_number');
        });
    }

    /**
     * Annule la migration (supprime la table)
     * Utilisé lors du rollback
     */
    public function down(): void
    {
        // Suppression de la table users si elle existe
        Schema::dropIfExists('users');
    }
};
