# TogglJira
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/badges/quality-score.png?b=master&s=137d085991ab18ad858c2f53453ac59c7583119d)](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/badges/build.png?b=master&s=beb29a34630a5553814cba3c0af960a2920e0b63)](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/build-status/master)

A sync tool to log time logged in Toggl to Jira issues. It works awesomely with the [Toggl Chrome plugin](https://chrome.google.com/webstore/detail/toggl-button-productivity/oejgccbfbmkkpaidnkphaiaecficdnfn).

## Usage

TogglJira expects Toggl entries to start with the JIRA issue ID, for example: 

`SUP-11 Support the Customer Service department`

It will then add a WorkLog entry to issue `SUP-11`.

Copy `config.json.dist` to `config.json`. fill it with your details, then run:

```php
php bin/toggljira.php help
```

or to start syncing:
```php
php bin/toggljira.php sync [--startDate=] [--endDate=] [--overwrite=] 
```

* By default the startDate is the last sync date from `config.json` or today.
* By default the endDate is now.
* By default worklogs will be combined (merged) and not overwritten.

### Custom start and end date

This will sync all worklogs starting from today.
```php
php bin/toggljira.php sync --startDate=today
```

This will sync all worklogs starting from 1 january 2018.
```php
php bin/toggljira.php sync --startDate=2018-01-01
```

This will sync all worklogs starting from 1 january 2018 to 1 february 2018.
```php
php bin/toggljira.php sync --startDate=2018-01-01 --endDate=2018-02-01
```

### Overwriting existing worklogs instead of combining (merging)

This will sync all worklogs starting from 1 january 2018 to 1 february 2018 and it will overwrite existing worklogs.
```php 
php bin/toggljira.php sync --startDate=2018-01-01 --endDate=2018-02-01 --overwrite=true
```

That's it, enjoy!
