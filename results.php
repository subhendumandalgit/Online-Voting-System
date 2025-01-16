<?php
session_start();

// Check if the admin is logged in
include('db.php'); // Include the database connection

// Query to fetch all events
$events_query = "SELECT event_id, event_name FROM events";
$events_result = mysqli_query($election_conn, $events_query);

// Check if the query was successful
if (!$events_result) {
    die("Error fetching events: " . mysqli_error($election_conn));
}

// Initialize the event data
$event_data = [];

// Fetch results for each event
while ($row = mysqli_fetch_assoc($events_result)) {
    $event_id = $row['event_id'];
    $event_name = $row['event_name'];

    // Query to get candidates and votes for each event
    $event_results_query = "
        SELECT candidates.candidate_name,  
               COUNT(votes.vote_id) AS vote_count
        FROM votes
        LEFT JOIN candidates ON votes.candidate_id = candidates.candidate_id
        WHERE votes.event_id = $event_id
        GROUP BY candidates.candidate_id
        ORDER BY vote_count DESC
    ";

    $event_results = mysqli_query($election_conn, $event_results_query);

    // Get the total number of votes for the event
    $total_votes_query = "
        SELECT COUNT(vote_id) AS total_votes 
        FROM votes 
        WHERE event_id = $event_id
    ";
    $total_votes_result = mysqli_query($election_conn, $total_votes_query);
    $total_votes_row = mysqli_fetch_assoc($total_votes_result);
    $total_votes = $total_votes_row['total_votes'];

    // Debugging: Check if total votes are fetched correctly
    // echo "Total votes for event $event_name: $total_votes<br>";

    $candidates_data = [];
    while ($candidate_row = mysqli_fetch_assoc($event_results)) {
        // Calculate the percentage of votes for each candidate
        $vote_percentage = $total_votes > 0 ? ($candidate_row['vote_count'] / $total_votes) * 100 : 0;

        $candidates_data[] = [
            'candidate_name' => $candidate_row['candidate_name'],
            'vote_count' => $candidate_row['vote_count'],
            'vote_percentage' => number_format($vote_percentage, 2) // Format percentage to 2 decimal places
        ];
    }

    // Store data for this event
    $event_data[$event_name] = [
        'total_votes' => $total_votes,
        'candidates' => $candidates_data
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Results</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color:cyan;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            text-align: center;
        }

        h2 {
            border: 5px solid #8FBC8F;
            padding: 0px 20px;
            background-color:#8FBC8F;
            font-size: 2.5em;
            color: #fff;
            text-transform: uppercase;
            margin-bottom: 40px;
            font-weight: bold;
        }

        h2:hover {
            color: black;
        }

        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 40px;
        }

        .card {
            background-size: cover;
            background-position: center;
            border-radius: 12px;
            box-shadow: 4px 20px rgba(0, 0, 0, 0.2);
            padding: 20px;
            width: 3000px;
            color: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
            position: relative;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25);
        }

        .card h3 {
            font-size: 1.8em;
            margin-bottom: 15px;
            color: #fff;
            font-weight: bold;
        }

        .card table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .card th, .card td {
            padding: 12px;
            text-align: left;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: 1px solid #ddd;
        }

        .card th {
            background-color: rgba(0, 0, 0, 0.7);
            color: #f1f1f1;
        }

        .card-footer {
            margin-top: 20px;
            font-weight: bold;
            font-size: 1.2em;
        }

        .no-results {
            text-align: center;
            color: #fff;
            font-size: 1.4em;
            background-color: rgba(255, 0, 0, 0.7);
            padding: 15px;
            border-radius: 10px;
        }

        .card-voted {
            background-color: rgba(0, 128, 0, 0.7); /* Green for events with votes */
        }

        .card-no-votes {
            background-color: rgba(255, 0, 0, 0.7); /* Red for events with no votes */
        }

        .back-btn {
            margin-top: 20px;
            padding: 12px 20px;
            font-size: 1.2em;
            background-color: #6495ED;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Voting Results</h2>

        <!-- Display Event Results in Cards -->
        <div class="cards-container">
            <?php if (!empty($event_data)): ?>
                <?php foreach ($event_data as $event_name => $event_info): ?>
                    <div class="card <?php echo empty($event_info['candidates']) ? 'card-no-votes' : 'card-voted'; ?>">
                        <h3>Event: <?php echo $event_name; ?></h3>
                        <?php if (empty($event_info['candidates'])): ?>
                            <div class="no-results">
                                <p>Vote not done yet</p>
                            </div>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Candidate Name</th>
                                        <th>Vote Count</th>
                                        <th>Vote Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $maxVotes = 0;
                                    $winners = [];
                                    foreach ($event_info['candidates'] as $candidate) {
                                        if ($candidate['vote_count'] > $maxVotes) {
                                            $maxVotes = $candidate['vote_count'];
                                            $winners = [$candidate['candidate_name']];
                                        } elseif ($candidate['vote_count'] == $maxVotes) {
                                            $winners[] = $candidate['candidate_name'];
                                        }

                                        echo "<tr>
                                                <td>{$candidate['candidate_name']}</td>
                                                <td>{$candidate['vote_count']}</td>
                                                <td>{$candidate['vote_percentage']}%</td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <div class="card-footer">
                                <?php
                                    if (count($winners) > 1) {
                                        echo "Draw between: " . implode(", ", $winners);
                                    } else {
                                        echo "Winner: " . $winners[0];
                                    }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>No voting data available.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Button to go back to login page -->
        <a href="index.html" class="back-btn">Back to Home</a>
    </div>
</body>
</html>
