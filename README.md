# Laravel MUI Catalogue

A modern Laravel backend with React.js frontend application that displays a catalogue of learning content fetched from the Acorn External Catalogue API.

## Features

- **Content Type Specialisation**: Implements a robust class hierarchy for different content types (Course, LiveLearning, Video, etc.)
- **Efficient Caching**: Implements multi-level caching for API responses to improve performance
- **Type-Safe Data Handling**: Uses Laravel Data v4 for strong typing and validation
- **Resilient API Integration**: Features retry mechanisms and comprehensive error handling
- **Modern UI**: Clean, responsive Material UI interface for displaying learning content

## Tech Stack

- **Backend**: Laravel 12.x
- **Frontend**: React 19 with Material UI
- **Data Layer**: Spatie Laravel Data
- **HTTP Client**: Guzzle with middleware for logging, caching and retries
- **Testing**: PHPUnit and for integration testing

## Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/laravel-mui-catalogue.git
cd laravel-mui-catalogue

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure .env with your Acorn API credentials
# ACORN_API_BASE_URL=https://staging.acornlms.com
# ACORN_API_TENANCY_ID=3
# ACORN_API_TOKEN=

# Run migrations (if applicable)
php artisan migrate

# Build assets
npm run dev
```

## Docker Development Environment

This project has been fully Dockerised to establish a consistent development environment for all team members. The Docker setup ensures that everyone on the team works with identical development conditions regardless of their local machine setup.

### Setting Up the Development Environment with Docker

```bash
# Clone the repository
git clone https://github.com/yourusername/laravel-mui-catalogue.git
cd laravel-mui-catalogue

# Copy the environment configuration file
cp .env.docker .env

# Modify .env configuration as needed, particularly the Redis settings

# Build and start the development containers
docker-compose up -d

# Check the container status
docker-compose ps
```

Visit http://localhost:8000 to access the development environment.

### Development Container Services

The Docker development environment includes the following services:

1. **app** - Laravel PHP application service
2. **web** - Nginx web server
3. **redis** - Redis for caching and queue services
4. **queue** - Laravel queue worker
5. **scheduler** - Laravel scheduler service

### Common Docker Commands for Development

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View service logs
docker-compose logs -f

# View logs for a specific service
docker-compose logs -f app

# Access the application container
docker-compose exec app sh

# Run Artisan commands in the container
docker-compose exec app php artisan list
```

### Customising Redis Configuration for Development

The default configuration uses the Redis service within the container. If you need to connect to an external Redis service, modify the following settings in your `.env` file:

```
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379
```

Note: If using a custom Redis service, you may also need to modify the queue, cache, and session driver configurations.

## Usage

```bash
# Start the Laravel development server
php artisan serve

# In a separate terminal, start the frontend dev server
npm run dev
```

Visit http://localhost:8000 to view the application.

## Architecture

### Backend

The backend follows a layered architecture:

1. **Controllers**: Handle HTTP requests and delegate to services
2. **Services**: Contain business logic and interact with external APIs
3. **Data Layer**: Transforms API responses into strongly-typed objects
4. **HTTP Client**: Handles communication with external APIs

### Content Type System

The application uses a sophisticated inheritance-based content type system:

- `Content`: Base class with shared properties and methods
- Specialised classes for each content type:
    - `CourseData`
    - `LiveLearningData`
    - `VideoData`
    - `ResourceData`
    - `ProgramData`
    - `PageData`
    - `PartnerContentData`
    - `UnknownContentData` (fallback)

### API Integration

The Acorn API integration features:

- Versioned API endpoints
- Comprehensive error handling
- Response caching for performance
- Retry mechanisms for resilience
- Detailed logging for debugging

## Development Highlights

### Strong Typing

Using Laravel Data v4, all content types are strongly typed with validation rules, ensuring data integrity throughout the application.

### Factory Pattern

The `ContentDataFactory` creates appropriate content instances based on type, providing a clean interface for content creation.

### Caching Strategy

Implements a multi-tiered caching strategy:

- HTTP response caching
- Processed content caching
- Configurable TTL and cache invalidation

### Testing

Comprehensive integration tests ensure the API connection is reliable and data processing works correctly, with special handling for problematic content types.

## Caching Strategy

The system implements an intelligent caching strategy to optimise ACORN API access performance. Key features include:

### Cache Configuration

Cache configuration is located in `config/acorn.php`:

```php
'cache' => [
    'enabled' => true,  // Enable/disable caching
    'ttl' => 900,       // Default cache time (seconds)
    'version' => 'v1',  // Cache version for easy invalidation
    'content_types' => [
        'course' => 900,           // Course: 15 minutes
        'live learning' => 300,    // Live Learning: 5 minutes
        'resource' => 1800,        // Resource: 30 minutes
        'video' => 1800,          // Video: 30 minutes
        'page' => 3600,           // Page: 1 hour
        'partnered content' => 3600, // Partnered Content: 1 hour
    ],
    'background_refresh' => [
        'enabled' => true,      // Enable background refresh
        'threshold' => 0.8,     // Trigger background refresh when cache reaches 80% of TTL
    ],
],
```

### Key Features

1. **Content Type-Specific Caching**: Different content types have customised cache durations
2. **Cache Version Control**: Version changes automatically refresh all caches
3. **Background Refresh**: When cache nears expiry, refresh occurs in background whilst returning current cached data
4. **Cache Bypass**: API supports `noCache=true` parameter for direct data retrieval
5. **Manual Refresh**: Provides API endpoints for manually refreshing specific content caches

### API Endpoints

#### Retrieve Content List

```
GET /api/v1/content?page=1&perPage=10&contentType=course&noCache=false
```

Parameters:

- `page` (optional): Page number (integer, minimum 1)
- `perPage` (optional): Number of items to display per page (integer, minimum 1, maximum 100)
- `contentType` (optional): Filter by content type (must be one of: course, live learning, resource, video, program, page, partnered content)
- `noCache` (optional): Whether to bypass cache (boolean: true/false)

Validation Rules:

- All parameters are optional
- Invalid parameters will return a 422 status code with detailed error messages
- Default values: page=1, perPage=10 (from config)

Example Error Response:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "contentType": ["The content type must be one of: course, live learning, resource, video, program, page, or partnered content."],
        "page": ["The page number must be at least 1."],
        "perPage": ["The items per page cannot exceed 100."]
    }
}
```

#### Retrieve Specific Content Item

```
GET /api/v1/content/{id}?noCache=false
```

Parameters:

- `id`: Content ID
- `noCache`: Whether to bypass cache (default false)

#### Refresh Cache

```
POST /api/v1/content/refresh-cache?id=123
```

or

```
POST /api/v1/content/refresh-cache?contentType=course
```

Parameters (at least one is required):

- `id`: Refresh cache for specific content item
- `contentType`: Refresh cache for all content of specific type

## Development Guide

```bash
# Clone the repository
git clone https://github.com/yourusername/laravel-mui-catalogue.git
cd laravel-mui-catalogue

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure .env with your Acorn API credentials
# ACORN_API_BASE_URL=https://staging.acornlms.com
# ACORN_API_TENANCY_ID=3
# ACORN_API_TOKEN=WTZ1RHJ3RjdPOW95N0tDT1pvWFNwR2tTQ042ejBKVHVMRUdsTE1PRQ==

# Run migrations (if applicable)
php artisan migrate

# Build assets
npm run dev
```

## Usage

```bash
# Start the Laravel development server
php artisan serve

# In a separate terminal, start the frontend dev server
npm run dev
```

Visit http://localhost:8000 to view the application.

## Architecture

### Backend

The backend follows a layered architecture:

1. **Controllers**: Handle HTTP requests and delegate to services
2. **Services**: Contain business logic and interact with external APIs
3. **Data Layer**: Transforms API responses into strongly-typed objects
4. **HTTP Client**: Handles communication with external APIs

### Content Type System

The application uses a sophisticated inheritance-based content type system:

- `Content`: Base class with shared properties and methods
- Specialised classes for each content type:
    - `CourseData`
    - `LiveLearningData`
    - `VideoData`
    - `ResourceData`
    - `ProgramData`
    - `PageData`
    - `PartnerContentData`
    - `UnknownContentData` (fallback)

### API Integration

The Acorn API integration features:

- Versioned API endpoints
- Comprehensive error handling
- Response caching for performance
- Retry mechanisms for resilience
- Detailed logging for debugging

## Development Highlights

### Strong Typing

Using Laravel Data v4, all content types are strongly typed with validation rules, ensuring data integrity throughout the application.

### Factory Pattern

The `ContentDataFactory` creates appropriate content instances based on type, providing a clean interface for content creation.

### Caching Strategy

Implements a multi-tiered caching strategy:

- HTTP response caching
- Processed content caching
- Configurable TTL and cache invalidation

### Testing

Comprehensive integration tests ensure the API connection is reliable and data processing works correctly, with special handling for problematic content types.
