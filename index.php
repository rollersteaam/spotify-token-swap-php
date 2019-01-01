<?php
    define("CLIENT_ID", "INSERT YOUR CLIENT ID HERE");
    define("SECRET", "INSERT YOUR CLIENT SECRET HERE");
    define("REDIRECT_URI", "INSERT YOUR REDIRECT URL HERE");

    function clean_request_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // # Request Variables
    // ## Get Access and Refresh Tokens
    $grant_type = clean_request_input($_POST["grant_type"]);
    $auth_code = clean_request_input($_POST["code"]);
    // ### Get Refreshed Access Token
    $refresh_token = clean_request_input($_POST["refresh_token"]);
    // ## Get Authorization Code
    $scope = clean_request_input($_GET["scope"]);
    $state = clean_request_input($_GET["state"]);
    $show_dialog = clean_request_input($_GET["show_dialog"]);

    // Gets the token response JSON, which contains the access and refresh tokens.
    //
    // Documentation: https://developer.spotify.com/documentation/general/guides/authorization-guide/#2-have-your-application-request-refresh-and-access-tokens-spotify-returns-access-and-refresh-tokens
    // Example response:
    // {
    //    "access_token": "NgCXRK...MzYjw",
    //    "token_type": "Bearer",
    //    "scope": "user-read-private user-read-email",
    //    "expires_in": 3600,
    //    "refresh_token": "NgAagA...Um_SHo"
    // }
    function get_token($auth_code)
    {
        $request = curl_init("https://accounts.spotify.com/api/token");
        curl_setopt($request, CURLOPT_HTTPHEADER,
        [
            "Authorization: Basic ".base64_encode(CLIENT_ID.":".SECRET)
        ]);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        // It should be noted here that 'http_build_query' COULD be replaced with an array, but it will change the content type
        // of the request to another one that it does not support.
        curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query([
            "grant_type" => "authorization_code",
            "code" => $auth_code,
            "redirect_uri" => REDIRECT_URI
        ]));
        $result = curl_exec($request);
        // Spotify usually returns a neutral boilerplate message however, so this is very unlikely...
        if (!$result)
        {
            trigger_error(curl_error($request));
        }
        curl_close($request);
        return $result;
    }

    // Gets the token response JSON, which contains a refreshed access token, but no refresh token (as it would be unnecessary).
    //
    // Documentation: https://developer.spotify.com/documentation/general/guides/authorization-guide/#4-requesting-a-refreshed-access-token-spotify-returns-a-new-access-token-to-your-app
    // Example response:
    // {
    //     "access_token": "NgA6ZcYI...ixn8bUQ",
    //     "token_type": "Bearer",
    //     "scope": "user-read-private user-read-email",
    //     "expires_in": 3600
    // }
    function refresh_token($refresh_token)
    {
        $request = curl_init("https://accounts.spotify.com/api/token");
        curl_setopt($request, CURLOPT_HTTPHEADER,
        [
            "Authorization: Basic ".base64_encode(CLIENT_ID.":".SECRET)
        ]);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        // It should be noted here that 'http_build_query' COULD be replaced with an array, but it will change the content type
        // of the request to another one that it does not support.
        curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query([
            "grant_type" => "refresh_token",
            "refresh_token" => $refresh_token
        ]));
        $result = curl_exec($request);
        // Spotify usually returns a neutral boilerplate message however, so this is very unlikely...
        if (!$result)
        {
            trigger_error(curl_error($request));
        }
        curl_close($request);
        return $result;
    }

    // Redirects the browser to perform a GET request to Spotify, obtaining the auth code.
    function goto_auth_code($scope, $state, $show_dialog) {
        header("Location: "."https://accounts.spotify.com/authorize/?".http_build_query(
        [
            "client_id" => CLIENT_ID,
            "response_type" => "code",
            "redirect_uri" => REDIRECT_URI,
            "scope" => $scope,
            "state" => $state,
            "show_dialog" => $show_dialog
        ]), true, 303);
        die();
    }

    // By checking the grant type, we can choose to print out the context relevant json.
    if ($grant_type === "authorization_code")
    {
        // This line prints out the JSON response to the page, allowing HttpClient to take the whole page as a response
        // and begin parsing its JSON, to then go on to deserialize and so and so fourth...
        echo get_token($auth_code);
    }
    else if ($grant_type === "refresh_token")
    {
        echo refresh_token($refresh_token);
    }
    else
    {
        goto_auth_code($scope, $state, $show_dialog);
    }
?>