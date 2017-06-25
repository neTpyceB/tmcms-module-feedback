# tmcms-module-feedback
Module Feedback (contacts) for The Modern Cms

Module allows to send feedback contact messages easily.

Adds menu item in admin panel to rewiew all received messages.

Example of usage, where `$data` is array with all fields that should be saved in database and sent to 2 emails

```php
ModuleFeedback::addNewFeedback($data, true, [Settings::getCommonEmail(), 'admin@example.com']);
```
