# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
## [0.14.0] - 2017-12-28
### Added
- Added package discovery
### Changed
- DF-1150 Update copyright and support email
- Separated resources from resource handlers

## [0.13.0] - 2017-11-03
### Changed
- Added RBAC support for package export
- Added subscription requirements to service provider
- Upgrade Swagger to OpenAPI 3.0 specification

## [0.12.2] - 2017-10-16
### Fixed
- Fixed AD group import issue after supporting has-one DB relationship

## [0.12.1] - 2017-09-18
### Added
- DF-1131 Support for AD SSO and SQLServer windows authentication

## [0.12.0] - 2017-08-17
### Changed
- Reworking API doc usage and generation

## [0.11.0] - 2017-07-27
### Added
- DF-1142 Added ldap_username field to user table. Added DF_JWT_USER_CLAIM env option to include user attribute in JWT
### Fixed
- DF-1141 Ignored fetching object by DN from child domain
- DF-1169 Fixed ldap login when uid field (username) is blank

## [0.10.0] - 2017-06-05
### Changed
- Cleanup - removal of php-utils dependency

## [0.9.0] - 2017-04-21
### Added
- DF-895 Added support for username based authentication

## [0.8.1] - 2017-03-06
- Fixed migration error: Specified key was too long; max key length is 767 bytes

## [0.8.0] - 2017-03-03
- Major restructuring to upgrade to Laravel 5.4 and be more dynamically available

### Fixed
- DF-716 Fixed AD login issue with accented characters in user names

## [0.7.0] - 2017-01-16
- Dependency changes
- Clean out of MERGE verb, handled at controller and routing

## [0.6.0] - 2016-11-17
- Dependency changes only

## [0.5.0] - 2016-10-03
### Added
- DF-425 Allowing configurable role per app for open registration, OAuth, and AD/Ldap services

## [0.4.1] - 2016-08-26
### Changed
- Making AD authentication not dependent on the full base DN

## [0.4.0] - 2016-08-21
### Changed
- General cleanup from declaration changes in df-core for service doc and providers

## [0.3.1] - 2016-07-08
### Added
- DF-752 Support for AD group hierarchy when mapping DreamFactory role to AD group

### Changed
- General cleanup from declaration changes in df-core

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
- Added --filter option to dreamfactory:ad-group-import utility

### Changed
- Changed swagger definition to work with Swagger 2.0

### Fixed
- Fixed issue with exceeding MaxPageSize by utilizing pagination

## [0.1.2] - 2015-12-19
### Added
- New 'computer' resource.

### Fixed
- Fixed primary key for role_adldap table model

## [0.1.1] - 2015-11-24
### Added
- New artisan console command dreamfactory:ad-group-import to import AD groups and DF role
- Mapping a DF role to AD group
- New 'group' and 'user' resource

## 0.1.0 - 2015-10-24
First official release working with the new [dreamfactory](https://github.com/dreamfactorysoftware/dreamfactory) project.

[Unreleased]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.14.0...HEAD
[0.14.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.13.0...0.14.0
[0.13.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.12.2...0.13.0
[0.12.2]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.12.1...0.12.2
[0.12.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.12.0...0.12.1
[0.12.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.11.0...0.12.0
[0.11.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.10.0...0.11.0
[0.10.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.9.0...0.10.0
[0.9.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.8.1...0.9.0
[0.8.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.8.0...0.8.1
[0.8.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.7.0...0.8.0
[0.7.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.6.0...0.7.0
[0.6.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.5.0...0.6.0
[0.5.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.4.1...0.5.0
[0.4.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.4.0...0.4.1
[0.4.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.3.1...0.4.0
[0.3.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.2...0.3.0
[0.2.2]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.1...0.2.2
[0.2.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.2...0.2.0
[0.1.2]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/dreamfactorysoftware/df-adldap/compare/0.1.0...0.1.1
