<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = "app_" . uniqid();
    $user_id = $_SESSION['user_id'];

    $application_data = [
        "app_id"      => $app_id,
        "user_id"     => $user_id,
        "status"      => "pending",
        "timestamp"   => time(),
        "date_human"  => date('Y-m-d H:i'),
        "biodata" => [
            "full_name"   => strip_tags($_POST['full_name']),
            "father_name" => strip_tags($_POST['father_name']),
            "email"       => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            "phone"       => strip_tags($_POST['phone']),
            "dob"         => $_POST['dob'],
            "address"     => strip_tags($_POST['address'])
        ],
        "portfolio" => [
            "sample"    => strip_tags($_POST['sample_text']),
            "bookmarks" => array_filter($_POST['bookmarks']) // Removes empty links
        ]
    ];

    // 1. Create directory if not exists
    if (!is_dir(DATA_PATH . "applications")) {
        mkdir(DATA_PATH . "applications", 0777, true);
    }

    // 2. Save Application File
    DBEngine::writeJSON("applications/$app_id.json", $application_data);

    // 3. Update User Status to 'pending'
    $user_vault = DBEngine::readJSON("user_data/$user_id.json");
    $user_vault['writer_status'] = 'pending';
    DBEngine::writeJSON("user_data/$user_id.json", $user_vault);

    echo "<script>alert('Application submitted successfully!'); window.location.href='../../index.php';</script>";
}