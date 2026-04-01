# Integration Project Between 1C and WordPress

## General Goal
Create a bidirectional data transfer mechanism between the enterprise accounting system 1C and a website built on the WordPress platform to facilitate efficient exchange of information and enhance interaction between systems.

## Main Task
Develop integration between the 1C Enterprise information system and the WordPress site by implementing automatic export and import of required data according to the predefined functional requirements.

## Security
REST API endpoints are secured using basic authentication scheme (HTTP Basic Authentication).

# Import Categories and Subcategories

To import categories and subcategories, send a request body in the following JSON format:

``` json
[
  {
    "id": 7,
    "name": "Household Linoleum",
    "description": "Category description",
    "parent": 0,
    "image": "https://example.com/image.jpg"
  },
  {
    "id": 8,
    "name": "21 class (bedrooms)",
    "description": "Subcategory description",
    "parent": 7,
    "image": "https://example.com/image.jpg"
  }
]
```

Request Fields:
- id (integer) — Unique identifier of the category.
- name (string) — Name of the category or subcategory.
- description (string) — Category description (optional).
- parent (integer) — Identifier of the parent category (use 0 for top-level categories).
- image (string) — Image URL of the category (optional).

## Create Category Endpoint

### Overview

Endpoint:POST https://example.com/wp-json/import/v1/categories

Description:Creates new product categories/subcategories in the system based on received JSON payload.

### Request Details

| Field       | Type      | Required | Description                                               |
|-------------|-----------|----------|-----------------------------------------------------------|
| `id`        | integer   | ✔️        | Unique numeric ID of the category                          |
| `name`      | string    | ✔️        | Human-readable name of the category                        |
| `description`| string    | ❌        | Optional textual description of the category               |
| `parent`    | integer   | ❌        | Parent category ID (use `0` for top-level categories)      |
| `image`     | string    | ❌        | Full URL of the category image                             |