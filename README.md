# Feeder

PHP data feeder for MySQL & Elasticsearch made with 💙 by Click & Mortar.

## Install

```
composer require clickandmortar/feeder ~0.1
```

Then:

Create a `.env` file at project's root based on `vendor/clickandmortar/feeder/.env.dist` template. 

Then create custom command(s) which extend `Feeder\Command\ElasticsearchCommand` in `src/Command`.

See `examples/ExampleCommand.php` for example usage.

## Usage

```
bin/feeder your:command
```

## Specific services

### Geocoding

