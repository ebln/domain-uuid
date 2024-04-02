# DEPRECATED

With the advent of [UUID Version 8](https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis-00#section-5.8) better use a regular UUID library and reimplement.

- With the first 16 bit of `custom_a` to specify a domain, model or table
- The `ID` taking up 64 bits from `custom_c` and the last 2 bits of `custom_b`
- While optional, the original idea was:
  - 4 bit for type, directly following the domain block in `custom_a`
    - coding types
      - «neutral»	implying no special semantics for the free bits between domain and id (0)
      - «sortable»  a monotonic increment, meant for legacy data without recoverable timestamps (1)
      - «timestamp» timestamp with an offset to the epoch (32 bit, filled up with all free bits; 2)
      - «sharding»  all bits in the middle would make up an instance id for sharding
- The `ID`s were supposed to be obfuscatable using [Knuth's integer hash of jenssegers/optimus](https://github.com/jenssegers/optimus)
- This all was meant to be a gentle path for migrating autoincremented row IDs to UUIDs, while making it possible to programatically extract the original table (mapped as domain id) and the original row id from the UUID.


----
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
