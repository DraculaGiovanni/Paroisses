# Plateforme Paroisses - Bénin

Plateforme web gratuite pour les paroisses catholiques du Bénin.

## Stack Technique

- **Backend:** Laravel 13.x + PHP 8.3
- **Frontend:** Vue 3 + Inertia.js + Tailwind CSS
- **Base de données:** MySQL 8.x
- **Auth:** Laravel Breeze (Vue)
- **Fuseau horaire:** Africa/Porto-Novo

## Installation

### Prérequis
- PHP 8.3+
- Composer
- MySQL 8.x
- Node.js 18+
- npm

### Installation locale

```bash
# Cloner le projet
git clone <repository-url>
cd paroisses

# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript
npm install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Configurer la base de données dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paroisses
DB_USERNAME=root
DB_PASSWORD=

# Exécuter les migrations
php artisan migrate

# (Optionnel) Générer des données de démonstration
php artisan db:seed --class=DemoDataSeeder

# Compiler les assets
npm run build

# Lancer le serveur de développement
php artisan serve
```

Accéder à l'application : http://localhost:8000

### Identifiants de démonstration

Après avoir lancé le seeder:
- **Super Admin:** super@paroisses.bj / password
- **Admin Paroisse 1:** admin0@paroisses.bj / password
- **Admin Paroisse 2:** admin1@paroisses.bj / password
- **Admin Paroisse 3:** admin2@paroisses.bj / password

## Architecture

### Multi-paroisses
Chaque paroisse est isolée avec:
- URL publique: `/p/{slug}`
- Interface admin: `/admin/*`
- Zone super admin: `/super/*`

### Rôles utilisateurs
1. **super_admin** - Gère toutes les paroisses
2. **admin** - Gère sa propre paroisse
3. **secretariat** - Lecture/écriture dans sa paroisse

### Fonctionnalités actuelles
- ✅ Gestion des horaires (messes, confessions, adorations)
- ✅ Gestion des annonces (brouillons, publiées, épinglées)
- ✅ Page publique par paroisse avec cache (10min TTL)
- ✅ Calcul automatique de la "prochaine messe"
- ✅ Isolation des données par paroisse
- ✅ Rate limiting sur login et authentification

### Fonctionnalités prévues
- 🔜 Intentions de messe avec codes de référence
- 🔜 Liens WhatsApp + QR codes
- 🔜 Notifications PWA (web push)
- 🔜 Suivi des quêtes
- 🔜 Demandes de sacrements

## Développement

### Structure du projet
```
app/
├── Actions/          # Logique métier complexe
├── Http/
│   ├── Controllers/  # Contrôleurs fins
│   ├── Requests/     # Form Requests (validation)
│   ├── Resources/    # API Resources (transformation)
│   └── Middleware/   # Middlewares personnalisés
├── Models/           # Modèles Eloquent
│   └── Concerns/     # Traits réutilisables
├── Observers/        # Observers (cache, events)
├── Policies/         # Policies (autorisation)
└── Notifications/    # Notifications (structure future)
```

### Commandes utiles

```bash
# Développement
npm run dev              # Compile assets en mode watch
php artisan serve        # Serveur de développement

# Tests
php artisan test         # Tests automatisés

# Base de données
php artisan migrate:fresh --seed --seeder=DemoDataSeeder

# Cache (effacer en développement)
php artisan optimize:clear

# Vérifier les requêtes lentes (en local)
tail -f storage/logs/laravel.log | grep "Slow query"
```

### Documentation

- **[BACKEND.md](BACKEND.md)** - Architecture backend, conventions, best practices
- **[DATABASE_PERFORMANCE.md](DATABASE_PERFORMANCE.md)** - Index, monitoring, optimisations
- **[SECURITY.md](SECURITY.md)** - Sécurité et isolation des paroisses

## Déploiement en Production

### 1. Préparer l'environnement

```bash
# Variables d'environnement
APP_ENV=production
APP_DEBUG=false
APP_URL=https://paroisses.bj

# Base de données
DB_CONNECTION=mysql
DB_HOST=<host>
DB_DATABASE=<database>
DB_USERNAME=<username>
DB_PASSWORD=<secure-password>

# Cache
CACHE_STORE=database    # ou 'file' ou 'redis'
SESSION_DRIVER=database

# Queue
QUEUE_CONNECTION=database
```

### 2. Installer et configurer

```bash
# Installer les dépendances (sans dev)
composer install --optimize-autoloader --no-dev

# Installer les dépendances JS
npm ci

# Compiler les assets pour production
npm run build

# Exécuter les migrations
php artisan migrate --force

# Optimisations
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Créer les tables de queue

```bash
php artisan queue:table
php artisan migrate
```

### 4. Configurer le worker de queue

Avec Supervisor (`/etc/supervisor/conf.d/paroisses-worker.conf`):
```ini
[program:paroisses-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/paroisses/artisan queue:work database --sleep=3 --tries=3 --timeout=90 --daemon
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/paroisses/storage/logs/worker.log
```

Recharger Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start paroisses-worker:*
```

### 5. Configurer le cron

Ajouter dans crontab (`crontab -e`):
```cron
* * * * * cd /path/to/paroisses && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Optimisations PHP (php.ini)

```ini
; Opcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
opcache.validate_timestamps=0  ; Production uniquement
opcache.save_comments=1

; Performances
memory_limit=256M
max_execution_time=60
upload_max_filesize=10M
post_max_size=10M

; Sessions
session.driver=database
session.cookie_secure=1       ; HTTPS uniquement
session.cookie_httponly=1
session.cookie_samesite=lax
```

### 7. Configuration Nginx

```nginx
server {
    listen 80;
    server_name paroisses.bj www.paroisses.bj;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name paroisses.bj www.paroisses.bj;
    
    root /path/to/paroisses/public;
    index index.php;

    # SSL
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    # Headers de sécurité (compléments)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript;
    
    # Cache statique
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Timeout pour requêtes longues
        fastcgi_read_timeout 60;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 8. Monitoring

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs worker
tail -f storage/logs/worker.log

# Logs Nginx
tail -f /var/log/nginx/error.log

# Vérifier les jobs en attente
php artisan queue:monitor
```

### 9. Maintenance

```bash
# Effacer les caches après un déploiement
php artisan optimize:clear
php artisan optimize

# Nettoyer les sessions expirées (cron quotidien)
php artisan session:gc

# Backup base de données (cron quotidien)
mysqldump -u user -p database > backup-$(date +%Y%m%d).sql
```

## Performance

### Objectifs atteints
- **Page publique:**
  - 3 requêtes SQL à froid
  - 0 requête à chaud (cache)
  - TTL 10 minutes, invalidation automatique
- **Listes admin:** Maximum 4 requêtes SQL par page
- **Pas de N+1:** Détecté automatiquement en développement

### Cache
- **Driver:** database (pas de Redis requis)
- **TTL:** 10 minutes pour pages publiques
- **Invalidation:** Automatique via Observers

## Sécurité

- ✅ Rate limiting sur login (5 tentatives/minute)
- ✅ Headers de sécurité (X-Frame-Options, CSP, etc.)
- ✅ Cookies sécurisés en production (secure, httponly, samesite)
- ✅ Isolation des données par paroisse (global scope + policies)
- ✅ Validation de toutes les entrées (Form Requests)
- ✅ Pas d'exposition de données sensibles (API Resources)
- ✅ Réponses 404 au lieu de 403 (ne pas révéler l'existence de ressources)

## Support

Pour toute question ou problème:
- Documentation: Voir `BACKEND.md` et `DATABASE_PERFORMANCE.md`
- Issues: Créer une issue GitHub
- Email: [contact]

## Licence

[À définir]

---

**Développé pour les paroisses catholiques du Bénin** 🇧🇯
