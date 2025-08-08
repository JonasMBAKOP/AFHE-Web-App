<?php
    //constantes de l'application

    define("ROLE_SUPER_ADMIN", "super_admin");
    define("ROLE_ADMIN", "admin");

    define("STATUS_ACTIVE", 1);
    define("STATUS_INACTIVE", 0);

    const HTTP_CODES = [
        "OK" => 200,
        "CREATED" => 201,
        "BAD_REQUEST" => 400,
        "UNAUTHORIZED" => 401,
        "FORBIDDEN" => 403,
        "NOT_FOUND" => 404,
        "SERVER_ERROR" => 500
    ];

?>