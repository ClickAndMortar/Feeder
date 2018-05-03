# Feeder

PHP data feeder for MySQL & Elasticsearch made with 💙 by Click & Mortar.

## Usage

```
composer require clickandmortar/feeder
```

Then:

* Create a `.env` file at project's root based on `vendor/clickandmortar/feeder/.env.dist` template. 
* Create a CLI app `bin/console` based on `examples/console` script


Create custom command(s) which extend `Feeder\Command\ElasticsearchCommand` in `src/Command`.

See `examples/ExampleCommand.php` for example usage.
