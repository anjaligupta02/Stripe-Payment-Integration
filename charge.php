<?php
require_once('vendor/autoload.php');

\Stripe\Stripe::setApiKey('sk_test_51Pb0aBH0ap9f5VKpJc12GCZXid1ypqKI2zBzqsenBIp802SMstAOiO6AGa6v7imJEnqkcR70XUJKDxZjvlwJntVf007Dlh04Rp'); // Replace with your actual Secret key

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['stripeToken'];

  try {
    $charge = \Stripe\Charge::create([
      'amount' => 1000, 
      'currency' => 'usd',
      'description' => 'Example charge',
      'source' => $token,
    ]);

    echo '<h1>Payment successful</h1>';

    $userId = 1; 
    $amount = 10.00; 
    $paymentStatus = 'succeeded';

    $conn = new mysqli('localhost', 'root', 'admin', 'stripe_payments');
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO payments (user_id, amount, payment_status) VALUES ('$userId', '$amount', '$paymentStatus')";

    if ($conn->query($sql) === TRUE) {
      echo "New record inserted successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
  } catch(\Stripe\Exception\CardException $e) {
    echo '<h1>Payment failed</h1>';
    echo '<p>' . $e->getError()->message . '</p>';
  }
} else {
  echo '<h1>Invalid Request</h1>';
}
