# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
DF-425 Allowing configurable role per app for open registration, OAuth, and AD/Ldap services.

### Changed

### Fixed

## [0.4.1] - 2016-08-26
### Changed
- Making AD authentication not dependent on the full base DN

## [0.4.0] - 2016-08-21
### Changed
- General cleanup from declaration changes in df-core for service doc and providers

## [0.3.1] - 2016-07-08
### Added
- DF-752 Added support for AD group hierarchy when mapping DreamFactory role to AD group.

### Changed
- General cleanup from declaration changes in df-core.

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

[Unreleased]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.4.1...HEAD
[0.4.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.4.0...0.4.1
[0.4.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.3.1...0.4.0
[0.3.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.2...0.3.0
[0.2.2]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.1...0.2.2
[0.2.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.2...0.2.0
[0.1.2]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.0...0.1.1
