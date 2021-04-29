# GraphQL Thumbnails for Statamic CMS

This Statamic GraphQL addon provides a `thumbnail` field on all `AssetInterface` fields. Either provide the argument `name` to query by predefined formats or use Just-In-Time thumbnail generation and provide `width`, `height` or `fit`.

## Features

- Predefined thumbnail Formats: Query by `name`
- Just-In-Time thumbnail: Query by `width`, `height` or `fit`
- Control Panel UI to define formats and enable/disable JIT thumbnail generation
- Permissions for managing GraphQL thumbnail Settings

![GraphQL Thumbnails for Statamic CMS](https://user-images.githubusercontent.com/46897060/116618272-1c6ef680-a93f-11eb-82ef-932761ea6ee1.png)

## Installation

Run `composer composer require legrisch/statamic-graphql-thumbnails`

## Setup

After installation, you must visit the control panel to define formats to query for or enable the JIT thumbnail generation.

## Usage

### Formats Usage

The `thumbnail` field requires an argument `name` which resolves to the name of one of your formats.

```graphql
asset {
  thumbnail(name: "small")
}
```

yields

```json
"asset": {
  "thumbnail": "http://absolute.url/to/thumbnail-small.jpg"
}
```

### JIT Usage

The `thumbnail` field requires an argument `width` or `height` with an integer. Additionally you may specify the parameter `fit`. The possible values are: `contain`, `max`, `fill`, `stretch`, `crop`, `crop_focal` with the default being `crop_focal`.

```graphql
asset {
  thumbnail(width: 100)
}
```

yields

```json
"asset": {
  "thumbnail": "http://absolute.url/to/thumbnail-with-100px-width.jpg"
}
```

### Full Examples

#### Query single thumbnail

```graphql
query MyQuery {
  entries(collection: "pages") {
    data {
      ... on Entry_Pages_Pages {
        image {
          id
          thumbnail(width: 200)
        }
      }
    }
  }
}
```

yields

```json
{
  "data": {
    "entries": {
      "data": [
        {
          "image": {
            "id": "assets::20210409232458.jpg",
            "thumbnail": "http://absolute.url/to/thumbnail.jpg"
          }
        }
      ]
    }
  }
}
```

#### Query multiple thumbnails

Use GraphQL aliases to query multiple thumbnails:

```graphql
query MyQuery {
  entries(collection: "pages") {
    data {
      ... on Entry_Pages_Pages {
        image {
          id
          thumbnailSmall: thumbnail(width: 100)
          thumbnailMedium: thumbnail(width: 250)
          thumbnailLarge: thumbnail(width: 500)
          thumbnailSquare: thumbnail(width: 500, height: 200, fit: "crop")
        }
      }
    }
  }
}
```

yields

```json
{
  "data": {
    "entries": {
      "data": [
        {
          "image": {
            "id": "assets::20210409232458.jpg",
            "thumbnailSmall": "http://absolute.url/to/small/thumbnail.jpg",
            "thumbnailMedium": "http://absolute.url/to/medium/thumbnail.jpg",
            "thumbnailLarge": "http://absolute.url/to/large/thumbnail.jpg",
            "thumbnailSquare": "http://absolute.url/to/square/thumbnail.jpg"
          }
        }
      ]
    }
  }
}
```

---

This addon follows [the official example](https://statamic.dev/graphql#custom-fields) and wraps it in an easy-to-use addon.
