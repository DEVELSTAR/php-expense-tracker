<?php
$conn = new mysqli(
  "localhost",
  "akibwork_expuser",
  "Saniya1@123",
  "akibwork_expense"
);

if ($conn->connect_error) {
  die("FAILED: " . $conn->connect_error);
}

echo "CONNECTED SUCCESSFULLY";
