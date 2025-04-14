<?php

Router::fallback(function() {
    header("Content-Type: application/json");
    die(
        json_encode(
            array(
                "status" => "error",
                "message" => "Page Not Found"
            ),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        )
    );
});

Router::get("/api/info/postalCode/{postal_code}", function($postal_code) {
    header("Content-Type: application/json");
    if (ctype_digit($postal_code) && is_numeric($postal_code)) {
        include("config.php");
        $sql = "SELECT * FROM tbl_postal_codes WHERE postal_code = :postal_code";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':postal_code', $postal_code, PDO::PARAM_STR);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            foreach ($resultados as $fila) {
                die(
                    json_encode(
                        array(
                            "status" => "success",
                            "message" => "Results for $postal_code found",
                            "results" => array(
                                "country" => $fila['country_code'],
                                "postal_code" => $fila['postal_code'],
                                "city" => $fila['place_name'],
                                "state" => $fila['admin_name1'],
                                "state_code" => $fila['admin_code1'],
                                "province" => $fila['admin_name2'],
                                "province_code" => $fila['admin_code2'],
                                "localization" => array(
                                    "latitude" => $fila['latitude'],
                                    "longitude" => $fila['longitude'],
                                    "accuracy" => $fila['accuracy']
                                )
                            )
                        ),
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    )
                );
            }
        } else {
            die(
                json_encode(
                    array(
                        "status" => "error",
                        "message" => "No results found",
                        "results" => false
                    ),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );
        }
    } else {
        die(
            json_encode(
                array(
                    "status" => "error",
                    "message" => "Only numeric string is accepted"
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }
});
Router::get("/api/info/localization", function() {
    header("Content-Type: application/json");

    $longitude = $_REQUEST['longitude'] ?? null;
    $latitude = $_REQUEST['latitude'] ?? null;

    if (($latitude && is_numeric($latitude)) || ($longitude && is_numeric($longitude))) {
        include("config.php");

        if ($latitude && $longitude) {
            $sql = "SELECT * FROM tbl_postal_codes WHERE latitude = :latitude AND longitude = :longitude";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
            $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        }
        elseif ($latitude) {
            $sql = "SELECT * FROM tbl_postal_codes WHERE latitude = :latitude";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        }
        elseif ($longitude) {
            $sql = "SELECT * FROM tbl_postal_codes WHERE longitude = :longitude";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            $grouped_by_community = [];

            foreach ($resultados as $fila) {
                $community = $fila['admin_name3'];

                if (!isset($grouped_by_community[$community])) {
                    $grouped_by_community[$community] = [
                        "city" => $community ?? "Unknown",
                        "country" => $fila['country_code'],
                        "localization" => [
                            "latitude" => $fila['latitude'],
                            "longitude" => $fila['longitude'],
                            "accuracy" => $fila['accuracy']
                        ],
                        "postal_codes" => [],
                        "other_info" => [
                            "state" => $fila['admin_name1'],
                            "state_code" => $fila['admin_code1'],
                            "province" => $fila['admin_name2'],
                            "province_code" => $fila['admin_code2']
                        ]
                    ];
                }

                $grouped_by_community[$community]['postal_codes'][] = $fila['postal_code'];
            }

            $final_results = array_values($grouped_by_community);

            die(
                json_encode(
                    array(
                        "status" => "success",
                        "message" => "Results found",
                        "results" => $final_results
                    ),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        } else {
            die(
                json_encode(
                    array(
                        "status" => "error",
                        "message" => "No results found",
                        "results" => false
                    ),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        }
    } else {
        die(
            json_encode(
                array(
                    "status" => "error",
                    "message" => "Both latitude and/or longitude are required and must be numeric"
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
    }
});



Router::get("/api/info/state/{community_code}", function($community_code) {
    header("Content-Type: application/json");

    $community_code = $community_code ?? null;

    if ($community_code) {
        include("config.php");

        $sql = "SELECT * FROM tbl_postal_codes WHERE admin_code1 = :community_code ORDER BY admin_code2, postal_code";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':community_code', $community_code, PDO::PARAM_STR);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            $provinces = [];

            foreach ($resultados as $fila) {
                $province_code = $fila['admin_code2'];
                $province_name = $fila['admin_name2'];
                $city_name = $fila['place_name'];
                
                if (!isset($provinces[$province_code])) {
                    $provinces[$province_code] = [
                        "province_code" => $province_code,
                        "province_name" => $province_name,
                        "cities" => []
                    ];
                }

                $provinces[$province_code]['cities'][] = [
                    "city_name" => $city_name,
                    "postal_codes" => [$fila['postal_code']],
                    "latitude" => $fila['latitude'],
                    "longitude" => $fila['longitude'],
                    "accuracy" => $fila['accuracy']
                ];
            }

            $final_provinces = array_values($provinces);

            die(
                json_encode(
                    array(
                        "status" => "success",
                        "message" => "Cities found for community $community_code",
                        "results" => $final_provinces
                    ),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        } else {
            die(
                json_encode(
                    array(
                        "status" => "error",
                        "message" => "No cities found for community $community_code",
                        "results" => false
                    ),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        }
    } else {
        die(
            json_encode(
                array(
                    "status" => "error",
                    "message" => "Community code is required"
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
    }
});

Router::get("/api/info/province/{province_code}", function($province_code) {
    header("Content-Type: application/json");

    $community_code = $province_code ?? null;

    if ($community_code) {
        include("config.php");

        include("config.php");

        $sql = "SELECT DISTINCT * FROM tbl_postal_codes WHERE admin_code2 = :community_code ORDER BY place_name";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':community_code', $community_code, PDO::PARAM_STR);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cities = array();

        foreach ($resultados as $row) {
            $cityName = $row['place_name'];
            $postalCode = $row['postal_code'];

            if (isset($cities[$cityName])) {
                $cities[$cityName]['postal_codes'][] = $postalCode;
            } else {
                $cities[$cityName] = array(
                    'city' => $cityName,
                    'province' => $row['admin_name2'],
                    'province_code' => $row['admin_code2'],
                    'state' => $row['admin_name1'],
                    'state_code' => $row['admin_code1'],
                    'localization' => array(
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude']
                    ),
                    'postal_codes' => array($postalCode)
                );
            }
        }

        if (count($cities) > 0) {
            die(
                json_encode(
                    array(
                        "status" => "success",
                        "message" => "Cities found for community $community_code",
                        "results" => array_values($cities)
                    ),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        } else {
            die(
                json_encode(
                    array(
                        "status" => "error",
                        "message" => "No cities found for community $community_code",
                        "results" => false
                    ),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        }
    } else {
        die(
            json_encode(
                array(
                    "status" => "error",
                    "message" => "Community code is required"
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
    }
});

Router::get("/api/info/states", function() {
    header("Content-Type: application/json");
    include("config.php");

    $sql = "SELECT DISTINCT admin_code1, admin_name1 FROM tbl_postal_codes ORDER BY admin_name1";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $states = array();
    foreach ($resultados as $row) {
        $states[] = array(
            "state_code" => $row['admin_code1'],
            "state_name" => $row['admin_name1'],
        );
    }
    if (count($resultados) > 0) {
        die(
            json_encode(
                array(
                    "status" => "success",
                    "message" => "Communities found",
                    "results" => $states
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
    } else {
        die(
            json_encode(
                array(
                    "status" => "error",
                    "message" => "No communities found",
                    "results" => false
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
    }
});
