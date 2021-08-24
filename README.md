# Sweetwater Developer Test

This code repository is a website using PHP with a MySQL back-end database, built as a developer trial by Nate Shannon for Sweetwater.

## System Requirements

This site should run on most new-ish WAMP/LAMP stacks.

This specific code was developed using:
- Windows 10 Home
- Apache 2.4.46
- MySQL 5.7.31
- PHP 7.3.21

## Project Requirements

Sweetwater Development Management said:

> When placing orders on a website, we provide a field for customers to add a quick comment to tell us something we should know about them or their order. We're supplying you a MySQL table with these various comments and want to see your approach the following tasks.

### Task 1 - Write a report that will display the comments from the table

Display the comments and group them into the following sections based on what the comment was about:
- Comments about candy
- Comments about call me / don't call me
- Comments about who referred me
- Comments about signature requirements upon delivery
- Miscellaneous comments (everything else)


### Task 2 - Populate the shipdate_expected field in this table with the date found in the `comments` field (where applicable)

The shipdate_expected field is currently populated with no date (0000-00-00). Some of comments included an "Expected Ship Date" in the text. Please parse out the date from the text and properly update the shipdate_expected field in the table.


### Other Requirements & Considerations

1. You can use any VCS platform you like — such as Gitlab or Github — as long as your project is publicly accessible.
2. Build your application so we can test it in-browser.
3. Write your application using PHP
4. We're interested in functionality, not design. It doesn't have to look pretty but your code should :-)
5. Don't use any other JavaScript libraries, such as jQuery.
6. Once you're done, send us the link to your project so we can look it over.
7. __Commit often.__ We want to see your progress throughout the project.
8. __Work quickly.__ This project was designed to be completed quickly, so don't spend too much time on it.
9. __Write your own code.__ While we understand that there are pakages out there that take care of common problems, we ultimately want to see what _YOU_ can build, not what someone else has built.
10. __Do your best work.__ We're using this project as a viewport into who you are as a developer. Show us what you can do!


## Project Design Concepts

At first glance, writing a report the fulfilled the basic requirements as defined above seemed pretty straight-forward. But then I started to think about how such a project could be used in a real-world situation, and found that I was expanding the project requirements a bit in order to add features that I would want in such a system. Take the requirement of identifying comments about candy, for instance; I found myself needing a way to define what candy is, handle misspelling of candy types, and efficiently search for comments about candy.

I also added some features that were not specified in the requirements, but were interesting, useful, or fun. Things like identifying people mentioned in the comments, determining their role in relation to the comment, highlighting candy with appropriate colors, pre-processing comments to increase search accuracy and efficiency, allowing system users to track call-backs made on comments which requested a phone call, implementing simple animations to show when async javascript operations have completed, and building a comment search page that allows easy filtering of all comments in the system.

There are a number of features that I would like to implement, but forced myself to skip for now in the interest of releasing the project more quickly, since the project does meet all of the requirements in its current state. These forgone, but potential future enhancements, include such things as ajax-enabled search result pagination.
