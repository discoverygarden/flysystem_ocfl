# Flysystem OCFL

A Flysystem adapter implementation allowing access to within OCFL storage structures.

Presently, targeting read-only access to binaries in [FCRepo 6 flavored storage](https://wiki.lyrasis.org/display/FEDORA6x/Fedora+OCFL+Object+Structure), especially via the lens of [Islandora](https://github.com/Islandora/islandora) which concerns primarily the storage of binaries.

## Execution Overview

OCFL storage is extensible at multiple levels:

- the layout proper, how objects are placed within the storage root, supporting:
  - [Flat Direct Storage](https://ocfl.github.io/extensions/0002-flat-direct-storage-layout.html)
  - [Hashed Truncated N-tuple Trees with Encapsulating Directories](https://ocfl.github.io/extensions/0003-hash-and-id-n-tuple-storage-layout.html)
  - [Hashed Truncated N-tuple Trees](https://ocfl.github.io/extensions/0004-hashed-n-tuple-storage-layout.html)
  - [Flat Omit Prefix Storage](https://ocfl.github.io/extensions/0006-flat-omit-prefix-storage-layout.html)
  - [N-tuple Omit Prefix Storage](https://ocfl.github.io/extensions/0007-n-tuple-omit-prefix-storage-layout.html)
- the structure of objects themselves
  - [Mutable HEAD](https://ocfl.github.io/extensions/0005-mutable-head.html)

The exact use of the OCFL storage is still up to the application proper, which may have different suggestions as to how objects should be structured, such as:
- [Fedora OCFL Object Structure](https://wiki.lyrasis.org/display/FEDORA6x/Fedora+OCFL+Object+Structure); and,
  - [Fedora Header Files](https://wiki.lyrasis.org/display/FEDORA6x/Fedora+Header+Files)

To support these multiple points of extensibility, we have defined:
- for layouts: the `OCFLPlugin` plugin type, with base implementations of those presently defined extensions.
  - We expect the layout to be specified in the `ocfl_layout.json` file in the root of the OCFL storage.
    - Strictly, per the [OCFL spec](https://ocfl.io/1.0/spec/#root-structure), this file is optional; however, having it present greatly simplifies the loading of the layout plugin.
- for object structures: Event evaluation based on the `flysystem_ocfl.inventory_location` event
  - This allows the "Mutable HEAD" extension to inject its bit of indirection, but allows falling back to the base location of an `inventory.json` sitting in the objects' root directory.
- for identifying target resources within objects: Event evaluation based on the `flysystem_ocfl.resource_location` event.
  - This allows the targeting of resources in the object, especially in the Fedora Commons 6 instance of which Islandora makes extensive use, where the object is based off of a singular "binary" resource, where fetching the object in certain instances (such as ours) is best interpreted as acquiring the byte-stream of the file.

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
      "root": "your-desired-scheme://ocfl-root",
      "id_prefix": "info:fedora/"
    }
  }
}
```

NOTE: An additional prefix within the bucket (`ocfl-root` in the example above, but could be anything) is presently necessary, due to [naive path normalization in Flysystem](https://github.com/thephpleague/flysystem/blob/3239285c825c152bcc315fe0e87d6b55f5972ed1/src/Adapter/AbstractAdapter.php#L49-L59).

### Configuration

There are presently just two points of configuration for the `ocfl` driver:

| Key | Description | Default |
| --- | --- | --- |
| `root` | The base path targetting the OCFL root storage. | N/A (required) |
| `id_prefix` | Prefix to add to incoming IDs. | the empty string |


## Known Issues

Not strictly an issue with this module, but may rise relating to the use of non-public filesystems in Drupal: https://www.drupal.org/project/drupal/issues/2786735

## Future Thoughts

- allow for fragments to be passed via Flysystem URIs, to be interpreted as different resources within a container object
  - no present use-case for this
