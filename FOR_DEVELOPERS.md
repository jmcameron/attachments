# Developer's guide

- [How to update release](#how-to-update-release-version)
- [How to update release package](#how-to-update-release-package)
- [Testing](#testing)

### How to update release version

- Update line VERSION = "<new version>" (for instance "4.0.2") in Makefile file
- launch make fixversions in an linux shell (This will update all xml versions (package/component/plugins)
- launch make fixsha to update the checksums for update server


### How to update release package

Update the README.md with new fix/features

launch make, this will create a attachments-<version>.zip in root directory

launch make fixsha, this will update the checksums in file update_pkg.xml

git push of all modifications

create a release with v<version> as name

upload the package file  attachments-<version>.zip into this release 


### Testing

This project uses PHPUnit for testing. To run the tests:

1. Install dependencies: `composer install`
2. Run all tests: `composer test` or `make test`
3. Run tests with coverage: `composer test-coverage` or `make test-coverage`

The test suite includes:
- Unit tests for core functionality in `tests/unit/`
- Integration tests in `tests/integration/` (when available)
- Helper tests for the AttachmentsPermissions class and related functionality

To run specific tests:
- `vendor/bin/phpunit tests/unit/Helper/` - Run all helper tests
- `vendor/bin/phpunit --testdox` - Run with human-readable output
- `vendor/bin/phpunit --coverage-html coverage/` - Run with coverage report

