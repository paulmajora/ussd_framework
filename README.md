# ussd framework
first commit

Understanding USSD Structure and Programming

USSD is a protocol used by GSM cellular telephones to communicate with the service provider's computers. USSD stands for Unstructured Supplementary Services Data.


The Problem with conventional USSD programming

During my short experience with USSD programming, I realized that it would take a lot of time and logical thinking to build any useful USSD menu with features like menu navigation and session management apart from the business logic.

This can cause a performance issue (delay) since several conditional statements would have to be executed and skipped as necessary upon every interaction. What if the operation to execute is directly mapped to the user’s choice?

This has to be done for each individual USSD application that is built thus introducing some form of repetition of work and effort.

What about if the menu flow is so nested and complicated? That means more time would be spent on writing complicates “switch” and “if” statements apart from the time needed to implement the core functionalities of the application.

What about build a framework to give some uniform structure to all USSD apps. With the most common features provided by the underlying framework?

This abstraction prevents the juggled switch and if statements inherent in most USSD applications.

Also the code becomes readable to anyone who understands the framework


Introducing the SpeedMenu USSD Framework


Main Features
•	Menu naming
•	Build dynamic menus
•	Automatic menu navigation
•	Automatic input/choice validation
•	Menu history
•	Simplified input and input validation model
•	Uniform data structure for menus
•	Session management








Advantages
•	Reduced development time & effort
•	Increased productivity on the part of the developer
•	Avoids complicated logic behind USSD menus and concentrate on business logic
•	Automatic input/choice validation
•	Simplified & decoupled structure of client applications
•	Code can be read and understood by other developers
•	Building dynamic menus is easy
•	Robust & Persistent Session management
•	Frame work can be ported to other languages


Now let’s start with an analysis of a simple USSD application

•	User dials a short code e.g. *123*23#


Now let’s start with the structure USSD application

Handling Menu
•	Show a menu
•	Accept a choice
•	Validate the choice
•	Take next action

Handling Inputs
•	Prompt user for input
•	Accept  the input
•	Validate the input
•	Take next action



Handling Prompts
•	Prompt user with some message
•	Take next action


From the above example one can deduce that all USSD application do at least the following 3 operations apart from the core application logic
1.	Show menu
2.	Accept input
3.	Show prompts

The framework introduces two main modules for handling USSD operations
The session management module: this module take care of starting and resuming sessions, storing/retrieving any session based data e.g. user inputs , navigation history(menu choices etc.) , serializing/de-serializing session data and the like.


The Helper Class (Navigation Manager) takes care of displaying menus, accepting and validating choices, soliciting, accepting and validating user inputs etc.

This module utilizes the session class for instance during navigations and user data entry among other tasks.

For instance if the framework receives a command to await and receive an input from the user, the Helper class automatically stores the input in the session under the msisdn when the input gets submitted by the user.

This makes it simple i.e. you can await, accept, validate, store user input with a single function call!





The Menu Data Structure
Each menu is configurable array – like object which specifies all the attributes of that particular menu in question.








The Choice Override Handler (choiceOverride)
This handler is unconditionally executed if defined and the framework was expecting the user to make a choice on the given menu object (current menu object).
You may set the choice validation settings at the time of rendering the menu object

The Input Complete Handler (inputComplete)
This function is executed only when the framework receives a valid input from the user. You may decide to accept another input, show a menu or complete a transaction in this function.
This function is only required on the menu object which accepts input



The Menu Item Handlers (action)
This object essentially represents the individual choices available and the respective functions to be executed upon a valid choice.
This object is used to enforce available options that can be selected by a user when a menu is displayed
The Menu Configuration Object
The only option on this object necessary is the menu initialization function/handler which must be executed when the menu loads. For instance you may decide to display a menu, accept input or prompt the user in this function depending on your preference.

This Object is reserved for internal framework operations and should not be used normally






A Sample Code in PHP is provided in the index.php file
















More examples 
Make a full sample
