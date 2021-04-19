# GraphQL Thumbnails for Statamic CMS

This little Statamic addon provides a useful `thumbnail` field on all `AssetInterface` fields taking an argument `width` to return a thumbnail with the defined width.

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
        },
      ]
    }
  }
}
```

## Query multiple thumbnails

Use it with a GraphQL alias to query multiple thumbnails:

```graphql
query MyQuery {
  entries(collection: "pages") {
    data {
      ... on Entry_Pages_Pages {
        image {
          id
          thumbnailSmall: thumbnail(width: 100)
          thumbnailLarge: thumbnail(width: 500)
          thumbnailMedium: thumbnail(width: 250)
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
            "thumbnailLarge": "http://absolute.url/to/large/thumbnail.jpg"
          }
        },
        {
          "image": {
            "id": "assets::20210409232458.jpg",
            "thumbnailSmall": "http://absolute.url/to/small/thumbnail.jpg",
            "thumbnailMedium": "http://absolute.url/to/medium/thumbnail.jpg",
            "thumbnailLarge": "http://absolute.url/to/large/thumbnail.jpg"
          }
        }
      ]
    }
  }
}
```

---

This addon follows [the official example](https://statamic.dev/graphql#custom-fields) and wraps it in an easy-to-use addon.