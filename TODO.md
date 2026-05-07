~~add group in url to figure presentation~~

~~add attribut unique to figure~~


~~fix error of detail about figure in welcome page when user click on a figure
(high)~~

~~check messages pagination (high)~~

~~verify empty message -> not send (high)~~

~~add theme in user page update (high)~~

week 17th April

~~fix the modals to edit and delete a picture or a video (high)~~

~~verify PSR 1 and 2 with PHPSTAN (high)~~

~~update Symfony and PHP (high)~~

~~Update the procedure to install the project (high)~~
    1. install Docker
    2. Check with `docker ps` if MySQL run
    3. Kill if necessary MySQL with `docker kill mySqlId`

**Check the application (high)**

~~delete default picture in modal~~

~~Fix the button to see medias in responsive "mode" to disconnected user 
(high)~~

**On welcome page update pagination to see 10 figures (high)**

**Hide buttons to edit or delete a figure in welcome page for disconnected 
user**

**Update README with adding the .env's configuration**






**verify flash messages (average)**

~~add style to error and notice flash messages (average)~~

~~standardize the name of form (average)~~


verify code with codacy (low)

refactor code in controller (low)

refactor code in JavaScript (low)

translate every comment in english (low)

change group names in GroupFixtures --> create array and use a loop (low)

change figure names in FigureFixtures --> create array and use a loop (low)

~~improve scrollbar to messages (low) --> add margin to see scrollbar~~

reformat code with Ctrl + Alt + L (low)

~~Shoe on the right the figure's name and the figure's description in the page 
with the detail of a figure (low)~~

~~update responsive in user page account (low)~~

~~add unique constraint on figure's slug~~

FigureForm FigureForm2 edit the medias with the parameters of the form in a
single form, and use inheritance (low) (create a new branch and to make a merge)

Add FOSJsRoutingBundle library to use Symfony's routes in JavaScript (low)

Increase size to textarea to edit figure's description (low)

Create "div" to enable the click in list of figures (low)


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
DATE and DATE_MUTABLE are the same, when I call a method like modify() or add
(), it changes the original state of the object's instance.
DATE_IMMUTABLE is immutable in other words the object's instance doesn't 
change, and when a method like modify() or add() a new instance is created.

week 17th April

How do you transform this `$pictureFigureRepository->findby(['id' => 
$data->id])` in entity ?

Have you a better attribute to store the modal to open ? He doesn't know.

Default picture to figure ? --> no, delete default picture in modal