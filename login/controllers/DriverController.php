<?php

require_once __DIR__ . '/../models/Driver.php';

class DriverController
{

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            http_response_code(405);

            echo json_encode([
                "error" => "Method not allowed"
            ]);

            return;
        }

        // Basic Validation

        $requiredFields = [

            'firstName',
            'lastName',
            'phone',

            'nic',
            'dob',
            'gender',

            'street',
            'town',
            'district',
            'province',

            'vehicleType',
            'vehicleBrand',
            'vehicleModel',

            'licenseNumber',

            'password',
            'confirmPassword'
        ];

        foreach ($requiredFields as $field) {

            if (
                !isset($_POST[$field]) ||
                trim($_POST[$field]) === ''
            ) {

                http_response_code(400);

                echo json_encode([
                    "error" => "$field is required"
                ]);

                return;
            }
        }

        // Password Match Check

        if (
            $_POST['password'] !==
            $_POST['confirmPassword']
        ) {

            http_response_code(400);

            echo json_encode([
                "error" => "Passwords do not match"
            ]);

            return;
        }

        // Save Driver

        $result =
            Driver::register(
                $_POST,
                $_FILES
            );

        if (!$result) {

            http_response_code(500);

            echo json_encode([
                "error" => "Driver registration failed"
            ]);

            return;
        }

        http_response_code(201);

        echo json_encode([
            "success" => true,
            "message" =>
            "Driver registration submitted successfully. Waiting for admin approval."
        ]);
    }

    //DRIVER LOGIN

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            http_response_code(405);

            echo json_encode([
                "error" => "Method not allowed"
            ]);

            return;
        }

        $body =
            file_get_contents(
                'php://input'
            );

        $data =
            json_decode(
                $body,
                true
            );

        if (!$data) {

            http_response_code(400);

            echo json_encode([
                "error" => "Invalid request"
            ]);

            return;
        }

        $phone =
            trim(
                $data['phone'] ?? ''
            );

        $password =
            trim(
                $data['password'] ?? ''
            );

        if (
            empty($phone) ||
            empty($password)
        ) {

            http_response_code(400);

            echo json_encode([
                "error" =>
                "Phone number and password are required"
            ]);

            return;
        }

        $driver =
            Driver::login(
                $phone,
                $password
            );

        if (!$driver) {

            http_response_code(401);

            echo json_encode([
                "error" =>
                "Invalid phone number or password"
            ]);

            return;
        }

        // Driver pending approval


        if ($driver['status'] === 'pending') {

            http_response_code(403);

            echo json_encode([
                "error" =>
                "Your account is still under review",
                "status" =>
                $driver['status']
            ]);

            return;
        }

        echo json_encode([
            "success" => true,
            "message" =>
            "Driver login successful",
            "driver" => $driver
        ]);
    }

    //ADMIN APPROVE DRIVER

    public function approveDriver(int $driverId)
    {
        $result =
            Driver::updateStatus(
                $driverId,
                'approved'
            );

        echo json_encode([
            "success" => $result
        ]);
    }


    //ADMIN REJECT DRIVER

    public function rejectDriver(int $driverId)
    {
        $result =
            Driver::updateStatus(
                $driverId,
                'rejected'
            );

        echo json_encode([
            "success" => $result
        ]);
    }
}
