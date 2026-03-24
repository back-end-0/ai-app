# AI App — Laravel AI-Powered E-Commerce Assistant

Full-stack AI-powered e-commerce application built with **Laravel 13** and **Laravel AI SDK (v0)**. Features an intelligent chat assistant, semantic product search, AI-generated descriptions, and Arabic translation.

## Tech Stack

| Layer     | Technology                                     |
|-----------|-------------------------------------------------|
| Backend   | PHP 8.3, Laravel 13, Laravel AI v0              |
| Frontend  | Vanilla JS, Tailwind CSS v4, Vite 8, Axios      |
| Database  | MySQL                                            |
| Testing   | Pest v4, PHPUnit v12                             |
| AI        | Groq / Gemini / Anthropic (configurable)         |

## Features

### AI Chat Assistant
- Multi-turn conversations stored in database
- Product-aware: knows all products, prices, and stock levels
- Rate limit and error handling
- Suggestion chips for quick prompts
- Copy-to-clipboard on message hover

### Product Catalog
- Paginated product grid (responsive 1/2/3 columns)
- Dark/light theme toggle with 6 accent colors
- English/Arabic language toggle with RTL support

### AI-Powered Search
- Semantic search via `ProductSearcher` agent
- Understands English and Arabic queries
- Handles semantic queries: "cheap", "out of stock", "audio products"
- One AI call for IDs, one `whereIn` query for data

### AI Description Generator
- Generates marketing descriptions for products
- Bulk "Generate All" for batch processing

### AI Product Translation (EN → AR)
- Translates product name and description to Arabic
- Structured output ensures reliable JSON responses
- Bulk "Translate All" for batch processing

## Project Structure

```
app/
├── Ai/
│   ├── Agents/
│   │   ├── ChatBot.php              # Conversational shopping assistant
│   │   ├── ProductSearcher.php      # Semantic search (structured output)
│   │   ├── DescriptionGenerator.php # Marketing copy generator
│   │   └── ProductTranslator.php    # EN→AR translation (structured output)
│   └── Tools/
│       ├── SearchProducts.php
│       └── GenerateProductDescription.php
├── Http/
│   ├── Controllers/Api/
│   │   ├── ChatController.php       # Conversations & messaging
│   │   └── ProductController.php    # Products, search, generate, translate
│   ├── Requests/
│   │   ├── SendMessageRequest.php
│   │   └── StoreConversationRequest.php
│   └── Resources/
│       ├── ConversationResource.php
│       └── MessageResource.php
├── Models/
│   ├── User.php
│   ├── Product.php                  # translations(), arabicTranslation()
│   └── ProductTranslation.php
database/
├── migrations/
├── factories/                       # UserFactory, ProductFactory
└── seeders/                         # ProductSeeder (20 products)
resources/
├── js/
│   ├── app.js                       # Entry point
│   ├── bootstrap.js                 # Axios setup
│   ├── chat.js                      # Chat UI logic
│   └── products.js                  # Products UI logic
└── views/
    ├── chat.blade.php               # Chat interface
    └── products.blade.php           # Product catalog
tests/Feature/Api/
├── ConversationTest.php             # 6 tests
└── SendMessageTest.php              # 6 tests
```

## API Endpoints

### Conversations

| Method   | Endpoint                                  | Description              |
|----------|-------------------------------------------|--------------------------|
| `GET`    | `/api/conversations`                      | List conversations       |
| `POST`   | `/api/conversations`                      | Create conversation      |
| `GET`    | `/api/conversations/{id}`                 | Show with messages       |
| `DELETE` | `/api/conversations/{id}`                 | Delete conversation      |
| `POST`   | `/api/conversations/{id}/messages`        | Send message, get AI reply |

### Products

| Method   | Endpoint                                  | Description              |
|----------|-------------------------------------------|--------------------------|
| `GET`    | `/api/products`                           | List (paginated, 9/page) |
| `GET`    | `/api/products/search?q=...`              | AI semantic search       |
| `POST`   | `/api/products/{id}/generate-description` | Generate AI description  |
| `POST`   | `/api/products/{id}/translate`            | Translate to Arabic      |

### Web Routes

| Route       | View              |
|-------------|-------------------|
| `/`         | Welcome           |
| `/chat`     | Chat interface     |
| `/products` | Product catalog    |

## AI Architecture

### Agents

| Agent                  | Interface            | Purpose                          |
|------------------------|----------------------|----------------------------------|
| `ChatBot`              | Conversational       | Shopping assistant with memory    |
| `ProductSearcher`      | HasStructuredOutput  | Returns matching product IDs     |
| `DescriptionGenerator` | Agent                | Returns marketing description    |
| `ProductTranslator`    | HasStructuredOutput  | Returns `{name, description}` in Arabic |

### How Search Works

```
User query ("سماعة" or "cheap headphones")
    ↓
ProductSearcher agent (has all products + Arabic names in context)
    ↓
Returns: { ids: [1, 14, 12] }
    ↓
Product::whereIn('id', $ids) → full product data with translations
```

One AI call + one DB query.

## Database Schema

### products

| Column      | Type              | Notes           |
|-------------|-------------------|-----------------|
| id          | bigint (PK)       | Auto-increment  |
| name        | string            |                 |
| description | text (nullable)   |                 |
| price       | decimal(10,2)     |                 |
| quantity    | unsigned int      | Default: 0      |
| image       | string (nullable) |                 |
| timestamps  |                   |                 |

### product_translations

| Column      | Type              | Notes                        |
|-------------|-------------------|------------------------------|
| id          | bigint (PK)       | Auto-increment               |
| product_id  | foreign key       | Cascades on delete           |
| locale      | string(10)        | e.g. "ar"                    |
| name        | string            | Translated name              |
| description | text (nullable)   | Translated description       |
| timestamps  |                   |                              |

Unique index on `[product_id, locale]`.

### agent_conversations

| Column     | Type           | Notes                           |
|------------|----------------|---------------------------------|
| id         | UUID (PK)      |                                 |
| user_id    | foreign (nullable) |                             |
| title      | string         |                                 |
| timestamps |                |                                 |

### agent_conversation_messages

| Column          | Type           | Notes                        |
|-----------------|----------------|------------------------------|
| id              | UUID (PK)      |                              |
| conversation_id | string         | Indexed                      |
| role            | string(25)     | user / assistant             |
| content         | text           |                              |
| usage           | JSON           | Token usage stats            |
| timestamps      |                |                              |

## Setup

### Requirements

- PHP 8.3+
- MySQL
- Node.js 18+
- Composer

### Installation

```bash
git clone <repo-url> ai-app
cd ai-app
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Configuration

Set your AI provider in `.env`:

```env
AI_PROVIDER=groq
GROQ_API_KEY=your-key-here

# Or:
# AI_PROVIDER=gemini
# GEMINI_API_KEY=your-key-here

# AI_PROVIDER=anthropic
# ANTHROPIC_API_KEY=your-key-here
```

Set your database:

```env
DB_CONNECTION=mysql
DB_DATABASE=ai_app
DB_USERNAME=root
DB_PASSWORD=
```

### Database Setup

```bash
php artisan migrate
php artisan db:seed --class=ProductSeeder
```

### Build & Run

```bash
npm run build
php artisan serve
```

Or for development with hot reload:

```bash
composer run dev
```

### Running Tests

```bash
php artisan test --compact
```

16 tests, 48 assertions.

### Code Formatting

```bash
vendor/bin/pint
```

## Frontend Theming

### Theme Toggle
Both chat and products pages support dark/light mode, persisted to `localStorage`.

### Accent Colors (Chat)
6 accent colors: Indigo, Violet, Rose, Emerald, Amber, Cyan. Changes message bubbles, avatars, send button, and all accent elements.

### Language Toggle (Products)
Switch between English and Arabic. Arabic mode enables RTL layout and shows translated content.
