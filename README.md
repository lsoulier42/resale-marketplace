# Resale Marketplace — Symfony 8.1 (API JSON) + React 19 (SPA)

Marketplace de revente entre particuliers : **Symfony 8.1 / PHP 8.5 en API JSON** (`/api/*`) + **SPA React** (card-based, glassmorphism rose/mauve) servie par nginx. Frontend dans `frontend/` (Vite + TypeScript).

## Stack technique

- PHP 8.5 (FPM) · Symfony 8.1 · PostgreSQL 18
- Nginx : SPA React (`frontend/dist`) + proxy `/api/*` vers PHP + `/uploads/*` statique
- Frontend : React 19 + Vite 8 + TypeScript strict, React Router, TanStack Query, Vitest
- Mailpit (interface web : http://localhost:1181, SMTP : 1126)

## Prérequis

- Docker & Docker Compose
- Make
- Node.js ≥ 20 (build/dev du frontend — Node 24 recommandé)

## Installation

```bash
cp .env.example .env   # puis ajuster si besoin (ports, identifiants, clés Stripe…)
make install
```

Build des images Docker, installation des dépendances Composer **et** npm, build de la SPA React, puis démarrage des containers. Les défauts de `.env.example` suffisent pour démarrer en développement (app sur http://localhost:8081). **La SPA doit être buildée (`make frontend-build`) pour que nginx serve l'application.**

## Commandes disponibles

| Commande | Description |
|---|---|
| `make install` | Build, install (composer + frontend) et démarrage complet |
| `make start` / `make stop` | Démarrage / arrêt des containers |
| `make connect` | Shell dans le container PHP |
| `make clear` | Vide le cache Symfony |
| `make composer-update` | Mise à jour des vendors PHP |
| `make frontend-install` | Installe les dépendances npm |
| `make frontend-build` | Build de la SPA vers `frontend/dist` |
| `make frontend-dev` | Serveur Vite (HMR) sur http://localhost:5173 |
| `make frontend-test` / `make frontend-lint` | Vitest / oxlint |

## URLs

| Service | URL |
|---|---|
| Application (SPA + API) | http://localhost:8081 |
| Vite dev (HMR, proxy `/api` → 8081) | http://localhost:5173 |
| Mailpit | http://localhost:1181 |
| PostgreSQL (host) | localhost:5532 |

## Développement

### Backend (Symfony console)

```bash
make connect
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --purge-with-truncate
```

### Frontend

```bash
make frontend-dev   # Vite sur :5173, proxy /api et /uploads vers :8081
```

### Fixtures & comptes de démonstration

Fixtures : 6 catégories, 3 vendeur·ses, 3 acheteur·ses + admin, articles, commandes, avis, visuels SVG dans `public/uploads/`. `--purge-with-truncate` vide les tables et remet les IDs à zéro.

Mot de passe commun : `demo1234`

| Rôle | Email |
|---|---|
| Admin | `admin@example.test` |
| Vendeur·ses | `alex@example.test`, `sam@example.test`, `jordan@example.test` |
| Acheteur·ses | `camille@example.test`, `julien@example.test`, `sophie@example.test` |

### Tests & qualité

```bash
php vendor/bin/phpunit                  # Backend : tests fonctionnels de l'API (tests/Api/)
vendor/bin/phpstan analyse              # PHPStan niveau 6
vendor/bin/phpcs                        # PSR-12
make frontend-test                      # Frontend : Vitest (frontend/src/**/*.test.tsx)
make frontend-lint                      # oxlint
```

## Architecture

- **API JSON** : `src/Controller/Api/*` — auth (json_login + CSRF header), vitrine publique, espace acheteur·se (commandes, adresses, profil, avis), vendeur·se (articles, médias, onboarding Stripe), webhooks Stripe, admin (users, customers), inscription publique.
- **Payloads** : mapping explicite via `src/Service/CatalogPresenter` et `src/Service/OrderPresenter` (pas de sérialisation réflexive — aucune fuite de `password`/compte Stripe en lecture publique).
- **Paiement (Stripe Connect)** : compte Connect standard par vendeur·se (onboarding KYC via Account Link), Checkout Session hébergée avec commission de la plateforme (`application_fee_amount`), webhooks (`checkout.session.completed` → `paid`, `account.updated`, `charge.refunded`). Inactif tant que `STRIPE_SECRET_KEY` est vide (développement).
- **Règles métier** : achat unitaire (OneToOne Item↔Order), `availableCount` décrémenté à la commande, transitions de statut via `OrderStatus::allowedTransitions()`.
- **SPA React** : `frontend/src/` — `api/` (client fetch + CSRF + endpoints), `auth/` (AuthProvider, gardes), `hooks/` (TanStack Query), `components/` (layout + kit UI verre + domaine), `pages/` (vitrine, espace acheteur·se, vendeur·se, admin), `styles/` (tokens rose/mauve, glassmorphism).

## Variables d'environnement

Les variables sont définies dans `.env`. Les principales :

| Variable | Valeur par défaut |
|---|---|
| `APP_PORT` | `8081` |
| `DATABASE_HOST` | `database` |
| `DATABASE_PORT` | `5432` |
| `DATABASE_NAME` | `resale-marketplace` |
| `MAILER_DSN` | `smtp://mailer:1025` |
| `STRIPE_SECRET_KEY` | *(vide — paiement désactivé)* |
| `STRIPE_WEBHOOK_SECRET` | *(vide)* |
| `PLATFORM_FEE_PERCENT` | `5` |
| `APP_BASE_URL` | `http://localhost:8081` |

Pour le paiement en production : renseigner `STRIPE_SECRET_KEY` / `STRIPE_WEBHOOK_SECRET`, pointer `APP_BASE_URL` vers le domaine réel et enregistrer le webhook Stripe vers `https://<domaine>/api/webhooks/stripe`.
