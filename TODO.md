~~add group in url to figure presentation~~

~~add attribut unique to figure~~


~~fix error of detail about figure in welcome page when user click on a figure
(high)~~

~~check messages pagination (high)~~

~~verify empty message -> not send (high)~~

~~add theme in user page update (high)~~

**verify PSR 1 and 2 with PHPSTAN (high)**

**update Symfony and PHP (high)**





**verify flash messages (average)**

**add style to error and notice flash messages (average)**

**standardize the name of form (average)**


verify code with codacy (low)

refactor code in controller (low)

refactor code in JavaScript (low)

translate every comment in english (low)

change group names in fixturesgroup --> create array and use a loop (low)

improve scrollbar to messages (low) --> add margin to see scrollbar

reformat code with Ctrl + Alt + L (low)

~~Shoe on the right the figure's name and the figure's description in the page 
with the detail of a figure (low)~~

~~update responsive in user page account (low)~~

~~add unique constraint on figure's slug~~

FigureForm FigureForm2 edit the medias with the parameters of the form in a
single form (low) (create a new branch and to make a merge)


### Question ?

Do I add slug to user page ? --> It's possible, but it has to add a property 
in user, and change the parameter in the UserController, call a method 
findBy or findById, see the return and if it needs call a method find by 
slug. This enables to research a user by lug or id.

Do I add group in all figure's url ? --> It's not necessary because we need 
not to SEO. 

I don't understand what is the different group of figures in Wikipedia. --> 
I must just use more indicative names.
[Snowboard freestyle Wikipedia](https://fr.wikipedia.org/wiki/Snowboard_freestyle#Les_types_de_tricks)

Do I transform DATE_MUTABLE to DATETIME_MUTABLE in Figure entity ? 
I must search difference between DATE and DATE_MUTABLE.