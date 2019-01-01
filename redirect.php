<?php
    header("Location: http://localhost:4002/?".http_build_query([
        "code" => $_GET["code"],
        "state" => $_GET["state"]
    ]), true, 303);
    die()
?>