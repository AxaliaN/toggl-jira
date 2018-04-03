# TogglJira
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/badges/quality-score.png?b=master&s=137d085991ab18ad858c2f53453ac59c7583119d)](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/badges/build.png?b=master&s=beb29a34630a5553814cba3c0af960a2920e0b63)](https://scrutinizer-ci.com/g/AxaliaN/toggl-jira/build-status/master)

A sync tool to log time logged in Toggl to Jira issues. It works awesomely with the [Toggl Chrome plugin](https://chrome.google.com/webstore/detail/toggl-button-productivity/oejgccbfbmkkpaidnkphaiaecficdnfn).

## Usage

TogglJira expects Toggl entries to start with the JIRA issue ID, for example: 

`SUP-11 Support the Customer Service department`

It will then add a WorkLog entry to issue `SUP-11`.

Copy `config.json.dist` to `config.json`. fill it with your details, then run:

`php bin/toggljira.php`

That's it!
