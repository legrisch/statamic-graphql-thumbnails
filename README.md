# GraphQL Thumbnails for Statamic CMS

This Statamic GraphQL addon provides a `thumbnail` field on all `AssetInterface` fields. Either provide the argument `name` to query by predefined formats or use Just-In-Time thumbnail generation and provide `width`, `height` or `fit`.

## Features

- Predefined thumbnail Formats: Query by `name`
- Just-In-Time thumbnail: Query by `width`, `height` or `fit`
- Control Panel UI to define formats and enable/disable JIT thumbnail generation
- Permissions for managing GraphQL thumbnail Settings

![GraphQL Thumbnails for Statamic CMS](https://user-images.githubusercontent.com/46897060/116623211-2fd19000-a946-11eb-8d45-f8908499e542.png)

## Installation

Run `composer require legrisch/statamic-graphql-thumbnails`

## Setup

After installation, you must visit the control panel to define formats to query for or enable the JIT thumbnail generation.

## Usage

### Formats Usage

The `thumbnail` field requires an argument `name` which resolves to the name of one of your formats.

If formats are defined, you can also directly access a property `srcset` on the Asset.

The field `placeholder` returns a base64 encoded image to use as a lazyload placeholder. The width and the amount of blur can be adjusted in the settings.

```graphql
asset {
  thumbnail(name: "small")
  srcset
  placeholder
}
```

yields

```json
"asset": {
  "thumbnail": "http://absolute.url/to/thumbnail-small.jpg",
  "srcset": "http://absolute.url/to/thumbnail-small.jpg 500w, http://absolute.url/to/thumbnail-medium.jpg 1000w",
  "placeholder": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD//gA7Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1…"
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

## License

This project is licensed under the MIT License.
