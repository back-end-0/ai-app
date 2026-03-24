# AI App — Full Technical Documentation

## Table of Contents

- [1. Overview](#1-overview)
- [2. Architecture](#2-architecture)
- [3. Setup & Installation](#3-setup--installation)
- [4. Database](#4-database)
- [5. AI Agents](#5-ai-agents)
- [6. API Reference](#6-api-reference)
- [7. Frontend](#7-frontend)
- [8. Testing](#8-testing)
- [9. Deployment](#9-deployment)

---

## 1. Overview

AI App is an AI-powered e-commerce assistant built on Laravel 13 with the Laravel AI SDK (v0). It provides:

- **AI Chat**: A conversational shopping assistant that knows all products
- **AI Search**: Semantic product search in English and Arabic
- **AI Descriptions**: Auto-generated marketing copy for products
- **AI Translation**: English-to-Arabic product translation
- **Product Catalog**: Paginated, themeable, bilingual product grid

### Tech Stack

| Component  | Technology                              |
|------------|-----------------------------------------|
| Language   | PHP 8.3                                 |
| Framework  | Laravel 13                              |
| AI SDK     | laravel/ai v0                           |
| Database   | MySQL                                   |
| Frontend   | Vanilla JS, Tailwind CSS v4, Vite 8     |
| HTTP       | Axios 1.11                              |
| Testing    | Pest v4, PHPUnit v12                    |
| Formatting | Laravel Pint v1                         |
| AI Provider| Groq (default), Gemini, Anthropic       |

---

## 2. Architecture

### Request Flow

```
Browser → Web Route → Blade View → JavaScript (Axios)
                                        ↓
                                   API Route
                                        ↓
                                   Controller
                                        ↓
                              ┌─────────┴─────────┐
                              │                    │
                         AI Agent            Eloquent Model
                              │                    │
                         AI Provider          MySQL Database
                         (Groq/etc)                │
                              │                    │
                              └─────────┬──────────┘
                                        ↓
                                  JSON Response
                                        ↓
                                  JavaScript renders UI
```

### Directory Structure

```
app/
├── Ai/
│   ├── Agents/                      # AI agent classes
│   │   ├── ChatBot.php              # Conversational assistant
│   │   ├── ProductSearcher.php      # Semantic search
│   │   ├── DescriptionGenerator.php # Marketing copy
│   │   └── ProductTranslator.php    # EN→AR translation
│   └── Tools/                       # AI tool definitions
│       ├── SearchProducts.php
│       └── GenerateProductDescription.php
├── Http/
│   ├── Controllers/Api/
│   │   ├── ChatController.php       # 5 methods
│   │   └── ProductController.php    # 4 methods
│   ├── Requests/                    # Form validation
│   │   ├── SendMessageRequest.php
│   │   └── StoreConversationRequest.php
│   └── Resources/                   # API transformers
│       ├── ConversationResource.php
│       └── MessageResource.php
├── Models/
│   ├── User.php
│   ├── Product.php
│   └── ProductTranslation.php
database/
├── migrations/                      # 7 migration files
├── factories/
│   ├── UserFactory.php
│   └── ProductFactory.php
└── seeders/
    ├── DatabaseSeeder.php
    └── ProductSeeder.php            # 20 sample products
resources/
├── css/app.css                      # Tailwind entry
├── js/
│   ├── app.js                       # Imports bootstrap + chat + products
│   ├── bootstrap.js                 # Axios config with CSRF
│   ├── chat.js                      # Chat page UI (~390 lines)
│   └── products.js                  # Products page UI (~370 lines)
└── views/
    ├── welcome.blade.php
    ├── chat.blade.php
    └── products.blade.php
routes/
├── api.php                          # API endpoints
└── web.php                          # Page routes
tests/
├── Pest.php                         # Test config
└── Feature/Api/
    ├── ConversationTest.php         # 6 tests
    └── SendMessageTest.php          # 6 tests
```

### Key Design Decisions

1. **Context Injection over Tool Use**: AI agents load product data directly in their `instructions()` method rather than using Tool calls. This is because Groq's API doesn't reliably support tool use with the Laravel AI SDK's schema format.

2. **Structured Output**: `ProductSearcher` and `ProductTranslator` use `HasStructuredOutput` to return typed JSON. This works reliably across all providers.

3. **Raw DB Queries for Conversations**: `ChatController` uses `DB::table()` for conversation queries because `agent_conversations` and `agent_conversation_messages` are Laravel AI framework tables without Eloquent models.

4. **Single-Page JS Modules**: Each page (`chat.js`, `products.js`) uses a guard clause (`if (!document.getElementById(...))`) to only run on its target page. Both are imported in `app.js`.

---

## 3. Setup & Installation

### Requirements

- PHP 8.3+
- MySQL 8.0+
- Node.js 18+
- Composer 2+

### Step-by-Step

```bash
# 1. Clone
git clone <repo-url> ai-app
cd ai-app

# 2. Install dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Configure .env (see below)

# 5. Database
php artisan migrate
php artisan db:seed --class=ProductSeeder

# 6. Build frontend
npm run build

# 7. Run
php artisan serve
```

### Environment Configuration

#### AI Provider (required)

```env
# Choose one provider:
AI_PROVIDER=groq
GROQ_API_KEY=gsk_your_key_here

# AI_PROVIDER=gemini
# GEMINI_API_KEY=your_key_here

# AI_PROVIDER=anthropic
# ANTHROPIC_API_KEY=your_key_here
```

#### Database (required)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_app
DB_USERNAME=root
DB_PASSWORD=
```

#### Other Settings

```env
APP_URL=http://localhost:8000
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### Development Mode

```bash
# Hot reload (frontend + backend)
composer run dev

# Or separately:
php artisan serve &
npm run dev
```

---

## 4. Database

### Entity Relationship

```
users
  └── agent_conversations (user_id FK)
        └── agent_conversation_messages (conversation_id FK)

products
  └── product_translations (product_id FK, unique [product_id, locale])
```

### Table: `products`

| Column      | Type              | Constraints       |
|-------------|-------------------|--------------------|
| id          | bigint unsigned   | PK, auto-increment |
| name        | varchar(255)      | NOT NULL           |
| description | text              | NULLABLE           |
| price       | decimal(10,2)     | NOT NULL           |
| quantity    | int unsigned      | DEFAULT 0          |
| image       | varchar(255)      | NULLABLE           |
| created_at  | timestamp         |                    |
| updated_at  | timestamp         |                    |

### Table: `product_translations`

| Column      | Type              | Constraints                          |
|-------------|-------------------|--------------------------------------|
| id          | bigint unsigned   | PK, auto-increment                   |
| product_id  | bigint unsigned   | FK → products.id, CASCADE on delete  |
| locale      | varchar(10)       | NOT NULL (e.g. "ar")                 |
| name        | varchar(255)      | NOT NULL                             |
| description | text              | NULLABLE                             |
| created_at  | timestamp         |                                      |
| updated_at  | timestamp         |                                      |

**Index**: UNIQUE(`product_id`, `locale`)

### Table: `agent_conversations` (Laravel AI)

| Column     | Type              | Constraints                    |
|------------|-------------------|--------------------------------|
| id         | char(36)          | PK (UUID)                      |
| user_id    | bigint unsigned   | NULLABLE, FK → users.id        |
| title      | varchar(255)      | NOT NULL                       |
| created_at | timestamp         |                                |
| updated_at | timestamp         |                                |

### Table: `agent_conversation_messages` (Laravel AI)

| Column          | Type              | Constraints              |
|-----------------|-------------------|--------------------------|
| id              | char(36)          | PK (UUID)                |
| conversation_id | varchar(255)      | NOT NULL, indexed        |
| user_id         | bigint unsigned   | NULLABLE                 |
| agent           | varchar(255)      | Agent class name         |
| role            | varchar(25)       | "user" or "assistant"    |
| content         | text              | Message content          |
| attachments     | JSON              | NULLABLE                 |
| tool_calls      | JSON              | NULLABLE                 |
| tool_results    | JSON              | NULLABLE                 |
| usage           | JSON              | Token usage stats        |
| meta            | JSON              | NULLABLE                 |
| created_at      | timestamp         |                          |
| updated_at      | timestamp         |                          |

### Models

#### Product (`app/Models/Product.php`)

```php
// Fillable
['name', 'description', 'price', 'quantity', 'image']

// Casts
'price' => 'decimal:2'
'quantity' => 'integer'

// Relationships
translations(): HasMany → ProductTranslation
arabicTranslation(): HasOne → ProductTranslation (where locale='ar')
```

#### ProductTranslation (`app/Models/ProductTranslation.php`)

```php
// Fillable
['product_id', 'locale', 'name', 'description']

// Relationships
product(): BelongsTo → Product
```

### Factory & Seeder

**ProductFactory**: Generates random products with name, description, price ($5-$500), quantity (0-100). Has `outOfStock()` state.

**ProductSeeder**: Creates 20 tech products (10 with descriptions, 10 without). Uses `firstOrCreate` to prevent duplicates on re-seeding.

---

## 5. AI Agents

All agents are in `app/Ai/Agents/` and implement interfaces from `laravel/ai`.

### ChatBot

```
File: app/Ai/Agents/ChatBot.php
Interfaces: Agent, Conversational
Traits: Promptable, RemembersConversations
```

**Purpose**: Multi-turn shopping assistant with full product knowledge.

**How it works**:
- Loads all products (id, name, description, price, quantity) as JSON in `instructions()`
- Uses `RemembersConversations` to maintain chat history per conversation
- Called via `$agent->continue($conversationId, as: $user)->prompt($message)`

**Context example**:
```
You are a helpful AI shopping assistant. You have access to the following product catalog:
[{"id":1,"name":"Wireless Headphones","description":"...","price":"49.99","quantity":25}, ...]

Use this data to answer questions about products including price, availability, and comparisons.
```

### ProductSearcher

```
File: app/Ai/Agents/ProductSearcher.php
Interfaces: Agent, HasStructuredOutput
Traits: Promptable
```

**Purpose**: Semantic product search supporting English and Arabic.

**How it works**:
- Loads all products with Arabic translations in `instructions()`
- Returns structured output: `{ ids: [3, 5, 1] }`
- Controller fetches full product data with `whereIn($ids)`

**Schema**:
```php
public function schema(JsonSchema $schema): array
{
    return [
        'ids' => $schema->array(
            $schema->integer()
        )->description('Array of matching product IDs')->required(),
    ];
}
```

**Context includes**:
```json
[
  {"id": 1, "name": "Wireless Headphones", "name_ar": "سماعات رأس لاسلكية", "price": "49.99", "quantity": 25},
  ...
]
```

**Search flow**:
```
"سماعة" → AI sees name_ar matches → returns {ids: [1, 14, 12]}
"cheap"  → AI interprets semantically → returns {ids: [10, 5, 8]}
"out of stock" → AI checks quantity=0 → returns {ids: [3]}
```

### DescriptionGenerator

```
File: app/Ai/Agents/DescriptionGenerator.php
Interfaces: Agent
Traits: Promptable
```

**Purpose**: Generates 1-2 sentence marketing description for a product.

**Input**: `"Product: Wireless Headphones, Price: $49.99"`
**Output**: `"Experience crystal-clear audio with these premium wireless headphones..."`

### ProductTranslator

```
File: app/Ai/Agents/ProductTranslator.php
Interfaces: Agent, HasStructuredOutput
Traits: Promptable
```

**Purpose**: Translates product name and description from English to Arabic.

**Schema**:
```php
return [
    'name' => $schema->string()->description('Translated product name in Arabic')->required(),
    'description' => $schema->string()->description('Translated product description in Arabic')->required(),
];
```

**Input**: `"Product name: Wireless Headphones\nProduct description: Premium audio..."`
**Output**: `{ "name": "سماعات رأس لاسلكية", "description": "..." }`

---

## 6. API Reference

Base URL: `/api`

### Conversations

#### List Conversations

```
GET /api/conversations
```

**Response** (paginated):
```json
{
  "data": [
    {
      "id": "019d1e73-14bc-707f-96d9-19d05b4a36d4",
      "title": "New Conversation",
      "user_id": null,
      "created_at": "2026-03-24T06:00:00.000000Z",
      "updated_at": "2026-03-24T06:05:00.000000Z"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

#### Create Conversation

```
POST /api/conversations
Content-Type: application/json

{ "title": "My Chat" }  // title is optional, defaults to "New Conversation"
```

**Response**:
```json
{
  "data": {
    "id": "019d1e73-14bc-707f-96d9-19d05b4a36d4",
    "title": "My Chat",
    "user_id": null,
    "created_at": "2026-03-24T06:00:00.000000Z",
    "updated_at": "2026-03-24T06:00:00.000000Z"
  }
}
```

#### Show Conversation

```
GET /api/conversations/{id}
```

**Response**:
```json
{
  "data": {
    "id": "019d1e73-...",
    "title": "My Chat",
    "user_id": null,
    "created_at": "...",
    "updated_at": "...",
    "messages": [
      {
        "id": "019d1e74-...",
        "conversation_id": "019d1e73-...",
        "role": "user",
        "content": "What products do you have?",
        "created_at": "..."
      },
      {
        "id": "019d1e74-...",
        "conversation_id": "019d1e73-...",
        "role": "assistant",
        "content": "Here are our products...",
        "usage": { "input_tokens": 500, "output_tokens": 150 },
        "created_at": "..."
      }
    ]
  }
}
```

#### Delete Conversation

```
DELETE /api/conversations/{id}
```

**Response**: `204 No Content`

#### Send Message

```
POST /api/conversations/{id}/messages
Content-Type: application/json

{ "message": "What headphones do you have?" }
```

**Validation**: `message` — required, string, max 10,000 characters.

**Response**:
```json
{
  "data": {
    "conversation_id": "019d1e73-...",
    "response": "We have two headphone products...",
    "message": {
      "id": "019d1e75-...",
      "conversation_id": "019d1e73-...",
      "role": "assistant",
      "content": "We have two headphone products...",
      "usage": { "input_tokens": 800, "output_tokens": 200 },
      "created_at": "..."
    },
    "usage": { "input_tokens": 800, "output_tokens": 200 }
  }
}
```

**Error responses**:
- `404`: Conversation not found
- `422`: Validation error (missing/invalid message)
- `429`: AI provider rate limited
- `503`: AI provider error

### Products

#### List Products

```
GET /api/products?per_page=9&page=1
```

**Query params**:
- `per_page` (int, default 9)
- `page` (int, default 1)

**Response** (Laravel pagination):
```json
{
  "data": [
    {
      "id": 1,
      "name": "Wireless Headphones",
      "description": "Premium audio experience...",
      "price": "49.99",
      "quantity": 25,
      "arabic_translation": {
        "id": 1,
        "product_id": 1,
        "locale": "ar",
        "name": "سماعات رأس لاسلكية",
        "description": "تجربة صوتية متميزة..."
      }
    }
  ],
  "links": { ... },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 9, "total": 20 }
}
```

#### AI Search

```
GET /api/products/search?q=سماعة
```

**Query params**:
- `q` (string, required, max 500)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Wireless Headphones",
      "description": "...",
      "price": "49.99",
      "quantity": 25,
      "arabic_translation": { ... }
    },
    {
      "id": 14,
      "name": "Noise Cancelling Earbuds",
      "description": "...",
      "price": "79.99",
      "quantity": 15,
      "arabic_translation": { ... }
    }
  ],
  "query": "سماعة"
}
```

**Error**: `503` — AI search error

#### Generate Description

```
POST /api/products/{id}/generate-description
```

**Response (generated)**:
```json
{
  "data": {
    "product_id": 5,
    "description": "Navigate your digital world effortlessly...",
    "generated": true
  }
}
```

**Response (already exists)**:
```json
{
  "data": {
    "product_id": 1,
    "description": "Premium audio experience...",
    "generated": false,
    "message": "Product already has a description."
  }
}
```

#### Translate Product

```
POST /api/products/{id}/translate
```

**Response (translated)**:
```json
{
  "data": {
    "product_id": 5,
    "name": "فأرة لاسلكية",
    "description": "تنقل في عالمك الرقمي بسهولة...",
    "generated": true
  }
}
```

**Response (already exists)**:
```json
{
  "data": {
    "product_id": 1,
    "name": "سماعات رأس لاسلكية",
    "description": "تجربة صوتية متميزة...",
    "generated": false,
    "message": "Translation already exists."
  }
}
```

### Web Routes

| Route       | View                 | Description     |
|-------------|----------------------|-----------------|
| `GET /`     | welcome.blade.php    | Landing page    |
| `GET /chat` | chat.blade.php       | Chat interface  |
| `GET /products` | products.blade.php | Product catalog |

---

## 7. Frontend

### Architecture

Both pages (`/chat` and `/products`) share the same build pipeline:

```
resources/js/app.js
  ├── bootstrap.js    → Axios with CSRF token
  ├── chat.js         → Chat page (guarded: only runs if #conversation-list exists)
  └── products.js     → Products page (guarded: only runs if #products-grid exists)
```

### Chat Page (`chat.blade.php` + `chat.js`)

#### Theme System

CSS variables define all colors:

```css
.theme-dark {
    --bg-body: #0c0c14;
    --bg-sidebar: #111119;
    --bg-card: #16161f;
    --bg-input: #1a1a28;
    --text-primary: #f1f1f4;
    --text-secondary: #9ca3af;
    --bubble-ai: #1a1a28;
    ...
}

.theme-light {
    --bg-body: #f7f7f8;
    --bg-sidebar: #ffffff;
    --text-primary: #111827;
    ...
}
```

#### Accent Colors

6 accent color schemes, each defining:

```css
.accent-indigo {
    --accent: #6366f1;        /* Primary accent */
    --accent-light: #818cf8;  /* Lighter variant */
    --accent-dim: rgba(99,102,241,0.12);  /* Background tint */
    --accent-glow: rgba(99,102,241,0.25); /* Shadow glow */
}
```

Available: `indigo`, `violet`, `rose`, `emerald`, `amber`, `cyan`

#### State Management

```javascript
// Persisted to localStorage
localStorage.getItem('chat-theme')   // 'theme-dark' | 'theme-light'
localStorage.getItem('chat-accent')  // 'indigo' | 'violet' | ...

// Runtime state
let currentConversationId = null;
let isSending = false;
```

#### Message Formatting

Messages support basic formatting:
- `**bold**` → `<strong>bold</strong>`
- `` `code` `` → `<code>code</code>`
- Line breaks → `<br>`

#### Copy to Clipboard

Each message bubble has a copy button that appears on hover:
- User messages: copy button appears on the left
- AI messages: copy button appears on the right
- Shows checkmark for 1.5s after successful copy

#### Suggestion Chips

Empty state shows clickable suggestion chips:
- "What products do you have?"
- "Compare prices"
- "What's in stock?"

Clicking a chip auto-sends that message.

### Products Page (`products.blade.php` + `products.js`)

#### Language Toggle

```javascript
let currentLang = 'en'; // 'en' | 'ar'
```

When `ar`:
- Cards show Arabic name/description from `arabic_translation`
- Cards set `dir="rtl"`
- Font switches to Noto Sans Arabic

#### Search Flow

```javascript
searchForm → submit → POST /api/products/search?q=...
  → isSearchMode = true
  → products = result.data
  → renderProducts()
  → hide pagination

searchInput → clear → exitSearch()
  → isSearchMode = false
  → loadPage(currentPage)
```

#### Bulk Actions

"Generate All" and "Translate All" buttons process products sequentially:

```javascript
// Generate All: processes products without descriptions
for (const p of allProducts.filter(p => !p.description)) {
    await generateDescription(p.id);
    // Update button text: "3/7"
}

// Translate All: processes products without Arabic translation
for (const p of allProducts.filter(p => !p.arabic_translation)) {
    await translateProduct(p.id);
    // Update button text: "5/12"
}
```

#### Stats Bar

```
[20] Total    [3] No desc    [5] No AR
```

Calculated from `allProducts` array (fetched with `per_page=999`).

---

## 8. Testing

### Running Tests

```bash
# All tests
php artisan test --compact

# Filtered
php artisan test --compact --filter=ConversationTest

# With coverage
php artisan test --coverage
```

### Test Structure

Tests use **Pest v4** with `RefreshDatabase` trait.

#### ConversationTest (6 tests)

| Test | Description |
|------|-------------|
| `it can list conversations` | Verifies pagination structure |
| `it can create a conversation with title` | POST with title |
| `it can create a conversation without title` | POST with default title |
| `it can show a conversation with messages` | GET with nested messages |
| `it can delete a conversation` | DELETE cascades to messages |
| `it returns 404 for missing conversation` | GET non-existent ID |

#### SendMessageTest (6 tests)

| Test | Description |
|------|-------------|
| `it can send a message and get response` | Full AI round-trip |
| `it persists messages in database` | Verifies both user + assistant stored |
| `it validates message is required` | 422 without message |
| `it validates message max length` | 422 with >10000 chars |
| `it returns 404 for missing conversation` | POST to non-existent |
| `it passes correct message to agent` | Verifies prompt content |

### AI Faking in Tests

Laravel AI provides `Agent::fake()` which auto-generates fake responses matching structured output schemas:

```php
use Laravel\Ai\Contracts\Agent;

Agent::fake();

// Now all agent calls return fake data
// Structured output agents return data matching their schema
```

---

## 9. Deployment

### Production Build

```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Build frontend
npm run build

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
php artisan db:seed --class=ProductSeeder
```

### Environment Checklist

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# AI Provider
AI_PROVIDER=groq
GROQ_API_KEY=your_production_key

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=ai_app
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/ai-app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Performance Notes

- **AI calls are slow** (~1-3s per request). The chat UI shows typing dots during this time.
- **Product data is loaded in agent instructions**. With many products (1000+), consider paginating the agent context or switching to Tool Use.
- **Eager loading** is used everywhere (`with('arabicTranslation:...')`) to prevent N+1 queries.
- **Pagination** is set to 9 products per page by default.

### Rate Limiting

Groq has rate limits (~30 requests/minute on free tier). The app handles this:
- Chat: Returns "Rate limited. Please wait a moment and try again." (429)
- Products: Returns "Search error: ..." (503)
- Frontend shows error messages to the user

### Code Formatting

Always run before committing:
```bash
vendor/bin/pint --dirty

```
