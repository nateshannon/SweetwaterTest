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

At first glance, writing a report that fulfilled the basic requirements as defined above seemed straight-forward and sufficient. But then I started to think about how such a project could be used in a real-world situation, and found I was expanding the project requirements a bit in order to add features I would want in such a system. Take the requirement of identifying comments about candy, for instance; I found myself needing a way to define what candy is, handle misspelling of candy types, and efficiently search for comments about candy.

I also added some features that were not specified in the requirements, but were interesting, useful, or fun. Things like identifying people mentioned in the comments, determining their role in relation to the comment, highlighting candy with appropriate colors, pre-processing comments to increase search accuracy and efficiency, allowing system users to track call-backs made on comments which requested a phone call, implementing simple animations to show when async javascript operations have completed, and building a comment search page that allows easy filtering of all comments in the system.

There are a number of features that I would like to implement, but forced myself to skip for now in the interest of releasing the project now, since the project meets all of the requirements in its current state and I need to stop adding/tweaking features and just get the project back to Sweetwater for evaluation. These forgone, but potential future enhancements, include such things as ajax-enabled search result pagination, increased name detection system accuracy (perhaps using machine learning), user preferences (results per page, default search filters, etc), a method to enter or import new comments, and a way to export search results as a printer-friendly PDF.


## How To Use This Project

While much of this project should be self-explanatory, below is a list of feature and how you use them.

Feature | Purpose | Location | Usage
------- | ------- | -------- | -----
Metadata | Pre-processes comments. Parses text into boolean flags and dates. Builds relationships between comments and detected keywords, increasing search efficiency. Allows searches to find misspelled keywords. ___Note:__ Metadata MUST be generated before comments will appear in search results._ | Metadata navigation tab at the top of the page. | If there are comments that have not been processed, clicking `Process Pending Comments` will initiate the metadata generation process. `Clear Metadata` will erase the current metadata and reset parse values to their defaults. After making changes to the lists of candy or people, you need to clear the metadata, and re-generate it using `Process Pending Comments`.
Candy List | Display list of candy types defined in the system. | Candy navigation tab at the top of the page. | This is a list of known candies that will be detected when generating metadata.
Delete Candy/Person | Remove candy or person from the system. | Red button on the right side of Candy or Person List. | Clicking the button will prompt for confirmation, and if given, will remove the candy or person from the system.
Candy Color | Sets the primary and secondary color of a candy type. | The color-picker boxes on the Candy List | Clicking a color-picker box will open a color selection dialog. After choosing a color, clicking outside the dialog will cause the color to be saved. The color box will brightly flash yellow, then fade out, showing the color has been saved.
Find Comments | Shortcut to search results related to the candy or person on which the button was clicked. | Black button on the right side of the Candy or People list. | Clicking the button opens the search results, pre-filtered to only show results for the clicked candy or person.
New Candy/New Person | Open the dialog to add a new candy or person to the system. | Blue button at the top, right side or the Candy or People list. | Clicking the button will open a form allowing you to specify the name of a new candy or person, click Create to save the new entry.
Search | Find comments. | Search navigation tab at the top of the page. | You specify the criteria by which to search/filter the comments and click the `Filter Comments` button.
Search Results | Display and navigate through comments returned by the search. | Beneath the search criteria, after clicking `Filter Comments`. | Comments are displayed, eight at a time, and the full list of returned results can be navigated using the pagination below the results.
Search Result | Display a single comment result. | One item among the list of all search results. | Blue header contains the order ID#. Next, at the top of the gray box, the comment body is shown. At the bottom of the gray box, the switches specifying Call Wanted, Require Signature, and Call Completed are shown. Call Completed only displays if Call Wanted is set to yes. If detected, the expected ship date is shown in a black footer, and removed from the comment body. Finally, candies and people detected in the comment are highlighted. Sweetwater Sales staff are highlighted with a striped, blue background, black border, a red underline, and a person icon. Referrers are shown in yellow with an award ribbon icon. Candies are displayed using the primary and secondary color specified on the Candy List.
Call Completed | Specify that the call requested by the customer has been made. | On a search result, which is flagged as Call Wanted. | Clicking Call Completed will toggle the status, and save the change to the database. A "Saved" message will flash and then fade-out after a successful save.



## Notes

* Under Other Requirements & Considerations, regarding #5. Bootstrap was used for layout purposes, after recieving approval.
* Under Other Requirements & Considerations, regarding #8. This project was completed fairly quickly in terms of time spent writing code, approximately 20 hours. But took a few weeks as the calendar goes, due to limitations on my time due to my job and lengthy commute.
* Under Other Requirements & Considerations, regarding #10. I've been continually coming up with ways to refactor and improve this. So, I had to force myself to stop working on it, and release it as-is.
