<?php
$conn = new mysqli("localhost","akibwork_expuser","PASSWORD","akibwork_expense");

if ($conn->connect_error) {
  die("FAILED");
}
echo "CONNECTED";
