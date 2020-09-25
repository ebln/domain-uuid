Namespaced IDs via UUID
=======================

Creates non-standard UUIDs – resembling namespace-based using MD5-hashes i.e. version 3

Yet it encodes a domain ID (e.g. mapped tables or entities) and numeric IDs (e.g. database rows).
Between domain ID and node ID there's space left for a timestamp or other extra information to enable monotonic sorting.

This is meant to support the migraton of tables having classic autoincremented IDs towards a DB design were you'd have UUIDs as primary keys.

----

Inspired by:
* https://itnext.io/laravel-the-mysterious-ordered-uuid-29e7500b4f8?gi=a3f68dcb88fa
* https://github.com/ramsey/uuid/blob/master/src/Codec/TimestampFirstCombCodec.php
* ULID
* UUIDv6
* KSUID
* https://github.com/symfony/polyfill-uuid/blob/master/Uuid.php
* https://instagram-engineering.tumblr.com/post/10853187575/sharding-ids-at-instagram

To be done
https://github.com/jenssegers/optimus
