# 📋 TaskApp - Gestor de Tareas

Sistema completo de gestión de tareas con autenticación JWT, desarrollado con React + Vite y Laravel.

![React](https://img.shields.io/badge/React-18.2-blue?logo=react)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red?logo=laravel)
![Docker](https://img.shields.io/badge/Docker-Ready-blue?logo=docker)
![License](https://img.shields.io/badge/License-MIT-green)

## 📑 Tabla de Contenidos

- [Características](#-características)
- [Tecnologías](#-tecnologías)
- [Arquitectura](#-arquitectura)
- [Requisitos Previos](#-requisitos-previos)
- [Instalación](#-instalación)
- [Despliegue con Docker](#-despliegue-con-docker)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [API Endpoints](#-api-endpoints)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Troubleshooting](#-troubleshooting)

## ✨ Características

### Frontend
- ✅ Autenticación con JWT (Laravel Sanctum)
- ✅ Dashboard interactivo con estadísticas
- ✅ CRUD completo de tareas
- ✅ Modal de formulario reutilizable
- ✅ Diseño responsive con Tailwind CSS
- ✅ Navbar dinámico según estado de autenticación
- ✅ Manejo de estados de carga y errores

### Backend
- ✅ API RESTful con Laravel
- ✅ Autenticación JWT con Sanctum
- ✅ Validación de datos
- ✅ Relaciones de base de datos
- ✅ Middleware de protección de rutas
- ✅ CORS configurado
- ✅ Endpoints de prueba (dummy)

## 🛠 Tecnologías

### Frontend
| Tecnología | Versión | Uso |
|-----------|---------|-----|
| **React** | 18.2+ | Framework principal |
| **Vite** | 4.x | Build tool y dev server |
| **Tailwind CSS** | 3.x | Framework CSS |
| **Lucide React** | Latest | Librería de iconos |
| **React Hooks** | - | Manejo de estado |

### Backend
| Tecnología | Versión | Uso |
|-----------|---------|-----|
| **Laravel** | 12.x | Framework PHP |
| **Laravel Sanctum** | 3.x | Autenticación API |
| **MySQL** | 8.0.x | Base de datos |
| **PHP** | 8.4+ | Lenguaje del backend |

### DevOps
| Tecnología | Uso |
|-----------|-----|
| **Docker** | Contenerización |
| **Docker Compose** | Orquestación de contenedores |
| **Nginx** | Servidor web para React |
| **Git** | Control de versiones |

## 🏗 Arquitectura

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────┐
│                 │         │                  │         │             │
│   React + Vite  │────────▶│  Laravel API     │────────▶│    MySQL    │
│   (Frontend)    │  HTTP   │  (Backend)       │   SQL   │   (DB)      │
│   Port: 5000    │         │  Port: 8080      │         │ Port: 3306  │
│                 │         │                  │         │             │
└─────────────────┘         └──────────────────┘         └─────────────┘
        │                            │
        │                            │
        ▼                            ▼
   Nginx (80)                JWT Authentication
                              (Sanctum)
```

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

- **Node.js** >= 18.x
- **npm** >= 9.x o **yarn**
- **PHP** >= 8.4
- **Composer** >= 2.x
- **Docker** >= 20.x
- **Docker Compose** >= 2.x
- **Git**

### Verificar instalaciones:

```bash
node --version
npm --version
php --version
composer --version
docker --version
docker-compose --version
```

## 🚀 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/robtoes/laravel-php.git
cd laravel-php
```

### 2. Configuración del Frontend

```bash
cd front-darient

# Instalar dependencias
npm install

# Copiar archivo de variables de entorno
cp .env.example .env

# Configurar variables
nano .env
```

**Archivo `.env` del frontend:**
```env
VITE_API_URL=http://localhost:8080
VITE_APP_NAME=TaskApp
```

### 3. Configuración del Backend

```bash
cd ../laravel-taskmanager

# Instalar dependencias
composer install

# Copiar archivo de variables de entorno
cp .env.example .env

# Generar key de la aplicación
php artisan key:generate

# Configurar base de datos en .env
nano .env
```

**Archivo `.env` del backend:**
```env
APP_NAME=TaskApp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
```

### 4. Ejecutar Migraciones

```bash
# Si usas Docker (ver siguiente sección)
docker-compose exec backend php artisan migrate

# Si usas instalación local
php artisan migrate
```

### 5. Crear Usuario de Prueba

```bash
php artisan tinker
```

### 5.1 Para crear usuarios
```bash
$user = \App\Models\User::create([
    'name' => 'Admin',
    'lastName' => 'Admin',
    'email' => 'admin@taskapp.com',
    'password' => Hash::make('password123')
]);
```
### 5.2 Para crear tareas
```bash
$user = Task::factory()->create();
```


## 🐳 Despliegue con Docker

### Estructura de Archivos Necesaria

```
proyecto/
├── front-darient/
│   ├── src/
│   ├── public/
│   ├── package.json
│   ├── vite.config.js
│   ├── Dockerfile
│   ├── nginx.conf
│   └── .dockerignore
├── laravel-taskmanager/
│   ├── app/
│   ├── routes/
│   ├── database/
│   ├── composer.json
│   ├── Dockerfile
│   └── .dockerignore
└── docker-compose.yml
```

### Paso 1: Crear Dockerfile del front-darient

**Archivo: `front-darient/Dockerfile`**

```dockerfile
FROM node:18-alpine AS builder

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

ARG VITE_API_URL='http://localhost:8080'
ENV VITE_API_URL=$VITE_API_URL

RUN npm run build

FROM nginx:alpine

COPY --from=builder /app/dist /usr/share/nginx/html

COPY /nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
```

### Paso 2: Crear nginx.conf

**Archivo: `front-darient/nginx.conf`**

```nginx
server {
    listen 80;
    server_name localhost;
    root /usr/share/nginx/html;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Paso 3: Crear Dockerfile del laravel-taskmanager

**Archivo: `laravel-taskmanager/Dockerfile`**

```dockerfile
# Use the official PHP image as a base image
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the existing application directory contents to the working directory
COPY . /var/www

# Copy the existing application directory permissions to the working directory
COPY --chown=www-data:www-data . /var/www

# Change current user to www
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
```

### Paso 4: Crear docker-compose.yml

**Archivo: `docker-compose.yml`** (en la raíz del proyecto)

```yaml
version: '3.8'
services:
  frontend:
    build:
      context: ../front-darient
      dockerfile: Dockerfile
    container_name: react-app
    ports:
      - "5000:80"
    restart: unless-stopped
    networks:
      - app-network
    depends_on:
      - app
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel-app
    restart: unless-stopped
    tty: true
    environment:
      SERVICE_NAME: app
      SERVICE_TAGS: dev
    working_dir: /var/www
    volumes:
      - .:/var/www
      - ./docker-compose/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - app-network
    depends_on:
      - webserver

  webserver:
    image: nginx:latest
    container_name: nginx-webserver
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - .:/var/www
      - ./docker-compose/nginx:/etc/nginx/conf.d/
    networks:
      - app-network
    depends_on:
      - db

  db:
    image: mysql:8.0.43
    container_name: mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: laravel
      MYSQL_ROOT_PASSWORD: root
      MYSQL_PASSWORD: root
      MYSQL_USER: laravel
    ports:
      - "3306:3306"
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

### Paso 5: Ejecutar Docker Compose

```bash
# Construir e iniciar todos los servicios
docker-compose up -d --build

# Ver logs en tiempo real
docker-compose logs -f

# Ver estado de los contenedores
docker-compose ps
```

### Paso 6: Ejecutar Migraciones

```bash
# Esperar a que MySQL esté listo (30 segundos)
sleep 30

# Ejecutar migraciones
docker-compose exec backend php artisan migrate

# Crear usuario de prueba
docker-compose exec backend php artisan tinker
```

### Paso 7: Verificar Despliegue

Abre tu navegador y accede a:

- **Frontend**: http://localhost:5000
- **Backend API**: http://localhost:8080/api/test
- **Credenciales de prueba**:
  - Email: `admin@taskapp.com`
  - Password: `password123`

## ⚙️ Configuración

### Variables de Entorno del Frontend

```env
# .env
VITE_API_URL=http://localhost:8080
VITE_APP_NAME=TaskApp
```

### Variables de Entorno del Backend

```env
# .env
APP_NAME=TaskApp
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
```

## 💻 Uso

### Comandos Docker Compose Útiles

```bash
# Iniciar servicios
docker-compose up -d

# Detener servicios
docker-compose down

# Ver logs
docker-compose logs -f [servicio]

# Reconstruir servicios
docker-compose up -d --build

# Ejecutar comando en contenedor
docker-compose exec backend php artisan [comando]

# Acceder a bash del contenedor
docker-compose exec backend bash

# Reiniciar un servicio
docker-compose restart [servicio]

# Ver uso de recursos
docker stats
```

### Desarrollo Local (sin Docker)

#### Frontend
```bash
cd front-darient
npm install
npm run dev
# Abre: http://localhost:5173
```

#### Backend
```bash
cd laravel-taskmanager
composer install
php artisan serve
# Abre: http://localhost:8080
```

## 📡 API Endpoints

### Autenticación

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| POST | `/api/login` | Login de usuario | No |
| POST | `/api/register` | Registro de usuario | No |
| POST | `/api/logout` | Cerrar sesión | Sí |
| GET | `/api/me` | Usuario autenticado | Sí |

### Tareas

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/tasks`      | Listar tareas | Sí |
| POST | `/api/tasks`     | Crear tarea | Sí |
| GET | `/api/tasks/detail/{id}` | Ver tarea | Sí |
| POST | `/api/tasks/update/{id}` | Actualizar tarea | Sí |
| DELETE | `/api/tasks/{id}` | Eliminar tarea | Sí |
| GET | `/api/tasks/stats` | Estadísticas | Sí |

### Pruebas (Dummy)

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/test` | Test básico | No |
| GET | `/api/test/protected` | Test protegido | Sí |
| POST | `/api/test/echo` | Echo de datos | No |

### Ejemplos de Uso

```bash
# Login
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@taskapp.com","password":"password123"}'

# Crear tarea
curl -X POST http://localhost:8080/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"titulo":"Mi tarea","descripcion":"Descripción"}'

# Listar tareas
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/api/tasks
```

## 📁 Estructura del Proyecto

```
taskapp/
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── Modal.jsx
│   │   │   ├── NavBar.jsx
│   │   │   └── Pagination.jsx
│   │   ├── contexts/
│   │   │   └── AuthContext.jsx
│   │   ├── pages/
│   │   │   ├── Login.jsx
│   │   │   ├── Tasks.jsx
│   │   │   └── Dashboard.jsx
│   │   ├── App.jsx
│   │   └── main.jsx
│   ├── public/
│   ├── Dockerfile
│   ├── nginx.conf
│   ├── package.json
│   └── vite.config.js
│
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── TaskController.php
│   │   │   │   └── DummyController.php
│   │   │   └── Middleware/
│   │   └── Models/
│   │       ├── User.php
│   │       └── Task.php
│   ├── database/
│   │   └── migrations/
│   │       ├── create_users_table.php
│   │       └── create_tasks_table.php
│   ├── routes/
│   │   └── api.php
│   ├── config/
│   │   ├── sanctum.php
│   │   └── cors.php
│   ├── Dockerfile
│   └── composer.json
│
├── docker-compose.yml
└── README.md
```

## 🐛 Troubleshooting

### Error: Puerto ya en uso

```bash
# Ver qué proceso usa el puerto
lsof -i :3000
lsof -i :8000

# Matar proceso
kill -9 [PID]

# O cambiar puerto en docker-compose.yml
ports:
  - "8080:80"  # Cambia 3000 por 8080
```

### Error: Cannot connect to MySQL

```bash
# Verificar que MySQL esté corriendo
docker-compose ps

# Ver logs de MySQL
docker-compose logs mysql

# Reiniciar MySQL
docker-compose restart mysql

# Esperar más tiempo antes de migrar
sleep 60
docker-compose exec backend php artisan migrate
```

### Error: CORS

Verifica en `backend/config/cors.php`:

```php
'allowed_origins' => ['http://localhost:5000'],
'supports_credentials' => true,
```

### Error: 401 Unauthorized

```bash
# Verificar token
echo $TOKEN

# Regenerar token
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@taskapp.com","password":"password123"}'
```

### Limpiar y Reiniciar Todo

```bash
# Detener y eliminar todo
docker-compose down -v

# Eliminar imágenes
docker rmi taskapp-frontend taskapp-backend

# Reconstruir desde cero
docker-compose up -d --build

# Ejecutar migraciones
sleep 30
docker-compose exec backend php artisan migrate:fresh
```

## 📝 Notas Adicionales

- La carpeta `dist` del frontend se genera automáticamente en el build de Docker
- Los tokens JWT no expiran por defecto (configurable en `config/sanctum.php`)
- La paginación por defecto es de 9 tareas por página
- Los logs de Nginx están en `/var/log/nginx/` dentro del contenedor

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Autores

- **Tu Nombre** - *Desarrollo inicial* - [tu-usuario](https://github.com/tu-usuario)

## 🙏 Agradecimientos

- React Team por el excelente framework
- Laravel Team por Sanctum
- Tailwind CSS por el framework de estilos
- Comunidad de código abierto

---

**⭐ Si este proyecto te fue útil, considera darle una estrella en GitHub!**