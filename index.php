<?php

//Creates or resumes a session via cookies
if(!isset($_SESSION)) 
{ 
    session_start(); 
} 
// Include the database connection file (MySQLi version)
// require_once 'database_connect.php';
$sql_var = require __DIR__ . "/database_connect.php";



// Function to check if the user is logged in
function isUserLoggedIn()
{
    return isset($_SESSION["user_id"]);
}

// Handle the request to save a recipe
if (isset($_GET['action']) && $_GET['action'] === 'save_recipe') {
    // Check if the user is logged in
    if (isset($_SESSION["user_id"])) {
        $userId = $_SESSION["user_id"];
        $recipeId = $_GET['recipe_id'];

        // Insert the saved recipe into the database
        $sql = "INSERT INTO saved_recipes (user_id, recipe_id) VALUES ('$userId', '$recipeId')";
        if ($sql_object->query($sql) === TRUE) {
            echo "Recipe saved successfully!";
        } else {
            echo "Error saving recipe: " . $sql_object->error;
        }
        exit; // Exit to prevent displaying the entire HTML page again
    } else {
        echo "Please log in to save this recipe.";
        exit; // Exit to prevent displaying the entire HTML page again
    }
}

// Handle the request to remove a recipe from favorites
if (isset($_GET['action']) && $_GET['action'] === 'remove_recipe') {
    // Check if the user is logged in
    if (isset($_SESSION["user_id"])) {
        $userId = $_SESSION["user_id"];
        $recipeId = $_GET['recipe_id'];

        // Remove the recipe from the database
        $sql = "DELETE FROM saved_recipes WHERE user_id = '$userId' AND recipe_id = '$recipeId'";
        if ($sql_object->query($sql) === TRUE) {
            echo "Recipe removed successfully!";
        } else {
            echo "Error removing recipe: " . $sql_object->error;
        }
        exit; // Exit to prevent displaying the entire HTML page again
    } else {
        echo "Please log in to remove this recipe.";
        exit; // Exit to prevent displaying the entire HTML page again
    }
}

// Handle the request to add a rating to the database
if (isset($_GET['action']) && $_GET['action'] === 'save_rating') {
    echo "hello world";
    // Check if the user is logged in
    if (isset($_SESSION["user_id"])) {
        $userId = $_SESSION["user_id"];
        $recipe_Id = $_GET['recipe_id'];
        $rate = $_GET['rating'];

        //query to insert rating to MySQL database
        $query = "INSERT into ratings (recipe_id, rating, user) VALUES ('$recipe_Id', '$rate', '$userId')";

        if ($sql_object->query($query) === TRUE) {
            echo "Rating added successfully!";
        } else {
            echo "Error adding rating: " . $sql_object->error;
        }
        exit; // Exit to prevent displaying the entire HTML page again
    } else {
        echo "Please log in to add this rating.";
        exit; // Exit to prevent displaying the entire HTML page again
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
</head>

<body>

    <h1>Home</h1>
    <!-- search box for texting finding the recipes -->
    <section>
        <form action="search.php" method="GET">
            <input type="text" name="search" placeholder="Search for recipes">
            <button type="submit">Search</button>
        </form>
    </section>

    <!--Checks if session data is stored-->
    <?php if (isset($_SESSION["user_id"])) : ?>

        <p>You are now logged in</p>
        <!--Allows the user to log out-->
        <p><a href="logout.php">Log out</a></p>
        <!--Gives the user links to log in or sign up-->
    <?php else : ?>
        <p>Please <a href="login_page.php">Log In</a> or <a href="signup_form.html">Sign Up</a></p>
    <?php endif; ?>

    <div>
        <?php
        // Include the database connection file (MySQLi version)
        require_once 'database_connect.php';

        // Retrieve recipes from the database
        $sql = "SELECT * FROM recipes"; // Modify this query according to your database structure
        $result = $sql_object->query($sql);
        // Check if there are any recipes in the result
        if ($result->num_rows > 0) {
            // Fetch and display the recipes
            while ($recipe = $result->fetch_assoc()) {
                $rec = $recipe['recipe_id'];

                //fetch any ratings the recipes may have
                $rate_sql = "SELECT * FROM ratings WHERE recipe_id = '$rec'";
                $rate = $sql_object->query($rate_sql);
                $rate_result = $rate->fetch_assoc();
                
                if ($rate->num_rows > 0) {
                    if ($rate->num_rows > 1) {
                        //get the average of the ratings
                        $av_sql = "SELECT ROUND(AVG(rating), 1) AS rate_av FROM ratings";
                        $av_sql_result = $sql_object->query($av_sql);
                        $av = $av_sql_result->fetch_assoc();
                        $av_rating = $av['rate_av'] . '/5';
                    } else {
                        $av_rating = $rate_result['rating'] . '/5';
                    }
                } else {
                    $av_rating = "No ratings yet";
                }

                // Display the recipe information as needed recipe details (description, ingredients, etc.)
                echo "<form method=POST>";
                echo "<div class='recipe-card'>";
                echo "<h3>" . $recipe['Name'] . "</h3>";
                echo "<h4>" . $av_rating . "</h4>";
                echo "<p>" . $recipe['Description'] . "</p>";
                echo "<p>" . $recipe['Prep_time'] . "</p>";
                echo "<p>" . $recipe['Cook_time'] . "</p>";

                // Check if the user is logged in and show the "Save" or "Remove" button accordingly
                if (isUserLoggedIn()) {
                    // Check if the recipe is saved or not and display the appropriate button
                    $isSaved = isRecipeSaved($_SESSION["user_id"], $recipe['recipe_id']);
                    if ($isSaved) {
                        echo "<button class='remove-button' data-recipe-id='" . $recipe['recipe_id'] . "'>Remove</button>";
                    } else {
                        echo "<button class='save-button' data-recipe-id='" . $recipe['recipe_id'] . "'>Save</button>";
                    }

                    //Radio buttons for rating system
                    echo "<div class='rating'>";
                    echo "<div class='star-icon'>";
                    echo "<input type='radio' name='rating' id='rating' value='1'>";
                    echo "<label for=rating class='star'>1</label>";
                    echo "<input type='radio' name='rating' id='rating' value='2'>";
                    echo "<label for=rating class='star'>2</label>";
                    echo "<input type='radio' name='rating' id='rating' value='3'>";
                    echo "<label for=rating class='star'>3</label>";
                    echo "<input type='radio' name='rating' id='rating' value='4'>";
                    echo "<label for=rating class='star'>4</label>";
                    echo "<input type='radio' name='rating' id='rating' value='5'>";
                    echo "<label for=rating class='star'>5</label>";

                    //check if the recipe has been rated by the user
                    $isRated = isRatingSaved($_SESSION["user_id"], $recipe['recipe_id']);

                    //if rated a message will appear. Otherwise a button will appear to rate
                    if ($isRated) {
                        echo "<p>You have submitted a rating for this recipe</p>";
                    } else {
                        echo "<button class='save-rating' id=recipe value='" . $recipe['recipe_id'] . "' data-rating-id='" . $recipe['recipe_id'] . "'>Rate</button>";
                    }

                    echo "</div>";

                } else {
                    echo "<p>Please <a href='login_page.php'>log in</a> to save this recipe.</p>";
                }

                echo "</div>";
            }
            

        } else {
            echo "No recipes found.";
        }
        // Close the database connection
        $sql_object->close();
        ?>
    </div>

    <!-- Assign the login status to a JavaScript variable -->
    <script>
        var isLoggedIn = <?php echo isset($_SESSION["user_id"]) ? "true" : "false"; ?>;
    </script>

    <!-- Include the external script.js file -->
    <script src="script.js"></script>

</body>
</html>
