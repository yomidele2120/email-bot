<?php
// worker/run_sequences.php
// Run this on a daily schedule via a separate Railway cron service.
// Each run checks for sequences that are due, fires the current step as a
// fresh campaign, advances to the next step, and reschedules for later.

require __DIR__ . '/../vendor/autoload.php';

use App\Sequence;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$count = Sequence::runDueSequences();
echo "Processed $count due sequence(s).\n";
