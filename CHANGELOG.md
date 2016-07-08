# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### DF-752 Added support for AD group hierarchy when mapping DreamFactory role to AD group.

### Changed

### Fixed

## [0.3.0] - 2016-05-27
### Changed
- Moved seeding functionality to service provider to adhere to df-core changes.
- Licensing changed to support subscription plan, see latest [dreamfactory](https://github.com/dreamfactorysoftware/dreamfactory).
- Updating service type labels.

## [0.2.2] - 2016-04-21
### Added
- Added AD username to user table and lookup

## [0.2.1] - 2016-03-07
### Fixed
- Cleanup swagger output to pass validation

## [0.2.0]
### Added
- Added filtering on all resources.
- Added --filter option to dreamfactory:ad-group-import utility.

### Changed
- Changed swagger definition to work with Swagger 2.0

### Fixed
- Fixed issue with exceeding MaxPageSize by utilizing pagination.

## [0.1.2] - 2015-12-19
### Added
- New 'computer' resource.

### Fixed
- Fixed primary key for role_adldap table model.

## [0.1.1] - 2015-11-24
### Added
- New artisan console command dreamfactory:ad-group-import to import AD groups and DF role.
- Mapping a DF role to AD group.
- New 'group' and 'user' resource.

## 0.1.0 - 2015-10-24
First official release working with the new [dreamfactory](https://github.com/dreamfactorysoftware/dreamfactory) project.

[Unreleased]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.3.0...HEAD
[0.3.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.2...0.3.0
[0.2.2]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.1...0.2.2
[0.2.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.2...0.2.0
[0.1.2]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.0...0.1.1
