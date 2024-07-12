<?php
require_once('vendor/autoload.php'); 

\Stripe\Stripe::setApiKey('sk_test_51Pb0aBH0ap9f5VKpJc12GCZXid1ypqKI2zBzqsenBIp802SMstAOiO6AGa6v7imJEnqkcR70XUJKDxZjvlwJntVf007Dlh04Rp'); // Replace with your actual Secret key

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

$endpoint_secret = 'whsec_2992ede3c602f0d110f9704d6aa16c2153c62d3d2589a72c7e5c245e13dbd1f6'; // Replace with your actual signing secret

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  http_response_code(400);
  exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
  http_response_code(400);
  exit();
}

if ($event->type == 'payment_intent.succeeded') {
  $paymentIntent = $event->data->object;

  $userId = 1; 
  $amount = $paymentIntent->amount / 100;
  $paymentStatus = 'succeeded';
  
  $servername = "localhost";
  $username = "root";
  $password = "admin";
  $dbname = "stripe_payments";
  
  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $sql = "INSERT INTO payments (user_id, amount, payment_status) VALUES (?, ?, ?)";
  
  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit();
  }
  
  $stmt->bind_param("ids", $userId, $amount, $paymentStatus);
  
  if ($stmt->execute()) {
    echo "New record inserted successfully";
  } else {
    echo "Error: " . $stmt->error;
  }
  
  $stmt->close();
  $conn->close();
}

http_response_code(200);

