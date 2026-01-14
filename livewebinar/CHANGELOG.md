# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] 2025-11-28
### Added
- Secure API requests

## [2.1.4] 2025-11-27
### Added
- Throttle 401 requests

## [2.1.3] 2025-11-25
### Added
- Secure auth endpoint

## [2.1.2] 2025-11-25
### Added
- Add X-Livewebinar-Plugin-Version header to request

## [2.1.1] 2024-12-03
### Added
- Plugin version prompt for admin

## [2.1.0] 2022-07-28
### Changed
- Fix bugs when calling API


## [2.0.0] 2022-07-28
### Changed
- Authorization changed to JWT. No username & password required. https://app.livewebinar.com/api-apps

## [1.0.0] 2022-07-28
### Added
- Fullscreen modal for embedded room

### Changed
- Code cleanup
- Styles

### Removed
- Logs displaying in panel functionality
- Unused files and directories

## [0.9.9] 2022-07-21
### Changed
- Fixes for image resize and regression errors
- Post adjustments to changes in LiveWebinar Free plan
- Using post title as LiveWebinar event name if none given

## [0.9.8] 2022-07-20
### Added
- Resize capability for LiveWebinar image block
- Select2 on LiveWebinar blocks insert

### Changed
- Fix for select2
- Layout for LiveWebinar post in editor

## [0.9.7] 2022-07-14
### Added
- Image from LiveWebinar storage block

### Changed
- Styling for settings
- View content for blocks moved to separate files

## [0.9.6] 2022-07-12
### Added
- Missing translations
- Some templates, css and js separation

### Changed
- Changelog version numeration

## [0.9.5] 2022-07-08
### Changed
- Access token storage and related logic

## [0.9.4] 2022-07-08
### Added
- Plugin adaptation for translations and Polish translation

## [0.9.3] 2022-07-07
### Added
- JS file for post frontend

### Changed
- JS and CSS filenames to satisfy convention

## [0.9.2] 2022-07-06
### Changed
- Method of reloading post data after save - ajax
- Start date takes timezone set in WP into account

## [0.9.1] 2022-07-04
### Added
- Classroom mode
- Fixes
- Font awesome library

### Changed
- Error handling includes response code

## [0.9.0] - 2022-06-29
### Added
- Checking if exec is available for logs display
- Proper post template
- Header and footer loading for block templates in posts
- Styles and scripts for both editor and frontend side of post
- Select2 usage for blocks
- Embed room option for posts

### Changed
- Password token creation and deletion moved to separate request in preparation for API changes

## [0.8.8] - 2022-06-24
### Added
- Displaying last lines of logs
- Widgets sorting in blocks selects
- Profile picture and username usage for logged in admin for embed room
- Host role for logged in admin for embed room
- Waiting room and presenters control

### Changed
- Selects in post use select2 library
- Agenda field in post uses WYSIWYG editor
- Display of permanent rooms in selects of blocks

## [0.8.7] - 2022-06-22
### Added
- Deactivation of widgets on LiveWebinar post deletion
- Options to not deactivate widgets on LiveWebinar on post deletion
- Global version number used for scripts and styles registration
- Option for appending link to new tab for embed room block

### Changed
- Display of widgets in blocks' selects

## [0.8.6] - 2022-06-21
### Added
- Toggle visibility for settings fields
- Logs

## [0.8.5] - 2022-06-08
### Added
- Validation for post
- Post reloading to show changed data

## [0.8.4] - 2022-06-07
### Changed
- Merged menus
- Event_Post type registration added to activation hook
- Event_Post type template adjustment for block templates
- Join room url in block and Event_Post

### Added
- Form setting option in Event_Post type form
- Layout options in Event_Post type form

## [0.8.3] - 2022-06-06
### Changed
- Default values in forms

### Added
- Info and links on Settings page

### Removed
- Evergreen widget type

## [0.8.2] - 2022-06-03
### Added
- Changelog

### Changed
- Api connector - contains properties with last response, error, error message string and response body string

## [0.8.1] - 2022-06-02
### Added
- Activation hook
- Deactivation hook

## [0.8.0] - 2022-06-01
### Added
- Custom post type
- Uninstall hook

## [0.7.0] - 2022-05-20
### Added
- Blocks: Embed room, Room info

## [0.6.0] - 2022-05-11
### Added
- Class loader
- Initial api connector
- Settings page
