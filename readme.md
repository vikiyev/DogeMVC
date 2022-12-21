# DogeMVC

Using query parameters, we can control which controllers and methods are loaded.

## MVC Pattern

Model

- Data related logic
- Interacts with the database
- Communicates with Controller
- Can sometimes update the view

View

- What the user sees in the browser
- Communicates with the controller
- Can be passed dynamic values from controller

Controller

- Receives input from the url, form, view etc.
- Processes requests
- Gets data from the model
- Passes data to the view
