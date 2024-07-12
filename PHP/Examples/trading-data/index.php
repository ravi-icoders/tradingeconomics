<?php
$apiKey = 'd2e9c1e570ce4aa:2pphxj8pmxn8ln0';
function makeApiRequest($url) {
    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close cURL
    curl_close($ch);

    // Check for rate limit exceeded
    if ($httpCode == 429) {
        // Wait for 1 second before retrying
        sleep(1);
        return makeApiRequest($url);
    }

    return $response;
}
 
$countryUrl = 'https://api.tradingeconomics.com/country?c='.$apiKey;
$indicatoresUrl = 'https://api.tradingeconomics.com/indicators?c='.$apiKey;
$countries = makeApiRequest($countryUrl);
$indicators = makeApiRequest($indicatoresUrl);
$countriesData = json_decode($countries, true);
$indicatorsData = json_decode($indicators, true);
$allowCountry = ['mexico','sweden'];
$allowIndicators = ['wages','banks balance sheet', 'business confidence','capital flows'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://parsleyjs.org/src/parsley.css">
    <title>Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        select, input[type="date"], button {
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        canvas {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .table-no-data {
            text-align: center !important;
        }
        .historical-data {
            display: none;
        }
        .historical-chart {
            display: none;
        }
        .chart-labels {
            margin-top: 20px;
            font-size: 16px;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            line-height: 1.6;
        }
        .chart-labels strong {
            color: #333;
        }
        .validation {
            display: inline-block;
            margin-left: 10px;
            color: red;
        }
        #data-form th {
            width: 30%;
        }
        #api-key {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Data of Country and Indicator Wise</h2>
        <form method="POST" id="data-form" data-parsley-validate>
            <table>
                <tr>
                    <th>Country</th>
                    <td>
                        <select name="country" id="country" required data-parsley-country data-parsley-required-message="Please Select Country" data-parsley-errors-container=".validation.country">
                            <option value="">Select Country</option>
                            <?php
                                foreach($countriesData as $country){
                                    $countrylower = strtolower($country['Country']);
                                    if(in_array($countrylower, $allowCountry)){
                            ?>
                            <option value="<?php echo $countrylower; ?>"><?php echo $country['Country']; ?></option>
                            <?php
                                    }
                                }
                            ?>
                        </select>
                        <span class="validation country"></span>
                    </td>
                </tr>
                <tr>
                    <th>Indicator</th>
                    <td>
                        <select name="indicator" id="indicator" required data-parsley-indicator data-parsley-required-message="Please Select Indicator" data-parsley-errors-container=".validation.indicator">
                            <option value="">Pick Indicator</option>
                            <?php
                                foreach($indicatorsData as $indicator){
                                    $indicatorlower = strtolower($indicator['Category']);
                                    if(in_array($indicatorlower, $allowIndicators)){
                            ?>
                            <option value="<?php echo $indicatorlower; ?>"><?php echo $indicator['Category']; ?></option>
                            <?php
                                    }
                                }
                            ?>
                        </select>
                        <span class="validation indicator"></span>
                    </td>
                </tr>
                <tr>
                    <th>From Date</th>
                    <td>
                        <input type="date" name="from_date" id="from_date" required data-parsley-from_date data-parsley-required-message="Please Select From Date" data-parsley-errors-container=".validation.from-date">
                        <span class="validation from-date"></span>
                    </td>
                </tr>
                <tr>
                    <th>To Date</th>
                    <td>
                        <input type="date" name="to_date" id="to_date" required data-parsley-to_date data-parsley-required-message="Please Select To Date" data-parsley-errors-container=".validation.to-date">
                        <span class="validation to-date"></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="right"><button type="submit" name="submit">Submit</button></td>
                </tr>
            </table>
        </form>

        <div class="historical-chart">
            <h2>Chart</h2>
            <div class="chart-labels" id="chartLabels"></div>
            <canvas id="myChart"></canvas>
        </div>

        <table class="historical-data">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Category</th>
                    <th>Date Time</th>
                    <th>Close</th>
                    <th>Frequency</th>
                    <th>Historical Data Symbol</th>
                    <th>LastUpdate</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
        </table>
    </div>

    <!-- Embed the API key as a data attribute -->
    <div id="api-key" data-api-key="<?php echo $apiKey; ?>"></div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <script>
        $('#data-form').parsley();

        document.addEventListener('DOMContentLoaded', (event) => {
            // Set the from_date input to "2015-01-01"
            const fromDateInput = document.getElementById('from_date');
            fromDateInput.value = '2015-01-01';

            // Get today's date
            const today = new Date();

            // Format today's date as YYYY-MM-DD for setting the to_date input
            const toDateString = today.toISOString().substring(0, 10);

            // Set the to_date input to today's date
            const toDateInput = document.getElementById('to_date');
            toDateInput.value = toDateString;
        });

        document.addEventListener('DOMContentLoaded', (e) => {
            const submitButton = document.querySelector('button[name="submit"]');
            submitButton.addEventListener('click', (event) => {
                event.preventDefault();
                const form = $('#data-form');
                if (form.parsley().validate()) {
                    submitData();
                }
            });
        });

        function formatDateTime(dateTimeString) {
            const date = new Date(dateTimeString);
            const year = date.getFullYear();
            const month = ('0' + (date.getMonth() + 1)).slice(-2); // Months are zero-based
            const day = ('0' + date.getDate()).slice(-2);
            let hours = date.getHours();
            const minutes = ('0' + date.getMinutes()).slice(-2);
            const seconds = ('0' + date.getSeconds()).slice(-2);
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds} ${ampm}`;
        }

        async function submitData() {
            const country = document.getElementById('country').value;
            const indicator = document.getElementById('indicator').value;
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;

            if (country === '0' || indicator === '0' || !fromDate || !toDate) {
                alert('Please fill all fields.');
                return;
            }

            const apiKey = document.getElementById('api-key').getAttribute('data-api-key');
            const url = `https://api.tradingeconomics.com/historical/country/${country}/indicator/${indicator}/${fromDate}/${toDate}?c=${apiKey}`;

            try {
                const response = await fetch(url);
                const result = await response.json();

                const filteredResults = result.filter(item => item.Frequency !== null);
                // const labels = filteredResults.map(item => item.DateTime);
                const labels = filteredResults.map(item => formatDateTime(item.DateTime));
                const data = filteredResults.map(item => item.Value);

                const ctx = document.getElementById('myChart').getContext('2d');

                // Check if window.myChart is already defined
                if (window.myChart) {
                    // Check if it's a Chart instance
                    if (window.myChart instanceof Chart) {
                        // Destroy the existing chart instance
                        window.myChart.destroy();
                    }
                }

                // Create new Chart instance
                window.myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: `${country.toUpperCase()} ${indicator.toUpperCase()}`,
                            data: data,
                            backgroundColor: 'rgba(153, 255, 51, 0.6)',
                            borderColor: 'rgba(153, 255, 51, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'month'
                                },
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Value'
                                }
                            }
                        }
                    }
                });

                // Clear table rows and populate with new data
                const tbody = document.querySelector('.historical-data tbody');
                tbody.innerHTML = '';
                filteredResults.forEach(item => {
                    const row = `<tr>
                        <td>${item.Country}</td>
                        <td>${item.Category}</td>
                        <td>${formatDateTime(item.DateTime)}</td>
                        <td>${item.Value}</td>
                        <td>${item.Frequency}</td>
                        <td>${item.HistoricalDataSymbol}</td>
                        <td>${formatDateTime(item.LastUpdate)}</td>
                    </tr>`;
                    tbody.innerHTML += row;
                });

                if(filteredResults.length <= 0){
                    const row = `<tr>
                        <td colspan="7" class="table-no-data">No Data found</td>
                    </tr>`;
                    tbody.innerHTML += row;
                }

                // Display the selected data above the chart
                document.getElementById('chartLabels').innerHTML = `
                    <strong>Country:</strong> ${country.toUpperCase()} <br>
                    <strong>Indicator:</strong> ${indicator.toUpperCase()} <br>
                    <strong>From Date:</strong> ${fromDate} <br>
                    <strong>To Date:</strong> ${toDate}
                `;

                $('.historical-chart').css('display','block');
                $('.historical-data').css('display','inline-table');
            } catch (error) {
                console.error('Error:', error);
            }
            
        }
    </script>
</body>
</html>
