# LibreSignage API documentation

## API endpoints

* /api/listing_gen.php
    * Get the full list of existing content screen URIs. The list
      is a JSON encoded array of the URLs (`['URI1', 'URI2', ...]`).
      The URIs are file paths relative to the document root.
* /api/LibreSignage_license.php
    * Get the raw text version of the LibreSignage license.
* /api/library_licenses.php
    * Get the raw text version of the license information of the
      libraries used in LibreSignage.
