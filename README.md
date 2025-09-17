
# Postal Code and Localization API

This project provides an API for retrieving postal code and geographic information based on different queries like postal codes, geographic coordinates, provinces, and states. The API is designed to handle requests in a flexible way, providing detailed information for each query.

## Table of Contents
- [Overview](#overview)
- [API Endpoints](#api-endpoints)
  - Get Postal Code Info
  - Get Localization Info
  - Get State Info
  - Get Province Info
  - Get States Info
- [Error Handling](#error-handling)
- [Technologies Used](#technologies-used)
- [Setup](#setup)

## Overview
The API allows users to query various geographic data about postal codes, cities, provinces, and states in Spain. It provides endpoints for:
- Searching by postal code.
- Searching by geographic coordinates (latitude and longitude).
- Retrieving cities by province or community code.
- Listing all states (autonomous communities) in the country.

Each request returns detailed data, including:
- Postal code.
- City name.
- Province.
- State and country.
- Geolocation (latitude, longitude).
  
### Features
- **Flexible Search**: The API allows searches by postal code, latitude/longitude, province, or state (community).
- **Comprehensive Data**: Every response includes all related geographic information such as province, state, and coordinates.
- **Grouping by Province or Community**: Results can be grouped based on their provinces or communities for easy categorization.

## API Endpoints

### 1. **Get Postal Code Info**
This endpoint retrieves detailed information for a given postal code.

**Endpoint**: `/api/info/postalCode/{postal_code}`  
**Method**: `GET`

**Parameters**:
- `postal_code`: The postal code to search for (numeric string).

**Example Request**:
```http
GET /api/info/postalCode/48001
```

**Example Response**:
```json
{
  "status": "success",
  "message": "Results for 48001 found",
  "results": {
    "country": "ES",
    "postal_code": "48001",
    "city": "Bilbao",
    "state": "País Vasco",
    "state_code": "PV",
    "province": "Vizcaya",
    "province_code": "BI",
    "localization": {
      "latitude": "43.263300",
      "longitude": "-2.928600",
      "accuracy": 4
    }
  }
}
```

### 2. **Get Localization Info**
This endpoint retrieves cities based on the provided latitude and/or longitude. It can handle searches by either or both parameters.

**Endpoint**: `/api/info/localization`  
**Method**: `GET`

**Parameters**:
- `latitude`: Latitude to search by.
- `longitude`: Longitude to search by.

**Example Request**:
```http
GET /api/info/localization?latitude=43.3183&longitude=-1.9812
```

**Example Response**:
```json
{
  "status": "success",
  "message": "Results found",
  "results": [
    {
      "city": "Donostia San Sebastián",
      "country": "ES",
      "localization": {
        "latitude": "43.3183",
        "longitude": "-1.9812",
        "accuracy": "4"
      },
      "postal_codes": ["20001", "20002", "20003"],
      "other_info": {
        "state": "País Vasco",
        "state_code": "PV",
        "province": "Gipuzkoa",
        "province_code": "SS"
      }
    }
  ]
}
```

### 3. **Get State Info**
This endpoint retrieves all cities and postal codes for a given state/community code.

**Endpoint**: `/api/info/state/{community_code}`  
**Method**: `GET`

**Parameters**:
- `community_code`: The state/community code (e.g., "PV" for País Vasco).

**Example Request**:
```http
GET /api/info/state/PV
```

**Example Response**:
```json
{
  "status": "success",
  "message": "Cities found for community PV",
  "results": [
    {
      "province_code": "SS",
      "province_name": "Gipuzkoa",
      "cities": [
        {
          "city_name": "Donostia San Sebastián",
          "postal_codes": ["20001", "20002", "20003"],
          "latitude": "43.3183",
          "longitude": "-1.9812",
          "accuracy": "4"
        }
      ]
    }
  ]
}
```

### 4. **Get Province Info**
This endpoint retrieves all cities and postal codes for a given province code.

**Endpoint**: `/api/info/province/{province_code}`  
**Method**: `GET`

**Parameters**:
- `province_code`: The province code (e.g., "SS" for Gipuzkoa).

**Example Request**:
```http
GET /api/info/province/SS
```

**Example Response**:
```json
{
  "status": "success",
  "message": "Cities found for province SS",
  "results": [
    {
      "city": "Donostia San Sebastián",
      "province": "Gipuzkoa",
      "province_code": "SS",
      "state": "País Vasco",
      "state_code": "PV",
      "localization": {
        "latitude": "43.3183",
        "longitude": "-1.9812"
      },
      "postal_codes": ["20001", "20002"]
    }
  ]
}
```

### 5. **Get States Info**
This endpoint retrieves a list of all states (autonomous communities) in Spain.

**Endpoint**: `/api/info/states`  
**Method**: `GET`

**Example Request**:
```http
GET /api/info/states
```

**Example Response**:
```json
{
  "status": "success",
  "message": "Communities found",
  "results": [
    {
      "state_code": "PV",
      "state_name": "País Vasco"
    },
    {
      "state_code": "CT",
      "state_name": "Cataluna"
    }
  ]
}
```

## Error Handling
The API provides clear and structured error messages in the following cases:
- **Invalid Postal Code**: If the provided postal code is not numeric.
- **No Results Found**: If no data matches the provided query.
- **Missing Parameters**: If required query parameters (latitude, longitude, etc.) are missing.
  
Example error response:
```json
{
  "status": "error",
  "message": "No results found",
  "results": false
}
```

## Technologies Used
- **PHP**: For the backend API logic.
- **SQLite** or **MySQL**: For storing and querying the postal codes and geographic data.
- **PDO**: For database interaction.
- **Router Class**: To handle HTTP routes and requests.

## Setup
1. [Clone the repository](https://github.com/enekolizarraga/Spain-Postal-Code-API).
2. Configure your database connection in the `config.php` file.
3. Ensure that your database contains the `tbl_postal_codes` table with the necessary fields (`postal_code`, `place_name`, `latitude`, `longitude`, etc.).
4. Run the project in your PHP server or local environment.

PD: This repo is made to work in Apache WebServers, but if you're using nginx, you should think about converting the .htaccess file content to nginx config friendly.

---

### Made with love in Donostia San Sebastián ❤️
