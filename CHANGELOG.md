# Changelog

Toutes les modifications notables de Resale Marketplace sont documentées dans ce fichier.

Le format s'inspire de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et ce projet respecte le [Semantic Versioning](https://semver.org/lang/fr/).

## [Unreleased]

### Changé

- **Stripe Connect sur Accounts v2** : les comptes vendeur·ses sont créés via l'API Accounts v2 (configuration « recipient », la plateforme est le marchand et transfère aux vendeur·ses), onboarding KYC hébergé via Account Link v2. Requis car Stripe rejette désormais la création de comptes v1 (et les plateformes françaises exigent des account tokens pour la configuration merchant). La readiness du compte se lit désormais sur `requirements` (compatible v1 et v2).
- **Synchronisation Stripe au retour de l'onboarding** : nouvel endpoint `POST /api/me/stripe/refresh` (appel live à Stripe) appelé automatiquement par le profil au retour de l'onboarding hébergé (`?stripe=return`) — l'indicateur « compte prêt » s'affiche sans dépendre du webhook. Le webhook `account.updated` reste utilisé pour les mises à jour ultérieures.
- **Refonte visuelle « Marketplace clair »** : palette neutre avec accent vert émeraude, typographie système, suppression du verre dépoli. Header marketplace sticky (logo, recherche, menu compte, nav secondaire), bottom-nav mobile, cartes produit nues en grille dense, fiches article avec barre d'achat sticky (mobile) et bloc vendeur.
- **Catalogue** : recherche plein texte sur le titre (`?q=`), tri par prix (`?sort=price_asc|price_desc`), filtres par catégorie en sidebar (desktop) et chips (mobile), état URL partageable.

## [1.0.0] - 2026-08-26

Première release publique : marketplace de revente entre particuliers, générique et prête à l'emploi (Symfony API JSON + SPA React, PostgreSQL).

### Ajouté

- **Authentification** : inscription (client·e et vendeur·se), confirmation d'email, connexion sécurisée (CSRF sur l'API), rôles admin / vendeur·se / acheteur·se.
- **Catalogue public** : articles avec photos, catégories, fiches vendeur·se, avis, pagination et filtrage.
- **Espace acheteur·se** : passer commande, adresses de livraison, suivi de commande (statuts), avis après livraison.
- **Espace vendeur·se** : publier et gérer des articles, téléverser des médias, mettre à jour les statuts de commande (expédition, suivi).
- **Paiement Stripe Connect** : onboarding KYC des vendeur·ses (Account Link), Checkout Session hébergée avec commission de la plateforme, webhooks (`checkout.session.completed` → payé, `account.updated`, `charge.refunded`). Désactivable (dev) en laissant `STRIPE_SECRET_KEY` vide.
- **Administration** : gestion des utilisateurs et des comptes clients.
- **Fixtures de démonstration** : 6 catégories, vendeur·ses, acheteur·ses, articles et avis (`demo1234` pour tous les comptes).
- **Qualité** : 41 tests fonctionnels backend (PHPUnit), PHPStan niveau 6, PHPCS PSR-12, 27 tests frontend (Vitest), oxlint, CI GitHub Actions (backend + frontend).

### Sécurité

- Aucune donnée personnelle réelle : emails de démonstration en `@example.test`, aucune clé Stripe commitée, `.env` ignoré par git.
- Protection CSRF sur toutes les mutations de l'API ; webhooks Stripe authentifiés par signature.
