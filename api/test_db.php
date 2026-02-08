<?php
$conn = new mysqli(
  "localhost",
  "akibwork_expuser",
  "Saniya1@asd",
  "akibwork_expense"
);

if ($conn->connect_error) {
  die("FAILED: " . $conn->connect_error);
}

echo "CONNECTED SUCCESSFULLY";
