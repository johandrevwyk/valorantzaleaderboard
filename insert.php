<?php
// Database connection settings
$host = "";
$user = "";
$password = "";
$database = ""; // Replace with your actual database name
$port = 3306;

// Create a database connection
$conn = new mysqli($host, $user, $password, $database, $port);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user input from the form
$name = $_POST["name"];

// Remove spaces before the '#' character
$name = str_replace(" #", "#", $name);

// Maximum number of retries
$max_retries = 5; // You can adjust this as needed

// Function to send a GET request and handle retries
function send_get_request_with_retry($url, $max_retries)
{
    $retry_count = 0;
    $response = null;

    while ($retry_count < $max_retries) {
        $response = @file_get_contents($url);

        if ($response !== false) {
            break; // Successful response, exit the retry loop
        }

        // Wait for a few seconds before retrying
        sleep(5);
        $retry_count++;
    }

    return $response;
}

// Function to check VPN usage
function check_vpn_usage($ip)
{
    $ipinfo_access_token = "57d0c2a77cfae6"; // Replace with your actual ipinfo access token
    $ipinfo_api_url = "http://ipinfo.io/$ip?token=$ipinfo_access_token";
    $ipinfo_response = file_get_contents($ipinfo_api_url);
    $ipinfo_data = json_decode($ipinfo_response);

    if (isset($ipinfo_data->vpn) && $ipinfo_data->vpn === true) {
        return true; // VPN usage detected
    }

    return false; // No VPN usage detected
}

// Validate the input
if (strpos($name, "#") !== false) {
    // Split the name and tag
    list($name_part, $tag_part) = explode("#", $name, 2);

    // Check if the user's name contains non-ASCII characters
    if (preg_match('/[^\x20-\x7F]/', $name_part)) {
        echo "Error: Usernames with non-ASCII characters are not allowed.";
    } else {
        // Check for VPN usage
        if (check_vpn_usage($_SERVER['REMOTE_ADDR'])) {
            echo "Error: VPN usage is not allowed.";
        } else {
            // Continue with country restrictions check

            // Check if the user's country is in the list of allowed countries
            $allowed_countries = ["ZA", "NA", "RE", "MZ", "MU"]; // ISO country codes
            $ipinfo_access_token = ""; // Replace with your actual ipinfo access token
            $ipinfo_api_url = "http://ipinfo.io/{$_SERVER['REMOTE_ADDR']}?token=$ipinfo_access_token";
            $ipinfo_response = file_get_contents($ipinfo_api_url);
            $ipinfo_data = json_decode($ipinfo_response);

            if (isset($ipinfo_data->country) && in_array($ipinfo_data->country, $allowed_countries)) {
                // User is from an allowed country, proceed with the rest of the code

                // Check if the user already exists in the database
                $check_sql = "SELECT * FROM users WHERE name = '$name'";

                $result = $conn->query($check_sql);

                if ($result && $result->num_rows > 0) {
                    // User already exists
                    echo "Error: This account is already in the database.";
                } else {
                    // Send a POST request to the external API
                    $api_url = "https://api.kyroskoh.xyz/valorant/v1/mmr/eu/{$name_part}/{$tag_part}?show=combo&display=0";
                    $api_response = send_get_request_with_retry($api_url, $max_retries);

                    if ($api_response === false) {
                        echo "Error: This account could not be added. Failed to fetch data from API even after $max_retries retries. Check your entries";
                    } else {
                        // Rest of your code...

                        // Parse the rank and rr from the API response
                        preg_match('/(.*\d+)\s*-\s*(\d+)RR/', $api_response, $matches);

                        if (count($matches) >= 3) {
                            $rank = trim($matches[1]);
                            $rr = intval($matches[2]);
                        } elseif (strpos($api_response, "null") !== false) {
                            // If the API response contains "null," set rank to "Unranked" and rr to 0
                            $rank = "Unranked";
                            $rr = 1;
                        } else {
                            print($name_part);
                            print($tag_part);
                            print($api_response);
                            echo "Error: This account could not be added, the tag is invalid or no response from API. Please try again";
                            exit; // Exit the script if there's an error
                        }

                        // SQL query to insert data into the database
                        $sql = "INSERT INTO users (name, rank, rr) VALUES ('$name', '$rank', $rr)";

                        if ($conn->query($sql) === TRUE) {
                            echo "User added successfully";

                            // Get the user's IP address
                            $user_ip = $_SERVER['REMOTE_ADDR'];

                            // Send a message to Discord webhook
                            $discord_webhook_url = "";
                            $discord_message = "New user added: **$name** from IP address: $user_ip"; // Include the IP address in the message

                            $data = [
                                "content" => $discord_message
                            ];

                            $ch = curl_init($discord_webhook_url);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($ch);
                            curl_close($ch);

                            header('Location: https://heartbreakhotel.info/leaderboard');
                        } else {
                            echo "Error: " . $sql . "<br>" . $conn->error;
                        }
                    }
                }
            } else {
                echo "Error: Please contact the owner";
            }
        }
    }
} else {
    echo "Error: Input requires Riot Tag";
}

// Close the database connection
$conn->close();
?>
