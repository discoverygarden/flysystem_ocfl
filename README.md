# Flysystem OCFL

A Flysystem adapter implementation allowing access to within OCFL storage structures.

Presently, targeting read-only access to binaries in [FCRepo 6 flavored storage](https://wiki.lyrasis.org/display/FEDORA6x/Fedora+OCFL+Object+Structure), especially via the lens of [Islandora](https://github.com/Islandora/islandora) which concerns primarily the storage of binaries.

## Usage

This should offer a similar parallel to the `fedora` driver shipped with `islandora/islandora`, which might be configured with something like:

```json
{
  "fedora": {
    "driver": "fedora",
    "config": {
      "root": "http://localhost:8080/fcrepo/rest/"
    }
  }
}
```

To instead read directly from the OCFL storage layout, with something like:

```json
{
  "fedora": {
    "driver": "ocfl",
    "config": {
      "root": "/opt/fcrepo/fcrepo/data/ocfl-root",
      "id_prefix": "info:fedora/"
    }
  }
}
```

Hypothetically, this could even be chained with another Flysystem implementation such as S3, such as:

```json
{
  "your-desired-scheme": {
    "driver": "s3",
    "config": {
      "bucket": "your-ocfl-root-bucket",
    }
  },
  "fedora": {
    "driver": "ocfl",
    "config": {
      "root": "your-desired-scheme://",
      "id_prefix": "info:fedora/"
    }
  }
}
```

NOTE: The single slash on `your-desired-scheme:/` is intentional, as we expect to add a slash when concatenating additional path components.

### Configuration

There are presently just two points of configuration for the `ocfl` driver:

| Key | Description | Default |
| --- | --- | --- |
| `root` | The base path targetting the OCFL root storage. | N/A (required) |
| `id_prefix` | Prefix to add to incoming IDs. | the empty string |


## Known Issues

Not strictly an issue with this module, but may rise relating to the use of non-public filesystems in Drupal: https://www.drupal.org/project/drupal/issues/2786735

## Future Thoughts

- allow for fragments to be passed via Flysystem, to be interpreted as different resources within an object
  - no present use-case for this
