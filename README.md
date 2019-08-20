# TogglJira
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/badges/quality-score.png?b=master&s=137d085991ab18ad858c2f53453ac59c7583119d)](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/badges/build.png?b=master&s=beb29a34630a5553814cba3c0af960a2920e0b63)](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/build-status/master)

A sync tool to log time logged in Toggl to Jira issues. It works awesomely with the [Toggl Chrome plugin](https://chrome.google.com/webstore/detail/toggl-button-productivity/oejgccbfbmkkpaidnkphaiaecficdnfn).

## Usage

TogglJira expects Toggl entries to start with the JIRA issue ID, for example: 

`SUP-11 Support the Customer Service department`

It will then add a WorkLog entry to issue `SUP-11`.

Copy `config.json.dist` to `config.json`. Fill in your details. Leave `jiraLoginUsername` as an empty string if your login username and username are the same, which might not be the case with JIRA Cloud and Atlassian ID. The `fillIssueID` and `fillIssueComment` keys can also be left blank if not needed. The `notifyUsers` key controls whether JIRA will send an email to people watching the issue when adding or updating time entries.

Then run:

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

## Bonus: Running with Docker

A `docker-compose.yml` file is included in the repository. If you are familiar with Docker Compose, you can use this to run the script without having to configure PHP locally.

The code gets mounted to `/usr/src/toggl-jira` in the Docker container, so you can run it with:

```
docker-compose run --rm php php bin/toggljira.php sync --startDate=yesterday
```

You can also install Composer dependencies like this, which ensures they get installed in a PHP 7.1 environment. You can ignore the `git` warnings; it will still work:

```
# NOTE: At least on Linux, this will cause the vendor files to be owned by root if your user is in the Docker group. You will have to delete them with sudo if you need to later on.

docker-compose run --rm php composer install
```
